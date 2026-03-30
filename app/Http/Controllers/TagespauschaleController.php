<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\Organisation;
use App\Models\Tagespauschale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagespauschaleController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    private function horizon(): Carbon
    {
        $org     = Organisation::find($this->orgId());
        $vorlauf = max(5, min(30, $org->einsatz_vorlauf_tage ?? 10));
        return today()->addDays($vorlauf);
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

        if ($selectedKlientId) {
            $klient = Klient::find($selectedKlientId);
            if ($klient && !$klient->zustaendig_id) {
                return redirect()->route('tagespauschalen.index')
                    ->with('fehler', 'Kein Zuständiger auf dem Klienten «' . $klient->vorname . ' ' . $klient->nachname . '» gesetzt — bitte zuerst in den Klient-Stammdaten zuweisen.');
            }
        }

        $istTiersPayant = Organisation::find($this->orgId())?->abrechnungslogik === 'tiers_payant';

        return view('tagespauschalen.create', compact('klienten', 'selectedKlientId', 'istTiersPayant'));
    }

    public function store(Request $request)
    {
        $autoVerlaengern = $request->boolean('auto_verlaengern');

        $request->validate([
            'klient_id'        => ['required', 'exists:klienten,id'],
            'datum_von'        => ['required', 'date'],
            'datum_bis'        => $autoVerlaengern
                ? ['nullable', 'date', 'after_or_equal:datum_von']
                : ['required', 'date', 'after_or_equal:datum_von'],
            'ansatz'           => ['required', 'numeric', 'min:0'],
            'text'             => ['nullable', 'string', 'max:500'],
            'auto_verlaengern' => ['boolean'],
        ], [
            'datum_bis.required' => 'Enddatum ist erforderlich wenn keine automatische Verlängerung aktiv ist.',
        ]);

        $klient = Klient::findOrFail($request->klient_id);
        if (!$klient->zustaendig_id) {
            return back()->withInput()->with('fehler',
                'Kein Zuständiger auf dem Klienten gesetzt — bitte zuerst in den Klient-Stammdaten zuweisen.');
        }

        $datumBis = !$autoVerlaengern && $request->filled('datum_bis')
            ? $request->datum_bis
            : null;

        if (Tagespauschale::hatUeberlappung(
            $request->klient_id, $this->orgId(),
            $request->datum_von, $datumBis
        )) {
            return back()->withInput()->with('fehler',
                'Überlappung mit einer bestehenden Tagespauschale für diesen Klienten in diesem Zeitraum.');
        }

        $horizon = $this->horizon();

        $tagespauschale = DB::transaction(function () use ($request, $autoVerlaengern, $datumBis, $horizon) {
            $tp = Tagespauschale::create([
                'organisation_id'  => $this->orgId(),
                'klient_id'        => $request->klient_id,
                'rechnungstyp'     => 'kvg',
                'datum_von'        => $request->datum_von,
                'datum_bis'        => $datumBis,
                'auto_verlaengern' => $autoVerlaengern,
                'ansatz'           => $request->ansatz,
                'text'             => $request->text,
                'erstellt_von'     => auth()->id(),
            ]);
            $tp->generiereFehlende($horizon);
            return $tp;
        });

        $anzahl = $tagespauschale->einsaetze()->count();

        AuditLog::schreiben('erstellt', 'Tagespauschale', $tagespauschale->id,
            "Tagespauschale für Klient {$tagespauschale->klient->nachname}: {$anzahl} Einsätze generiert");

        return redirect()->route('tagespauschalen.show', $tagespauschale)
            ->with('erfolg', "Tagespauschale erstellt — {$anzahl} Einsätze generiert.");
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

        $autoVerlaengern = $request->boolean('auto_verlaengern');

        $request->validate([
            'datum_von'        => ['required', 'date'],
            'datum_bis'        => $autoVerlaengern
                ? ['nullable', 'date']
                : ['required', 'date', 'after_or_equal:datum_von'],
            'ansatz'           => ['required', 'numeric', 'min:0'],
            'text'             => ['nullable', 'string', 'max:500'],
            'auto_verlaengern' => ['boolean'],
        ], [
            'datum_bis.required' => 'Enddatum ist erforderlich wenn keine automatische Verlängerung aktiv ist.',
        ]);

        $neuVon = Carbon::parse($request->datum_von);
        $neuBis = !$autoVerlaengern && $request->filled('datum_bis')
            ? Carbon::parse($request->datum_bis)
            : null;
        $altVon = $tagespauschale->datum_von->copy();
        $altBis = $tagespauschale->datum_bis?->copy();

        // Prüfen ob verrechnete Einsätze betroffen sind
        $ersteVerrechnet  = $tagespauschale->einsaetze()->where('verrechnet', true)->min('datum');
        $letzteVerrechnet = $tagespauschale->einsaetze()->where('verrechnet', true)->max('datum');

        if ($ersteVerrechnet && $neuVon->gt($ersteVerrechnet)) {
            return back()->withInput()->with('fehler',
                'Gültig von kann nicht nach dem ersten verrechneten Einsatz (' . Carbon::parse($ersteVerrechnet)->format('d.m.Y') . ') verschoben werden.');
        }
        if ($letzteVerrechnet && $neuBis && $neuBis->lt($letzteVerrechnet)) {
            return back()->withInput()->with('fehler',
                'Gültig bis kann nicht vor dem letzten verrechneten Einsatz (' . Carbon::parse($letzteVerrechnet)->format('d.m.Y') . ') gesetzt werden.');
        }

        if (Tagespauschale::hatUeberlappung(
            $tagespauschale->klient_id, $this->orgId(),
            $request->datum_von, $neuBis?->format('Y-m-d'), $tagespauschale->id
        )) {
            return back()->withInput()->with('fehler',
                'Überlappung mit einer anderen Tagespauschale dieses Klienten.');
        }

        $horizon = $this->horizon();

        DB::transaction(function () use ($tagespauschale, $neuVon, $neuBis, $altVon, $altBis, $horizon, $autoVerlaengern, $request) {
            // datum_von vorgezogen → neue Einsätze am Anfang generieren
            if ($neuVon->lt($altVon)) {
                $this->generiereBereich($tagespauschale, $neuVon, $altVon->copy()->subDay());
            }
            // datum_von nach hinten → unverrechnete Einsätze am Anfang löschen
            if ($neuVon->gt($altVon)) {
                $tagespauschale->einsaetze()
                    ->where('datum', '<', $neuVon)->where('verrechnet', false)->delete();
            }

            // Neue Werte speichern
            $tagespauschale->update([
                'datum_von'        => $request->datum_von,
                'datum_bis'        => $neuBis?->format('Y-m-d'),
                'auto_verlaengern' => $autoVerlaengern,
                'ansatz'           => $request->ansatz,
                'text'             => $request->text,
            ]);

            // datum_bis verkürzt oder neu gesetzt: unverrechnete Einsätze dahinter löschen
            if ($neuBis && ($altBis === null || $neuBis->lt($altBis))) {
                $tagespauschale->einsaetze()
                    ->where('datum', '>', $neuBis)->where('verrechnet', false)->delete();
            }

            // Fehlende Einsätze bis Horizont auffüllen
            $tagespauschale->refresh();
            $tagespauschale->generiereFehlende($horizon);
        });

        AuditLog::schreiben('geaendert', 'Tagespauschale', $tagespauschale->id,
            "Tagespauschale aktualisiert: {$neuVon->format('d.m.Y')} – " . ($neuBis?->format('d.m.Y') ?? '∞') . ", CHF {$request->ansatz}/Tag");

        return redirect()->route('tagespauschalen.show', $tagespauschale)
            ->with('erfolg', 'Tagespauschale gespeichert.');
    }

    public function beenden(Tagespauschale $tagespauschale)
    {
        $this->autorisiereZugriff($tagespauschale);

        $gestern = today()->subDay();
        $tagespauschale->loescheZukuenftigeEinsaetze(today());
        $tagespauschale->update([
            'datum_bis'        => $gestern->format('Y-m-d'),
            'auto_verlaengern' => false,
        ]);

        return redirect()->route('tagespauschalen.show', $tagespauschale)
            ->with('erfolg', 'Tagespauschale beendet per ' . $gestern->format('d.m.Y') . '.');
    }

    public function neustart(Tagespauschale $tagespauschale)
    {
        $this->autorisiereZugriff($tagespauschale);

        $horizon = $this->horizon();
        $tagespauschale->update([
            'datum_bis'        => null,
            'auto_verlaengern' => true,
        ]);
        $tagespauschale->refresh();
        $anzahl = $tagespauschale->generiereFehlende($horizon);

        return redirect()->route('tagespauschalen.show', $tagespauschale)
            ->with('erfolg', 'Tagespauschale neu gestartet' . ($anzahl > 0 ? " — {$anzahl} Einsätze generiert" : '') . '.');
    }

    private function generiereBereich(Tagespauschale $tp, Carbon $von, Carbon $bis): void
    {
        $zustaendigId = $tp->klient?->zustaendig_id ?? $tp->erstellt_von;
        $current = $von->copy();
        while ($current <= $bis) {
            Einsatz::create([
                'organisation_id'   => $tp->organisation_id,
                'klient_id'         => $tp->klient_id,
                'benutzer_id'       => $zustaendigId,
                'tagespauschale_id' => $tp->id,
                'datum'             => $current->copy(),
                'datum_bis'         => $current->copy(),
                'verrechnet'        => false,
                'status'            => $current->lt(today()) ? 'abgeschlossen' : 'geplant',
            ]);
            $current->addDay();
        }
    }

    private function autorisiereZugriff(Tagespauschale $tagespauschale): void
    {
        if ($tagespauschale->organisation_id !== $this->orgId()) abort(403);
    }
}
