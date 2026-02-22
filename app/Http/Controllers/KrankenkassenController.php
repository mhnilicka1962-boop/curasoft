<?php

namespace App\Http\Controllers;

use App\Models\Krankenkasse;
use Illuminate\Http\Request;

class KrankenkassenController extends Controller
{
    private function orgId(): int { return auth()->user()->organisation_id; }

    public function index(Request $request)
    {
        $query = Krankenkasse::where('organisation_id', $this->orgId())->orderBy('name');
        if ($request->filled('suche')) {
            $s = $request->suche;
            $query->where(fn($q) => $q
                ->where('name', 'ilike', "%{$s}%")
                ->orWhere('kuerzel', 'ilike', "%{$s}%")
                ->orWhere('ean_nr', 'ilike', "%{$s}%")
            );
        }
        if ($request->filled('status')) {
            $query->where('aktiv', $request->status === 'aktiv');
        }
        $krankenkassen = $query->paginate(25)->withQueryString();
        return view('stammdaten.krankenkassen.index', compact('krankenkassen'));
    }

    public function create()
    {
        return view('stammdaten.krankenkassen.create');
    }

    public function store(Request $request)
    {
        $daten = $request->validate([
            'name'    => ['required', 'string', 'max:200'],
            'kuerzel' => ['nullable', 'string', 'max:20'],
            'ean_nr'  => ['nullable', 'string', 'max:20'],
            'bag_nr'  => ['nullable', 'string', 'max:20'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'plz'     => ['nullable', 'string', 'max:10'],
            'ort'     => ['nullable', 'string', 'max:100'],
            'telefon' => ['nullable', 'string', 'max:50'],
            'email'   => ['nullable', 'email', 'max:255'],
        ]);
        Krankenkasse::create(array_merge($daten, ['organisation_id' => $this->orgId(), 'aktiv' => true]));
        return redirect()->route('krankenkassen.index')->with('erfolg', 'Krankenkasse wurde angelegt.');
    }

    public function edit(Krankenkasse $krankenkasse)
    {
        $this->autorisiereZugriff($krankenkasse);
        return view('stammdaten.krankenkassen.edit', compact('krankenkasse'));
    }

    public function update(Request $request, Krankenkasse $krankenkasse)
    {
        $this->autorisiereZugriff($krankenkasse);
        $daten = $request->validate([
            'name'    => ['required', 'string', 'max:200'],
            'kuerzel' => ['nullable', 'string', 'max:20'],
            'ean_nr'  => ['nullable', 'string', 'max:20'],
            'bag_nr'  => ['nullable', 'string', 'max:20'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'plz'     => ['nullable', 'string', 'max:10'],
            'ort'     => ['nullable', 'string', 'max:100'],
            'telefon' => ['nullable', 'string', 'max:50'],
            'email'   => ['nullable', 'email', 'max:255'],
            'aktiv'   => ['boolean'],
        ]);
        $krankenkasse->update($daten);
        return redirect()->route('krankenkassen.index')->with('erfolg', 'Krankenkasse wurde gespeichert.');
    }

    private function autorisiereZugriff(Krankenkasse $krankenkasse): void
    {
        if ($krankenkasse->organisation_id !== $this->orgId()) abort(403);
    }
}
