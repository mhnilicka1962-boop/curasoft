<?php

namespace App\Http\Controllers;

use App\Models\Leistungsart;
use App\Models\Leistungsregion;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionenController extends Controller
{
    public function index()
    {
        $regionen = Region::withCount('tarife')->orderBy('kuerzel')->get();
        return view('stammdaten.regionen.index', compact('regionen'));
    }

    public function show(Region $region)
    {
        $tarife = $region->tarife()->with('leistungsart')->orderBy('leistungsart_id')->get();
        return view('stammdaten.regionen.show', compact('region', 'tarife'));
    }

    public function tarifSpeichern(Request $request, Region $region)
    {
        $request->validate([
            'leistungsart_id'  => ['required', 'exists:leistungsarten,id'],
            'gueltig_ab'       => ['required', 'date'],
            'ansatz'           => ['required', 'numeric', 'min:0'],
            'kkasse'           => ['required', 'numeric', 'min:0'],
            'ansatz_akut'      => ['required', 'numeric', 'min:0'],
            'kkasse_akut'      => ['required', 'numeric', 'min:0'],
        ]);

        Leistungsregion::create([
            'leistungsart_id'  => $request->leistungsart_id,
            'region_id'        => $region->id,
            'gueltig_ab'       => $request->gueltig_ab,
            'ansatz'           => $request->ansatz,
            'kkasse'           => $request->kkasse,
            'ansatz_akut'      => $request->ansatz_akut,
            'kkasse_akut'      => $request->kkasse_akut,
            'kassenpflichtig'  => $request->boolean('kassenpflichtig', true),
            'verrechnung'      => true,
            'einsatz_minuten'  => $request->boolean('einsatz_minuten'),
            'einsatz_stunden'  => $request->boolean('einsatz_stunden', true),
            'einsatz_tage'     => $request->boolean('einsatz_tage'),
            'mwst'             => $request->boolean('mwst'),
        ]);

        return back()->with('erfolg', 'Neuer Tarif-Eintrag gespeichert.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kuerzel'     => ['required', 'string', 'max:4', 'unique:regionen,kuerzel'],
            'bezeichnung' => ['required', 'string', 'max:100'],
        ]);

        $region = Region::create($request->only('kuerzel', 'bezeichnung'));

        // Auto-Copy: alle aktiven Leistungsarten mit Default-Ansätzen eintragen
        $leistungsarten = Leistungsart::where('aktiv', true)->get();
        foreach ($leistungsarten as $la) {
            Leistungsregion::firstOrCreate(
                ['leistungsart_id' => $la->id, 'region_id' => $region->id],
                [
                    'ansatz'          => $la->ansatz_default,
                    'kkasse'          => $la->kvg_default,
                    'ansatz_akut'     => $la->ansatz_akut_default,
                    'kkasse_akut'     => $la->kvg_akut_default,
                    'kassenpflichtig' => $la->kassenpflichtig,
                    'gueltig_ab'      => today(),
                    'verrechnung'     => true,
                    'einsatz_minuten' => false,
                    'einsatz_stunden' => true,
                    'einsatz_tage'    => false,
                    'mwst'            => false,
                ]
            );
        }

        return back()->with('erfolg', "Region/Kanton «{$region->kuerzel}» erstellt — {$leistungsarten->count()} Leistungsarten automatisch angelegt.");
    }

    public function update(Request $request, Region $region)
    {
        $request->validate([
            'bezeichnung' => ['required', 'string', 'max:100'],
        ]);

        $region->update(['bezeichnung' => $request->bezeichnung]);
        return back()->with('erfolg', 'Region wurde aktualisiert.');
    }

    public function initialisieren(Region $region)
    {
        $leistungsarten = Leistungsart::where('aktiv', true)->get();
        $neu = 0;

        foreach ($leistungsarten as $la) {
            $existiert = Leistungsregion::where('leistungsart_id', $la->id)
                ->where('region_id', $region->id)
                ->exists();

            if (!$existiert) {
                Leistungsregion::create([
                    'leistungsart_id'  => $la->id,
                    'region_id'        => $region->id,
                    'ansatz'           => $la->ansatz_default,
                    'kkasse'           => $la->kvg_default,
                    'ansatz_akut'      => $la->ansatz_akut_default,
                    'kkasse_akut'      => $la->kvg_akut_default,
                    'kassenpflichtig'  => $la->kassenpflichtig,
                    'gueltig_ab'       => today(),
                    'verrechnung'      => true,
                    'einsatz_minuten'  => false,
                    'einsatz_stunden'  => true,
                    'einsatz_tage'     => false,
                    'mwst'             => false,
                ]);
                $neu++;
            }
        }

        $msg = $neu > 0
            ? "{$neu} Leistungsart(en) mit Standard-Tarifen initialisiert."
            : "Alle Leistungsarten bereits konfiguriert — nichts geändert.";

        return back()->with('erfolg', $msg);
    }

    public function destroy(Region $region)
    {
        if ($region->tarife()->exists()) {
            return back()->withErrors(['fehler' => "Region «{$region->kuerzel}» kann nicht gelöscht werden — Tarife sind zugeordnet."]);
        }

        $region->delete();
        return back()->with('erfolg', 'Region wurde gelöscht.');
    }
}
