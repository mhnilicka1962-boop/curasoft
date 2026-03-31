<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Tour;
use App\Services\GeocodingService;
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
            ->with('benutzer', 'einsaetze.klient', 'einsaetze.einsatzLeistungsarten.leistungsart');

        if ($request->filled('benutzer_id')) {
            $query->where('benutzer_id', $request->benutzer_id);
        }

        if (auth()->user()->rolle === 'pflege') {
            $query->where('benutzer_id', auth()->id());
        }

        $touren      = $query->orderBy('start_zeit')->get();
        $mitarbeiter = Benutzer::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->where('anstellungsart', '!=', 'angehoerig')
            ->orderBy('nachname')->get();

        // Einsätze ohne Tour für diesen Tag (Lücken-Warnung) — Angehörige ausschliessen
        $ohneTouren = Einsatz::where('organisation_id', $this->orgId())
            ->whereDate('datum', $datum)
            ->whereNull('tour_id')
            ->where(fn($q) => $q->whereNotNull('serie_id')->orWhereNotNull('tagespauschale_id'))
            ->whereHas('benutzer', fn($q) => $q->where('anstellungsart', '!=', 'angehoerig'))
            ->with('klient', 'benutzer', 'einsatzLeistungsarten.leistungsart')
            ->orderBy('benutzer_id')
            ->orderBy('zeit_von')
            ->get()
            ->groupBy('benutzer_id');

        return view('touren.index', compact('touren', 'datum', 'mitarbeiter', 'ohneTouren'));
    }

    public function create(Request $request)
    {
        $mitarbeiter      = Benutzer::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->where('anstellungsart', '!=', 'angehoerig')
            ->orderBy('nachname')->get();
        $vorDatum         = $request->filled('datum') ? $request->datum : date('Y-m-d');
        $vorBenutzerId    = $request->filled('benutzer_id') ? (int) $request->benutzer_id : null;
        $vorBezeichnung   = $request->filled('bezeichnung') ? $request->bezeichnung : null;

        $verfuegbareEinsaetze = collect();
        if ($vorBenutzerId) {
            $verfuegbareEinsaetze = Einsatz::where('organisation_id', $this->orgId())
                ->whereDate('datum', $vorDatum)
                ->where('benutzer_id', $vorBenutzerId)
                ->whereNull('tour_id')
                ->with('klient', 'einsatzLeistungsarten.leistungsart')
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
            'start_zeit'    => ['required', 'date_format:H:i'],
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

        $tour->load('benutzer', 'einsaetze.klient', 'einsaetze.einsatzLeistungsarten.leistungsart');

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
            ->where(fn($q) => $q->whereNotNull('serie_id')->orWhereNotNull('tagespauschale_id'))
            ->with('klient', 'einsatzLeistungsarten.leistungsart')
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

        $kartenEinsaetze = $tour->einsaetze
            ->filter(fn($e) => $e->klient && $e->klient->klient_lat && $e->klient->klient_lng)
            ->sortBy('tour_reihenfolge')
            ->values();

        return view('touren.show', compact('tour', 'offeneEinsaetze', 'rapportZahlen', 'konflikteIds', 'kartenEinsaetze'));
    }

    public function update(Request $request, Tour $tour)
    {
        if ($tour->organisation_id !== $this->orgId()) abort(403);

        $daten = $request->validate([
            'bezeichnung' => ['required', 'string', 'max:200'],
            'status'      => ['required', 'in:geplant,gestartet,abgeschlossen'],
            'start_zeit'  => ['required', 'date_format:H:i'],
            'end_zeit'    => ['nullable', 'date_format:H:i'],
            'bemerkung'   => ['nullable', 'string', 'max:500'],
        ]);

        $tour->update($daten);
        return back()->with('erfolg', 'Tour wurde aktualisiert.');
    }

    public function zeitenSetzen(Tour $tour)
    {
        if ($tour->organisation_id !== $this->orgId()) abort(403);

        $start = $tour->start_zeit ?? '08:00:00';
        $current = \Carbon\Carbon::createFromFormat('H:i:s', strlen($start) === 5 ? $start . ':00' : $start);

        $einsaetze = $tour->einsaetze()->orderBy('tour_reihenfolge')->get();

        foreach ($einsaetze as $einsatz) {
            $minuten = $einsatz->minuten ?: 60;
            $von = $current->format('H:i');
            $current->addMinutes($minuten);
            $bis = $current->format('H:i');
            $einsatz->update(['zeit_von' => $von, 'zeit_bis' => $bis]);
        }

        return back()->with('erfolg', 'Zeiten wurden automatisch gesetzt.');
    }

    public function destroy(Tour $tour)
    {
        if ($tour->organisation_id !== $this->orgId()) abort(403);

        $datum = $tour->datum->format('Y-m-d');

        Einsatz::where('tour_id', $tour->id)->delete();

        $tour->delete();

        return redirect()->route('touren.index', ['datum' => $datum])
            ->with('erfolg', 'Tour wurde gelöscht.');
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

    // Reihenfolge per Drag & Drop speichern
    public function reihenfolgeAktualisieren(Tour $tour, Request $request)
    {
        if ($tour->organisation_id !== $this->orgId()) abort(403);

        $reihenfolge = $request->validate(['reihenfolge' => 'required|array'])['reihenfolge'];

        foreach ($reihenfolge as $i => $id) {
            Einsatz::where('id', $id)
                ->where('tour_id', $tour->id)
                ->update(['tour_reihenfolge' => $i + 1]);
        }

        return response()->json(['ok' => true]);
    }

    // Touren automatisch aus Einsätzen generieren (Reset + Neu)
    public function generieren(Request $request)
    {
        $datum = $request->validate(['datum' => ['required', 'date']])['datum'];

        // Bestehende Touren des Tages zurücksetzen — nur wenn kein Einsatz bereits gestartet/abgeschlossen
        $bestehende = Tour::where('organisation_id', $this->orgId())
            ->where('datum', $datum)
            ->get();
        foreach ($bestehende as $t) {
            $hatAktiveEinsaetze = $t->einsaetze()
                ->where(fn($q) => $q->whereNotNull('checkin_zeit')->orWhere('status', 'abgeschlossen'))
                ->exists();
            if ($hatAktiveEinsaetze) {
                return redirect()->route('touren.index', ['datum' => $datum])
                    ->with('fehler', 'Tour "' . $t->bezeichnung . '" kann nicht zurückgesetzt werden — ein Einsatz wurde bereits gestartet.');
            }
        }
        foreach ($bestehende as $t) {
            // Tagespauschalen: auch zeit_von/zeit_bis zurücksetzen (haben keine feste Zeit)
            $t->einsaetze()->whereNotNull('tagespauschale_id')->update(['tour_id' => null, 'tour_reihenfolge' => null, 'zeit_von' => null, 'zeit_bis' => null]);
            $t->einsaetze()->whereNull('tagespauschale_id')->update(['tour_id' => null, 'tour_reihenfolge' => null]);
            $t->delete();
        }

        $einsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->whereDate('datum', $datum)
            ->where(fn($q) => $q->whereNotNull('serie_id')->orWhereNotNull('tagespauschale_id'))
            ->whereHas('benutzer', fn($q) => $q->where('anstellungsart', '!=', 'angehoerig'))
            ->with('klient', 'benutzer')
            ->orderBy('benutzer_id')
            ->orderBy('zeit_von')
            ->get();

        if ($einsaetze->isEmpty()) {
            return redirect()->route('touren.index', ['datum' => $datum])
                ->with('warnung', 'Keine Einsätze für diesen Tag gefunden.');
        }

        $toursErstellt  = 0;
        $einsatzZaehler = 0;

        foreach ($einsaetze->groupBy('benutzer_id') as $benutzerId => $gruppe) {
            $benutzer = $gruppe->first()->benutzer;

            $fruehesteZeit = $gruppe->filter(fn($e) => $e->zeit_von)->min('zeit_von') ?? '08:00';

            $tour = Tour::firstOrCreate(
                ['organisation_id' => $this->orgId(), 'benutzer_id' => $benutzerId, 'datum' => $datum],
                [
                    'bezeichnung' => 'Tour ' . $benutzer->vorname . ' · ' . \Carbon\Carbon::parse($datum)->format('d.m.Y'),
                    'start_zeit'  => substr($fruehesteZeit, 0, 5),
                    'status'      => 'geplant',
                ]
            );

            if ($tour->wasRecentlyCreated) $toursErstellt++;

            $max = $tour->einsaetze()->max('tour_reihenfolge') ?? 0;
            $i   = 0;
            foreach ($gruppe as $einsatz) {
                $einsatz->update(['tour_id' => $tour->id, 'tour_reihenfolge' => $max + ++$i]);
                $einsatzZaehler++;
            }

            // Route optimieren — nur reguläre Einsätze (keine Tagespauschalen)
            $alleEinsaetze = $tour->einsaetze()->with('klient')->get();
            $regulaere     = $alleEinsaetze->whereNull('tagespauschale_id');
            $pauschalen    = $alleEinsaetze->whereNotNull('tagespauschale_id');

            $punkte = $regulaere
                ->filter(fn($e) => $e->klient?->klient_lat && $e->klient?->klient_lng)
                ->map(fn($e) => ['id' => $e->id, 'lat' => (float) $e->klient->klient_lat, 'lng' => (float) $e->klient->klient_lng])
                ->values()->toArray();

            if (count($punkte) >= 2) {
                $optimiert = GeocodingService::optimiereReihenfolge($punkte);
                foreach ($optimiert as $reihenfolge => $einsatzId) {
                    Einsatz::where('id', $einsatzId)->update(['tour_reihenfolge' => $reihenfolge + 1]);
                }
            }

            // Zeiten sequenziell neu berechnen — nur reguläre Einsätze
            $start   = $tour->start_zeit ?: '08:00:00';
            $current = \Carbon\Carbon::createFromFormat('H:i:s', strlen($start) === 5 ? $start . ':00' : $start);
            foreach ($regulaere->sortBy('tour_reihenfolge') as $einsatz) {
                $minuten = $einsatz->minuten ?: 60;
                $einsatz->update([
                    'zeit_von' => $current->format('H:i'),
                    'zeit_bis' => $current->copy()->addMinutes($minuten)->format('H:i'),
                ]);
                $current->addMinutes($minuten);
            }

            // Tagespauschalen ans Ende der Tour setzen (ohne Zeitberechnung)
            $maxReihenfolge = $regulaere->max('tour_reihenfolge') ?? 0;
            foreach ($pauschalen as $i => $einsatz) {
                $einsatz->update(['tour_reihenfolge' => $maxReihenfolge + $i + 1]);
            }
        }

        $meldung = $toursErstellt > 0
            ? "$toursErstellt Tour(en) erstellt mit $einsatzZaehler Einsätzen — Routen optimiert."
            : "$einsatzZaehler Einsätze zu bestehenden Touren hinzugefügt — Routen optimiert.";

        return redirect()->route('touren.index', ['datum' => $datum])->with('erfolg', $meldung);
    }

    // Route optimieren: Nearest-Neighbor nach GPS-Koordinaten
    public function routeOptimieren(Tour $tour)
    {
        if ($tour->organisation_id !== $this->orgId()) abort(403);

        $einsaetze = $tour->einsaetze()
            ->with('klient')
            ->orderBy('tour_reihenfolge')
            ->get();

        $punkte = $einsaetze
            ->filter(fn($e) => $e->klient?->klient_lat && $e->klient?->klient_lng)
            ->map(fn($e) => [
                'id'  => $e->id,
                'lat' => (float) $e->klient->klient_lat,
                'lng' => (float) $e->klient->klient_lng,
            ])->values()->toArray();

        if (count($punkte) < 2) {
            return back()->with('warnung', 'Zu wenige Einsätze mit Koordinaten für Optimierung (mind. 2 nötig).');
        }

        $optimiert = GeocodingService::optimiereReihenfolge($punkte);

        foreach ($optimiert as $reihenfolge => $einsatzId) {
            Einsatz::where('id', $einsatzId)->update(['tour_reihenfolge' => $reihenfolge + 1]);
        }

        // Zeiten sequenziell ab start_zeit neu berechnen
        $start   = $tour->start_zeit ?: '08:00:00';
        $current = \Carbon\Carbon::createFromFormat('H:i:s', strlen($start) === 5 ? $start . ':00' : $start);
        foreach ($tour->einsaetze()->orderBy('tour_reihenfolge')->get() as $einsatz) {
            $minuten = $einsatz->minuten ?: 60;
            $einsatz->update([
                'zeit_von' => $current->format('H:i'),
                'zeit_bis' => $current->copy()->addMinutes($minuten)->format('H:i'),
            ]);
            $current->addMinutes($minuten);
        }

        return back()->with('erfolg', count($optimiert) . ' Einsätze nach kürzester Route sortiert und Zeiten neu gesetzt.');
    }
}
