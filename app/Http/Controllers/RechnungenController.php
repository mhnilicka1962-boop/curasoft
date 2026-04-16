<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\Organisation;
use App\Models\Rechnung;
use App\Models\RechnungsPosition;
use App\Services\BexioService;
use App\Services\PdfExportService;
use App\Services\XmlExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RechnungenController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    public function index(Request $request)
    {
        $query = Rechnung::with('klient')
            ->where('organisation_id', $this->orgId())
            ->orderByDesc('rechnungsdatum');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('jahr')) {
            $query->whereYear('rechnungsdatum', $request->jahr);
        }
        if ($request->filled('monat')) {
            $query->whereMonth('rechnungsdatum', $request->monat);
        }
        if ($request->filled('suche')) {
            $s = $request->suche;
            $query->where(fn($q) => $q
                ->where('rechnungsnummer', 'ilike', "%{$s}%")
                ->orWhereHas('klient', fn($k) => $k
                    ->where('nachname', 'ilike', "%{$s}%")
                    ->orWhere('vorname', 'ilike', "%{$s}%")
                )
            );
        }

        $rechnungen = $query->paginate(25)->withQueryString();

        $totale = Rechnung::where('organisation_id', $this->orgId())
            ->selectRaw('status, COUNT(*) as anzahl, SUM(betrag_total) as summe')
            ->groupBy('status')
            ->get()->keyBy('status');

        $klienten      = Klient::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();
        $leistungsarten = \App\Models\Leistungsart::where('aktiv', true)->orderBy('bezeichnung')->get();

        return view('rechnungen.index', compact('rechnungen', 'totale', 'klienten', 'leistungsarten'));
    }

    public function create(Request $request)
    {
        $klienten = Klient::where('organisation_id', $this->orgId())
            ->where('aktiv', true)->orderBy('nachname')->get();

        // Wenn Klient + Periode gewählt: passende Einsätze laden
        $einsaetze   = collect();
        $klient      = null;

        if ($request->filled('klient_id') && $request->filled('periode_von') && $request->filled('periode_bis')) {
            $klient = Klient::findOrFail($request->klient_id);
            $einsaetze = Einsatz::where('klient_id', $klient->id)
                ->where('verrechnet', false)
                ->whereNotNull('checkout_zeit')
                ->whereBetween('datum', [$request->periode_von, $request->periode_bis])
                ->orderBy('datum')
                ->get();
        }

        return view('rechnungen.create', compact('klienten', 'einsaetze', 'klient'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'klient_id'     => ['required', 'exists:klienten,id'],
            'periode_von'   => ['required', 'date'],
            'periode_bis'   => ['required', 'date', 'after_or_equal:periode_von'],
            'rechnungstyp'  => ['required', 'in:kombiniert,kvg,klient,gemeinde'],
            'einsatz_ids'   => ['required', 'array', 'min:1'],
            'einsatz_ids.*' => ['exists:einsaetze,id'],
        ]);

        $rechnung = Rechnung::create([
            'organisation_id' => $this->orgId(),
            'klient_id'       => $request->klient_id,
            'rechnungsnummer' => Rechnung::naechsteNummer($this->orgId()),
            'periode_von'     => $request->periode_von,
            'periode_bis'     => $request->periode_bis,
            'rechnungsdatum'  => today(),
            'status'          => 'entwurf',
            'rechnungstyp'    => $request->rechnungstyp,
        ]);

        foreach ($request->einsatz_ids as $einsatzId) {
            $einsatz = Einsatz::findOrFail($einsatzId);
            RechnungsPosition::create([
                'rechnung_id' => $rechnung->id,
                'einsatz_id'  => $einsatz->id,
                'datum'       => $einsatz->datum,
                'menge'       => $einsatz->minuten ?? 0,
                'einheit'     => 'minuten',
                'tarif_patient' => 0,
                'tarif_kk'      => 0,
                'betrag_patient'=> 0,
                'betrag_kk'     => 0,
            ]);
            $einsatz->update(['verrechnet' => true]);
        }

        AuditLog::schreiben('erstellt', 'Rechnung', $rechnung->id,
            "Rechnung {$rechnung->rechnungsnummer} erstellt");

        return redirect()->route('rechnungen.show', $rechnung)
            ->with('erfolg', "Rechnung {$rechnung->rechnungsnummer} wurde erstellt.");
    }

    public function show(Rechnung $rechnung)
    {
        $this->autorisiereZugriff($rechnung);
        $rechnung->load(['klient', 'lauf', 'positionen.einsatz.einsatzLeistungsarten.leistungsart', 'positionen.leistungstyp']);
        return view('rechnungen.show', compact('rechnung'));
    }

    public function statusUpdate(Request $request, Rechnung $rechnung)
    {
        $this->autorisiereZugriff($rechnung);

        $request->validate([
            'status'     => ['required', 'in:entwurf,gesendet,bezahlt,storniert'],
            'bezahlt_am' => ['nullable', 'date'],
        ]);

        $alterStatus = $rechnung->status;

        $update = ['status' => $request->status];
        if ($request->status === 'bezahlt') {
            $update['bezahlt_am'] = $request->filled('bezahlt_am')
                ? $request->bezahlt_am
                : today()->toDateString();
        } elseif ($alterStatus === 'bezahlt') {
            $update['bezahlt_am'] = null;
        }

        $rechnung->update($update);

        AuditLog::schreiben('geaendert', 'Rechnung', $rechnung->id,
            "Status: {$alterStatus} → {$request->status}");

        return back()->with('erfolg', 'Status wurde aktualisiert.');
    }

    public function positionUpdate(Request $request, RechnungsPosition $position)
    {
        if ($position->rechnung->organisation_id !== $this->orgId()) abort(403);

        $request->validate([
            'tarif_patient' => ['required', 'numeric', 'min:0'],
            'tarif_kk'      => ['required', 'numeric', 'min:0'],
        ]);

        $position->update([
            'tarif_patient'  => $request->tarif_patient,
            'tarif_kk'       => $request->tarif_kk,
            'betrag_patient' => round($position->menge / 60 * $request->tarif_patient, 2),
            'betrag_kk'      => round($position->menge / 60 * $request->tarif_kk, 2),
        ]);

        $position->rechnung->load('positionen');
        $position->rechnung->berechneTotale();

        return back()->with('erfolg', 'Tarif aktualisiert.');
    }

    public function xmlExport(Rechnung $rechnung)
    {
        $this->autorisiereZugriff($rechnung);
        $rechnung->loadMissing(['klient.region', 'klient.krankenkassen.krankenkasse', 'positionen.leistungstyp.leistungsart']);

        $org     = Organisation::findOrFail($this->orgId());
        $service = new XmlExportService($org);

        try {
            $pfad = $service->rechnungExportieren($rechnung);
            return Storage::download($pfad, "rechnung_{$rechnung->rechnung_nr}.xml");
        } catch (\Exception $e) {
            return back()->with('fehler', 'XML-Export fehlgeschlagen: ' . $e->getMessage());
        }
    }

    public function pdfExport(Rechnung $rechnung)
    {
        $this->autorisiereZugriff($rechnung);

        $org     = Organisation::findOrFail($this->orgId());
        $service = new PdfExportService($org);

        try {
            $pfad = $service->rechnungExportieren($rechnung);
            return Storage::download($pfad, "rechnung_{$rechnung->rechnungsnummer}.pdf");
        } catch (\Exception $e) {
            return back()->with('fehler', 'PDF-Export fehlgeschlagen: ' . $e->getMessage());
        }
    }

    public function bexioStatusPruefen(Rechnung $rechnung)
    {
        $this->autorisiereZugriff($rechnung);

        $org = Organisation::findOrFail($this->orgId());
        if (empty($org->bexio_api_key)) {
            return back()->with('fehler', 'Kein Bexio API-Key konfiguriert.');
        }

        $service  = new BexioService($org);
        $ergebnis = $service->zahlungsstatusAktualisieren($rechnung);

        if (isset($ergebnis['fehler'])) {
            return back()->with('fehler', 'Bexio-Abfrage fehlgeschlagen: ' . $ergebnis['fehler']);
        }

        if ($ergebnis['aktualisiert']) {
            AuditLog::schreiben('geaendert', 'Rechnung', $rechnung->id,
                'Status via Bexio-Abgleich auf "bezahlt" gesetzt');
            return back()->with('erfolg', 'Rechnung wurde in Bexio als bezahlt erkannt und aktualisiert.');
        }

        return back()->with('erfolg', 'Bexio-Status: ' . ($ergebnis['status'] ?? '—') . ' — keine Änderung nötig.');
    }

    public function bexioSync(Rechnung $rechnung)
    {
        $this->autorisiereZugriff($rechnung);

        $org = Organisation::findOrFail($this->orgId());
        if (empty($org->bexio_api_key)) {
            return back()->with('fehler', 'Kein Bexio API-Key konfiguriert. Bitte unter Firma → Bexio einrichten.');
        }

        $rechnung->loadMissing(['klient', 'positionen.leistungsart']);

        $service = new BexioService($org);
        $ok = $service->rechnungSynchronisieren($rechnung);

        return back()->with(
            $ok ? 'erfolg' : 'fehler',
            $ok ? 'Rechnung wurde mit Bexio synchronisiert.' : 'Bexio-Sync fehlgeschlagen. Bitte Verbindung unter Firma prüfen.'
        );
    }

    public function gemeindeEmailEinzeln(Rechnung $rechnung)
    {
        $this->autorisiereZugriff($rechnung);

        $org = Organisation::findOrFail(auth()->user()->organisation_id);
        $pdfService = new PdfExportService($org);

        $email = $rechnung->klient->gemeinde_email;

        if (!$email) {
            return back()->with('fehler', 'Keine Gemeinde-E-Mail-Adresse beim Klienten hinterlegt.');
        }

        try {
            $rechnung->load(['klient.aktBeitrag', 'klient.region', 'positionen.einsatzLeistungsart.leistungsart']);
            $pfad = $pdfService->gemeindeRechnungExportieren($rechnung);
            $rechnung->refresh();

            \Illuminate\Support\Facades\Mail::send([], [], function ($m) use ($rechnung, $org, $pfad, $email) {
                $m->to($email)
                  ->subject('Restfinanzierungsrechnung ' . $rechnung->klient->nachname . ' ' . $rechnung->klient->vorname
                      . ' — ' . $rechnung->periode_von->format('M Y'))
                  ->text('Sehr geehrte Damen und Herren' . "\n\n"
                      . 'Beiliegend erhalten Sie die Restfinanzierungsrechnung für den Monat '
                      . $rechnung->periode_von->format('F Y') . '.' . "\n\n"
                      . 'Mit freundlichen Grüssen' . "\n"
                      . $org->name)
                  ->attach(\Illuminate\Support\Facades\Storage::path($pfad), [
                      'as'   => 'GDE-' . $rechnung->rechnungsnummer . '.pdf',
                      'mime' => 'application/pdf',
                  ]);
            });

            $rechnung->update([
                'gemeinde_versand_datum' => now(),
                'gemeinde_versand_an'    => $email,
                'gemeinde_fehler'        => null,
            ]);

            AuditLog::schreiben('geaendert', 'Rechnung', $rechnung->id, "Gemeinde-Email erneut versendet an {$email}");

            return back()->with('erfolg', "Gemeinde-Email versendet an {$email}.");
        } catch (\Exception $e) {
            $rechnung->update(['gemeinde_fehler' => mb_substr($e->getMessage(), 0, 500)]);
            return back()->with('fehler', 'Fehler beim Versand: ' . $e->getMessage());
        }
    }

    public function stornieren(Rechnung $rechnung)
    {
        $this->autorisiereZugriff($rechnung);

        if (in_array($rechnung->status, ['gesendet', 'bezahlt'])) {
            return back()->with('fehler', 'Rechnung wurde bereits versendet oder bezahlt — Stornierung nicht möglich.');
        }

        if ($rechnung->status === 'storniert') {
            return back()->with('fehler', 'Rechnung ist bereits storniert.');
        }

        // Einsätze dieser Rechnung zurücksetzen
        $einsatzIds = RechnungsPosition::where('rechnung_id', $rechnung->id)
            ->whereNotNull('einsatz_id')
            ->pluck('einsatz_id');

        Einsatz::whereIn('id', $einsatzIds)->update(['verrechnet' => false]);

        $rechnung->update(['status' => 'storniert']);

        AuditLog::schreiben('geaendert', 'Rechnung', $rechnung->id,
            "Rechnung {$rechnung->rechnungsnummer} storniert — {$einsatzIds->count()} Einsätze zurückgesetzt");

        return back()->with('erfolg',
            "Rechnung {$rechnung->rechnungsnummer} storniert — {$einsatzIds->count()} Einsätze wieder verrechenbar.");
    }

    public function einzelleistungErfassen(Request $request)
    {
        $request->validate([
            'klient_id'  => 'required|integer',
            'datum'      => 'required|date',
            'datum_bis'  => 'nullable|date|after_or_equal:datum',
            'bemerkung'  => 'required|string|max:500',
            'betrag_fix' => 'required|numeric|min:0.01',
        ]);

        $klient = Klient::where('id', $request->klient_id)
            ->where('organisation_id', $this->orgId())
            ->firstOrFail();

        $datum    = \Carbon\Carbon::parse($request->datum);
        $datumBis = $request->filled('datum_bis') ? \Carbon\Carbon::parse($request->datum_bis) : null;

        \App\Models\Einsatz::create([
            'organisation_id' => $this->orgId(),
            'klient_id'       => $klient->id,
            'benutzer_id'     => auth()->id(),
            'leistungsart_id' => null,
            'region_id'       => $klient->region_id,
            'datum'           => $datum,
            'datum_bis'       => $datumBis,
            'minuten'         => 0,
            'bemerkung'       => $request->bemerkung,
            'betrag_fix'      => $request->betrag_fix,
            'status'          => 'abgeschlossen',
            'checkin_methode' => 'manuell',
            'checkin_zeit'    => $datum->copy()->setTime(0, 0),
            'checkout_zeit'   => $datum->copy()->setTime(0, 1),
            'verrechnet'      => false,
        ]);

        return redirect()->route('klienten.show', $klient)
            ->with('erfolg', "Einzelleistung erfasst — wird im nächsten Rechnungslauf verrechnet.")
            ->with('einzelleistung_offen', true);
    }

    public function einzelleistungAktualisieren(Request $request, \App\Models\Einsatz $einsatz)
    {
        abort_if($einsatz->organisation_id !== $this->orgId(), 403);
        abort_if($einsatz->betrag_fix === null, 404);
        abort_if($einsatz->verrechnet, 422);

        $request->validate([
            'datum'      => 'required|date',
            'datum_bis'  => 'nullable|date|after_or_equal:datum',
            'bemerkung'  => 'required|string|max:500',
            'betrag_fix' => 'required|numeric|min:0',
        ]);

        $einsatz->update([
            'datum'      => $request->datum,
            'datum_bis'  => $request->datum_bis ?: $request->datum,
            'bemerkung'  => $request->bemerkung,
            'betrag_fix' => round((float) $request->betrag_fix, 2),
        ]);

        return redirect()->route('klienten.show', $einsatz->klient_id)
            ->with('erfolg', 'Einzelleistung aktualisiert.')
            ->with('einzelleistung_offen', true);
    }

    public function einzelleistungVorschau(\App\Models\Einsatz $einsatz)
    {
        abort_if($einsatz->organisation_id !== $this->orgId(), 403);
        abort_if($einsatz->betrag_fix === null, 404);

        $klient  = $einsatz->klient;
        $betrag  = round((float) $einsatz->betrag_fix, 2);
        $rechnung = new Rechnung([
            'organisation_id' => $this->orgId(),
            'klient_id'       => $klient->id,
            'rechnungsnummer' => 'VORSCHAU',
            'periode_von'     => $einsatz->datum,
            'periode_bis'     => $einsatz->datum_bis ?? $einsatz->datum,
            'rechnungsdatum'  => today(),
            'status'          => 'entwurf',
            'rechnungstyp'    => 'klient',
            'betrag_patient'  => $betrag,
            'betrag_kk'       => 0,
            'betrag_total'    => $betrag,
        ]);
        $rechnung->setRelation('klient', $klient);

        $position = new \App\Models\RechnungsPosition([
            'datum'          => $einsatz->datum,
            'menge'          => 1,
            'einheit'        => 'pauschal',
            'beschreibung'   => $einsatz->bemerkung,
            'tarif_patient'  => $betrag,
            'tarif_kk'       => 0,
            'betrag_patient' => $betrag,
            'betrag_kk'      => 0,
        ]);
        $rechnung->setRelation('positionen', collect([$position]));

        $pdfService = app(\App\Services\PdfExportService::class);
        $pdf = $pdfService->rechnungAlsPdfString($rechnung);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="vorschau-einzelleistung.pdf"',
        ]);
    }

    public function einzelleistungLoeschen(\App\Models\Einsatz $einsatz)
    {
        abort_if($einsatz->organisation_id !== $this->orgId(), 403);
        abort_if($einsatz->betrag_fix === null, 404);
        abort_if($einsatz->verrechnet, 422);

        $klientId = $einsatz->klient_id;
        $einsatz->delete();

        return redirect()->route('klienten.show', $klientId)
            ->with('erfolg', 'Einzelleistung gelöscht.');
    }

    public function auswertungPdf(Request $request)
    {
        $query = Rechnung::with('klient')
            ->where('organisation_id', $this->orgId())
            ->orderBy('rechnungsdatum')
            ->orderBy('id');

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('jahr'))   $query->whereYear('rechnungsdatum', $request->jahr);
        if ($request->filled('monat'))  $query->whereMonth('rechnungsdatum', $request->monat);
        if ($request->filled('suche')) {
            $s = $request->suche;
            $query->where(fn($q) => $q
                ->where('rechnungsnummer', 'ilike', "%{$s}%")
                ->orWhereHas('klient', fn($k) => $k
                    ->where('nachname', 'ilike', "%{$s}%")
                    ->orWhere('vorname', 'ilike', "%{$s}%")
                )
            );
        }

        $rechnungen = $query->get();
        $org        = Organisation::findOrFail($this->orgId());

        $monate = ['1'=>'Januar','2'=>'Februar','3'=>'März','4'=>'April','5'=>'Mai','6'=>'Juni',
                   '7'=>'Juli','8'=>'August','9'=>'September','10'=>'Oktober','11'=>'November','12'=>'Dezember'];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdfs.rechnungen_auswertung', [
            'rechnungen'    => $rechnungen,
            'org'           => $org,
            'filterJahr'    => $request->jahr,
            'filterMonat'   => $request->monat,
            'filterStatus'  => $request->status,
            'filterSuche'   => $request->suche,
            'monatsname'    => $monate[$request->monat] ?? '',
            'summePatient'  => $rechnungen->sum('betrag_patient'),
            'summeKk'       => $rechnungen->sum('betrag_kk'),
            'summeTotal'    => $rechnungen->sum('betrag_total'),
        ])->setPaper('a4', 'landscape')->setOptions(['defaultFont' => 'DejaVu Sans']);

        $filename = 'rechnungen_auswertung_' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    public function bezahltAmUpdate(Request $request, Rechnung $rechnung)
    {
        $this->autorisiereZugriff($rechnung);

        $request->validate([
            'bezahlt_am' => ['nullable', 'date'],
        ]);

        $rechnung->update(['bezahlt_am' => $request->bezahlt_am ?: null]);

        return back()->with('erfolg', 'Zahlungseingang gespeichert.');
    }

    public function csvExport(Request $request)
    {
        $query = Rechnung::with('klient')
            ->where('organisation_id', $this->orgId())
            ->orderBy('rechnungsdatum')
            ->orderBy('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('jahr')) {
            $query->whereYear('rechnungsdatum', $request->jahr);
        }
        if ($request->filled('monat')) {
            $query->whereMonth('rechnungsdatum', $request->monat);
        }
        if ($request->filled('suche')) {
            $s = $request->suche;
            $query->where(fn($q) => $q
                ->where('rechnungsnummer', 'ilike', "%{$s}%")
                ->orWhereHas('klient', fn($k) => $k
                    ->where('nachname', 'ilike', "%{$s}%")
                    ->orWhere('vorname', 'ilike', "%{$s}%")
                )
            );
        }

        $rechnungen = $query->get();

        $filename = 'rechnungen_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use ($rechnungen) {
            $out = fopen('php://output', 'w');
            // BOM für Excel UTF-8
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, [
                'Rechnungsnummer',
                'Rechnungsdatum',
                'Jahr',
                'Monat',
                'Periode von',
                'Periode bis',
                'Typ',
                'Status',
                'Betrag Patient CHF',
                'Betrag KK CHF',
                'Betrag Total CHF',
                'Zahlungseingang',
                'Klient Name',
                'Klient Vorname',
                'Klient Geburtsdatum',
                'Email Versand am',
                'Erfasst am',
                'Mutiert am',
            ], ';');

            foreach ($rechnungen as $r) {
                fputcsv($out, [
                    $r->rechnungsnummer,
                    $r->rechnungsdatum?->format('d.m.Y') ?? '',
                    $r->rechnungsdatum?->format('Y') ?? '',
                    $r->rechnungsdatum?->format('n') ?? '',
                    $r->periode_von?->format('d.m.Y') ?? '',
                    $r->periode_bis?->format('d.m.Y') ?? '',
                    $r->rechnungstyp ?? '',
                    $r->status ?? '',
                    number_format((float) $r->betrag_patient, 2, '.', ''),
                    number_format((float) $r->betrag_kk, 2, '.', ''),
                    number_format((float) $r->betrag_total, 2, '.', ''),
                    $r->bezahlt_am?->format('d.m.Y') ?? '',
                    $r->klient?->nachname ?? '',
                    $r->klient?->vorname ?? '',
                    $r->klient?->geburtsdatum?->format('d.m.Y') ?? '',
                    $r->email_versand_datum?->format('d.m.Y') ?? '',
                    $r->created_at?->format('d.m.Y H:i') ?? '',
                    $r->updated_at?->format('d.m.Y H:i') ?? '',
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function autorisiereZugriff(Rechnung $rechnung): void
    {
        if ($rechnung->organisation_id !== $this->orgId()) abort(403);
    }
}
