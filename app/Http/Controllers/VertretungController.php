<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Einsatz;
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
            ->with('klient', 'leistungsart', 'tour')
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
            if ($vertretung && !$vertretung->darfLeistungsart($e->leistungsart_id)) {
                $einsaetzeWarnung->push($e);
            } else {
                $einsaetzeOk->push($e);
            }
        }

        return view('vertretung.vorschau', compact(
            'daten', 'einsaetzeOk', 'einsaetzeWarnung',
            'mitarbeiter', 'benutzer', 'vertretung'
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
                $anzahl++;
            }
        }

        return redirect()->route('vertretung.index')
            ->with('erfolg', $anzahl . ' Einsatz' . ($anzahl !== 1 ? 'ätze' : '') . ' auf die Vertretung übertragen.');
    }
}
