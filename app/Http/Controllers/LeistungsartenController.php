<?php

namespace App\Http\Controllers;

use App\Models\Leistungsart;
use App\Models\Leistungsregion;
use App\Models\Region;
use Illuminate\Http\Request;

class LeistungsartenController extends Controller
{
    public function index()
    {
        $leistungsarten = Leistungsart::withCount('tarife')
            ->orderBy('bezeichnung')
            ->get();
        return view('stammdaten.leistungsarten.index', compact('leistungsarten'));
    }

    public function create()
    {
        return view('stammdaten.leistungsarten.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bezeichnung'         => ['required', 'string', 'max:100'],
            'einheit'             => ['required', 'in:minuten,stunden,tage'],
            'gueltig_ab'          => ['nullable', 'date'],
            'gueltig_bis'         => ['nullable', 'date'],
            'ansatz_default'      => ['nullable', 'numeric', 'min:0'],
            'kvg_default'         => ['nullable', 'numeric', 'min:0'],
            'ansatz_akut_default' => ['nullable', 'numeric', 'min:0'],
            'kvg_akut_default'    => ['nullable', 'numeric', 'min:0'],
        ]);

        Leistungsart::create([
            'bezeichnung'         => $request->bezeichnung,
            'einheit'             => $request->einheit,
            'kassenpflichtig'     => $request->boolean('kassenpflichtig', true),
            'aktiv'               => true,
            'gueltig_ab'          => $request->gueltig_ab,
            'gueltig_bis'         => $request->gueltig_bis,
            'ansatz_default'      => $request->ansatz_default ?? 0,
            'kvg_default'         => $request->kvg_default ?? 0,
            'ansatz_akut_default' => $request->ansatz_akut_default ?? 0,
            'kvg_akut_default'    => $request->kvg_akut_default ?? 0,
        ]);

        return redirect()->route('leistungsarten.index')
            ->with('erfolg', 'Leistungsart wurde erstellt.');
    }

    public function show(Leistungsart $leistungsart)
    {
        $tarife   = $leistungsart->tarife()->with('region')->orderBy('region_id')->orderByDesc('gueltig_ab')->get();
        $regionen = Region::orderBy('kuerzel')->get();
        return view('stammdaten.leistungsarten.show', compact('leistungsart', 'tarife', 'regionen'));
    }

    public function tarifeBearbeiten(Leistungsart $leistungsart, Leistungsregion $tarif)
    {
        return view('stammdaten.leistungsarten.tarif_edit', compact('leistungsart', 'tarif'));
    }

    public function tarifeAktualisieren(Request $request, Leistungsart $leistungsart, Leistungsregion $tarif)
    {
        $request->validate([
            'ansatz'          => ['required', 'numeric', 'min:0'],
            'kkasse'          => ['required', 'numeric', 'min:0'],
            'ansatz_akut'     => ['required', 'numeric', 'min:0'],
            'kkasse_akut'     => ['required', 'numeric', 'min:0'],
            'gueltig_ab'      => ['nullable', 'date'],
        ]);

        $tarif->update([
            'ansatz'           => $request->ansatz,
            'kkasse'           => $request->kkasse,
            'ansatz_akut'      => $request->ansatz_akut,
            'kkasse_akut'      => $request->kkasse_akut,
            'gueltig_ab'       => $request->gueltig_ab,
            'verrechnung'      => $request->boolean('verrechnung', true),
            'einsatz_minuten'  => $request->boolean('einsatz_minuten'),
            'einsatz_stunden'  => $request->boolean('einsatz_stunden'),
            'einsatz_tage'     => $request->boolean('einsatz_tage'),
            'mwst'             => $request->boolean('mwst'),
        ]);

        return redirect()->route('leistungsarten.show', $leistungsart)
            ->with('erfolg', "Tarif {$tarif->region->kuerzel} gespeichert.");
    }

    public function edit(Leistungsart $leistungsart)
    {
        return view('stammdaten.leistungsarten.edit', compact('leistungsart'));
    }

    public function update(Request $request, Leistungsart $leistungsart)
    {
        $request->validate([
            'bezeichnung'         => ['required', 'string', 'max:100'],
            'einheit'             => ['required', 'in:minuten,stunden,tage'],
            'gueltig_ab'          => ['nullable', 'date'],
            'gueltig_bis'         => ['nullable', 'date'],
            'ansatz_default'      => ['nullable', 'numeric', 'min:0'],
            'kvg_default'         => ['nullable', 'numeric', 'min:0'],
            'ansatz_akut_default' => ['nullable', 'numeric', 'min:0'],
            'kvg_akut_default'    => ['nullable', 'numeric', 'min:0'],
            'tarmed_code'         => ['nullable', 'string', 'max:20'],
        ]);

        $leistungsart->update([
            'bezeichnung'         => $request->bezeichnung,
            'einheit'             => $request->einheit,
            'kassenpflichtig'     => $request->boolean('kassenpflichtig', true),
            'aktiv'               => $request->boolean('aktiv', true),
            'gueltig_ab'          => $request->gueltig_ab,
            'gueltig_bis'         => $request->gueltig_bis,
            'ansatz_default'      => $request->ansatz_default ?? 0,
            'kvg_default'         => $request->kvg_default ?? 0,
            'ansatz_akut_default' => $request->ansatz_akut_default ?? 0,
            'kvg_akut_default'    => $request->kvg_akut_default ?? 0,
            'tarmed_code'         => $request->tarmed_code ?: null,
        ]);

        return redirect()->route('leistungsarten.show', $leistungsart)
            ->with('erfolg', 'Leistungsart wurde aktualisiert.');
    }

    public function tarifSpeichern(Request $request, Leistungsart $leistungsart)
    {
        $request->validate([
            'region_id'       => ['required', 'exists:regionen,id'],
            'ansatz'          => ['required', 'numeric', 'min:0'],
            'kkasse'          => ['required', 'numeric', 'min:0'],
            'ansatz_akut'     => ['nullable', 'numeric', 'min:0'],
            'kkasse_akut'     => ['nullable', 'numeric', 'min:0'],
            'gueltig_ab'      => ['nullable', 'date'],
        ]);

        Leistungsregion::create([
            'leistungsart_id'  => $leistungsart->id,
            'region_id'        => $request->region_id,
            'ansatz'           => $request->ansatz,
            'kkasse'           => $request->kkasse,
            'ansatz_akut'      => $request->ansatz_akut ?? 0,
            'kkasse_akut'      => $request->kkasse_akut ?? 0,
            'kassenpflichtig'  => $request->boolean('kassenpflichtig', $leistungsart->kassenpflichtig),
            'gueltig_ab'       => $request->gueltig_ab,
            'verrechnung'      => true,
            'einsatz_minuten'  => false,
            'einsatz_stunden'  => true,
            'einsatz_tage'     => false,
            'mwst'             => false,
        ]);

        return back()->with('erfolg', 'Tarif wurde gespeichert.');
    }

    public function tarifLoeschen(Leistungsart $leistungsart, Region $region)
    {
        Leistungsregion::where('leistungsart_id', $leistungsart->id)
            ->where('region_id', $region->id)
            ->delete();

        return back()->with('erfolg', 'Tarif wurde gelöscht.');
    }
}
