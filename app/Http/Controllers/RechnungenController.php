<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\Organisation;
use App\Models\Rechnung;
use App\Models\RechnungsPosition;
use App\Services\BexioService;
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

        return view('rechnungen.index', compact('rechnungen', 'totale'));
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
            'klient_id'   => ['required', 'exists:klienten,id'],
            'periode_von' => ['required', 'date'],
            'periode_bis' => ['required', 'date', 'after_or_equal:periode_von'],
            'einsatz_ids' => ['required', 'array', 'min:1'],
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
        $rechnung->loadMissing(['klient.region', 'klient.krankenkassen.krankenkasse', 'positionen.leistungsart']);

        $org     = Organisation::findOrFail($this->orgId());
        $service = new XmlExportService($org);

        try {
            $pfad = $service->rechnungExportieren($rechnung);
            return Storage::download($pfad, "rechnung_{$rechnung->rechnung_nr}.xml");
        } catch (\Exception $e) {
            return back()->with('fehler', 'XML-Export fehlgeschlagen: ' . $e->getMessage());
        }
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

    private function autorisiereZugriff(Rechnung $rechnung): void
    {
        if ($rechnung->organisation_id !== $this->orgId()) abort(403);
    }
}
