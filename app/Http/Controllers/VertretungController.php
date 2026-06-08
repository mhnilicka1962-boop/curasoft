<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Einsatz;
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

        return view('vertretung.index', compact('mitarbeiter'));
    }

    public function vorschau(Request $request)
    {
        $daten = $request->validate([
            'benutzer_id'   => ['required', 'exists:benutzer,id'],
            'datum_von'     => ['required', 'date'],
            'datum_bis'     => ['required', 'date', 'after_or_equal:datum_von'],
            'vertretung_id' => ['nullable', 'exists:benutzer,id'],
        ]);

        $einsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->where('benutzer_id', $daten['benutzer_id'])
            ->whereBetween('datum', [$daten['datum_von'], $daten['datum_bis']])
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
        $vertretung = !empty($daten['vertretung_id']) ? Benutzer::find($daten['vertretung_id']) : null;

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

        return view('vertretung.vorschau', compact(
            'daten', 'einsaetzeOk', 'einsaetzeWarnung',
            'mitarbeiter', 'benutzer', 'vertretung', 'konflikte'
        ));
    }

    public function ausfuehren(Request $request)
    {
        $daten = $request->validate([
            'einsatz_ids'   => ['required', 'array', 'min:1'],
            'einsatz_ids.*' => ['exists:einsaetze,id'],
            'vertretung_id' => ['required', 'exists:benutzer,id'],
        ]);

        $anzahl = 0;
        foreach ($daten['einsatz_ids'] as $id) {
            $einsatz = Einsatz::find($id);
            if ($einsatz
                && $einsatz->organisation_id === $this->orgId()
                && $einsatz->status === 'geplant'
            ) {
                $einsatz->update(['benutzer_id' => $daten['vertretung_id']]);
                $this->einsatzInTourVerschieben($einsatz);
                $anzahl++;
            }
        }

        return redirect()->route('vertretung.index')
            ->with('erfolg', $anzahl . ' Einsatz' . ($anzahl !== 1 ? 'ätze' : '') . ' auf die Vertretung übertragen.');
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
}
