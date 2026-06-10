<?php

namespace App\Http\Controllers;

use App\Models\Abwesenheit;
use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Serie;
use App\Models\Tour;
use Illuminate\Http\Request;

class VertretungController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    public function index()
    {
        $mitarbeiter = Benutzer::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->where('anstellungsart', '!=', 'angehoerig')
            ->orderBy('nachname')
            ->get();

        $abwesenheiten = Abwesenheit::where('organisation_id', $this->orgId())
            ->where('datum_bis', '>=', today())
            ->with('benutzer')
            ->orderBy('datum_von')
            ->get();

        return view('vertretung.index', compact('mitarbeiter', 'abwesenheiten'));
    }

    public function archiv(Request $request)
    {
        $suche = trim($request->input('q', ''));

        $abwesenheiten = Abwesenheit::where('organisation_id', $this->orgId())
            ->where('datum_bis', '<', today())
            ->with('benutzer', 'vertretung')
            ->orderByDesc('datum_bis')
            ->get()
            ->map(function ($abw) {
                $serieIds = Serie::where('benutzer_id', $abw->benutzer_id)
                    ->where('organisation_id', $abw->organisation_id)
                    ->pluck('id');
                $einsaetze = Einsatz::whereIn('serie_id', $serieIds)
                    ->whereBetween('datum', [$abw->datum_von, $abw->datum_bis])
                    ->with('klient')
                    ->get();
                $abw->anzahl_einsaetze = $einsaetze->count();
                $abw->klienten = $einsaetze
                    ->pluck('klient')
                    ->filter()
                    ->unique('id')
                    ->map(fn($k) => $k->vorname . ' ' . $k->nachname)
                    ->sort()
                    ->values();
                return $abw;
            })
            ->when($suche !== '', function ($col) use ($suche) {
                $s = mb_strtolower($suche);
                return $col->filter(function ($abw) use ($s) {
                    $maName = mb_strtolower($abw->benutzer->vorname . ' ' . $abw->benutzer->nachname);
                    if (str_contains($maName, $s)) return true;
                    foreach ($abw->klienten as $k) {
                        if (str_contains(mb_strtolower($k), $s)) return true;
                    }
                    return false;
                })->values();
            });

        return view('vertretung.archiv', compact('abwesenheiten', 'suche'));
    }

    public function erstellen()
    {
        $mitarbeiter = Benutzer::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->where('anstellungsart', '!=', 'angehoerig')
            ->orderBy('nachname')
            ->get();

        return view('vertretung.erstellen', compact('mitarbeiter'));
    }

    public function abwesenheitSpeichern(Request $request)
    {
        $daten = $request->validate([
            'benutzer_id'  => ['required', 'exists:benutzer,id'],
            'vertretung_id'=> ['nullable', 'exists:benutzer,id'],
            'datum_von'    => ['required', 'date'],
            'datum_bis'    => ['required', 'date', 'after_or_equal:datum_von'],
        ]);

        Abwesenheit::create([
            'organisation_id' => $this->orgId(),
            'benutzer_id'     => $daten['benutzer_id'],
            'vertretung_id'   => $daten['vertretung_id'] ?? null,
            'datum_von'       => $daten['datum_von'],
            'datum_bis'       => $daten['datum_bis'],
        ]);

        // Wenn Standardvertretung gewählt → alle Einsätze ab heute sofort übertragen
        $anzahl = 0;
        if (!empty($daten['vertretung_id'])) {
            $einsaetze = Einsatz::where('organisation_id', $this->orgId())
                ->where('benutzer_id', $daten['benutzer_id'])
                ->whereBetween('datum', [max($daten['datum_von'], today()->format('Y-m-d')), $daten['datum_bis']])
                ->where('status', 'geplant')
                ->get();

            foreach ($einsaetze as $einsatz) {
                $alteTourId = $einsatz->tour_id;
                $einsatz->update(['benutzer_id' => $daten['vertretung_id']]);
                $this->einsatzInTourVerschieben($einsatz);
                $this->leereTourLoeschen($alteTourId);
                $anzahl++;
            }
        }

        $msg = $anzahl > 0
            ? "Vertretung erfasst — {$anzahl} Einsätze automatisch übertragen."
            : 'Vertretung erfasst — bitte Einsätze im Detail übertragen.';

        return redirect()->route('vertretung.vorschau.get', [
            'benutzer_id' => $daten['benutzer_id'],
            'datum_von'   => $daten['datum_von'],
            'datum_bis'   => $daten['datum_bis'],
        ])->with('erfolg', $msg);
    }

    public function abwesenheitLoeschen(Abwesenheit $abwesenheit)
    {
        abort_if($abwesenheit->organisation_id !== $this->orgId(), 403);

        // Zukünftige Einsätze aus den Serien der abwesenden Person zurück auf diese Person
        $serieIds = Serie::where('benutzer_id', $abwesenheit->benutzer_id)
            ->where('organisation_id', $abwesenheit->organisation_id)
            ->pluck('id');

        $zurueck = Einsatz::whereIn('serie_id', $serieIds)
            ->where('organisation_id', $abwesenheit->organisation_id)
            ->whereBetween('datum', [$abwesenheit->datum_von, $abwesenheit->datum_bis])
            ->where('datum', '>=', today())
            ->where('benutzer_id', '!=', $abwesenheit->benutzer_id)
            ->where('status', 'geplant')
            ->get();

        foreach ($zurueck as $einsatz) {
            $alteTourId = $einsatz->tour_id;
            $einsatz->update(['benutzer_id' => $abwesenheit->benutzer_id]);
            $this->einsatzInTourVerschieben($einsatz);
            $this->leereTourLoeschen($alteTourId);
        }

        $name = optional(\App\Models\Benutzer::find($abwesenheit->benutzer_id))->vorname;
        $abwesenheit->delete();

        return back()->with('erfolg', 'Vertretung gelöscht — ' . $zurueck->count() . ' zukünftige Einsätze zurück auf ' . $name . '.');
    }

    public function vorschau(Request $request)
    {
        $daten = $request->validate([
            'benutzer_id'   => ['required', 'exists:benutzer,id'],
            'datum_von'     => ['required', 'date'],
            'datum_bis'     => ['required', 'date', 'after_or_equal:datum_von'],
            'vertretung_id' => ['nullable', 'exists:benutzer,id'],
        ]);

        $mitVergangenheit = $request->boolean('mit_vergangenheit');
        $vonEffektiv      = $mitVergangenheit ? $daten['datum_von'] : max($daten['datum_von'], today()->format('Y-m-d'));

        $einsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->where('benutzer_id', $daten['benutzer_id'])
            ->whereBetween('datum', [$vonEffektiv, $daten['datum_bis']])
            ->where('status', 'geplant')
            ->with('klient', 'einsatzLeistungsarten.leistungsart', 'tour')
            ->orderBy('datum')
            ->orderBy('zeit_von')
            ->get();

        $mitarbeiter = Benutzer::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->where('anstellungsart', '!=', 'angehoerig')
            ->orderBy('nachname')
            ->get();

        $benutzer   = Benutzer::find($daten['benutzer_id']);

        // Vertretung: explizit übergeben, sonst aus abwesenheiten-Eintrag
        $vertretungId = $daten['vertretung_id'] ?? null;
        if (!$vertretungId) {
            $abwesenheit  = Abwesenheit::where('organisation_id', $this->orgId())
                ->where('benutzer_id', $daten['benutzer_id'])
                ->where('datum_von', '<=', $daten['datum_bis'])
                ->where('datum_bis', '>=', $daten['datum_von'])
                ->whereNotNull('vertretung_id')
                ->first();
            $vertretungId = $abwesenheit?->vertretung_id;
        }
        $vertretung = $vertretungId ? Benutzer::find($vertretungId) : null;

        // Qualifikationsprüfung
        $einsaetzeOk       = collect();
        $einsaetzeWarnung  = collect();

        foreach ($einsaetze as $e) {
            if ($vertretung && $e->einsatzLeistungsarten->contains(fn($el) => !$vertretung->darfLeistungsart($el->leistungsart_id))) {
                $einsaetzeWarnung->push($e);
            } else {
                $einsaetzeOk->push($e);
            }
        }

        // Zeitkonflikt: übertragbarer Einsatz überlappt mit einem bestehenden
        // Einsatz der Vertretung am selben Tag (gleiche Formel wie Kalender/Tour).
        // Weicher Hinweis — übertragbar bleibt es trotzdem.
        $konflikte = [];
        if ($vertretung) {
            $eigene = Einsatz::where('organisation_id', $this->orgId())
                ->where('benutzer_id', $vertretung->id)
                ->whereBetween('datum', [$daten['datum_von'], $daten['datum_bis']])
                ->where('status', 'geplant')
                ->whereNotNull('zeit_von')
                ->whereNotNull('zeit_bis')
                ->with('klient')
                ->get();

            foreach ($einsaetzeOk as $e) {
                if (!$e->zeit_von || !$e->zeit_bis) continue;
                foreach ($eigene as $x) {
                    if ($x->datum->format('Y-m-d') !== $e->datum->format('Y-m-d')) continue;
                    if ($e->zeit_von < $x->zeit_bis && $x->zeit_von < $e->zeit_bis) {
                        $konflikte[$e->id] = substr($x->zeit_von, 0, 5) . '–' . substr($x->zeit_bis, 0, 5)
                            . ($x->klient ? ' · ' . $x->klient->vollname() : '');
                        break;
                    }
                }
            }
        }

        // Bereits übertragene Einsätze: aus Sandras Serien, jetzt bei jemand anderem
        $serieIds = Serie::where('benutzer_id', $benutzer->id)
            ->where('organisation_id', $this->orgId())
            ->pluck('id');

        $bereitsUebertragen = Einsatz::whereIn('serie_id', $serieIds)
            ->where('organisation_id', $this->orgId())
            ->whereBetween('datum', [$vonEffektiv, $daten['datum_bis']])
            ->where('benutzer_id', '!=', $benutzer->id)
            ->where('status', 'geplant')
            ->with('klient', 'benutzer', 'einsatzLeistungsarten.leistungsart')
            ->orderBy('datum')
            ->orderBy('zeit_von')
            ->get()
            ->each(fn($e) => $e->bereits_uebertragen = true);

        // Einheitliche Liste: offen + erledigt, nach Datum/Zeit sortiert
        $alleEinsaetze = $einsaetzeOk
            ->each(fn($e) => $e->bereits_uebertragen = false)
            ->concat($bereitsUebertragen)
            ->sortBy([['datum', 'asc'], ['zeit_von', 'asc']])
            ->values();

        return view('vertretung.vorschau', compact(
            'daten', 'alleEinsaetze', 'einsaetzeWarnung',
            'mitarbeiter', 'benutzer', 'vertretung', 'konflikte', 'mitVergangenheit'
        ));
    }

    public function ausfuehren(Request $request)
    {
        $daten = $request->validate([
            'einsatz_ids'     => ['required', 'array', 'min:1'],
            'einsatz_ids.*'   => ['exists:einsaetze,id'],
            'vertretung_ids'  => ['required', 'array'],
            'vertretung_ids.*'=> ['exists:benutzer,id'],
        ]);

        $anzahl = 0;
        foreach ($daten['einsatz_ids'] as $id) {
            $vertretungId = $daten['vertretung_ids'][$id] ?? null;
            if (!$vertretungId) continue;
            $einsatz = Einsatz::find($id);
            if ($einsatz
                && $einsatz->organisation_id === $this->orgId()
                && $einsatz->status === 'geplant'
            ) {
                $alteTourId = $einsatz->tour_id;
                $einsatz->update(['benutzer_id' => $vertretungId]);
                $this->einsatzInTourVerschieben($einsatz);
                $this->leereTourLoeschen($alteTourId);
                $anzahl++;
            }
        }

        $params = $request->only(['benutzer_id', 'datum_von', 'datum_bis']);

        return redirect()->route('vertretung.vorschau.get', $params)
            ->with('erfolg', $anzahl . ' Einsatz' . ($anzahl !== 1 ? 'ätze' : '') . ' übertragen.');
    }

    /**
     * Einsatz in die Tour der (neuen) zuständigen Person am Einsatz-Tag legen.
     * Ohne diesen Schritt bliebe der Einsatz in der Tour des Abwesenden und
     * würde der Vertretung in ihrer Tagesansicht nicht angezeigt.
     * Logik gespiegelt von EinsaetzeGenerieren::einsatzZurTourZuweisen.
     */
    private function einsatzInTourVerschieben(Einsatz $einsatz): void
    {
        $datum = $einsatz->datum->format('Y-m-d');

        $tour = Tour::where('organisation_id', $einsatz->organisation_id)
            ->where('benutzer_id', $einsatz->benutzer_id)
            ->whereDate('datum', $datum)
            ->first();

        if (!$tour) {
            $ma = Benutzer::find($einsatz->benutzer_id);
            $tour = Tour::create([
                'organisation_id' => $einsatz->organisation_id,
                'benutzer_id'     => $einsatz->benutzer_id,
                'datum'           => $datum,
                'bezeichnung'     => 'Tour ' . ($ma?->vorname ?? '') . ' · ' . $einsatz->datum->format('d.m.Y'),
                'start_zeit'      => $einsatz->zeit_von ?? '08:00:00',
                'status'          => 'geplant',
            ]);
        }

        $max = $tour->einsaetze()->max('tour_reihenfolge') ?? 0;
        $einsatz->update([
            'tour_id'          => $tour->id,
            'tour_reihenfolge' => $max + 1,
        ]);
    }

    private function leereTourLoeschen(?int $tourId): void
    {
        if (!$tourId) return;
        $tour = Tour::find($tourId);
        if ($tour && $tour->einsaetze()->count() === 0) {
            $tour->delete();
        }
    }
}
