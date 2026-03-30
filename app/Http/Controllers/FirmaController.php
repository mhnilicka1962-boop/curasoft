<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\OrganisationRegion;
use App\Models\Region;
use App\Services\BexioService;
use Illuminate\Http\Request;

class FirmaController extends Controller
{
    private function org(): Organisation
    {
        return Organisation::findOrFail(auth()->user()->organisation_id);
    }

    public function index()
    {
        $org = $this->org();
        $org->load('regionen.region');

        $alleRegionen    = Region::orderBy('kuerzel')->get();
        $orgRegionenMap  = $org->regionen->keyBy('region_id');

        $hatRechnungen = \App\Models\Rechnung::where('organisation_id', $org->id)->exists();

        // Zähler: fehlende Einsätze bis Horizont (gleiche Logik wie Generator, nur zählen)
        $vorlauf  = max(5, min(30, $org->einsatz_vorlauf_tage ?? 10));
        $horizon  = today()->addDays($vorlauf);
        $einsaetzeOffen = $this->zaehleFehlende($horizon);
        $generierungsLog = \Illuminate\Support\Facades\DB::table('generierungs_log')
            ->orderByDesc('ausgefuehrt_at')->limit(10)->get();

        return view('stammdaten.firma.index', compact('org', 'alleRegionen', 'orgRegionenMap', 'hatRechnungen', 'einsaetzeOffen', 'horizon', 'generierungsLog'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'                     => ['required', 'string', 'max:150'],
            'zsr_nr'                   => ['nullable', 'string', 'max:20'],
            'gln'                      => ['nullable', 'digits:13'],
            'mwst_nr'                  => ['nullable', 'string', 'max:30'],
            'adresse'                  => ['nullable', 'string', 'max:200'],
            'postfach'                 => ['nullable', 'string', 'max:50'],
            'adresszusatz'             => ['nullable', 'string', 'max:150'],
            'plz'                      => ['nullable', 'string', 'max:10'],
            'ort'                      => ['nullable', 'string', 'max:100'],
            'telefon'                  => ['nullable', 'string', 'max:50'],
            'fax'                      => ['nullable', 'string', 'max:50'],
            'email'                    => ['nullable', 'email', 'max:150'],
            'website'                  => ['nullable', 'string', 'max:150'],
            'bank'                     => ['nullable', 'string', 'max:100'],
            'bankadresse'              => ['nullable', 'string', 'max:150'],
            'iban'                     => ['nullable', 'string', 'max:30'],
            'postcheckkonto'           => ['nullable', 'string', 'max:30'],
            'rechnungsadresse_position'=> ['nullable', 'in:links,rechts'],
            'logo_ausrichtung'         => ['nullable', 'in:links_anschrift_rechts,rechts_anschrift_links,mitte_anschrift_fusszeile'],
            'logo'                     => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'theme_farbe_primaer'      => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme_layout'             => ['nullable', 'in:sidebar,topnav'],
            'abrechnungslogik'         => ['nullable', 'in:tiers_garant,tiers_payant'],
        ]);

        $org = $this->org();

        $updates = array_merge(
            $request->only([
                'name', 'zsr_nr', 'gln', 'mwst_nr', 'adresse', 'postfach', 'adresszusatz',
                'plz', 'ort', 'telefon', 'fax', 'email', 'website',
                'bank', 'bankadresse', 'iban', 'postcheckkonto',
                'rechnungsadresse_position', 'logo_ausrichtung', 'abrechnungslogik',
            ]),
            ['druck_mit_firmendaten' => $request->boolean('druck_mit_firmendaten', true)]
        );

        // Logo hochladen
        if ($request->hasFile('logo')) {
            $file      = $request->file('logo');
            $tenant    = app()->bound('tenant') ? app('tenant') : null;
            $slug      = $tenant ? $tenant->subdomain : 'demo';
            $dateiname = 'logo_' . $slug . '.' . ($file->guessExtension() ?? 'jpg');
            if (!is_dir(public_path('uploads'))) {
                mkdir(public_path('uploads'), 0755, true);
            }
            // Altes Logo löschen
            if ($org->logo_pfad && file_exists(public_path($org->logo_pfad))) {
                unlink(public_path($org->logo_pfad));
            }
            $file->move(public_path('uploads'), $dateiname);
            $updates['logo_pfad'] = 'uploads/' . $dateiname;
        }

        // Design-Einstellungen
        $envUpdates = [];
        if ($request->filled('theme_farbe_primaer')) {
            $updates['theme_farbe_primaer'] = $request->theme_farbe_primaer;
            $envUpdates['CS_FARBE_PRIMAER'] = $request->theme_farbe_primaer;
        }
        if ($request->filled('theme_layout')) {
            $updates['theme_layout'] = $request->theme_layout;
            $envUpdates['CS_LAYOUT']  = $request->theme_layout;
        }
        if (!empty($envUpdates)) {
            $this->updateEnv($envUpdates);
            \Artisan::call('config:clear');
        }

        $org->update($updates);

        return back()->with('erfolg', 'Firmadaten wurden gespeichert.');
    }

    private function updateEnv(array $values): void
    {
        $envPfad = base_path('.env');
        $inhalt  = file_get_contents($envPfad);
        foreach ($values as $key => $value) {
            $pattern     = '/^' . preg_quote($key, '/') . '=.*/m';
            $replacement = $key . '=' . $value;
            if (preg_match($pattern, $inhalt)) {
                $inhalt = preg_replace($pattern, $replacement, $inhalt);
            } else {
                $inhalt .= "\n" . $replacement;
            }
        }
        file_put_contents($envPfad, $inhalt);
    }

    /** Kanton-Einstellungen speichern (ESR / IBAN-Override) */
    public function regionSpeichern(Request $request)
    {
        $request->validate([
            'region_id'       => ['required', 'exists:regionen,id'],
            'aktiv'           => ['boolean'],
            'zsr_nr'          => ['nullable', 'string', 'max:20'],
            'iban'            => ['nullable', 'string', 'max:30'],
            'postcheckkonto'  => ['nullable', 'string', 'max:30'],
            'bank'            => ['nullable', 'string', 'max:100'],
            'bankadresse'     => ['nullable', 'string', 'max:150'],
            'esr_teilnehmernr'=> ['nullable', 'string', 'max:20'],
            'qr_iban'         => ['nullable', 'string', 'max:30'],
            'bemerkung'       => ['nullable', 'string', 'max:500'],
        ]);

        $org = $this->org();

        OrganisationRegion::updateOrCreate(
            ['organisation_id' => $org->id, 'region_id' => $request->region_id],
            array_merge(
                $request->only(['zsr_nr', 'iban', 'postcheckkonto', 'bank', 'bankadresse', 'esr_teilnehmernr', 'qr_iban', 'bemerkung']),
                ['aktiv' => $request->boolean('aktiv', true)]
            )
        );

        return back()->with('erfolg', 'Kanton-Einstellungen gespeichert.');
    }

    public function bexioSpeichern(Request $request)
    {
        $request->validate([
            'bexio_api_key'    => ['nullable', 'string', 'max:200'],
            'bexio_mandant_id' => ['nullable', 'string', 'max:50'],
        ]);

        $org = $this->org();

        // Nur aktualisieren wenn ein neuer Key eingegeben wurde (nicht die Platzhalter)
        $updates = ['bexio_mandant_id' => $request->bexio_mandant_id];
        if ($request->bexio_api_key && $request->bexio_api_key !== '••••••••') {
            $updates['bexio_api_key'] = $request->bexio_api_key;
        }

        $org->update($updates);
        return back()->with('erfolg', 'Bexio-Einstellungen gespeichert.');
    }

    public function bexioTesten()
    {
        $org     = $this->org();
        $service = new BexioService($org);
        $result  = $service->verbindungTesten();

        if ($result['ok']) {
            return back()->with('erfolg', 'Bexio-Verbindung OK: ' . ($result['info'] ?? ''));
        }
        return back()->with('fehler', 'Bexio-Verbindung fehlgeschlagen: ' . ($result['fehler'] ?? 'Unbekannter Fehler'));
    }

    private function zaehleFehlende(\Carbon\Carbon $horizon): int
    {
        $orgId = auth()->user()->organisation_id;
        $total = 0;

        // Serien
        $serien = \App\Models\Serie::whereHas('klient', fn($q) => $q->where('aktiv', true))
            ->where('organisation_id', $orgId)
            ->where('gueltig_ab', '<=', today())
            ->where(fn($q) => $q
                ->where('auto_verlaengern', true)
                ->orWhere(fn($q2) => $q2
                    ->where('auto_verlaengern', false)
                    ->whereNotNull('gueltig_bis')
                    ->where('gueltig_bis', '>', today())
                )
            )
            ->get();

        foreach ($serien as $serie) {
            $serieHorizont = (!$serie->auto_verlaengern && $serie->gueltig_bis?->lt($horizon))
                ? $serie->gueltig_bis
                : $horizon;

            $letzter = \App\Models\Einsatz::where('serie_id', $serie->id)
                ->orderByDesc('datum')->value('datum');

            $ab = $letzter
                ? \Carbon\Carbon::parse($letzter)->addDay()
                : \Carbon\Carbon::parse($serie->gueltig_ab)->max(today());

            if ($ab->gt($serieHorizont)) continue;

            $wochentage = array_map('intval', $serie->wochentage ?? []);
            $current    = $ab->copy()->startOfDay();

            while ($current->lte($serieHorizont)) {
                $passt = match ($serie->rhythmus) {
                    'taeglich'     => true,
                    'woechentlich' => empty($wochentage) || in_array($current->dayOfWeek, $wochentage),
                    default        => false,
                };
                if ($passt) $total++;
                $current->addDay();
            }
        }

        // Tagespauschalen
        $pauschalen = \App\Models\Tagespauschale::whereHas('klient', fn($q) => $q->where('aktiv', true))
            ->where('organisation_id', $orgId)
            ->where('datum_von', '<=', today())
            ->where(fn($q) => $q
                ->where('auto_verlaengern', true)
                ->orWhere(fn($q2) => $q2
                    ->where('auto_verlaengern', false)
                    ->whereNotNull('datum_bis')
                    ->where('datum_bis', '>', today())
                )
            )
            ->get();

        foreach ($pauschalen as $tp) {
            $tpBis = $tp->datum_bis?->lt($horizon) ? $tp->datum_bis : $horizon;

            $letzter = \App\Models\Einsatz::where('tagespauschale_id', $tp->id)
                ->orderByDesc('datum')->value('datum');

            $ab = $letzter
                ? \Carbon\Carbon::parse($letzter)->addDay()
                : $tp->datum_von->copy();

            if ($ab->gt($tpBis)) continue;

            $total += $ab->copy()->startOfDay()->diffInDays($tpBis->copy()->startOfDay()) + 1;
        }

        return $total;
    }

    /** Einsatz-Vorlauf Einstellungen speichern */
    public function einsatzVorlaufSpeichern(Request $request)
    {
        abort_unless(auth()->user()->rolle === 'admin', 403);

        $request->validate([
            'einsatz_vorlauf_tage' => ['required', 'integer', 'min:5', 'max:30'],
        ]);

        $this->org()->update([
            'einsatz_vorlauf_tage' => $request->einsatz_vorlauf_tage,
        ]);

        return redirect(route('firma.index') . '#einsatz-generierung')->with('erfolg', 'Einsatz-Vorlauf gespeichert.');
    }

    /** Einsätze jetzt generieren (manuell) — max. 50 pro Klick */
    public function einsaetzeJetztGenerieren()
    {
        abort_unless(auth()->user()->rolle === 'admin', 403);

        $org     = $this->org();
        $vorlauf = max(5, min(30, $org->einsatz_vorlauf_tage ?? 10));
        $horizon = today()->addDays($vorlauf);

        $serien = \App\Models\Serie::whereHas('klient', fn($q) => $q->where('aktiv', true))
            ->where('gueltig_ab', '<=', today())
            ->where(fn($q) => $q
                ->where('auto_verlaengern', true)
                ->orWhere(fn($q2) => $q2
                    ->where('auto_verlaengern', false)
                    ->whereNotNull('gueltig_bis')
                    ->where('gueltig_bis', '>', today())
                )
            )
            ->with('klient')
            ->get();

        $total        = 0;
        $benutzerCache = [];

        foreach ($serien as $serie) {
            if ($total >= 50) break;

            $serieHorizont = (!$serie->auto_verlaengern && $serie->gueltig_bis?->lt($horizon))
                ? $serie->gueltig_bis
                : $horizon;

            $letzter = \App\Models\Einsatz::where('serie_id', $serie->id)
                ->orderByDesc('datum')->value('datum');

            $ab = $letzter
                ? \Carbon\Carbon::parse($letzter)->addDay()
                : \Carbon\Carbon::parse($serie->gueltig_ab)->max(today());

            if ($ab->gt($serieHorizont)) continue;

            $wochentage = array_map('intval', $serie->wochentage ?? []);
            $leTyp      = $serie->leistungserbringer_typ ?? 'fachperson';
            $benutzerId = $serie->benutzer_id;
            $current    = $ab->copy()->startOfDay();

            while ($current->lte($serieHorizont) && $total < 50) {
                $passt = match ($serie->rhythmus) {
                    'taeglich'     => true,
                    'woechentlich' => empty($wochentage) || in_array($current->dayOfWeek, $wochentage),
                    default        => false,
                };

                if ($passt) {
                    $minuten = collect($serie->leistungsarten)->sum('minuten');
                    $e = \App\Models\Einsatz::create([
                        'organisation_id'        => $serie->organisation_id,
                        'klient_id'              => $serie->klient_id,
                        'benutzer_id'            => $benutzerId,
                        'region_id'              => $serie->klient->region_id,
                        'datum'                  => $current->format('Y-m-d'),
                        'zeit_von'               => $serie->zeit_von,
                        'zeit_bis'               => $serie->zeit_bis,
                        'minuten'                => $minuten ?: null,
                        'leistungserbringer_typ' => $leTyp,
                        'bemerkung'              => $serie->bemerkung,
                        'status'                 => 'geplant',
                        'serie_id'               => $serie->id,
                    ]);

                    foreach ($serie->leistungsarten as $la) {
                        \App\Models\EinsatzLeistungsart::create([
                            'einsatz_id'      => $e->id,
                            'leistungsart_id' => $la['id'],
                            'minuten'         => $la['minuten'],
                        ]);
                    }

                    if ($leTyp !== 'angehoerig' && $benutzerId) {
                        if (!isset($benutzerCache[$benutzerId])) {
                            $benutzerCache[$benutzerId] = \App\Models\Benutzer::find($benutzerId);
                        }
                        $ma   = $benutzerCache[$benutzerId];
                        $tour = \App\Models\Tour::where('organisation_id', $serie->organisation_id)
                            ->where('benutzer_id', $benutzerId)
                            ->whereDate('datum', $current->format('Y-m-d'))
                            ->first();
                        if (!$tour) {
                            $tour = \App\Models\Tour::create([
                                'organisation_id' => $serie->organisation_id,
                                'benutzer_id'     => $benutzerId,
                                'datum'           => $current->format('Y-m-d'),
                                'bezeichnung'     => 'Tour ' . ($ma?->vorname ?? '') . ' · ' . $current->format('d.m.Y'),
                                'start_zeit'      => $e->zeit_von ?? '08:00:00',
                                'status'          => 'geplant',
                            ]);
                        }
                        $max = $tour->einsaetze()->max('tour_reihenfolge') ?? 0;
                        $e->update(['tour_id' => $tour->id, 'tour_reihenfolge' => $max + 1]);
                    }

                    $total++;
                }
                $current->addDay();
            }
        }

        // Tagespauschalen
        if ($total < 50) {
            $pauschalen = \App\Models\Tagespauschale::whereHas('klient', fn($q) => $q->where('aktiv', true))
                ->where('datum_von', '<=', today())
                ->where(fn($q) => $q
                    ->where('auto_verlaengern', true)
                    ->orWhere(fn($q2) => $q2
                        ->where('auto_verlaengern', false)
                        ->whereNotNull('datum_bis')
                        ->where('datum_bis', '>', today())
                    )
                )
                ->get();

            foreach ($pauschalen as $tp) {
                if ($total >= 50) break;

                $tpBis = $tp->datum_bis?->lt($horizon) ? $tp->datum_bis : $horizon;

                $letzter = \App\Models\Einsatz::where('tagespauschale_id', $tp->id)
                    ->orderByDesc('datum')->value('datum');

                $ab = $letzter
                    ? \Carbon\Carbon::parse($letzter)->addDay()
                    : $tp->datum_von->copy();

                if ($ab->gt($tpBis)) continue;

                $zustaendigId = $tp->klient?->zustaendig_id ?? $tp->erstellt_von;
                $current      = $ab->copy()->startOfDay();

                while ($current->lte($tpBis) && $total < 50) {
                    \App\Models\Einsatz::create([
                        'organisation_id'   => $tp->organisation_id,
                        'klient_id'         => $tp->klient_id,
                        'benutzer_id'       => $zustaendigId,
                        'tagespauschale_id' => $tp->id,
                        'datum'             => $current->format('Y-m-d'),
                        'datum_bis'         => $current->format('Y-m-d'),
                        'verrechnet'        => false,
                        'status'            => $current->lt(today()) ? 'abgeschlossen' : 'geplant',
                    ]);
                    $current->addDay();
                    $total++;
                }
            }
        }

        $org->update(['letzter_generierungs_lauf' => now()]);

        \Illuminate\Support\Facades\DB::table('generierungs_log')->insert([
            'ausgefuehrt_at'      => now(),
            'einsaetze_generiert' => $total,
            'fehler'              => 0,
            'via'                 => 'manuell',
            'meldung'             => null,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $meldung = $total > 0
            ? $total . ' Einsätze bis ' . $horizon->format('d.m.Y') . ' generiert.'
            : 'Keine fehlenden Einsätze — alles aktuell bis ' . $horizon->format('d.m.Y') . '.';

        return redirect(route('firma.index') . '#einsatz-generierung')->with('erfolg', $meldung);
    }

    /** Logo löschen */
    public function logoLoeschen()
    {
        $org = $this->org();
        if ($org->logo_pfad && file_exists(public_path($org->logo_pfad))) {
            unlink(public_path($org->logo_pfad));
        }
        $org->update(['logo_pfad' => null]);
        return back()->with('erfolg', 'Logo wurde gelöscht.');
    }

    /** Kanton aus Org entfernen */
    public function regionEntfernen(Request $request, Region $region)
    {
        OrganisationRegion::where('organisation_id', $this->org()->id)
            ->where('region_id', $region->id)
            ->delete();

        return back()->with('erfolg', "Kanton {$region->kuerzel} wurde entfernt.");
    }
}
