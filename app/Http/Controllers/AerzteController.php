<?php

namespace App\Http\Controllers;

use App\Models\Arzt;
use App\Models\Region;
use Illuminate\Http\Request;

class AerzteController extends Controller
{
    private function orgId(): int { return auth()->user()->organisation_id; }

    public function index(Request $request)
    {
        $query = Arzt::where('organisation_id', $this->orgId())->orderBy('nachname');
        if ($request->filled('suche')) {
            $s = $request->suche;
            $query->where(fn($q) => $q
                ->where('nachname', 'ilike', "%{$s}%")
                ->orWhere('vorname', 'ilike', "%{$s}%")
                ->orWhere('praxis_name', 'ilike', "%{$s}%")
                ->orWhere('zsr_nr', 'ilike', "%{$s}%")
            );
        }
        if ($request->filled('status')) {
            $query->where('aktiv', $request->status === 'aktiv');
        }
        $aerzte = $query->paginate(25)->withQueryString();
        return view('stammdaten.aerzte.index', compact('aerzte'));
    }

    public function create()
    {
        $regionen = Region::orderBy('kuerzel')->get();
        return view('stammdaten.aerzte.create', compact('regionen'));
    }

    public function store(Request $request)
    {
        $daten = $request->validate([
            'anrede'       => ['nullable', 'string', 'max:20'],
            'vorname'      => ['nullable', 'string', 'max:100'],
            'nachname'     => ['required', 'string', 'max:100'],
            'zsr_nr'       => ['nullable', 'string', 'max:20'],
            'gln_nr'       => ['nullable', 'string', 'max:20'],
            'fachrichtung' => ['nullable', 'string', 'max:100'],
            'praxis_name'  => ['nullable', 'string', 'max:200'],
            'adresse'      => ['nullable', 'string', 'max:255'],
            'plz'          => ['nullable', 'string', 'max:10'],
            'ort'          => ['nullable', 'string', 'max:100'],
            'region_id'    => ['nullable', 'exists:regionen,id'],
            'telefon'      => ['nullable', 'string', 'max:50'],
            'fax'          => ['nullable', 'string', 'max:50'],
            'email'        => ['nullable', 'email', 'max:255'],
        ]);
        Arzt::create(array_merge($daten, ['organisation_id' => $this->orgId(), 'aktiv' => true]));
        return redirect()->route('aerzte.index')->with('erfolg', 'Arzt wurde angelegt.');
    }

    public function edit(Arzt $arzt)
    {
        $this->autorisiereZugriff($arzt);
        $regionen = Region::orderBy('kuerzel')->get();
        return view('stammdaten.aerzte.edit', compact('arzt', 'regionen'));
    }

    public function update(Request $request, Arzt $arzt)
    {
        $this->autorisiereZugriff($arzt);
        $daten = $request->validate([
            'anrede'       => ['nullable', 'string', 'max:20'],
            'vorname'      => ['nullable', 'string', 'max:100'],
            'nachname'     => ['required', 'string', 'max:100'],
            'zsr_nr'       => ['nullable', 'string', 'max:20'],
            'gln_nr'       => ['nullable', 'string', 'max:20'],
            'fachrichtung' => ['nullable', 'string', 'max:100'],
            'praxis_name'  => ['nullable', 'string', 'max:200'],
            'adresse'      => ['nullable', 'string', 'max:255'],
            'plz'          => ['nullable', 'string', 'max:10'],
            'ort'          => ['nullable', 'string', 'max:100'],
            'region_id'    => ['nullable', 'exists:regionen,id'],
            'telefon'      => ['nullable', 'string', 'max:50'],
            'fax'          => ['nullable', 'string', 'max:50'],
            'email'        => ['nullable', 'email', 'max:255'],
            'aktiv'        => ['boolean'],
        ]);
        $arzt->update($daten);
        return redirect()->route('aerzte.index')->with('erfolg', 'Arzt wurde gespeichert.');
    }

    private function autorisiereZugriff(Arzt $arzt): void
    {
        if ($arzt->organisation_id !== $this->orgId()) abort(403);
    }
}
