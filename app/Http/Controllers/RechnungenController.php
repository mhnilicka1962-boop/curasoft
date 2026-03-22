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
        $rechnung->load(['klient', 'positionen.einsatz']);
        return view('rechnungen.show', compact('rechnung'));
    }

    public function statusUpdate(Request $request, Rechnung $rechnung)
    {
        $this->autorisiereZugriff($rechnung);

        $request->validate([
            'status' => ['required', 'in:entwurf,gesendet,bezahlt,storniert'],
        ]);

        $alterStatus = $rechnung->status;
        $rechnung->update(['status' => $request->status]);

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

    private function autorisiereZugriff(Rechnung $rechnung): void
    {
        if ($rechnung->organisation_id !== $this->orgId()) abort(403);
    }
}
