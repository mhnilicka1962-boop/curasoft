<?php

namespace App\Http\Controllers;

use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\Leistungsart;
use App\Models\Leistungstyp;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RapportierungController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    public function show(Request $request, Klient $klient, int $jahr, int $monat)
    {
        abort_if($klient->organisation_id !== $this->orgId(), 403);

        $periodeVon = Carbon::create($jahr, $monat, 1)->startOfMonth();
        $periodeBis = $periodeVon->copy()->endOfMonth();
        $tage       = $periodeBis->day;

        // Leistungsarten + Leistungstypen
        $leistungsarten = Leistungsart::where('aktiv', true)
            ->orderBy('id')
            ->with(['leistungstypen' => fn($q) => $q->where('aktiv', true)->orderBy('bezeichnung')])
            ->get();

        // Lookup: leistungstyp.bezeichnung → leistungstyp.id
        $ltByName = [];
        foreach ($leistungsarten as $la) {
            foreach ($la->leistungstypen as $lt) {
                $ltByName[$lt->bezeichnung] = $lt->id;
            }
        }

        // Alle Einsätze dieses Klienten im Monat (mit aktivitaeten)
        $einsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->where('klient_id', $klient->id)
            ->whereBetween('datum', [$periodeVon, $periodeBis])
            ->whereNull('tagespauschale_id')
            ->whereNull('betrag_fix')
            ->with('aktivitaeten', 'benutzer')
            ->get();

        // Raster Rapportierung: [leistungstyp_id][tag] = ['minuten' => X]
        $raster = [];

        // App-Einsätze Summe: [leistungsart_id][tag] = ['minuten' => X, 'aktiv' => bool, 'einsatz_id' => ?, 'kommentar' => ?]
        $appRaster = [];

        foreach ($einsaetze as $e) {
            $tag = $e->datum->day;

            if ($e->checkin_methode === 'rapportierung') {
                // Rapportierungs-Einsatz → History aus admin_kommentar lesen
                $rapHistoryNachLt = [];
                if ($e->admin_kommentar) {
                    $zeilen = explode("\n", $e->admin_kommentar);
                    foreach (array_slice($zeilen, 1) as $zeile) {
                        if (preg_match('/\| (.+?): \d/', $zeile, $m)) {
                            $rapHistoryNachLt[$m[1]][] = $zeile;
                        }
                    }
                }

                foreach ($e->aktivitaeten as $akt) {
                    $ltId = $ltByName[$akt->aktivitaet] ?? null;
                    if (!$ltId) continue;
                    if (!isset($raster[$ltId][$tag])) {
                        $raster[$ltId][$tag] = ['minuten' => 0, 'admin_override' => false, 'admin_entry' => false, 'kommentar' => null, 'history' => []];
                    }
                    $raster[$ltId][$tag]['minuten']    += $akt->minuten;
                    $raster[$ltId][$tag]['admin_entry'] = true;
                    $raster[$ltId][$tag]['history']     = $rapHistoryNachLt[$akt->aktivitaet] ?? [];
                }
            } else {
                // App-Einsatz → in Leistungsart-Summe
                $laId = $e->leistungsart_id;
                if (!isset($appRaster[$laId][$tag])) {
                    $appRaster[$laId][$tag] = ['minuten' => 0, 'aktiv' => false, 'einsatz_id' => null, 'kommentar' => null];
                }
                $appRaster[$laId][$tag]['minuten'] += $e->minuten ?? 0;

                if ($e->status === 'aktiv') {
                    $appRaster[$laId][$tag]['aktiv']      = true;
                    $appRaster[$laId][$tag]['einsatz_id'] = $e->id;
                } elseif (!$appRaster[$laId][$tag]['einsatz_id']) {
                    $appRaster[$laId][$tag]['einsatz_id'] = $e->id;
                    $appRaster[$laId][$tag]['kommentar']  = $e->admin_kommentar;
                }

                // Welche Aktivitäten wurden vom Admin geändert? Format: "Admin: LT1, LT2\nHistory..."
                $changedAkt    = [];
                $historyNachLt = [];
                if ($e->admin_kommentar) {
                    $komZeilen = explode("\n", $e->admin_kommentar);
                    if (str_starts_with($komZeilen[0], 'Admin: ')) {
                        foreach (explode(', ', substr($komZeilen[0], 7)) as $name) {
                            $changedAkt[trim($name)] = true;
                        }
                    }
                    foreach (array_slice($komZeilen, 1) as $zeile) {
                        if (preg_match('/\| (.+?): \d/', $zeile, $m)) {
                            $historyNachLt[$m[1]][] = $zeile;
                        }
                    }
                }

                // App-Aktivitäten auch in Leistungstyp-Raster einlesen
                foreach ($e->aktivitaeten as $akt) {
                    $ltId = $ltByName[$akt->aktivitaet] ?? null;
                    if (!$ltId) continue;
                    if (!isset($raster[$ltId][$tag])) {
                        $raster[$ltId][$tag] = ['minuten' => 0, 'admin_override' => false, 'admin_entry' => false, 'kommentar' => null, 'history' => []];
                    }
                    $raster[$ltId][$tag]['minuten'] += $akt->minuten;
                    if (isset($changedAkt[$akt->aktivitaet])) {
                        $raster[$ltId][$tag]['admin_override'] = true;
                        $raster[$ltId][$tag]['kommentar']      = $e->admin_kommentar;
                        $raster[$ltId][$tag]['history']        = $historyNachLt[$akt->aktivitaet] ?? [];
                    }
                }
            }
        }

        // Tage mit aktivem Einsatz → für Admin gesperrt
        $aktivTage = [];
        $aktivEinsaetze = [];
        foreach ($einsaetze as $e) {
            if ($e->status === 'aktiv') {
                $name = $e->benutzer ? $e->benutzer->vorname . ' ' . $e->benutzer->nachname : 'Mitarbeiter';
                $aktivTage[$e->datum->day] = $name . ' ist vor Ort (eingecheckt)';
                $aktivEinsaetze[$e->datum->day] = [
                    'einsatz_id'   => $e->id,
                    'name'         => $name,
                    'checkin_zeit' => $e->checkin_zeit?->format('H:i'),
                    'datum'        => $e->datum->format('d.m.Y'),
                ];
            }
        }

        $vorMonat   = $periodeVon->copy()->subMonth();
        $naechMonat = $periodeVon->copy()->addMonth();

        // Verfügbare Monate für Dropdowns
        $verfuegbareMonate = Einsatz::where('organisation_id', $this->orgId())
            ->where('klient_id', $klient->id)
            ->whereNull('tagespauschale_id')
            ->selectRaw("EXTRACT(YEAR FROM datum)::int AS j, EXTRACT(MONTH FROM datum)::int AS m")
            ->distinct()
            ->orderBy('j')->orderBy('m')
            ->get()
            ->groupBy('j')
            ->map(fn($rows) => $rows->pluck('m')->sort()->values());

        return view('klienten.rapportierung', compact(
            'klient', 'leistungsarten', 'raster', 'appRaster', 'tage',
            'jahr', 'monat', 'periodeVon', 'periodeBis',
            'vorMonat', 'naechMonat', 'aktivTage', 'aktivEinsaetze',
            'verfuegbareMonate'
        ));
    }

    public function speichern(Request $request, Klient $klient, int $jahr, int $monat)
    {
        abort_if($klient->organisation_id !== $this->orgId(), 403);

        $periodeVon = Carbon::create($jahr, $monat, 1)->startOfMonth();
        $periodeBis = $periodeVon->copy()->endOfMonth();

        // Hauptbetreuer ermitteln
        $hauptbetreuer = $klient->betreuungspersonen()->where('rolle', 'hauptbetreuer')->first();
        $benutzerId    = $hauptbetreuer?->benutzer_id ?? $klient->zustaendig_id ?? auth()->id();

        // Bestehende Rapportierungs-Einsätze: History + alte Werte merken, dann löschen
        $alteEinsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->where('klient_id', $klient->id)
            ->whereBetween('datum', [$periodeVon, $periodeBis])
            ->where('checkin_methode', 'rapportierung')
            ->with('aktivitaeten')
            ->get();

        // [tag][leistungsart_id] = ['history' => [...], 'minuten' => [lt_name => min]]
        $alteDaten = [];
        foreach ($alteEinsaetze as $ae) {
            $tag  = $ae->datum->day;
            $laId = $ae->leistungsart_id;
            $alteDaten[$tag][$laId] = [
                'history'  => [],
                'minuten'  => [],
            ];
            if ($ae->admin_kommentar) {
                $zeilen = explode("\n", $ae->admin_kommentar);
                $alteDaten[$tag][$laId]['history'] = array_values(array_filter(array_slice($zeilen, 1)));
            }
            foreach ($ae->aktivitaeten as $akt) {
                $alteDaten[$tag][$laId]['minuten'][$akt->aktivitaet] = $akt->minuten;
            }
        }

        $alteEinsaetze->each->delete();

        $eintraege = $request->input('eintraege', []);

        // Alle benötigten Leistungstypen einmalig laden
        $ltIds = array_keys($eintraege);
        $ltMap = Leistungstyp::with('leistungsart')->whereIn('id', $ltIds)->get()->keyBy('id');

        // Gruppieren: [tag][leistungsart_id] = [['lt' => $lt, 'minuten' => X], ...]
        $grouped = [];
        foreach ($eintraege as $ltId => $tage) {
            $lt = $ltMap[$ltId] ?? null;
            if (!$lt) continue;
            foreach ($tage as $tag => $minuten) {
                $minuten = (int) $minuten;
                if ($minuten <= 0) continue;
                $grouped[(int) $tag][$lt->leistungsart_id][] = ['lt' => $lt, 'minuten' => $minuten];
            }
        }

        // Pro Tag + Leistungsart: App-Einsatz überschreiben oder neuen Rapportierungs-Einsatz erstellen
        foreach ($grouped as $tag => $laGroups) {
            foreach ($laGroups as $laId => $entries) {
                $totalMinuten = array_sum(array_column($entries, 'minuten'));
                $datum        = Carbon::create($jahr, $monat, $tag);

                // Prüfen ob App-Einsatz für diesen Tag + Leistungsart existiert
                $appEinsatz = Einsatz::where('organisation_id', $this->orgId())
                    ->where('klient_id', $klient->id)
                    ->where('datum', $datum->toDateString())
                    ->where('leistungsart_id', $laId)
                    ->where(fn($q) => $q->where('checkin_methode', '!=', 'rapportierung')->orWhereNull('checkin_methode'))
                    ->whereNull('tagespauschale_id')
                    ->first();

                if ($appEinsatz) {
                    // Alte Aktivitäten merken
                    $oldAkt = $appEinsatz->aktivitaeten->keyBy('aktivitaet');

                    // Vorherig geänderte LT-Namen + History aus bestehendem Kommentar
                    $adminGeaendert = [];
                    $historyZeilen  = [];
                    if ($appEinsatz->admin_kommentar) {
                        $komZeilen = explode("\n", $appEinsatz->admin_kommentar);
                        if (str_starts_with($komZeilen[0], 'Admin: ')) {
                            foreach (explode(', ', substr($komZeilen[0], 7)) as $n) {
                                $adminGeaendert[trim($n)] = true;
                            }
                        }
                        $historyZeilen = array_values(array_filter(array_slice($komZeilen, 1)));
                    }

                    // Neu geänderte LT-Namen + History-Einträge
                    $jetzt = now()->format('d.m.Y H:i');
                    foreach ($entries as $entry) {
                        $oldMin = $oldAkt[$entry['lt']->bezeichnung]->minuten ?? 0;
                        if ($oldMin != $entry['minuten']) {
                            $ltName = $entry['lt']->bezeichnung;
                            $adminGeaendert[$ltName] = true;
                            $historyZeilen[] = $jetzt . ' | ' . $ltName . ': ' . $oldMin . '→' . $entry['minuten'] . ' Min.';
                        }
                    }

                    // Nur betroffene Aktivitäten aktualisieren — Rest unangetastet
                    foreach ($entries as $entry) {
                        $appEinsatz->aktivitaeten()->updateOrCreate(
                            ['aktivitaet' => $entry['lt']->bezeichnung],
                            [
                                'organisation_id' => $this->orgId(),
                                'kategorie'       => $entry['lt']->leistungsart->bezeichnung,
                                'minuten'         => $entry['minuten'],
                            ]
                        );
                    }
                    $neuerKommentar = null;
                    if (!empty($adminGeaendert)) {
                        $neuerKommentar = 'Admin: ' . implode(', ', array_keys($adminGeaendert));
                        if (!empty($historyZeilen)) {
                            $neuerKommentar .= "\n" . implode("\n", $historyZeilen);
                        }
                    }
                    $appEinsatz->update([
                        'minuten'         => $totalMinuten,
                        'admin_kommentar' => $neuerKommentar,
                    ]);
                } else {
                    // Neuen Rapportierungs-Einsatz erstellen
                    $jetzt       = now()->format('d.m.Y H:i');
                    $alteMin     = $alteDaten[$tag][$laId]['minuten'] ?? [];
                    $alteHistory = $alteDaten[$tag][$laId]['history'] ?? [];

                    $ltNamen    = [];
                    $neueZeilen = [];
                    foreach ($entries as $entry) {
                        $ltName  = $entry['lt']->bezeichnung;
                        $vorher  = $alteMin[$ltName] ?? 0;
                        $ltNamen[] = $ltName;
                        if ($vorher != $entry['minuten']) {
                            $neueZeilen[] = $jetzt . ' | ' . $ltName . ': ' . $vorher . '→' . $entry['minuten'] . ' Min.';
                        }
                    }
                    $allHistory = array_merge($alteHistory, $neueZeilen);
                    $kommentar  = 'Admin: ' . implode(', ', $ltNamen)
                        . (!empty($allHistory) ? "\n" . implode("\n", $allHistory) : '');

                    $einsatz = Einsatz::create([
                        'organisation_id' => $this->orgId(),
                        'klient_id'       => $klient->id,
                        'benutzer_id'     => $benutzerId,
                        'leistungsart_id' => $laId,
                        'region_id'       => $klient->region_id,
                        'datum'           => $datum,
                        'minuten'         => $totalMinuten,
                        'status'          => 'abgeschlossen',
                        'checkin_methode' => 'rapportierung',
                        'checkin_zeit'    => $datum->copy()->setTime(0, 0),
                        'checkout_zeit'   => $datum->copy()->setTime(0, 0)->addMinutes($totalMinuten),
                        'verrechnet'      => false,
                        'admin_kommentar' => $kommentar,
                    ]);
                    foreach ($entries as $entry) {
                        $einsatz->aktivitaeten()->create([
                            'organisation_id' => $this->orgId(),
                            'kategorie'       => $entry['lt']->leistungsart->bezeichnung,
                            'aktivitaet'      => $entry['lt']->bezeichnung,
                            'minuten'         => $entry['minuten'],
                        ]);
                    }
                }
            }
        }

        return redirect()
            ->route('klienten.rapportierung', [$klient, $jahr, $monat])
            ->with('erfolg', 'Rapportierung gespeichert.');
    }

    public function checkout(Request $request, Einsatz $einsatz)
    {
        abort_if($einsatz->organisation_id !== $this->orgId(), 403);
        abort_if($einsatz->status !== 'aktiv', 422);

        $request->validate(['checkout_zeit' => 'required|date_format:H:i']);

        $zeit = Carbon::parse($einsatz->datum->format('Y-m-d') . ' ' . $request->checkout_zeit);

        $einsatz->update([
            'checkout_zeit' => $zeit,
            'status'        => 'abgeschlossen',
            'minuten'       => (int) $einsatz->checkin_zeit->diffInMinutes($zeit),
        ]);

        return response()->json(['ok' => true]);
    }

    public function korrigieren(Request $request, Einsatz $einsatz)
    {
        abort_if($einsatz->organisation_id !== $this->orgId(), 403);

        $request->validate([
            'minuten'         => 'required|integer|min:0',
            'admin_kommentar' => 'nullable|string|max:500',
        ]);

        $einsatz->update([
            'minuten'         => $request->minuten,
            'admin_kommentar' => $request->admin_kommentar,
        ]);

        return response()->json(['ok' => true]);
    }
}
