<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\EinsatzLeistungsart;
use App\Models\Klient;
use App\Models\Leistungsart;
use Illuminate\Http\Request;

class EinsaetzeController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    private function formView(Einsatz $einsatz)
    {
        $klienten = Klient::where('organisation_id', $this->orgId())
            ->where('aktiv', true)->orderBy('nachname')->get();
        $leistungsarten = Leistungsart::where('aktiv', true)->where('einheit', '!=', 'tage')->orderBy('bezeichnung')->get();
        $mitarbeiter = (auth()->user()->rolle === 'admin')
            ? Benutzer::where('organisation_id', $this->orgId())->where('aktiv', true)->where('anstellungsart', '!=', 'angehoerig')->orderBy('nachname')->get()
            : collect();
        $kbEintraege    = \App\Models\KlientBenutzer::where('beziehungstyp', 'angehoerig_pflegend')->where('aktiv', true)->get();
        $benutzerLookup = Benutzer::whereIn('id', $kbEintraege->pluck('benutzer_id')->unique())->get()->keyBy('id');
        $angehoerigeMap = $kbEintraege->groupBy('klient_id')
            ->map(fn($gruppe) => $gruppe->map(fn($kb) => [
                'id'   => $kb->benutzer_id,
                'name' => ($b = $benutzerLookup[$kb->benutzer_id] ?? null) ? $b->nachname . ' ' . $b->vorname : '?',
            ])->values());
        $angehoerigenBenutzer = Benutzer::whereIn('id',
            \App\Models\KlientBenutzer::where('klient_id', $einsatz->klient_id)
                ->where('beziehungstyp', 'angehoerig_pflegend')->where('aktiv', true)->pluck('benutzer_id')
        )->orderBy('nachname')->get();
        return view('einsaetze.form', compact('einsatz', 'klienten', 'leistungsarten', 'mitarbeiter', 'angehoerigeMap', 'angehoerigenBenutzer'));
    }

    public function index(Request $request)
    {
        $rolle   = auth()->user()->rolle;
        $userId  = auth()->id();
        $heute   = today();
        $ansicht = $request->get('ansicht', 'anstehend');

        $q = Einsatz::with(['klient', 'benutzer', 'helfer', 'einsatzLeistungsarten.leistungsart'])
            ->where('organisation_id', $this->orgId());

        if ($rolle === 'pflege') {
            $q->where('benutzer_id', $userId);
        } elseif ($request->filled('benutzer_id')) {
            $q->where('benutzer_id', $request->benutzer_id);
        }

        if ($ansicht === 'vergangen') {
            $q->where(fn($sub) =>
                $sub->whereDate('datum', '<', $heute)
                    ->orWhereIn('status', ['abgeschlossen', 'storniert'])
            );
            if ($request->filled('nur_unverrechnete')) {
                $q->where('verrechnet', false)->where('status', 'abgeschlossen');
            }
            $q->with(['rechnungsPosition.rechnung.lauf']);
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

        if ($request->filled('klient_id')) {
            $q->where('klient_id', $request->klient_id);
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
            if ($request->leistungsart_id === 'tagespauschale') {
                $q->whereNotNull('tagespauschale_id');
            } else {
                $q->whereHas('einsatzLeistungsarten', fn($sub) =>
                    $sub->where('leistungsart_id', $request->leistungsart_id)
                );
            }
        }

        $sortRichtung = ($ansicht === 'vergangen') ? 'desc' : 'asc';

        $einsaetze = $q->orderBy('datum', $sortRichtung)
            ->orderBy('zeit_von')
            ->paginate(30)
            ->withQueryString();

        $leistungsarten = Leistungsart::where('aktiv', true)->where('einheit', '!=', 'tage')->orderBy('bezeichnung')->get();

        $mitarbeiter = ($rolle === 'admin')
            ? Benutzer::where('organisation_id', $this->orgId())
                ->where('aktiv', true)
                ->orderBy('nachname')
                ->get()
            : collect();

        $meineWoche = null;
        if ($rolle === 'pflege') {
            $meineWoche = Einsatz::with(['klient', 'einsatzLeistungsarten.leistungsart'])
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

        $leistungsarten = Leistungsart::where('aktiv', true)->where('einheit', '!=', 'tage')->orderBy('bezeichnung')->get();

        $mitarbeiter = (auth()->user()->rolle === 'admin')
            ? Benutzer::where('organisation_id', $this->orgId())
                ->where('aktiv', true)
                ->where('anstellungsart', '!=', 'angehoerig')
                ->orderBy('nachname')
                ->get()
            : collect();

        $kbEintraege    = \App\Models\KlientBenutzer::where('beziehungstyp', 'angehoerig_pflegend')
            ->where('aktiv', true)->get();
        $benutzerLookup = Benutzer::whereIn('id', $kbEintraege->pluck('benutzer_id')->unique())
            ->get()->keyBy('id');
        $angehoerigeMap = $kbEintraege
            ->groupBy('klient_id')
            ->map(fn($gruppe) => $gruppe->map(fn($kb) => [
                'id'   => $kb->benutzer_id,
                'name' => ($b = $benutzerLookup[$kb->benutzer_id] ?? null)
                            ? $b->nachname . ' ' . $b->vorname : '?',
            ])->values());

        $angehoerigenBenutzer = Benutzer::whereIn('id',
            \App\Models\KlientBenutzer::where('klient_id', $einsatz->klient_id)
                ->where('beziehungstyp', 'angehoerig_pflegend')->where('aktiv', true)->pluck('benutzer_id')
        )->orderBy('nachname')->get();

        return view('einsaetze.form', compact('einsatz', 'klienten', 'leistungsarten', 'mitarbeiter', 'angehoerigeMap', 'angehoerigenBenutzer'));
    }

    public function store(Request $request)
    {
        // Nur angehakte Checkboxen haben eine id — herausfiltern
        $request->merge([
            'leistungsarten' => collect($request->input('leistungsarten', []))
                ->filter(fn($la) => !empty($la['id']))
                ->values()
                ->toArray(),
        ]);

        $daten = $request->validate([
            'klient_id'              => ['required', 'exists:klienten,id'],
            'leistungsarten'         => ['required', 'array', 'min:1'],
            'leistungsarten.*.id'    => ['required', 'exists:leistungsarten,id'],
            'leistungsarten.*.minuten' => ['required', 'integer', 'min:5'],
            'datum'                  => ['required', 'date'],
            'datum_bis'              => ['nullable', 'date', 'after_or_equal:datum'],
            'zeit_von'               => ['nullable', 'date_format:H:i', fn($a, $v, $f) =>
                                          $v && (int)\Carbon\Carbon::createFromFormat('H:i', $v)->format('i') % 5 !== 0
                                              ? $f('Startzeit muss in 5-Minuten-Schritten sein (KLV-Vorschrift).') : null],
            'zeit_bis'               => ['nullable', 'date_format:H:i', function($a, $v, $f) use ($request) {
                                          if (!$v) return;
                                          if ((int)\Carbon\Carbon::createFromFormat('H:i', $v)->format('i') % 5 !== 0)
                                              return $f('Endzeit muss in 5-Minuten-Schritten sein (KLV-Vorschrift).');
                                          if ($request->zeit_von) {
                                              $dauer = \Carbon\Carbon::createFromFormat('H:i', $request->zeit_von)
                                                          ->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', $v));
                                              if ($dauer < 10)
                                                  $f('Einsatz muss mindestens 10 Minuten dauern (KLV-Mindestdauer).');
                                          }
                                      }],
            'verordnung_id'          => ['nullable', 'exists:klient_verordnungen,id'],
            'leistungserbringer_typ' => ['nullable', 'in:fachperson,angehoerig'],
            'benutzer_id'            => ['nullable', 'exists:benutzer,id'],
            'helfer_id'              => ['nullable', 'exists:benutzer,id'],
            'bemerkung'              => ['nullable', 'string', 'max:1000'],
        ]);

        $klient = Klient::findOrFail($daten['klient_id']);

        $benutzerId = (auth()->user()->rolle === 'admin' && !empty($daten['benutzer_id']))
            ? $daten['benutzer_id']
            : auth()->id();

        // Prüfen ob Pflegeperson alle gewählten Leistungsarten erbringen darf
        $pflegeperson = \App\Models\Benutzer::find($benutzerId);
        if ($pflegeperson) {
            foreach ($daten['leistungsarten'] as $la) {
                if (!$pflegeperson->darfLeistungsart((int) $la['id'])) {
                    $laName = Leistungsart::find($la['id'])?->bezeichnung ?? 'Leistungsart';
                    return back()->withInput()->withErrors([
                        'leistungsarten' => $pflegeperson->vorname . ' ' . $pflegeperson->nachname . ' ist für "' . $laName . '" nicht freigegeben.',
                    ]);
                }
            }
        }

        $basis = [
            'organisation_id'        => $this->orgId(),
            'klient_id'              => $daten['klient_id'],
            'verordnung_id'          => $daten['verordnung_id'] ?? null,
            'leistungserbringer_typ' => $daten['leistungserbringer_typ'] ?? 'fachperson',
            'benutzer_id'            => $benutzerId,
            'helfer_id'              => $daten['helfer_id'] ?? null,
            'region_id'              => $klient->region_id,
            'datum_bis'              => $daten['datum_bis'] ?? null,
            'zeit_von'               => $daten['zeit_von'] ?? null,
            'zeit_bis'               => $daten['zeit_bis'] ?? null,
            'minuten'                => (isset($daten['zeit_von'], $daten['zeit_bis']))
                                          ? \Carbon\Carbon::createFromFormat('H:i', $daten['zeit_von'])
                                              ->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', $daten['zeit_bis']))
                                          : null,
            'bemerkung'              => $daten['bemerkung'] ?? null,
            'status'                 => 'geplant',
        ];

        // Einzelner Einsatz
        $einsatz = Einsatz::create(array_merge($basis, ['datum' => $daten['datum']]));
        foreach ($daten['leistungsarten'] as $la) {
            $einsatz->einsatzLeistungsarten()->create([
                'leistungsart_id' => $la['id'],
                'minuten'         => $la['minuten'],
            ]);
        }

        if ($request->filled('_klient_redirect')) {
            return redirect()->route('klienten.show', $daten['klient_id'])
                ->with('erfolg', 'Einsatz wurde geplant.');
        }

        if ($request->filled('_tour_redirect')) {
            $tour = \App\Models\Tour::where('id', $request->_tour_redirect)
                ->where('organisation_id', $this->orgId())->first();
            if ($tour) {
                $max = $tour->einsaetze()->max('tour_reihenfolge') ?? 0;
                $einsatz->update(['tour_id' => $tour->id, 'tour_reihenfolge' => $max + 1]);
                return redirect()->route('touren.show', $tour)
                    ->with('erfolg', 'Einsatz angelegt und Tour zugewiesen.');
            }
        }

        if ($request->filled('_nach_touren')) {
            return redirect()->route('touren.create', [
                'benutzer_id' => $einsatz->benutzer_id,
                'datum'       => $einsatz->datum->format('Y-m-d'),
            ])->with('erfolg', 'Einsatz angelegt – jetzt Tour erstellen.');
        }

        return redirect()->route('einsaetze.show', $einsatz)
            ->with('erfolg', 'Einsatz wurde angelegt.');
    }

    public function show(Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);
        $einsatz->load('klient', 'benutzer', 'helfer', 'einsatzLeistungsarten.leistungsart', 'region', 'aktivitaeten');
        return $this->formView($einsatz);
    }

    public function vorOrt(Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);
        $einsatz->load([
            'klient.adressen',
            'klient.kontakte',
            'klient.diagnosen',
            'klient.krankenkassen.krankenkasse',
            'klient.verordnungen' => fn($q) => $q->where('aktiv', true),
            'einsatzLeistungsarten.leistungsart',
            'verordnung',
            'helfer',
            'aktivitaeten',
            'rapporte' => fn($q) => $q->orderByDesc('datum')->orderByDesc('id'),
        ]);
        $gespeicherteAktivitaeten = $einsatz->aktivitaeten
            ->keyBy(fn($a) => $a->kategorie . '|' . $a->aktivitaet);
        return view('einsaetze.vor-ort', compact('einsatz', 'gespeicherteAktivitaeten'));
    }

    public function aktivitaetenSpeichern(\Illuminate\Http\Request $request, Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);

        $einsatz->aktivitaeten()->delete();

        foreach ($request->input('akt', []) as $key) {
            [$kategorie, $aktivitaet] = explode('|', $key, 2);
            $minuten = max(5, (int) ($request->input("min.$key") ?? 5));
            $einsatz->aktivitaeten()->create([
                'organisation_id' => $einsatz->organisation_id,
                'kategorie'       => $kategorie,
                'aktivitaet'      => $aktivitaet,
                'minuten'         => $minuten,
            ]);
        }

        // einsatz_leistungsarten.minuten aus Aktivitäten ableiten
        $this->syncLeistungsartenMinuten($einsatz);

        return back()->with('erfolg', 'Leistungen gespeichert.');
    }

    private function syncLeistungsartenMinuten(Einsatz $einsatz): void
    {
        // Aktivitätsname → leistungsart_id (via leistungstypen)
        $ltMap = \App\Models\Leistungstyp::pluck('leistungsart_id', 'bezeichnung');

        // Minuten pro leistungsart_id summieren
        $summen = [];
        foreach ($einsatz->aktivitaeten()->get() as $akt) {
            $laId = $ltMap[$akt->aktivitaet] ?? null;
            if (!$laId) continue;
            $summen[$laId] = ($summen[$laId] ?? 0) + $akt->minuten;
        }

        // einsatz_leistungsarten aktualisieren
        foreach ($einsatz->einsatzLeistungsarten()->get() as $ela) {
            $ela->update(['minuten' => $summen[$ela->leistungsart_id] ?? 0]);
        }
    }

    public function edit(Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);
        $einsatz->load('klient', 'einsatzLeistungsarten.leistungsart', 'aktivitaeten');
        return $this->formView($einsatz);
    }

    public function update(Request $request, Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);

        if ($einsatz->tagespauschale_id) {
            $request->validate(['bemerkung' => ['nullable', 'string', 'max:1000']]);
            $einsatz->update(['bemerkung' => $request->bemerkung]);
            return redirect()->route('einsaetze.show', $einsatz)->with('erfolg', 'Bemerkung gespeichert.');
        }

        // Nur angehakte Checkboxen haben eine id — herausfiltern
        $request->merge([
            'leistungsarten' => collect($request->input('leistungsarten', []))
                ->filter(fn($la) => !empty($la['id']))
                ->values()
                ->toArray(),
        ]);

        $regeln = [
            'klient_id'              => ['required', 'exists:klienten,id'],
            'leistungsarten'         => ['required', 'array', 'min:1'],
            'leistungsarten.*.id'    => ['required', 'exists:leistungsarten,id'],
            'leistungsarten.*.minuten' => ['required', 'integer', 'min:5'],
            'datum'                  => ['required', 'date'],
            'datum_bis'              => ['nullable', 'date', 'after_or_equal:datum'],
            'zeit_von'               => ['nullable', 'date_format:H:i', fn($a, $v, $f) =>
                                          $v && (int)\Carbon\Carbon::createFromFormat('H:i', $v)->format('i') % 5 !== 0
                                              ? $f('Startzeit muss in 5-Minuten-Schritten sein (KLV-Vorschrift).') : null],
            'zeit_bis'               => ['nullable', 'date_format:H:i', function($a, $v, $f) use ($request) {
                                          if (!$v) return;
                                          if ((int)\Carbon\Carbon::createFromFormat('H:i', $v)->format('i') % 5 !== 0)
                                              return $f('Endzeit muss in 5-Minuten-Schritten sein (KLV-Vorschrift).');
                                          if ($request->zeit_von) {
                                              $dauer = \Carbon\Carbon::createFromFormat('H:i', $request->zeit_von)
                                                          ->diffInMinutes(\Carbon\Carbon::createFromFormat('H:i', $v));
                                              if ($dauer < 10)
                                                  $f('Einsatz muss mindestens 10 Minuten dauern (KLV-Mindestdauer).');
                                          }
                                      }],
            'bemerkung'              => ['nullable', 'string', 'max:1000'],
        ];

        if (auth()->user()->rolle === 'admin') {
            $regeln['benutzer_id'] = ['nullable', 'exists:benutzer,id'];
            $regeln['helfer_id']   = ['nullable', 'exists:benutzer,id'];
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

        if (auth()->user()->rolle === 'admin') {
            $daten['helfer_id'] = $daten['helfer_id'] ?? null;
        } else {
            unset($daten['helfer_id']);
        }

        // Prüfen ob Pflegeperson alle gewählten Leistungsarten erbringen darf
        $benutzerId   = $daten['benutzer_id'] ?? $einsatz->benutzer_id;
        $pflegeperson = \App\Models\Benutzer::find($benutzerId);
        if ($pflegeperson) {
            foreach ($daten['leistungsarten'] as $la) {
                if (!$pflegeperson->darfLeistungsart((int) $la['id'])) {
                    $laName = Leistungsart::find($la['id'])?->bezeichnung ?? 'Leistungsart';
                    return back()->withInput()->withErrors([
                        'leistungsarten' => $pflegeperson->vorname . ' ' . $pflegeperson->nachname . ' ist für "' . $laName . '" nicht freigegeben.',
                    ]);
                }
            }
        }

        $updateDaten = collect($daten)->except(['leistungsarten'])->toArray();
        $einsatz->update($updateDaten);

        // Leistungsarten neu setzen
        $einsatz->einsatzLeistungsarten()->delete();
        foreach ($daten['leistungsarten'] as $la) {
            $einsatz->einsatzLeistungsarten()->create([
                'leistungsart_id' => $la['id'],
                'minuten'         => $la['minuten'],
            ]);
        }

        return redirect()->route('einsaetze.show', $einsatz)
            ->with('erfolg', 'Einsatz wurde gespeichert.');
    }

    public function destroy(Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);

        if ($einsatz->tagespauschale_id) {
            return back()->with('fehler', 'Tagespauschalen-Einsätze können nicht manuell gelöscht werden.');
        }
        if ($einsatz->status !== 'geplant') {
            return back()->with('fehler', 'Nur geplante Einsätze können gelöscht werden.');
        }
        if ($einsatz->tour_id) {
            return back()->with('fehler', 'Einsatz ist einer Tour zugewiesen — zuerst aus der Tour entfernen.');
        }

        $klientId = $einsatz->klient_id;
        $einsatz->delete();

        return redirect()->route('klienten.show', $klientId)
            ->with('erfolg', 'Einsatz wurde gelöscht.');
    }

    public function destroySerie(Request $request, string $serieId)
    {
        $anzahl = Einsatz::where('organisation_id', $this->orgId())
            ->where('serie_id', $serieId)
            ->whereDate('datum', '>=', today())
            ->whereNotIn('status', ['abgeschlossen'])
            ->whereNull('tour_id')
            ->count();

        Einsatz::where('organisation_id', $this->orgId())
            ->where('serie_id', $serieId)
            ->whereDate('datum', '>=', today())
            ->whereNotIn('status', ['abgeschlossen'])
            ->whereNull('tour_id')
            ->delete();

        $meldung = $anzahl . ' Einsatz' . ($anzahl !== 1 ? 'ätze' : '') . ' der Serie gelöscht.';

        if ($request->filled('_klient_redirect')) {
            return redirect()->route('klienten.show', $request->_klient_redirect)
                ->with('erfolg', $meldung);
        }

        return redirect()->route('einsaetze.index')->with('erfolg', $meldung);
    }

    private function autorisiereZugriff(Einsatz $einsatz): void
    {
        if ($einsatz->organisation_id !== $this->orgId()) abort(403);
    }
}
