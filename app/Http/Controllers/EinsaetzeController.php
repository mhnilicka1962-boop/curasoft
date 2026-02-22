<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\Leistungsart;
use Illuminate\Http\Request;

class EinsaetzeController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    public function index(Request $request)
    {
        $rolle   = auth()->user()->rolle;
        $userId  = auth()->id();
        $heute   = today();
        $ansicht = $request->get('ansicht', 'anstehend'); // 'anstehend' | 'vergangen'

        $q = Einsatz::with(['klient', 'benutzer', 'leistungsart'])
            ->where('organisation_id', $this->orgId());

        // Pflege sieht nur eigene Einsätze
        if ($rolle === 'pflege') {
            $q->where('benutzer_id', $userId);
        } elseif ($request->filled('benutzer_id')) {
            $q->where('benutzer_id', $request->benutzer_id);
        }

        // Ansicht: anstehend vs. vergangen
        if ($ansicht === 'vergangen') {
            $q->where(fn($sub) =>
                $sub->whereDate('datum', '<', $heute)
                    ->orWhereIn('status', ['abgeschlossen', 'storniert'])
            );
        } else {
            $q->whereDate('datum', '>=', $heute)
              ->whereNotIn('status', ['abgeschlossen', 'storniert']);
        }

        if ($request->filled('suche')) {
            $suche = '%' . $request->suche . '%';
            $q->whereHas('klient', fn($k) =>
                $k->where('nachname', 'ilike', $suche)
                  ->orWhere('vorname', 'ilike', $suche)
            );
        }

        if ($request->filled('datum_von')) {
            $q->whereDate('datum', '>=', $request->datum_von);
        }
        if ($request->filled('datum_bis')) {
            $q->whereDate('datum', '<=', $request->datum_bis);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('leistungsart_id')) {
            $q->where('leistungsart_id', $request->leistungsart_id);
        }

        $sortRichtung = ($ansicht === 'vergangen') ? 'desc' : 'asc';

        $einsaetze = $q->orderBy('datum', $sortRichtung)
            ->orderBy('zeit_von')
            ->paginate(30)
            ->withQueryString();

        $leistungsarten = Leistungsart::where('aktiv', true)->orderBy('bezeichnung')->get();

        $mitarbeiter = ($rolle === 'admin')
            ? Benutzer::where('organisation_id', $this->orgId())
                ->where('aktiv', true)
                ->orderBy('nachname')
                ->get()
            : collect();

        // Meine Woche (nur Pflege-Rolle): eigene Einsätze der nächsten 14 Tage, nach Tag gruppiert
        $meineWoche = null;
        if ($rolle === 'pflege') {
            $meineWoche = Einsatz::with(['klient', 'leistungsart'])
                ->where('organisation_id', $this->orgId())
                ->where('benutzer_id', $userId)
                ->whereDate('datum', '>=', $heute)
                ->whereDate('datum', '<=', $heute->copy()->addDays(13))
                ->whereNotIn('status', ['storniert'])
                ->orderBy('datum')
                ->orderBy('zeit_von')
                ->get()
                ->groupBy(fn($e) => $e->datum->format('Y-m-d'));
        }

        return view('einsaetze.index', compact(
            'einsaetze', 'leistungsarten', 'mitarbeiter', 'ansicht', 'meineWoche'
        ));
    }

    public function create()
    {
        $klienten = Klient::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->orderBy('nachname')
            ->get();

        $leistungsarten = Leistungsart::where('aktiv', true)->orderBy('bezeichnung')->get();

        $mitarbeiter = (auth()->user()->rolle === 'admin')
            ? Benutzer::where('organisation_id', $this->orgId())
                ->where('aktiv', true)
                ->orderBy('nachname')
                ->get()
            : collect();

        return view('einsaetze.create', compact('klienten', 'leistungsarten', 'mitarbeiter'));
    }

    public function store(Request $request)
    {
        $daten = $request->validate([
            'klient_id'       => ['required', 'exists:klienten,id'],
            'leistungsart_id' => ['required', 'exists:leistungsarten,id'],
            'datum'           => ['required', 'date'],
            'datum_bis'       => ['nullable', 'date', 'after_or_equal:datum'],
            'zeit_von'        => ['nullable', 'date_format:H:i'],
            'zeit_bis'        => ['nullable', 'date_format:H:i'],
            'benutzer_id'     => ['nullable', 'exists:benutzer,id'],
            'bemerkung'       => ['nullable', 'string', 'max:1000'],
        ]);

        $klient = Klient::findOrFail($daten['klient_id']);

        $benutzerId = (auth()->user()->rolle === 'admin' && !empty($daten['benutzer_id']))
            ? $daten['benutzer_id']
            : auth()->id();

        $einsatz = Einsatz::create([
            'organisation_id' => $this->orgId(),
            'klient_id'       => $daten['klient_id'],
            'leistungsart_id' => $daten['leistungsart_id'],
            'benutzer_id'     => $benutzerId,
            'region_id'       => $klient->region_id,
            'datum'           => $daten['datum'],
            'datum_bis'       => $daten['datum_bis'] ?? null,
            'zeit_von'        => $daten['zeit_von'] ?? null,
            'zeit_bis'        => $daten['zeit_bis'] ?? null,
            'bemerkung'       => $daten['bemerkung'] ?? null,
            'status'          => 'geplant',
        ]);

        if ($request->filled('_klient_redirect')) {
            return redirect()->route('klienten.show', $daten['klient_id'])
                ->with('erfolg', 'Einsatz wurde geplant.');
        }

        return redirect()->route('einsaetze.show', $einsatz)
            ->with('erfolg', 'Einsatz wurde angelegt.');
    }

    public function show(Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);
        $einsatz->load('klient', 'benutzer', 'leistungsart', 'region');
        return view('einsaetze.show', compact('einsatz'));
    }

    public function edit(Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);
        $einsatz->load('klient', 'leistungsart');

        $klienten = Klient::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->orderBy('nachname')
            ->get();

        $leistungsarten = Leistungsart::where('aktiv', true)->orderBy('bezeichnung')->get();

        $mitarbeiter = (auth()->user()->rolle === 'admin')
            ? Benutzer::where('organisation_id', $this->orgId())
                ->where('aktiv', true)
                ->orderBy('nachname')
                ->get()
            : collect();

        return view('einsaetze.edit', compact('einsatz', 'klienten', 'leistungsarten', 'mitarbeiter'));
    }

    public function update(Request $request, Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);

        $regeln = [
            'klient_id'       => ['required', 'exists:klienten,id'],
            'leistungsart_id' => ['required', 'exists:leistungsarten,id'],
            'datum'           => ['required', 'date'],
            'datum_bis'       => ['nullable', 'date', 'after_or_equal:datum'],
            'zeit_von'        => ['nullable', 'date_format:H:i'],
            'zeit_bis'        => ['nullable', 'date_format:H:i'],
            'bemerkung'       => ['nullable', 'string', 'max:1000'],
        ];

        if (auth()->user()->rolle === 'admin') {
            $regeln['benutzer_id'] = ['nullable', 'exists:benutzer,id'];
            $regeln['status']      = ['required', 'in:geplant,aktiv,abgeschlossen,storniert'];
        }

        $daten = $request->validate($regeln);

        $klient = Klient::findOrFail($daten['klient_id']);
        $daten['region_id'] = $klient->region_id;

        if (auth()->user()->rolle === 'admin' && !empty($daten['benutzer_id'])) {
            $daten['benutzer_id'] = $daten['benutzer_id'];
        } else {
            unset($daten['benutzer_id']);
        }

        $einsatz->update($daten);

        return redirect()->route('einsaetze.show', $einsatz)
            ->with('erfolg', 'Einsatz wurde gespeichert.');
    }

    private function autorisiereZugriff(Einsatz $einsatz): void
    {
        if ($einsatz->organisation_id !== $this->orgId()) abort(403);
    }
}
