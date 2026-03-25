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
use App\Services\BexioService;
use App\Services\PdfExportService;
use App\Services\XmlExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use ZipStream\ZipStream;
use ZipStream\Option\Archive as ZipArchiveOptions;

class RechnungslaufController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    public function index(Request $request)
    {
        $jahr  = $request->input('jahr');
        $monat = $request->input('monat');

        $laeufe = Rechnungslauf::where('organisation_id', $this->orgId())
            ->with('ersteller')
            ->withSum('rechnungen', 'betrag_total')
            ->when($jahr,  fn($q) => $q->whereYear('periode_von', $jahr))
            ->when($monat, fn($q) => $q->whereMonth('periode_von', $monat))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Verfügbare Jahre für Dropdown
        $jahre = Rechnungslauf::where('organisation_id', $this->orgId())
            ->selectRaw('EXTRACT(YEAR FROM periode_von)::int AS jahr')
            ->groupBy('jahr')
            ->orderByDesc('jahr')
            ->pluck('jahr');

        return view('rechnungen.lauf.index', compact('laeufe', 'jahre', 'jahr', 'monat'));
    }

    public function vorschauPdf(Request $request)
    {
        $request->validate([
            'klient_id'   => ['required', 'integer'],
            'periode_von' => ['required', 'date'],
            'periode_bis' => ['required', 'date'],
            'pauschale'   => ['nullable', 'boolean'],
        ]);

        $klient = Klient::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->findOrFail($request->klient_id);

        $nurPauschale = (bool) $request->input('pauschale', false);

        $einsaetze = Einsatz::where('klient_id', $klient->id)
            ->where('verrechnet', false)
            ->where(function ($q) {
                $q->whereNotNull('checkout_zeit')->orWhereNotNull('tagespauschale_id');
            })
            ->whereBetween('datum', [$request->periode_von, $request->periode_bis])
            ->with('leistungsart', 'tagespauschale')
            ->orderBy('datum')
            ->get();

        $einsaetze = $nurPauschale
            ? $einsaetze->filter(fn($e) => $e->tagespauschale_id)
            : $einsaetze->filter(fn($e) => !$e->tagespauschale_id);

        if ($einsaetze->isEmpty()) {
            abort(404, 'Keine Einsätze für diese Vorschau gefunden.');
        }

        // Temporäre Rechnung bauen (nicht speichern)
        $rechnungstyp = $nurPauschale
            ? ($einsaetze->first()->tagespauschale->rechnungstyp ?? $klient->rechnungstyp ?? 'kombiniert')
            : ($klient->rechnungstyp ?? 'kombiniert');

        $rechnung = new Rechnung([
            'organisation_id' => $this->orgId(),
            'klient_id'       => $klient->id,
            'rechnungsnummer' => 'VORSCHAU',
            'periode_von'     => $request->periode_von,
            'periode_bis'     => $request->periode_bis,
            'rechnungsdatum'  => today(),
            'status'          => 'entwurf',
            'rechnungstyp'    => $rechnungstyp,
        ]);
        $rechnung->id = 0;
        $rechnung->setRelation('klient', $klient->load(['region', 'krankenkassen.krankenkasse', 'adressen']));

        $tarifCache = [];
        $positionen = collect();

        if ($nurPauschale) {
            $tp      = $einsaetze->first()->tagespauschale;
            $ansatz  = (float) $tp->ansatz;
            [$tarifPat, $tarifKk] = match($tp->rechnungstyp) {
                'kvg'  => [0.0, $ansatz],
                default=> [$ansatz, 0.0],
            };
            $anzahl   = $einsaetze->count();
            $vonDatum = $einsaetze->min('datum');
            $bisDatum = $einsaetze->max('datum');
            $pos = new RechnungsPosition([
                'rechnung_id'    => 0,
                'einsatz_id'     => null,
                'datum'          => $vonDatum,
                'menge'          => $anzahl,
                'einheit'        => 'tage',
                'beschreibung'   => ($tp->text ? $tp->text . ' ' : '') .
                                    \Carbon\Carbon::parse($vonDatum)->format('d.m.Y') . ' – ' .
                                    \Carbon\Carbon::parse($bisDatum)->format('d.m.Y'),
                'tarif_patient'  => $tarifPat,
                'tarif_kk'       => $tarifKk,
                'betrag_patient' => $this->r5($tarifPat * $anzahl),
                'betrag_kk' => $this->r5($tarifKk * $anzahl),
            ]);
            $positionen->push($pos);
        } else {
            foreach ($einsaetze as $einsatz) {
                [$tarifPat, $tarifKk] = $this->tarifeFuerEinsatz($einsatz, $klient, $rechnungstyp, $tarifCache);
                $m = $einsatz->minuten ?? 0;
                $pos = new RechnungsPosition([
                    'rechnung_id'    => 0,
                    'einsatz_id'     => $einsatz->id,
                    'datum'          => $einsatz->datum,
                    'menge'          => $m,
                    'einheit'        => 'minuten',
                    'beschreibung'   => null,
                    'tarif_patient'  => $tarifPat,
                    'tarif_kk'       => $tarifKk,
                    'betrag_patient' => $this->r5($m / 60 * $tarifPat),
                    'betrag_kk' => $this->r5($m / 60 * $tarifKk),
                ]);
                $pos->setRelation('einsatz', $einsatz);
                $positionen->push($pos);
            }
        }

        $rechnung->setRelation('positionen', $positionen);
        $rechnung->betrag_patient = $positionen->sum('betrag_patient');
        $rechnung->betrag_kk      = $positionen->sum('betrag_kk');
        $rechnung->betrag_total   = $rechnung->betrag_patient + $rechnung->betrag_kk;

        $org     = Organisation::findOrFail($this->orgId());
        $service = new PdfExportService($org);
        $pdf     = $service->rechnungAlsPdfString($rechnung);

        $name = "vorschau_{$klient->nachname}_{$request->periode_von}.pdf";
        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$name}\"",
        ]);
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

        [$lauf, $erstellt, $uebersprungen] = $this->erstelleLauf(
            $request->periode_von, $request->periode_bis, $selectedIds
        );

        if (!$lauf) {
            return back()->with('fehler', 'Keine verrechenbaren Einsätze gefunden.');
        }

        return redirect()->route('rechnungslauf.show', $lauf)
            ->with('erfolg', "{$erstellt} Rechnungen erstellt, {$uebersprungen} Klienten ohne Einsätze übersprungen.");
    }

    public function show(Rechnungslauf $lauf)
    {
        $this->autorisiereZugriff($lauf);
        $lauf->load(['rechnungen.klient', 'ersteller']);

        // hat_pauschale + hat_einzelleistung Flag pro Rechnung setzen (ohne N+1)
        $rechnungIds = $lauf->rechnungen->pluck('id');
        $pauschaleIds = \DB::table('rechnungs_positionen')
            ->whereIn('rechnung_id', $rechnungIds)
            ->where('einheit', 'tage')
            ->pluck('rechnung_id')->unique();
        $einzelleistungIds = \DB::table('rechnungs_positionen')
            ->whereIn('rechnung_id', $rechnungIds)
            ->where('einheit', 'pauschal')
            ->pluck('rechnung_id')->unique();

        $lauf->rechnungen->each(function ($r) use ($pauschaleIds, $einzelleistungIds) {
            $r->hat_pauschale       = $pauschaleIds->contains($r->id);
            $r->hat_einzelleistung  = $einzelleistungIds->contains($r->id);
        });

        $emailAnzahl = $lauf->rechnungen->filter(
            fn($r) => $r->klient->versandart_patient === 'email' && $r->klient->email
        )->count();

        $postAnzahl = $lauf->rechnungen->filter(
            fn($r) => $r->klient->versandart_patient !== 'email'
        )->count();

        $kvgAnzahl = $lauf->rechnungen->whereIn('rechnungstyp', ['kvg', 'kombiniert'])->count();

        $postEntwurfAnzahl = $lauf->rechnungen->filter(
            fn($r) => $r->klient->versandart_patient !== 'email' && $r->status === 'entwurf'
        )->count();

        $xmlEntwurfAnzahl = $lauf->rechnungen
            ->whereIn('rechnungstyp', ['kvg', 'kombiniert'])
            ->where('status', 'entwurf')
            ->count();

        return view('rechnungen.lauf.show', compact('lauf', 'emailAnzahl', 'postAnzahl', 'kvgAnzahl', 'postEntwurfAnzahl', 'xmlEntwurfAnzahl'));
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
            $zipOpt = new ZipArchiveOptions(); $zipOpt->setSendHttpHeaders(false);
            $zip = new ZipStream(null, $zipOpt);
            foreach ($rechnungen as $rechnung) {
                try {
                    if (!$rechnung->pdf_pfad || !Storage::exists($rechnung->pdf_pfad)) {
                        $pdfService->rechnungExportieren($rechnung);
                        $rechnung->refresh();
                    }
                    if ($rechnung->pdf_pfad && Storage::exists($rechnung->pdf_pfad)) {
                        $zip->addFileFromPath(
                            $rechnung->rechnungsnummer . '.pdf',
                            Storage::path($rechnung->pdf_pfad),
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
            $zipOpt = new ZipArchiveOptions(); $zipOpt->setSendHttpHeaders(false);
            $zip = new ZipStream(null, $zipOpt);
            foreach ($rechnungen as $rechnung) {
                try {
                    $pfad = $xmlService->rechnungExportieren($rechnung);
                    $zip->addFileFromPath(
                        $rechnung->rechnungsnummer . '.xml',
                        Storage::path($pfad),
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

        if (!$lr || !$lr->verrechnung) {
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

            $rechnungstyp     = $klient->rechnungstyp ?? 'kombiniert';
            $normale          = $einsaetze->filter(fn($e) => !$e->tagespauschale_id && $e->betrag_fix === null);
            $tagespauschalen  = $einsaetze->filter(fn($e) => $e->tagespauschale_id);
            $einzelleistungen = $einsaetze->filter(fn($e) => !$e->tagespauschale_id && $e->betrag_fix !== null);

            // Zeile für normale Einsätze
            if ($normale->isNotEmpty()) {
                $betragPat = 0; $betragKk = 0; $minuten = 0; $ohneTypCount = 0;
                foreach ($normale as $einsatz) {
                    $istTage = $einsatz->leistungsart?->einheit === 'tage';
                    [$tp, $tk] = $this->tarifeFuerEinsatz($einsatz, $klient, $rechnungstyp, $tarifCache);
                    if ($istTage) {
                        $tage       = $einsatz->anzahlTage() ?? 1;
                        $betragPat += $this->r5($tage * $tp);
                        $betragKk  += $this->r5($tage * $tk);
                    } else {
                        $m          = $einsatz->minuten ?? 0;
                        $betragPat += $this->r5($m / 60 * $tp);
                        $betragKk  += $this->r5($m / 60 * $tk);
                        $minuten   += $m;
                    }
                    if (!$einsatz->leistungsart_id) $ohneTypCount++;
                }
                if ($ohneTypCount > 0) $ohneLeistungsart++;
                $betrag = $betragPat + $betragKk;
                $zeilen[] = [
                    'klient'         => $klient,
                    'label'          => null,
                    'rechnungstyp'   => $rechnungstyp,
                    'anzahl'         => $normale->count(),
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

            // Separate Zeile für Tagespauschalen
            if ($tagespauschalen->isNotEmpty()) {
                $betragPat = 0; $betragKk = 0;
                $tpRechnungstyp = $tagespauschalen->first()->tagespauschale->rechnungstyp ?? $rechnungstyp;
                foreach ($tagespauschalen as $einsatz) {
                    $pauschale = $einsatz->tagespauschale;
                    $ansatz    = (float) $pauschale->ansatz;
                    [$tp, $tk] = match($pauschale->rechnungstyp) {
                        'kvg'  => [0.0, $ansatz],
                        default=> [$ansatz, 0.0],
                    };
                    $betragPat += $tp;
                    $betragKk  += $tk;
                }
                $betrag = $betragPat + $betragKk;
                $zeilen[] = [
                    'klient'         => $klient,
                    'label'          => 'Pauschale',
                    'rechnungstyp'   => $tpRechnungstyp,
                    'anzahl'         => $tagespauschalen->count(),
                    'minuten'        => 0,
                    'betrag_patient' => $betragPat,
                    'betrag_kk'      => $betragKk,
                    'betrag'         => $betrag,
                    'versandart'     => $klient->versandart_patient ?? 'post',
                    'ohne_tarif'     => false,
                    'ohne_einsaetze' => false,
                ];
                $totalBetrag += $betrag;
                $anzahlMit++;
            }

            // Zeile für Einzelleistungen
            if ($einzelleistungen->isNotEmpty()) {
                $betrag = $einzelleistungen->sum(fn($e) => (float) $e->betrag_fix);
                $zeilen[] = [
                    'klient'         => $klient,
                    'label'          => 'Einzelleistung',
                    'rechnungstyp'   => 'klient',
                    'anzahl'         => $einzelleistungen->count(),
                    'minuten'        => 0,
                    'betrag_patient' => $betrag,
                    'betrag_kk'      => 0,
                    'betrag'         => $betrag,
                    'versandart'     => $klient->versandart_patient ?? 'post',
                    'ohne_tarif'     => false,
                    'ohne_einsaetze' => false,
                ];
                $totalBetrag += $betrag;
                $anzahlMit++;
            }
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

    public function postAbschliessen(Rechnungslauf $lauf)
    {
        $this->autorisiereZugriff($lauf);

        $anzahl = $lauf->rechnungen()
            ->whereHas('klient', fn($q) => $q->where('versandart_patient', '!=', 'email'))
            ->where('status', 'entwurf')
            ->update(['status' => 'gesendet', 'updated_at' => now()]);

        return back()->with('erfolg', "{$anzahl} Post/Manuell-Rechnung(en) als versendet markiert.");
    }

    public function xmlAbschliessen(Rechnungslauf $lauf)
    {
        $this->autorisiereZugriff($lauf);

        $anzahl = $lauf->rechnungen()
            ->whereIn('rechnungstyp', ['kvg', 'kombiniert'])
            ->where('status', 'entwurf')
            ->update(['status' => 'gesendet', 'updated_at' => now()]);

        return back()->with('erfolg', "{$anzahl} KVG/XML-Rechnung(en) als versendet markiert.");
    }

    public function bexioAbgleich(Rechnungslauf $lauf)
    {
        $this->autorisiereZugriff($lauf);

        $org = Organisation::findOrFail(auth()->user()->organisation_id);
        if (empty($org->bexio_api_key)) {
            return back()->with('fehler', 'Kein Bexio API-Key konfiguriert.');
        }

        $rechnungen = $lauf->rechnungen()
            ->whereNotNull('bexio_rechnung_id')
            ->whereNotIn('status', ['bezahlt', 'storniert'])
            ->get();

        if ($rechnungen->isEmpty()) {
            return back()->with('erfolg', 'Keine offenen Bexio-Rechnungen zum Abgleichen vorhanden.');
        }

        $service  = new BexioService($org);
        $ergebnis = $service->sammelstatusAktualisieren($rechnungen);

        AuditLog::schreiben('geaendert', 'Rechnungslauf', $lauf->id,
            "Bexio-Abgleich: {$ergebnis['aktualisiert']} aktualisiert, {$ergebnis['fehler']} Fehler");

        $meldung = "{$ergebnis['aktualisiert']} Rechnung(en) als bezahlt aktualisiert.";
        if ($ergebnis['fehler'] > 0) {
            $meldung .= " ({$ergebnis['fehler']} Fehler bei der Abfrage.)";
        }

        return back()->with('erfolg', $meldung);
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
        $rechnungIds = $lauf->rechnungen()->pluck('id');

        // Normale Einsätze via einsatz_id auf Position
        $einsatzIds = \App\Models\RechnungsPosition::whereIn('rechnung_id', $rechnungIds)
            ->whereNotNull('einsatz_id')
            ->pluck('einsatz_id')
            ->unique();
        Einsatz::whereIn('id', $einsatzIds)->update(['verrechnet' => false]);

        // Tagespauschalen-Einsätze: konsolidierte Position hat einsatz_id=null → via Klient + Periode zurücksetzen
        $klientIds = $lauf->rechnungen()->pluck('klient_id')->unique();
        Einsatz::whereIn('klient_id', $klientIds)
            ->whereNotNull('tagespauschale_id')
            ->whereBetween('datum', [$lauf->periode_von, $lauf->periode_bis])
            ->where('verrechnet', true)
            ->update(['verrechnet' => false]);

        // Rechnungen (+ Positionen via cascade) löschen
        $anzahl = $lauf->rechnungen()->count();
        $lauf->rechnungen()->each(fn($r) => $r->delete());

        AuditLog::schreiben('geloescht', 'Rechnungslauf', $lauf->id,
            "Lauf #{$lauf->id} storniert: {$anzahl} Rechnungen gelöscht, {$einsatzIds->count()} Einsätze zurückgesetzt");

        $lauf->delete();

        return redirect()->route('rechnungslauf.index')
            ->with('erfolg', "Lauf #{$lauf->id} storniert — {$anzahl} Rechnungen gelöscht, {$einsatzIds->count()} Einsätze wieder verrechenbar.");
    }

    // Lauf stornieren + sofort neuen Lauf mit gleicher Periode + gleichen Klienten erstellen
    public function wiederholen(Rechnungslauf $lauf)
    {
        $this->autorisiereZugriff($lauf);

        $blockiert = $lauf->rechnungen()->whereIn('status', ['gesendet', 'bezahlt'])->count();
        if ($blockiert > 0) {
            return back()->with('fehler',
                "{$blockiert} Rechnung(en) bereits versendet/bezahlt — Lauf kann nicht storniert werden.");
        }

        $periode_von = $lauf->periode_von->format('Y-m-d');
        $periode_bis = $lauf->periode_bis->format('Y-m-d');

        // Schritt 1: Einsätze zurücksetzen + Lauf löschen
        $rechnungIds = $lauf->rechnungen()->pluck('id');
        $einsatzIds  = RechnungsPosition::whereIn('rechnung_id', $rechnungIds)
            ->whereNotNull('einsatz_id')
            ->pluck('einsatz_id')
            ->unique();
        Einsatz::whereIn('id', $einsatzIds)->update(['verrechnet' => false]);

        // Tagespauschalen via Klient + Periode zurücksetzen
        $klientIds = $lauf->rechnungen()->pluck('klient_id')->unique();
        Einsatz::whereIn('klient_id', $klientIds)
            ->whereNotNull('tagespauschale_id')
            ->whereBetween('datum', [$periode_von, $periode_bis])
            ->where('verrechnet', true)
            ->update(['verrechnet' => false]);

        $alteLaufId = $lauf->id;
        $lauf->rechnungen()->each(fn($r) => $r->delete());
        $lauf->delete();

        AuditLog::schreiben('geloescht', 'Rechnungslauf', $alteLaufId,
            "Lauf #{$alteLaufId} storniert (Wiederholen): {$einsatzIds->count()} Einsätze zurückgesetzt");

        // Schritt 2: ALLE Klienten mit verrechenbaren Einsätzen in der Periode neu ermitteln
        $alleKlientIds = Einsatz::where('organisation_id', $this->orgId())
            ->where('verrechnet', false)
            ->where(function ($q) {
                $q->whereNotNull('checkout_zeit')
                  ->orWhereNotNull('tagespauschale_id');
            })
            ->whereBetween('datum', [$periode_von, $periode_bis])
            ->pluck('klient_id')
            ->unique()
            ->values()
            ->all();

        if (empty($alleKlientIds)) {
            return redirect()->route('rechnungslauf.index')
                ->with('fehler', "Lauf storniert — keine verrechenbaren Einsätze für {$periode_von} bis {$periode_bis} gefunden.");
        }

        [$neuerLauf, $erstellt, $uebersprungen] = $this->erstelleLauf(
            $periode_von, $periode_bis, $alleKlientIds
        );

        return redirect()->route('rechnungslauf.show', $neuerLauf)
            ->with('erfolg', "Lauf wiederholt — {$erstellt} Rechnungen erstellt, {$uebersprungen} übersprungen.");
    }

    private function erstelleLauf(string $periode_von, string $periode_bis, array $klientIds): array
    {
        $klienten = Klient::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->whereIn('id', $klientIds)
            ->get();

        $erstellt      = 0;
        $uebersprungen = 0;
        $tarifCache    = [];

        // Schritt 1: Alle Einsätze sammeln — Lauf wird erst danach angelegt
        $klientenDaten = [];
        foreach ($klienten as $klient) {
            $alleEinsaetze = Einsatz::where('klient_id', $klient->id)
                ->where('verrechnet', false)
                ->where(function ($q) {
                    $q->whereNotNull('checkout_zeit')
                      ->orWhereNotNull('tagespauschale_id');
                })
                ->whereBetween('datum', [$periode_von, $periode_bis])
                ->with('leistungsart', 'tagespauschale')
                ->orderBy('datum')
                ->get();

            if ($alleEinsaetze->isEmpty()) {
                $uebersprungen++;
                continue;
            }

            $klientenDaten[] = [
                'klient'          => $klient,
                'normale'         => $alleEinsaetze->filter(fn($e) => !$e->tagespauschale_id && $e->betrag_fix === null)->values(),
                'tagespauschalen' => $alleEinsaetze->filter(fn($e) => $e->tagespauschale_id)->values(),
                'einzelleistungen'=> $alleEinsaetze->filter(fn($e) => !$e->tagespauschale_id && $e->betrag_fix !== null)->values(),
            ];
        }

        // Keine Einsätze → kein Lauf
        if (empty($klientenDaten)) {
            return [null, 0, $uebersprungen];
        }

        // Schritt 2: Lauf anlegen
        $lauf = Rechnungslauf::create([
            'organisation_id'     => $this->orgId(),
            'periode_von'         => $periode_von,
            'periode_bis'         => $periode_bis,
            'anzahl_erstellt'     => 0,
            'anzahl_uebersprungen'=> 0,
            'status'              => 'abgeschlossen',
            'erstellt_von'        => auth()->id(),
        ]);

        // Schritt 3: Pro Klient — getrennte Rechnungen für normale Einsätze und Tagespauschalen
        foreach ($klientenDaten as $daten) {
            $klient       = $daten['klient'];
            $rechnungstyp = $klient->rechnungstyp ?? 'kombiniert';

            // Rechnung für normale Einsätze
            if ($daten['normale']->isNotEmpty()) {
                $rechnung = Rechnung::create([
                    'organisation_id' => $this->orgId(),
                    'klient_id'       => $klient->id,
                    'rechnungsnummer' => Rechnung::naechsteNummer($this->orgId()),
                    'periode_von'     => $periode_von,
                    'periode_bis'     => $periode_bis,
                    'rechnungsdatum'  => today(),
                    'status'          => 'entwurf',
                    'rechnungstyp'    => $rechnungstyp,
                    'rechnungslauf_id'=> $lauf->id,
                ]);

                foreach ($daten['normale'] as $einsatz) {
                    if ($einsatz->betrag_fix !== null) {
                        // Einzelleistung mit Fixbetrag — kein Tarif-Lookup
                        $menge     = 1;
                        $einheit   = 'pauschal';
                        $betragFix = $this->r5((float) $einsatz->betrag_fix);
                        $betragPat = $betragFix;
                        $betragKk  = 0.0;
                        $tarifPat  = $betragFix;
                        $tarifKk   = 0.0;
                        RechnungsPosition::create([
                            'rechnung_id'    => $rechnung->id,
                            'einsatz_id'     => $einsatz->id,
                            'datum'          => $einsatz->datum,
                            'menge'          => $menge,
                            'einheit'        => $einheit,
                            'beschreibung'   => $einsatz->bemerkung,
                            'tarif_patient'  => $tarifPat,
                            'tarif_kk'       => $tarifKk,
                            'betrag_patient' => $betragPat,
                            'betrag_kk'      => $betragKk,
                        ]);
                    } else {
                        $istTage = $einsatz->leistungsart?->einheit === 'tage';
                        [$tarifPat, $tarifKk] = $this->tarifeFuerEinsatz($einsatz, $klient, $rechnungstyp, $tarifCache);
                        if ($istTage) {
                            $menge     = $einsatz->anzahlTage() ?? 1;
                            $einheit   = 'tage';
                            $betragPat = $this->r5($menge * $tarifPat);
                            $betragKk  = $this->r5($menge * $tarifKk);
                        } else {
                            $menge     = $einsatz->minuten ?? 0;
                            $einheit   = 'minuten';
                            $betragPat = $this->r5($menge / 60 * $tarifPat);
                            $betragKk  = $this->r5($menge / 60 * $tarifKk);
                        }
                        RechnungsPosition::create([
                            'rechnung_id'    => $rechnung->id,
                            'einsatz_id'     => $einsatz->id,
                            'datum'          => $einsatz->datum,
                            'menge'          => $menge,
                            'einheit'        => $einheit,
                            'beschreibung'   => null,
                            'tarif_patient'  => $tarifPat,
                            'tarif_kk'       => $tarifKk,
                            'betrag_patient' => $betragPat,
                            'betrag_kk'      => $betragKk,
                        ]);
                    }
                    $einsatz->update(['verrechnet' => true]);
                }

                $rechnung->load('positionen');
                $rechnung->berechneTotale();
                AuditLog::schreiben('erstellt', 'Rechnung', $rechnung->id,
                    "Rechnungslauf {$lauf->id}: Rechnung {$rechnung->rechnungsnummer} (Einsätze) für {$klient->nachname}");
                $erstellt++;
            }

            // Rechnung für Tagespauschalen (getrennt)
            if ($daten['tagespauschalen']->isNotEmpty()) {
                // Rechnungstyp von der ersten Tagespauschale ableiten
                $tpRechnungstyp = $daten['tagespauschalen']->first()->tagespauschale->rechnungstyp ?? $rechnungstyp;

                $rechnung = Rechnung::create([
                    'organisation_id' => $this->orgId(),
                    'klient_id'       => $klient->id,
                    'rechnungsnummer' => Rechnung::naechsteNummer($this->orgId()),
                    'periode_von'     => $periode_von,
                    'periode_bis'     => $periode_bis,
                    'rechnungsdatum'  => today(),
                    'status'          => 'entwurf',
                    'rechnungstyp'    => $tpRechnungstyp,
                    'rechnungslauf_id'=> $lauf->id,
                ]);

                // Eine konsolidierte Position für alle Tagespauschalen-Einsätze
                $tp       = $daten['tagespauschalen']->first()->tagespauschale;
                $ansatz   = (float) $tp->ansatz;
                $anzahl   = $daten['tagespauschalen']->count();
                $vonDatum = $daten['tagespauschalen']->min('datum');
                $bisDatum = $daten['tagespauschalen']->max('datum');
                [$tarifPat, $tarifKk] = match($tp->rechnungstyp) {
                    'kvg'  => [0.0, $ansatz],
                    default=> [$ansatz, 0.0],
                };
                RechnungsPosition::create([
                    'rechnung_id'    => $rechnung->id,
                    'einsatz_id'     => null,
                    'datum'          => $vonDatum,
                    'menge'          => $anzahl,
                    'einheit'        => 'tage',
                    'beschreibung'   => ($tp->text ? $tp->text . ' ' : '') .
                                        \Carbon\Carbon::parse($vonDatum)->format('d.m.Y') . ' – ' .
                                        \Carbon\Carbon::parse($bisDatum)->format('d.m.Y'),
                    'tarif_patient'  => $tarifPat,
                    'tarif_kk'       => $tarifKk,
                    'betrag_patient' => $this->r5($tarifPat * $anzahl),
                    'betrag_kk' => $this->r5($tarifKk * $anzahl),
                ]);
                $daten['tagespauschalen']->each->update(['verrechnet' => true]);

                $rechnung->load('positionen');
                $rechnung->berechneTotale();
                AuditLog::schreiben('erstellt', 'Rechnung', $rechnung->id,
                    "Rechnungslauf {$lauf->id}: Rechnung {$rechnung->rechnungsnummer} (Tagespauschalen) für {$klient->nachname}");
                $erstellt++;
            }

            // Separate Rechnung für Einzelleistungen (betrag_fix)
            if ($daten['einzelleistungen']->isNotEmpty()) {
                $rechnung = Rechnung::create([
                    'organisation_id' => $this->orgId(),
                    'klient_id'       => $klient->id,
                    'rechnungsnummer' => Rechnung::naechsteNummer($this->orgId()),
                    'periode_von'     => $periode_von,
                    'periode_bis'     => $periode_bis,
                    'rechnungsdatum'  => today(),
                    'status'          => 'entwurf',
                    'rechnungstyp'    => 'klient',
                    'rechnungslauf_id'=> $lauf->id,
                ]);

                foreach ($daten['einzelleistungen'] as $einsatz) {
                    $betrag = $this->r5((float) $einsatz->betrag_fix);
                    RechnungsPosition::create([
                        'rechnung_id'    => $rechnung->id,
                        'einsatz_id'     => $einsatz->id,
                        'datum'          => $einsatz->datum,
                        'menge'          => 1,
                        'einheit'        => 'pauschal',
                        'beschreibung'   => $einsatz->bemerkung,
                        'tarif_patient'  => $betrag,
                        'tarif_kk'       => 0.0,
                        'betrag_patient' => $betrag,
                        'betrag_kk'      => 0.0,
                    ]);
                    $einsatz->update(['verrechnet' => true]);
                }

                $rechnung->load('positionen');
                $rechnung->berechneTotale();
                AuditLog::schreiben('erstellt', 'Rechnung', $rechnung->id,
                    "Rechnungslauf {$lauf->id}: Rechnung {$rechnung->rechnungsnummer} (Einzelleistungen) für {$klient->nachname}");
                $erstellt++;
            }
        }

        $lauf->update([
            'anzahl_erstellt'     => $erstellt,
            'anzahl_uebersprungen'=> $uebersprungen,
        ]);

        return [$lauf, $erstellt, $uebersprungen];
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
            $einsatzIds = (clone $basis)->where('verrechnet', true)->pluck('id');
            $laufId = \DB::table('rechnungs_positionen')
                ->join('rechnungen', 'rechnungen.id', '=', 'rechnungs_positionen.rechnung_id')
                ->whereIn('rechnungs_positionen.einsatz_id', $einsatzIds)
                ->whereNotNull('rechnungen.rechnungslauf_id')
                ->value('rechnungen.rechnungslauf_id');
            $laufHinweis = $laufId ? " (Lauf #{$laufId})" : "";
            return "Alle {$bereitsVerrechnet} Einsätze bereits verrechnet{$laufHinweis}";
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

    /** Kaufmännische Rundung auf 0.05 CHF */
    private function r5(float $x): float { return round($x * 20) / 20; }

    private function autorisiereZugriff(Rechnungslauf $lauf): void
    {
        if ($lauf->organisation_id !== $this->orgId()) abort(403);
    }
}
