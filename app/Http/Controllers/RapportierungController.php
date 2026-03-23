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
            ->where('bezeichnung', '!=', 'Pauschale')
            ->orderBy('bezeichnung')
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
            $tag  = $e->datum->day;
            $laId = $e->leistungsart_id;

            // Leistungsart-Header Summe
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

            // Admin-Protokoll lesen
            $isAdminEntry  = ($e->checkin_methode === 'rapportierung');
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

            // Aktivitäten in Leistungstyp-Raster einlesen
            foreach ($e->aktivitaeten as $akt) {
                $ltId = $ltByName[$akt->aktivitaet] ?? null;
                if (!$ltId) continue;
                if (!isset($raster[$ltId][$tag])) {
                    $raster[$ltId][$tag] = ['minuten' => 0, 'admin_override' => false, 'admin_entry' => false, 'kommentar' => null, 'history' => []];
                }
                $raster[$ltId][$tag]['minuten'] += $akt->minuten;
                if ($isAdminEntry) {
                    $raster[$ltId][$tag]['admin_entry'] = true;
                    $raster[$ltId][$tag]['history']     = $historyNachLt[$akt->aktivitaet] ?? [];
                } elseif (isset($changedAkt[$akt->aktivitaet])) {
                    $raster[$ltId][$tag]['admin_override'] = true;
                    $raster[$ltId][$tag]['kommentar']      = $e->admin_kommentar;
                    $raster[$ltId][$tag]['history']        = $historyNachLt[$akt->aktivitaet] ?? [];
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

        $eintraege = $request->input('eintraege', []);

        // Alle benötigten Leistungstypen einmalig laden
        $ltIds = array_keys($eintraege);
        $ltMap = Leistungstyp::with('leistungsart')->whereIn('id', $ltIds)->get()->keyBy('id');

        $jetzt = now()->format('d.m.Y H:i');

        foreach ($eintraege as $ltId => $tage) {
            $lt = $ltMap[$ltId] ?? null;
            if (!$lt) continue;

            foreach ($tage as $tag => $minuten) {
                $minuten = (int) $minuten;
                $datum = Carbon::create($jahr, $monat, (int) $tag);

                // Einsatz suchen der diese Aktivität bereits enthält (App oder Admin, egal)
                $einsatz = Einsatz::where('organisation_id', $this->orgId())
                    ->where('klient_id', $klient->id)
                    ->where('datum', $datum->toDateString())
                    ->whereNull('tagespauschale_id')
                    ->whereNull('betrag_fix')
                    ->whereHas('aktivitaeten', fn($q) => $q->where('aktivitaet', $lt->bezeichnung))
                    ->with('aktivitaeten')
                    ->first();

                if ($einsatz) {
                    $oldAkt = $einsatz->aktivitaeten->firstWhere('aktivitaet', $lt->bezeichnung);
                    $oldMin = $oldAkt?->minuten ?? 0;

                    // Aktivität direkt überschreiben
                    $einsatz->aktivitaeten()
                        ->where('aktivitaet', $lt->bezeichnung)
                        ->update(['minuten' => $minuten]);

                    // Gesamtminuten des Einsatzes neu aus DB summieren
                    $neueTotal = $einsatz->aktivitaeten()->sum('minuten');

                    // History aufbauen
                    $adminGeaendert = [];
                    $historyZeilen  = [];
                    if ($einsatz->admin_kommentar) {
                        $komZeilen = explode("\n", $einsatz->admin_kommentar);
                        if (str_starts_with($komZeilen[0], 'Admin: ')) {
                            foreach (explode(', ', substr($komZeilen[0], 7)) as $n) {
                                $adminGeaendert[trim($n)] = true;
                            }
                        }
                        $historyZeilen = array_values(array_filter(array_slice($komZeilen, 1)));
                    }
                    if ($oldMin !== $minuten) {
                        $adminGeaendert[$lt->bezeichnung] = true;
                        $historyZeilen[] = $jetzt . ' | ' . $lt->bezeichnung . ': ' . $oldMin . '→' . $minuten . ' Min.';
                    }

                    $neuerKommentar = null;
                    if (!empty($adminGeaendert)) {
                        $neuerKommentar = 'Admin: ' . implode(', ', array_keys($adminGeaendert));
                        if (!empty($historyZeilen)) {
                            $neuerKommentar .= "\n" . implode("\n", $historyZeilen);
                        }
                    }
                    $einsatz->update([
                        'minuten'         => $neueTotal,
                        'admin_kommentar' => $neuerKommentar,
                    ]);

                } elseif ($minuten > 0) {
                    // Kein Einsatz mit dieser Aktivität → neuen Rapportierungs-Einsatz erstellen
                    $kommentar   = 'Admin: ' . $lt->bezeichnung . "\n"
                        . $jetzt . ' | ' . $lt->bezeichnung . ': 0→' . $minuten . ' Min.';

                    $neuerEinsatz = Einsatz::create([
                        'organisation_id' => $this->orgId(),
                        'klient_id'       => $klient->id,
                        'benutzer_id'     => $benutzerId,
                        'leistungsart_id' => $lt->leistungsart_id,
                        'region_id'       => $klient->region_id,
                        'datum'           => $datum,
                        'minuten'         => $minuten,
                        'status'          => 'abgeschlossen',
                        'checkin_methode' => 'rapportierung',
                        'checkin_zeit'    => $datum->copy()->setTime(0, 0),
                        'checkout_zeit'   => $datum->copy()->setTime(0, 0)->addMinutes($minuten),
                        'verrechnet'      => false,
                        'admin_kommentar' => $kommentar,
                    ]);
                    $neuerEinsatz->aktivitaeten()->create([
                        'organisation_id' => $this->orgId(),
                        'kategorie'       => $lt->leistungsart->bezeichnung,
                        'aktivitaet'      => $lt->bezeichnung,
                        'minuten'         => $minuten,
                    ]);
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
