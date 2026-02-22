<?php

namespace App\Http\Controllers;

use App\Models\Leistungsart;
use App\Models\Leistungstyp;
use Illuminate\Http\Request;

class EinsatzartenController extends Controller
{
    public function index(Request $request)
    {
        $leistungsarten = Leistungsart::where('aktiv', true)->orderBy('bezeichnung')->get();

        $query = Leistungstyp::with('leistungsart')->orderBy('leistungsart_id');

        if ($request->filled('leistungsart_id')) {
            $query->where('leistungsart_id', $request->leistungsart_id);
        }

        $einsatzarten = $query->orderBy('bezeichnung')->get();

        return view('stammdaten.einsatzarten.index', compact('einsatzarten', 'leistungsarten'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bezeichnung'     => ['required', 'string', 'max:100'],
            'leistungsart_id' => ['required', 'exists:leistungsarten,id'],
            'gueltig_ab'      => ['nullable', 'date'],
            'gueltig_bis'     => ['nullable', 'date'],
        ]);

        Leistungstyp::create([
            'bezeichnung'     => $request->bezeichnung,
            'leistungsart_id' => $request->leistungsart_id,
            'gueltig_ab'      => $request->gueltig_ab,
            'gueltig_bis'     => $request->gueltig_bis,
            'aktiv'           => true,
        ]);

        return back()->with('erfolg', 'Einsatzart wurde erstellt.');
    }

    public function edit(Leistungstyp $einsatzart)
    {
        $leistungsarten = Leistungsart::where('aktiv', true)->orderBy('bezeichnung')->get();
        return view('stammdaten.einsatzarten.edit', compact('einsatzart', 'leistungsarten'));
    }

    public function update(Request $request, Leistungstyp $einsatzart)
    {
        $request->validate([
            'bezeichnung'     => ['required', 'string', 'max:100'],
            'leistungsart_id' => ['required', 'exists:leistungsarten,id'],
            'gueltig_ab'      => ['nullable', 'date'],
            'gueltig_bis'     => ['nullable', 'date'],
        ]);

        $einsatzart->update([
            'bezeichnung'     => $request->bezeichnung,
            'leistungsart_id' => $request->leistungsart_id,
            'gueltig_ab'      => $request->gueltig_ab,
            'gueltig_bis'     => $request->gueltig_bis,
            'aktiv'           => $request->boolean('aktiv', true),
        ]);

        return redirect()->route('einsatzarten.index')->with('erfolg', 'Einsatzart wurde aktualisiert.');
    }

    public function destroy(Leistungstyp $einsatzart)
    {
        $einsatzart->delete();
        return back()->with('erfolg', 'Einsatzart wurde gelöscht.');
    }
}
