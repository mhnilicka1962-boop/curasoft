<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\Tagespauschale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagespauschaleController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    public function index(Request $request)
    {
        $query = Tagespauschale::with('klient')
            ->where('organisation_id', $this->orgId())
            ->orderByDesc('datum_von');

        if ($request->filled('klient_id')) {
            $query->where('klient_id', $request->klient_id);
        }

        $tagespauschalen = $query->paginate(50)->withQueryString();
        $klienten = Klient::where('organisation_id', $this->orgId())
            ->where('aktiv', true)->orderBy('nachname')->get();

        return view('tagespauschalen.index', compact('tagespauschalen', 'klienten'));
    }

    public function create(Request $request)
    {
        $klienten = Klient::where('organisation_id', $this->orgId())
            ->where('aktiv', true)->orderBy('nachname')->get();

        $selectedKlientId = $request->get('klient_id');

        return view('tagespauschalen.create', compact('klienten', 'selectedKlientId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'klient_id'    => ['required', 'exists:klienten,id'],
            'datum_von'    => ['required', 'date'],
            'datum_bis'    => ['required', 'date', 'after_or_equal:datum_von'],
            'rechnungstyp' => ['required', 'in:kvg,klient,gemeinde'],
            'ansatz'       => ['required', 'numeric', 'min:0'],
            'text'         => ['nullable', 'string', 'max:500'],
        ]);

        if (Tagespauschale::hatUeberlappung(
            $request->klient_id, $this->orgId(),
            $request->datum_von, $request->datum_bis
        )) {
            return back()->withInput()->with('fehler',
                'Überlappung mit einer bestehenden Tagespauschale für diesen Klienten in diesem Zeitraum.');
        }

        $tagespauschale = DB::transaction(function () use ($request) {
            $tp = Tagespauschale::create([
                'organisation_id' => $this->orgId(),
                'klient_id'       => $request->klient_id,
                'rechnungstyp'    => $request->rechnungstyp,
                'datum_von'       => $request->datum_von,
                'datum_bis'       => $request->datum_bis,
                'ansatz'          => $request->ansatz,
                'text'            => $request->text,
                'erstellt_von'    => auth()->id(),
            ]);
            $tp->generiereEinsaetze();
            return $tp;
        });

        AuditLog::schreiben('erstellt', 'Tagespauschale', $tagespauschale->id,
            "Tagespauschale für Klient {$tagespauschale->klient->nachname}: {$tagespauschale->anzahlTage()} Einsätze generiert");

        return redirect()->route('tagespauschalen.show', $tagespauschale)
            ->with('erfolg', "Tagespauschale erstellt — {$tagespauschale->anzahlTage()} Einsätze generiert.");
    }

    public function show(Tagespauschale $tagespauschale)
    {
        $this->autorisiereZugriff($tagespauschale);
        $tagespauschale->load('klient');

        $einsaetzeStats = $tagespauschale->einsaetze()
            ->selectRaw("TO_CHAR(datum, 'YYYY-MM') as monat, COUNT(*) as anzahl, SUM(CASE WHEN verrechnet THEN 1 ELSE 0 END) as verrechnet")
            ->groupBy('monat')
            ->orderBy('monat')
            ->get();

        $letzteVerrechnungDatum = $tagespauschale->einsaetze()
            ->where('verrechnet', true)
            ->max('datum');

        return view('tagespauschalen.show', compact('tagespauschale', 'einsaetzeStats', 'letzteVerrechnungDatum'));
    }

    public function update(Request $request, Tagespauschale $tagespauschale)
    {
        $this->autorisiereZugriff($tagespauschale);

        $request->validate([
            'datum_von'    => ['required', 'date'],
            'datum_bis'    => ['required', 'date', 'after_or_equal:datum_von'],
            'rechnungstyp' => ['required', 'in:kvg,klient,gemeinde'],
            'ansatz'       => ['required', 'numeric', 'min:0'],
            'text'         => ['nullable', 'string', 'max:500'],
        ]);

        $neuVon = \Carbon\Carbon::parse($request->datum_von);
        $neuBis = \Carbon\Carbon::parse($request->datum_bis);
        $altVon = $tagespauschale->datum_von->copy();
        $altBis = $tagespauschale->datum_bis->copy();

        // Prüfen ob verrechnte Einsätze betroffen sind
        $ersteVerrechnet = $tagespauschale->einsaetze()->where('verrechnet', true)->min('datum');
        $letzteVerrechnet = $tagespauschale->einsaetze()->where('verrechnet', true)->max('datum');

        if ($ersteVerrechnet && $neuVon->gt($ersteVerrechnet)) {
            return back()->withInput()->with('fehler',
                'datum_von kann nicht nach dem ersten verrechneten Einsatz (' . \Carbon\Carbon::parse($ersteVerrechnet)->format('d.m.Y') . ') verschoben werden.');
        }

        if ($letzteVerrechnet && $neuBis->lt($letzteVerrechnet)) {
            return back()->withInput()->with('fehler',
                'datum_bis kann nicht vor dem letzten verrechneten Einsatz (' . \Carbon\Carbon::parse($letzteVerrechnet)->format('d.m.Y') . ') gesetzt werden.');
        }

        // Overlap-Check (exkl. sich selbst)
        if (Tagespauschale::hatUeberlappung(
            $tagespauschale->klient_id, $this->orgId(),
            $request->datum_von, $request->datum_bis, $tagespauschale->id
        )) {
            return back()->withInput()->with('fehler',
                'Überlappung mit einer anderen Tagespauschale dieses Klienten.');
        }

        DB::transaction(function () use ($tagespauschale, $neuVon, $neuBis, $altVon, $altBis, $request) {
            // datum_von vorgezogen → neue Einsätze am Anfang generieren
            if ($neuVon->lt($altVon)) {
                $this->generiereBereich($tagespauschale, $neuVon, $altVon->copy()->subDay());
            }
            // datum_von nach hinten → unverrechnete Einsätze am Anfang löschen
            if ($neuVon->gt($altVon)) {
                $tagespauschale->einsaetze()
                    ->where('datum', '<', $neuVon)->where('verrechnet', false)->delete();
            }
            // datum_bis verlängert → neue Einsätze am Ende generieren
            if ($neuBis->gt($altBis)) {
                $this->generiereBereich($tagespauschale, $altBis->copy()->addDay(), $neuBis);
            }
            // datum_bis verkürzt → unverrechnete Einsätze am Ende löschen
            if ($neuBis->lt($altBis)) {
                $tagespauschale->einsaetze()
                    ->where('datum', '>', $neuBis)->where('verrechnet', false)->delete();
            }

            $tagespauschale->update([
                'datum_von'    => $request->datum_von,
                'datum_bis'    => $request->datum_bis,
                'rechnungstyp' => $request->rechnungstyp,
                'ansatz'       => $request->ansatz,
                'text'         => $request->text,
            ]);
        });

        AuditLog::schreiben('geaendert', 'Tagespauschale', $tagespauschale->id,
            "Tagespauschale aktualisiert: {$neuVon->format('d.m.Y')} – {$neuBis->format('d.m.Y')}, CHF {$request->ansatz}/Tag");

        return redirect()->route('tagespauschalen.show', $tagespauschale)
            ->with('erfolg', 'Tagespauschale gespeichert.');
    }

    private function generiereBereich(Tagespauschale $tp, \Carbon\Carbon $von, \Carbon\Carbon $bis): void
    {
        $current = $von->copy();
        while ($current <= $bis) {
            Einsatz::create([
                'organisation_id'   => $tp->organisation_id,
                'klient_id'         => $tp->klient_id,
                'benutzer_id'       => $tp->erstellt_von,
                'tagespauschale_id' => $tp->id,
                'datum'             => $current->copy(),
                'datum_bis'         => $current->copy(),
                'verrechnet'        => false,
                'status'            => 'abgeschlossen',
            ]);
            $current->addDay();
        }
    }

    private function autorisiereZugriff(Tagespauschale $tagespauschale): void
    {
        if ($tagespauschale->organisation_id !== $this->orgId()) abort(403);
    }
}
