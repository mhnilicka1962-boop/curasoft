<?php

namespace App\Http\Controllers;

use App\Mail\RechnungMail;
use App\Models\AuditLog;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\Leistungsregion;
use App\Models\Organisation;
use App\Models\Region;
use App\Models\Rechnung;
use App\Models\RechnungsPosition;
use App\Models\Rechnungslauf;
use App\Services\PdfExportService;
use App\Services\XmlExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use ZipStream\ZipStream;

class RechnungslaufController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    public function index()
    {
        $laeufe = Rechnungslauf::where('organisation_id', $this->orgId())
            ->with('ersteller')
            ->withSum('rechnungen', 'betrag_total')
            ->orderByDesc('id')
            ->paginate(20);

        return view('rechnungen.lauf.index', compact('laeufe'));
    }

    public function create(Request $request)
    {
        $vorschau = null;

        if ($request->filled('periode_von') && $request->filled('periode_bis')) {
            $vorschau = $this->vorschauBerechnen(
                $request->periode_von,
                $request->periode_bis,
            );
        }

        return view('rechnungen.lauf.create', compact('vorschau'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'periode_von' => ['required', 'date', 'before_or_equal:today'],
            'periode_bis' => ['required', 'date', 'after_or_equal:periode_von', 'before_or_equal:today'],
            'klienten'    => ['required', 'array', 'min:1'],
            'klienten.*'  => ['integer'],
        ]);

        $selectedIds = array_map('intval', $request->input('klienten', []));

        // Prüfen ob überhaupt verrechenbare Einsätze vorhanden — verhindert leere Läufe
        $hatEinsaetze = Einsatz::whereIn('klient_id', $selectedIds)
            ->where('verrechnet', false)
            ->where(function ($q) {
                $q->whereNotNull('checkout_zeit')
                  ->orWhereNotNull('tagespauschale_id');
            })
            ->whereBetween('datum', [$request->periode_von, $request->periode_bis])
            ->exists();

        if (!$hatEinsaetze) {
            return back()->with('fehler',
                'Keine verrechenbaren Einsätze gefunden — Einsätze möglicherweise bereits in einem früheren Lauf verrechnet.');
        }

        $lauf = Rechnungslauf::create([
            'organisation_id'     => $this->orgId(),
            'periode_von'         => $request->periode_von,
            'periode_bis'         => $request->periode_bis,
            'anzahl_erstellt'     => 0,
            'anzahl_uebersprungen'=> 0,
            'status'              => 'abgeschlossen',
            'erstellt_von'        => auth()->id(),
        ]);

        $klienten = Klient::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->whereIn('id', $selectedIds)
            ->get();

        $erstellt     = 0;
        $uebersprungen= 0;

        // Tarif-Cache damit gleiche Leistungsart+Region nicht mehrfach abgefragt wird
        $tarifCache = [];

        foreach ($klienten as $klient) {
            $einsaetze = Einsatz::where('klient_id', $klient->id)
                ->where('verrechnet', false)
                ->where(function ($q) {
                    $q->whereNotNull('checkout_zeit')
                      ->orWhereNotNull('tagespauschale_id');
                })
                ->whereBetween('datum', [$request->periode_von, $request->periode_bis])
                ->with('leistungsart', 'tagespauschale')
                ->orderBy('datum')
                ->get();

            if ($einsaetze->isEmpty()) {
                $uebersprungen++;
                continue;
            }

            $rechnungstyp = $klient->rechnungstyp ?? 'kombiniert';

            $rechnung = Rechnung::create([
                'organisation_id' => $this->orgId(),
                'klient_id'       => $klient->id,
                'rechnungsnummer' => Rechnung::naechsteNummer($this->orgId()),
                'periode_von'     => $request->periode_von,
                'periode_bis'     => $request->periode_bis,
                'rechnungsdatum'  => today(),
                'status'          => 'entwurf',
                'rechnungstyp'    => $rechnungstyp,
                'rechnungslauf_id'=> $lauf->id,
            ]);

            foreach ($einsaetze as $einsatz) {
                if ($einsatz->tagespauschale_id) {
                    // Tagespauschale: Tarif direkt vom Pauschale-Record
                    $tp        = $einsatz->tagespauschale;
                    $ansatz    = (float) $tp->ansatz;
                    [$tarifPat, $tarifKk] = match($tp->rechnungstyp) {
                        'kvg'  => [0.0, $ansatz],
                        default=> [$ansatz, 0.0],
                    };
                    $menge       = 1;
                    $einheit     = 'tage';
                    $betragPat   = round($tarifPat, 2);
                    $betragKk    = round($tarifKk, 2);
                    $beschreibung= $tp->text;
                } else {
                    $istTage = $einsatz->leistungsart?->einheit === 'tage';
                    [$tarifPat, $tarifKk] = $this->tarifeFuerEinsatz(
                        $einsatz, $klient, $rechnungstyp, $tarifCache
                    );
                    if ($istTage) {
                        $menge     = $einsatz->anzahlTage() ?? 1;
                        $einheit   = 'tage';
                        $betragPat = round($menge * $tarifPat, 2);
                        $betragKk  = round($menge * $tarifKk, 2);
                    } else {
                        $menge     = $einsatz->minuten ?? 0;
                        $einheit   = 'minuten';
                        $betragPat = round($menge / 60 * $tarifPat, 2);
                        $betragKk  = round($menge / 60 * $tarifKk, 2);
                    }
                    $beschreibung = null;
                }

                RechnungsPosition::create([
                    'rechnung_id'    => $rechnung->id,
                    'einsatz_id'     => $einsatz->id,
                    'datum'          => $einsatz->datum,
                    'menge'          => $menge,
                    'einheit'        => $einheit,
                    'beschreibung'   => $beschreibung,
                    'tarif_patient'  => $tarifPat,
                    'tarif_kk'       => $tarifKk,
                    'betrag_patient' => $betragPat,
                    'betrag_kk'      => $betragKk,
                ]);

                $einsatz->update(['verrechnet' => true]);
            }

            $rechnung->load('positionen');
            $rechnung->berechneTotale();

            AuditLog::schreiben('erstellt', 'Rechnung', $rechnung->id,
                "Rechnungslauf {$lauf->id}: Rechnung {$rechnung->rechnungsnummer} für {$klient->nachname}");

            $erstellt++;
        }

        $lauf->update([
            'anzahl_erstellt'     => $erstellt,
            'anzahl_uebersprungen'=> $uebersprungen,
        ]);

        return redirect()->route('rechnungslauf.show', $lauf)
            ->with('erfolg', "{$erstellt} Rechnungen erstellt, {$uebersprungen} Klienten ohne Einsätze übersprungen.");
    }

    public function show(Rechnungslauf $lauf)
    {
        $this->autorisiereZugriff($lauf);
        $lauf->load(['rechnungen.klient', 'ersteller']);

        $emailAnzahl = $lauf->rechnungen->filter(
            fn($r) => $r->klient->versandart_patient === 'email' && $r->klient->email
        )->count();

        $postAnzahl = $lauf->rechnungen->filter(
            fn($r) => $r->klient->versandart_patient !== 'email'
        )->count();

        $kvgAnzahl = $lauf->rechnungen->whereIn('rechnungstyp', ['kvg', 'kombiniert'])->count();

        return view('rechnungen.lauf.show', compact('lauf', 'emailAnzahl', 'postAnzahl', 'kvgAnzahl'));
    }

    public function emailVersand(Rechnungslauf $lauf)
    {
        $this->autorisiereZugriff($lauf);

        $org = Organisation::findOrFail($this->orgId());
        $pdfService = new PdfExportService($org);

        // Alle Email-Rechnungen laden — auch bereits fehlgeschlagene (Retry möglich)
        $rechnungen = $lauf->rechnungen()
            ->with('klient')
            ->get()
            ->filter(fn($r) => $r->klient->versandart_patient === 'email'
                && $r->klient->email
                && $r->status !== 'gesendet');  // bereits erfolgreich gesendete überspringen

        $versendet = 0;
        $fehler    = 0;

        foreach ($rechnungen as $rechnung) {
            $emailAdresse = $rechnung->klient->email;
            try {
                if (!$rechnung->pdf_pfad || !Storage::exists($rechnung->pdf_pfad)) {
                    $pdfService->rechnungExportieren($rechnung);
                    $rechnung->refresh();
                }

                Mail::to($emailAdresse)->send(new RechnungMail($rechnung, $org));

                $rechnung->update([
                    'status'              => 'gesendet',
                    'email_versand_datum' => now(),
                    'email_versand_an'    => $emailAdresse,
                    'email_fehler'        => null,   // vorherigen Fehler löschen
                ]);
                $versendet++;

            } catch (\Exception $e) {
                // Fehlermeldung einfrieren — max. 500 Zeichen
                $rechnung->update([
                    'email_fehler' => mb_substr($e->getMessage(), 0, 500),
                ]);
                $fehler++;
            }
        }

        $msg = "{$versendet} Email(s) versendet.";
        if ($fehler > 0) $msg .= " {$fehler} fehlgeschlagen — Details in der Tabelle.";

        return back()->with($fehler > 0 && $versendet === 0 ? 'fehler' : 'erfolg', $msg);
    }

    public function pdfZip(Rechnungslauf $lauf)
    {
        $this->autorisiereZugriff($lauf);

        $org = Organisation::findOrFail($this->orgId());
        $pdfService = new PdfExportService($org);

        $rechnungen = $lauf->rechnungen()->with('klient')->get();
        $dateiname  = "rechnungslauf_{$lauf->periode_von->format('Y-m')}.zip";

        return response()->stream(function () use ($rechnungen, $pdfService) {
            $zip = new ZipStream(sendHttpHeaders: false);
            foreach ($rechnungen as $rechnung) {
                try {
                    if (!$rechnung->pdf_pfad || !Storage::exists($rechnung->pdf_pfad)) {
                        $pdfService->rechnungExportieren($rechnung);
                        $rechnung->refresh();
                    }
                    if ($rechnung->pdf_pfad && Storage::exists($rechnung->pdf_pfad)) {
                        $zip->addFileFromPath(
                            fileName: $rechnung->rechnungsnummer . '.pdf',
                            path: Storage::path($rechnung->pdf_pfad),
                        );
                    }
                } catch (\Exception $e) {
                    // Einzelfehler überspringen
                }
            }
            $zip->finish();
        }, 200, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{$dateiname}\"",
        ]);
    }

    public function xmlZip(Rechnungslauf $lauf)
    {
        $this->autorisiereZugriff($lauf);

        $org = Organisation::findOrFail($this->orgId());
        $xmlService = new XmlExportService($org);

        $rechnungen = $lauf->rechnungen()
            ->with(['klient.region', 'klient.krankenkassen.krankenkasse', 'positionen.leistungstyp.leistungsart'])
            ->whereIn('rechnungstyp', ['kvg', 'kombiniert'])
            ->get();

        $dateiname = "xml_lauf_{$lauf->periode_von->format('Y-m')}.zip";

        return response()->stream(function () use ($rechnungen, $xmlService) {
            $zip = new ZipStream(sendHttpHeaders: false);
            foreach ($rechnungen as $rechnung) {
                try {
                    $pfad = $xmlService->rechnungExportieren($rechnung);
                    $zip->addFileFromPath(
                        fileName: $rechnung->rechnungsnummer . '.xml',
                        path: Storage::path($pfad),
                    );
                } catch (\Exception $e) {
                    // Einzelfehler überspringen
                }
            }
            $zip->finish();
        }, 200, [
            'Content-Type'        => 'application/zip',
            'Content-Disposition' => "attachment; filename=\"{$dateiname}\"",
        ]);
    }

    // ----------------------------------------------------------------
    // Private Hilfsmethoden
    // ----------------------------------------------------------------

    /**
     * Tarife für einen Einsatz aus Leistungsregion ermitteln.
     * Verteilt je nach Rechnungstyp des Klienten:
     *   kombiniert → Patient zahlt Anteil (ansatz - kkasse), KK zahlt kkasse
     *   kvg        → KK zahlt alles (ansatz), Patient 0
     *   klient     → Patient zahlt alles (ansatz), KK 0
     *   gemeinde   → Gemeinde = Patient-Feld (ansatz), KK 0
     *
     * @return array{0: float, 1: float} [tarif_patient, tarif_kk]
     */
    private function tarifeFuerEinsatz(Einsatz $einsatz, Klient $klient, string $rechnungstyp, array &$cache): array
    {
        $regionId       = $einsatz->region_id ?? $klient->region_id;
        $leistungsartId = $einsatz->leistungsart_id;

        if (!$leistungsartId) {
            return [0, 0];
        }

        $datum    = $einsatz->datum ?? today();
        $cacheKey = "{$leistungsartId}-{$regionId}-{$datum}";

        if (!isset($cache[$cacheKey])) {
            // Aktuellsten Tarif per Einsatzdatum: gültig_ab <= datum, neuester zuerst
            $lr = Leistungsregion::where('leistungsart_id', $leistungsartId)
                ->where('region_id', $regionId)
                ->where(fn($q) => $q->whereNull('gueltig_ab')->orWhere('gueltig_ab', '<=', $datum))
                ->where(fn($q) => $q->whereNull('gueltig_bis')->orWhere('gueltig_bis', '>=', $datum))
                ->orderByDesc('gueltig_ab')
                ->first();

            // Fallback: Leistungsregion ohne Region-Einschränkung (globaler Tarif)
            if (!$lr && $regionId) {
                $lr = Leistungsregion::where('leistungsart_id', $leistungsartId)
                    ->whereNull('region_id')
                    ->where(fn($q) => $q->whereNull('gueltig_ab')->orWhere('gueltig_ab', '<=', $datum))
                    ->orderByDesc('gueltig_ab')
                    ->first();
            }

            $cache[$cacheKey] = $lr;
        }

        $lr = $cache[$cacheKey];

        if (!$lr) {
            return [0, 0];
        }

        $ansatz = (float) $lr->ansatz;
        $kkasse = (float) $lr->kkasse;

        return match($rechnungstyp) {
            'kvg'     => [0,                      $ansatz],          // KK zahlt vollen Ansatz
            'klient'  => [$ansatz,                0],                // Patient zahlt vollen Ansatz
            'gemeinde'=> [$ansatz,                0],                // Gemeinde im Patienten-Feld
            default   => [max(0, $ansatz - $kkasse), $kkasse],       // kombiniert: Anteil aufteilen
        };
    }

    private function vorschauBerechnen(string $von, string $bis): array
    {
        $klienten = Klient::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->orderBy('nachname')
            ->get();

        $zeilen       = [];
        $totalMinuten = 0;
        $totalBetrag  = 0;
        $anzahlMit    = 0;
        $anzahlOhne   = 0;
        $ohneLeistungsart = 0;
        $tarifCache   = [];

        foreach ($klienten as $klient) {
            $einsaetze = Einsatz::where('klient_id', $klient->id)
                ->where('verrechnet', false)
                ->where(function ($q) {
                    $q->whereNotNull('checkout_zeit')
                      ->orWhereNotNull('tagespauschale_id');
                })
                ->whereBetween('datum', [$von, $bis])
                ->with('leistungsart', 'tagespauschale')
                ->get();

            if ($einsaetze->isEmpty()) {
                $anzahlOhne++;
                $zeilen[] = [
                    'klient'        => $klient,
                    'rechnungstyp'  => $klient->rechnungstyp ?? 'kombiniert',
                    'anzahl'        => 0,
                    'minuten'       => 0,
                    'betrag_patient'=> 0,
                    'betrag_kk'     => 0,
                    'betrag'        => 0,
                    'versandart'    => $klient->versandart_patient ?? 'post',
                    'ohne_tarif'    => false,
                    'ohne_einsaetze'=> true,
                    'grund'         => $this->grundErmitteln($klient->id, $von, $bis),
                ];
                continue;
            }

            $rechnungstyp = $klient->rechnungstyp ?? 'kombiniert';
            $betragPat    = 0;
            $betragKk     = 0;
            $minuten      = 0;
            $ohneTypCount = 0;

            foreach ($einsaetze as $einsatz) {
                if ($einsatz->tagespauschale_id) {
                    $pauschale  = $einsatz->tagespauschale;
                    $ansatz     = (float) $pauschale->ansatz;
                    [$tp, $tk]  = match($pauschale->rechnungstyp) {
                        'kvg'  => [0.0, $ansatz],
                        default=> [$ansatz, 0.0],
                    };
                    $betragPat += $tp;
                    $betragKk  += $tk;
                } else {
                    $istTage = $einsatz->leistungsart?->einheit === 'tage';
                    [$tp, $tk] = $this->tarifeFuerEinsatz($einsatz, $klient, $rechnungstyp, $tarifCache);
                    if ($istTage) {
                        $tage       = $einsatz->anzahlTage() ?? 1;
                        $betragPat += round($tage * $tp, 2);
                        $betragKk  += round($tage * $tk, 2);
                    } else {
                        $m          = $einsatz->minuten ?? 0;
                        $betragPat += round($m / 60 * $tp, 2);
                        $betragKk  += round($m / 60 * $tk, 2);
                        $minuten   += $m;
                    }
                    if (!$einsatz->leistungsart_id) $ohneTypCount++;
                }
            }

            if ($ohneTypCount > 0) $ohneLeistungsart++;

            $betrag = $betragPat + $betragKk;

            $zeilen[] = [
                'klient'         => $klient,
                'rechnungstyp'   => $rechnungstyp,
                'anzahl'         => $einsaetze->count(),
                'minuten'        => $minuten,
                'betrag_patient' => $betragPat,
                'betrag_kk'      => $betragKk,
                'betrag'         => $betrag,
                'versandart'     => $klient->versandart_patient ?? 'post',
                'ohne_tarif'     => $ohneTypCount > 0,
                'ohne_einsaetze' => false,
            ];

            $totalMinuten += $minuten;
            $totalBetrag  += $betrag;
            $anzahlMit++;
        }

        // Regionen ohne konfigurierte Tarife ermitteln
        $regionen_ohne_tarife = [];
        $verwendeteRegionen = collect($zeilen)->pluck('klient.region_id')->filter()->unique();
        foreach ($verwendeteRegionen as $regionId) {
            if (!Leistungsregion::where('region_id', $regionId)->exists()) {
                $r = Region::find($regionId);
                if ($r) $regionen_ohne_tarife[] = $r->kuerzel . ' — ' . $r->bezeichnung;
            }
        }

        return [
            'zeilen'              => $zeilen,
            'total_minuten'       => $totalMinuten,
            'total_betrag'        => $totalBetrag,
            'anzahl_mit'          => $anzahlMit,
            'anzahl_ohne'         => $anzahlOhne,
            'ohne_leistungsart'   => $ohneLeistungsart,
            'regionen_ohne_tarife'=> $regionen_ohne_tarife,
        ];
    }

    public function sammelPdf(Rechnungslauf $lauf)
    {
        $this->autorisiereZugriff($lauf);

        $org        = Organisation::findOrFail($this->orgId());
        $pdfService = new PdfExportService($org);

        // Nur Post/Manuell-Rechnungen (Email wurden bereits versendet)
        $rechnungen = $lauf->rechnungen()
            ->with('klient')
            ->get()
            ->filter(fn($r) => in_array($r->klient->versandart_patient ?? 'post', ['post', 'manuell']));

        if ($rechnungen->isEmpty()) {
            return back()->with('fehler', 'Keine Post/Manuell-Rechnungen in diesem Lauf.');
        }

        // PDFs sicherstellen
        foreach ($rechnungen as $rechnung) {
            if (!$rechnung->pdf_pfad || !Storage::exists($rechnung->pdf_pfad)) {
                $pdfService->rechnungExportieren($rechnung);
                $rechnung->refresh();
            }
        }

        // Alle PDFs mit FPDI zu einem Dokument zusammenführen
        $fpdi = new \setasign\Fpdi\Fpdi();
        $fpdi->SetAutoPageBreak(false);

        foreach ($rechnungen as $rechnung) {
            if (!$rechnung->pdf_pfad || !Storage::exists($rechnung->pdf_pfad)) continue;
            try {
                $pfad      = Storage::path($rechnung->pdf_pfad);
                $seitenAnz = $fpdi->setSourceFile($pfad);
                for ($s = 1; $s <= $seitenAnz; $s++) {
                    $tpl  = $fpdi->importPage($s);
                    $size = $fpdi->getTemplateSize($tpl);
                    $fpdi->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tpl);
                }
            } catch (\Exception $e) {
                // Einzelne fehlerhafte PDFs überspringen
            }
        }

        $dateiname = "sammelrechnung_{$lauf->periode_von->format('Y-m')}.pdf";
        $inhalt    = $fpdi->Output('S'); // 'S' = als String zurückgeben

        return response($inhalt, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$dateiname}\"",
        ]);
    }

    public function destroy(Rechnungslauf $lauf)
    {
        $this->autorisiereZugriff($lauf);

        // Bereits versendete oder bezahlte Rechnungen blockieren die Stornierung
        $blockiert = $lauf->rechnungen()->whereIn('status', ['gesendet', 'bezahlt'])->count();
        if ($blockiert > 0) {
            return back()->with('fehler',
                "{$blockiert} Rechnung(en) bereits versendet/bezahlt — Lauf kann nicht storniert werden.");
        }

        // Einsätze zurücksetzen: verrechnet → false
        $einsatzIds = \App\Models\RechnungsPosition::whereIn('rechnung_id', $lauf->rechnungen()->pluck('id'))
            ->whereNotNull('einsatz_id')
            ->pluck('einsatz_id')
            ->unique();

        Einsatz::whereIn('id', $einsatzIds)->update(['verrechnet' => false]);

        // Rechnungen (+ Positionen via cascade) löschen
        $anzahl = $lauf->rechnungen()->count();
        $lauf->rechnungen()->each(fn($r) => $r->delete());

        AuditLog::schreiben('geloescht', 'Rechnungslauf', $lauf->id,
            "Lauf #{$lauf->id} storniert: {$anzahl} Rechnungen gelöscht, {$einsatzIds->count()} Einsätze zurückgesetzt");

        $lauf->delete();

        return redirect()->route('rechnungslauf.index')
            ->with('erfolg', "Lauf #{$lauf->id} storniert — {$anzahl} Rechnungen gelöscht, {$einsatzIds->count()} Einsätze wieder verrechenbar.");
    }

    private function grundErmitteln(int $klientId, string $von, string $bis): string
    {
        $basis = Einsatz::where('klient_id', $klientId)->whereBetween('datum', [$von, $bis]);

        $total = (clone $basis)->count();
        if ($total === 0) {
            return 'Keine Einsätze in dieser Periode';
        }

        $bereitsVerrechnet = (clone $basis)->where('verrechnet', true)->count();
        if ($bereitsVerrechnet === $total) {
            return "Alle {$bereitsVerrechnet} Einsätze bereits verrechnet (anderer Lauf?)";
        }
        if ($bereitsVerrechnet > 0) {
            return "{$bereitsVerrechnet} von {$total} Einsätzen bereits verrechnet";
        }

        $ohneCheckout = (clone $basis)
            ->whereNull('checkout_zeit')
            ->whereNull('tagespauschale_id')
            ->count();
        if ($ohneCheckout > 0) {
            $wort = $ohneCheckout === 1 ? 'Einsatz' : 'Einsätze';
            return "{$ohneCheckout} {$wort} ohne Checkout — noch nicht abgeschlossen";
        }

        $nichtAbgeschlossen = (clone $basis)->where('status', '!=', 'abgeschlossen')->count();
        if ($nichtAbgeschlossen > 0) {
            return "{$nichtAbgeschlossen} Einsätze nicht im Status «abgeschlossen»";
        }

        return 'Keine verrechenbaren Einsätze gefunden';
    }

    private function autorisiereZugriff(Rechnungslauf $lauf): void
    {
        if ($lauf->organisation_id !== $this->orgId()) abort(403);
    }
}
