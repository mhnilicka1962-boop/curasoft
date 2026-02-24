<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Tour;
use Illuminate\Http\Request;

class TourenController extends Controller
{
    private function orgId(): int { return auth()->user()->organisation_id; }

    public function index(Request $request)
    {
        $datum = $request->filled('datum')
            ? \Carbon\Carbon::parse($request->datum)
            : today();

        $query = Tour::where('organisation_id', $this->orgId())
            ->whereDate('datum', $datum)
            ->with('benutzer', 'einsaetze.klient', 'einsaetze.leistungsart');

        if ($request->filled('benutzer_id')) {
            $query->where('benutzer_id', $request->benutzer_id);
        }

        if (auth()->user()->rolle === 'pflege') {
            $query->where('benutzer_id', auth()->id());
        }

        $touren      = $query->orderBy('start_zeit')->get();
        $mitarbeiter = Benutzer::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();

        // Einsätze ohne Tour für diesen Tag (Lücken-Warnung)
        $ohneTouren = Einsatz::where('organisation_id', $this->orgId())
            ->whereDate('datum', $datum)
            ->whereNull('tour_id')
            ->with('klient', 'benutzer', 'leistungsart')
            ->orderBy('benutzer_id')
            ->orderBy('zeit_von')
            ->get()
            ->groupBy('benutzer_id');

        return view('touren.index', compact('touren', 'datum', 'mitarbeiter', 'ohneTouren'));
    }

    public function create(Request $request)
    {
        $mitarbeiter      = Benutzer::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();
        $vorDatum         = $request->filled('datum') ? $request->datum : date('Y-m-d');
        $vorBenutzerId    = $request->filled('benutzer_id') ? (int) $request->benutzer_id : null;
        $vorBezeichnung   = $request->filled('bezeichnung') ? $request->bezeichnung : null;

        $verfuegbareEinsaetze = collect();
        if ($vorBenutzerId) {
            $verfuegbareEinsaetze = Einsatz::where('organisation_id', $this->orgId())
                ->whereDate('datum', $vorDatum)
                ->where('benutzer_id', $vorBenutzerId)
                ->whereNull('tour_id')
                ->with('klient', 'leistungsart')
                ->orderBy('zeit_von')
                ->get();

            if (!$vorBezeichnung) {
                $ma = $mitarbeiter->firstWhere('id', $vorBenutzerId);
                $vorBezeichnung = $ma ? 'Tour ' . $ma->vorname . ' · ' . \Carbon\Carbon::parse($vorDatum)->format('d.m.Y') : null;
            }
        }

        return view('touren.create', compact('mitarbeiter', 'vorDatum', 'vorBenutzerId', 'vorBezeichnung', 'verfuegbareEinsaetze'));
    }

    public function store(Request $request)
    {
        $daten = $request->validate([
            'benutzer_id'   => ['required', 'exists:benutzer,id'],
            'datum'         => ['required', 'date'],
            'bezeichnung'   => ['required', 'string', 'max:200'],
            'start_zeit'    => ['nullable', 'date_format:H:i'],
            'bemerkung'     => ['nullable', 'string', 'max:500'],
            'einsatz_ids'   => ['nullable', 'array'],
            'einsatz_ids.*' => ['exists:einsaetze,id'],
        ]);

        $tour = Tour::create([
            'organisation_id' => $this->orgId(),
            'benutzer_id'     => $daten['benutzer_id'],
            'datum'           => $daten['datum'],
            'bezeichnung'     => $daten['bezeichnung'],
            'start_zeit'      => $daten['start_zeit'] ?? null,
            'bemerkung'       => $daten['bemerkung'] ?? null,
            'status'          => 'geplant',
        ]);

        foreach ($daten['einsatz_ids'] ?? [] as $i => $einsatzId) {
            $einsatz = Einsatz::find($einsatzId);
            if ($einsatz && $einsatz->organisation_id === $this->orgId()) {
                $einsatz->update(['tour_id' => $tour->id, 'tour_reihenfolge' => $i + 1]);
            }
        }

        return redirect()->route('touren.show', $tour)->with('erfolg', 'Tour wurde erstellt.');
    }

    public function show(Tour $tour)
    {
        if ($tour->organisation_id !== $this->orgId()) abort(403);

        $tour->load('benutzer', 'einsaetze.klient', 'einsaetze.leistungsart');

        $einsatzIds    = $tour->einsaetze->pluck('id');
        $rapportZahlen = \Illuminate\Support\Facades\DB::table('rapporte')
            ->whereIn('einsatz_id', $einsatzIds)
            ->selectRaw('einsatz_id, COUNT(*) as anzahl')
            ->groupBy('einsatz_id')
            ->pluck('anzahl', 'einsatz_id');

        $offeneEinsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->whereDate('datum', $tour->datum)
            ->where('benutzer_id', $tour->benutzer_id)
            ->whereNull('tour_id')
            ->with('klient', 'leistungsart')
            ->orderBy('zeit_von')
            ->get();

        // Zeitkonflikt-Erkennung: Einsätze mit überlappenden geplanten Zeiten
        $konflikteIds = collect();
        $mitZeit = $tour->einsaetze->filter(fn($e) => $e->zeit_von && $e->zeit_bis)->values();
        for ($i = 0; $i < $mitZeit->count(); $i++) {
            for ($j = $i + 1; $j < $mitZeit->count(); $j++) {
                $a = $mitZeit[$i];
                $b = $mitZeit[$j];
                $aVon = \Carbon\Carbon::parse($a->zeit_von);
                $aBis = \Carbon\Carbon::parse($a->zeit_bis);
                $bVon = \Carbon\Carbon::parse($b->zeit_von);
                $bBis = \Carbon\Carbon::parse($b->zeit_bis);
                if ($aVon < $bBis && $aBis > $bVon) {
                    $konflikteIds->push($a->id);
                    $konflikteIds->push($b->id);
                }
            }
        }
        $konflikteIds = $konflikteIds->unique()->values();

        return view('touren.show', compact('tour', 'offeneEinsaetze', 'rapportZahlen', 'konflikteIds'));
    }

    public function update(Request $request, Tour $tour)
    {
        if ($tour->organisation_id !== $this->orgId()) abort(403);

        $daten = $request->validate([
            'bezeichnung' => ['required', 'string', 'max:200'],
            'status'      => ['required', 'in:geplant,gestartet,abgeschlossen'],
            'start_zeit'  => ['nullable', 'date_format:H:i'],
            'end_zeit'    => ['nullable', 'date_format:H:i'],
            'bemerkung'   => ['nullable', 'string', 'max:500'],
        ]);

        $tour->update($daten);
        return back()->with('erfolg', 'Tour wurde aktualisiert.');
    }

    public function einsatzZuweisen(Request $request, Tour $tour)
    {
        if ($tour->organisation_id !== $this->orgId()) abort(403);

        $request->validate([
            'einsatz_ids'   => ['required', 'array', 'min:1'],
            'einsatz_ids.*' => ['exists:einsaetze,id'],
        ]);

        $max = $tour->einsaetze()->max('tour_reihenfolge') ?? 0;
        $i   = 0;
        foreach ($request->einsatz_ids as $einsatzId) {
            $einsatz = Einsatz::find($einsatzId);
            if ($einsatz && $einsatz->organisation_id === $this->orgId() && !$einsatz->tour_id) {
                $einsatz->update(['tour_id' => $tour->id, 'tour_reihenfolge' => $max + ++$i]);
            }
        }

        // Zeitkonflikte nach Zuweisung prüfen
        $tour->load('einsaetze');
        $mitZeit = $tour->einsaetze->filter(fn($e) => $e->zeit_von && $e->zeit_bis)->values();
        $hatKonflikt = false;
        for ($x = 0; $x < $mitZeit->count() && !$hatKonflikt; $x++) {
            for ($y = $x + 1; $y < $mitZeit->count() && !$hatKonflikt; $y++) {
                $a = $mitZeit[$x]; $b = $mitZeit[$y];
                if (\Carbon\Carbon::parse($a->zeit_von) < \Carbon\Carbon::parse($b->zeit_bis) &&
                    \Carbon\Carbon::parse($a->zeit_bis) > \Carbon\Carbon::parse($b->zeit_von)) {
                    $hatKonflikt = true;
                }
            }
        }

        $meldung = $i . ' Einsatz' . ($i !== 1 ? 'ätze' : '') . ' der Tour zugewiesen.';
        if ($hatKonflikt) {
            return back()->with('erfolg', $meldung)->with('warnung', 'Achtung: Es gibt Zeitüberschneidungen in dieser Tour.');
        }
        return back()->with('erfolg', $meldung);
    }

    public function einsatzEntfernen(Tour $tour, Einsatz $einsatz)
    {
        if ($tour->organisation_id !== $this->orgId()) abort(403);
        if ($einsatz->tour_id !== $tour->id) abort(403);

        $einsatz->update(['tour_id' => null, 'tour_reihenfolge' => null]);
        return back()->with('erfolg', 'Einsatz wurde aus der Tour entfernt.');
    }
}
