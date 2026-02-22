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

        return view('touren.index', compact('touren', 'datum', 'mitarbeiter'));
    }

    public function create()
    {
        $mitarbeiter = Benutzer::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();
        return view('touren.create', compact('mitarbeiter'));
    }

    public function store(Request $request)
    {
        $daten = $request->validate([
            'benutzer_id' => ['required', 'exists:benutzer,id'],
            'datum'       => ['required', 'date'],
            'bezeichnung' => ['required', 'string', 'max:200'],
            'start_zeit'  => ['nullable', 'date_format:H:i'],
            'bemerkung'   => ['nullable', 'string', 'max:500'],
        ]);

        $tour = Tour::create(array_merge($daten, [
            'organisation_id' => $this->orgId(),
            'status'          => 'geplant',
        ]));

        return redirect()->route('touren.show', $tour)
            ->with('erfolg', 'Tour wurde erstellt.');
    }

    public function show(Tour $tour)
    {
        if ($tour->organisation_id !== $this->orgId()) abort(403);

        $tour->load('benutzer', 'einsaetze.klient', 'einsaetze.leistungsart');

        // Nicht zugewiesene Einsätze dieses Tages für diesen Mitarbeiter
        $offeneEinsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->whereDate('datum', $tour->datum)
            ->where('benutzer_id', $tour->benutzer_id)
            ->whereNull('tour_id')
            ->with('klient', 'leistungsart')
            ->orderBy('zeit_von')
            ->get();

        return view('touren.show', compact('tour', 'offeneEinsaetze'));
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
            'einsatz_id'       => ['required', 'exists:einsaetze,id'],
            'tour_reihenfolge' => ['nullable', 'integer', 'min:1'],
        ]);

        $einsatz = Einsatz::findOrFail($request->einsatz_id);
        if ($einsatz->organisation_id !== $this->orgId()) abort(403);

        $reihenfolge = $request->tour_reihenfolge
            ?? ($tour->einsaetze()->max('tour_reihenfolge') + 1);

        $einsatz->update([
            'tour_id'          => $tour->id,
            'tour_reihenfolge' => $reihenfolge,
        ]);

        return back()->with('erfolg', 'Einsatz wurde der Tour zugewiesen.');
    }

    public function einsatzEntfernen(Tour $tour, Einsatz $einsatz)
    {
        if ($tour->organisation_id !== $this->orgId()) abort(403);
        if ($einsatz->tour_id !== $tour->id) abort(403);

        $einsatz->update(['tour_id' => null, 'tour_reihenfolge' => null]);
        return back()->with('erfolg', 'Einsatz wurde aus der Tour entfernt.');
    }
}
