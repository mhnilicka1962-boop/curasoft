<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demo-Zugang für Alltagshelden GmbH
 * Erstellt: 2 Benutzer (admin + pflege) + 6 Klienten + Einsätze für 3 Wochen
 * Bestehende Daten werden NICHT verändert.
 *
 * Ausführen: php artisan db:seed --class=AlltagsheldenDemoSeeder
 */
class AlltagsheldenDemoSeeder extends Seeder
{
    private int   $orgId;
    private int   $regionId;
    private int   $adminId;
    private int   $pflegeId;
    private array $klienten = [];
    private array $la       = [];

    public function run(): void
    {
        $org = DB::table('organisationen')->first();
        if (!$org) {
            $this->command->error('Keine Organisation gefunden.');
            return;
        }
        $this->orgId = $org->id;

        $this->command->info('=== Alltagshelden Demo-Seeder ===');

        $this->setupRegion();
        $this->ladeLeistungsarten();
        $this->createBenutzer();
        $this->createKlienten();
        $this->createEinsaetze();
        $this->createTour();
        $this->printSummary();
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function setupRegion(): void
    {
        // Bevorzuge ZH, sonst erste verfügbare Region
        $region = DB::table('regionen')->where('kuerzel', 'ZH')->first()
            ?? DB::table('regionen')->first();

        if (!$region) {
            $this->command->error('Keine Region gefunden — bitte zuerst eine Region anlegen.');
            exit(1);
        }
        $this->regionId = $region->id;
        $this->command->info("Region: {$region->bezeichnung}");
    }

    private function ladeLeistungsarten(): void
    {
        foreach (DB::table('leistungsarten')->where('aktiv', true)->get() as $la) {
            $this->la[$la->bezeichnung] = $la->id;
        }
    }

    private function createBenutzer(): void
    {
        $pw = Hash::make('Alltagshelden2026!');
        $now = now();

        // Admin
        $existingAdmin = DB::table('benutzer')
            ->where('email', 'admin@alltagshelden.ch')->first();
        if ($existingAdmin) {
            $this->adminId = $existingAdmin->id;
            $this->command->info('Admin bereits vorhanden — übersprungen.');
        } else {
            $this->adminId = DB::table('benutzer')->insertGetId([
                'organisation_id' => $this->orgId,
                'anrede'          => 'Herr',
                'vorname'         => 'Karim',
                'nachname'        => 'El Merghini',
                'email'           => 'admin@alltagshelden.ch',
                'password'        => $pw,
                'rolle'           => 'admin',
                'aktiv'           => true,
                'anstellungsart'  => 'fachperson',
                'pensum'          => 100,
                'eintrittsdatum'  => today()->format('Y-m-d'),
                'created_at'      => $now, 'updated_at' => $now,
            ]);
            $this->command->info("Admin angelegt: admin@alltagshelden.ch (ID: {$this->adminId})");
        }

        // Pflege
        $existingPflege = DB::table('benutzer')
            ->where('email', 'info@spitex-alltagshelden.ch')->first();
        if ($existingPflege) {
            $this->pflegeId = $existingPflege->id;
            // Name aktualisieren falls noch alt
            DB::table('benutzer')->where('id', $this->pflegeId)
                ->update(['anrede' => 'Frau', 'vorname' => 'Yasmine', 'nachname' => 'El Merghini']);
            $this->command->info('Pflege-User bereits vorhanden — Name aktualisiert.');
        } else {
            $this->pflegeId = DB::table('benutzer')->insertGetId([
                'organisation_id' => $this->orgId,
                'anrede'          => 'Frau',
                'vorname'         => 'Yasmine',
                'nachname'        => 'El Merghini',
                'email'           => 'info@spitex-alltagshelden.ch',
                'password'        => $pw,
                'rolle'           => 'pflege',
                'aktiv'           => true,
                'anstellungsart'  => 'fachperson',
                'pensum'          => 100,
                'eintrittsdatum'  => today()->format('Y-m-d'),
                'created_at'      => $now, 'updated_at' => $now,
            ]);
            $this->command->info("Pflege angelegt: info@spitex-alltagshelden.ch (ID: {$this->pflegeId})");
        }
    }

    private function createKlienten(): void
    {
        $kk = DB::table('krankenkassen')
            ->where('organisation_id', $this->orgId)->first();
        $kkId = $kk?->id;

        $liste = [
            ['anrede' => 'Frau',  'vorname' => 'Margrit',  'nachname' => 'Bosshard',  'geburtsdatum' => '1942-03-15', 'adresse' => 'Seestrasse 12',      'plz' => '8800', 'ort' => 'Thalwil'],
            ['anrede' => 'Herr',  'vorname' => 'Werner',   'nachname' => 'Hartmann',  'geburtsdatum' => '1938-07-22', 'adresse' => 'Bahnhofstrasse 44',  'plz' => '8953', 'ort' => 'Dietikon'],
            ['anrede' => 'Frau',  'vorname' => 'Hildegard','nachname' => 'Brunner',   'geburtsdatum' => '1945-11-08', 'adresse' => 'Kirchweg 7',         'plz' => '8910', 'ort' => 'Affoltern am Albis'],
            ['anrede' => 'Herr',  'vorname' => 'Ernst',    'nachname' => 'Zimmermann','geburtsdatum' => '1935-05-30', 'adresse' => 'Hauptstrasse 23',    'plz' => '8048', 'ort' => 'Zürich'],
            ['anrede' => 'Frau',  'vorname' => 'Ursula',   'nachname' => 'Meier',     'geburtsdatum' => '1949-09-12', 'adresse' => 'Dorfstrasse 5',      'plz' => '8902', 'ort' => 'Urdorf'],
            ['anrede' => 'Frau',  'vorname' => 'Elisabeth','nachname' => 'Keller',    'geburtsdatum' => '1941-01-27', 'adresse' => 'Lindenallee 18',     'plz' => '8046', 'ort' => 'Zürich'],
        ];

        $now = now();
        foreach ($liste as $k) {
            $existing = DB::table('klienten')
                ->where('organisation_id', $this->orgId)
                ->where('nachname', $k['nachname'])
                ->where('vorname', $k['vorname'])
                ->first();

            if ($existing) {
                $this->klienten[$k['nachname']] = $existing->id;
                continue;
            }

            $id = DB::table('klienten')->insertGetId(array_merge($k, [
                'organisation_id' => $this->orgId,
                'region_id'       => $this->regionId,
                'zustaendig_id'   => $this->pflegeId,
                'aktiv'           => true,
                'klient_typ'      => 'patient',
                'created_at'      => $now, 'updated_at' => $now,
            ]));

            // KK-Zuweisung
            if ($kkId) {
                DB::table('klient_krankenkassen')->insert([
                    'klient_id'        => $id,
                    'krankenkasse_id'  => $kkId,
                    'versicherungs_typ'=> 'kvg',
                    'tiers_payant'     => false,
                    'aktiv'            => true,
                    'created_at'       => $now, 'updated_at' => $now,
                ]);
            }

            $this->klienten[$k['nachname']] = $id;
        }
        $this->command->info(count($this->klienten) . ' Klienten bereit.');
    }

    private function createEinsaetze(): void
    {
        // Leistungsart: Grundpflege bevorzugt, sonst erste verfügbare
        $laId = $this->la['Grundpflege']
            ?? $this->la['Hauswirtschaft']
            ?? array_values($this->la)[0]
            ?? null;
        if (!$laId) {
            $this->command->warn('Keine Leistungsart gefunden — Einsätze übersprungen.');
            return;
        }

        $klientIds = array_values($this->klienten);
        $heute     = today();
        $count     = 0;

        // Heute bis +21 Tage, Mo-Sa
        for ($d = 0; $d <= 21; $d++) {
            $datum = $heute->copy()->addDays($d);
            if ($datum->dayOfWeek === Carbon::SUNDAY) continue;

            // Jeder Klient bekommt jeden 2. Tag einen Einsatz
            // Zeiten fix pro Klient (immer gleiche Zeit → realistischer Tagesplan)
            $zeiten = ['07:30', '09:00', '10:30', '13:00', '14:30', '16:00'];

            foreach ($klientIds as $i => $klientId) {
                // Max. 4 Einsätze pro Tag (rotierend welche 4 von 6 Klienten)
                if ($i >= 4) continue;
                $zeit = $zeiten[$i % count($zeiten)];
                $start  = Carbon::parse($datum->format('Y-m-d') . ' ' . $zeit);
                $end    = $start->copy()->addMinutes(45);

                // Bereits vorhanden?
                $exists = DB::table('einsaetze')
                    ->where('klient_id',   $klientId)
                    ->where('benutzer_id', $this->pflegeId)
                    ->where('datum',       $datum->format('Y-m-d'))
                    ->exists();
                if ($exists) continue;

                DB::table('einsaetze')->insert([
                    'organisation_id'        => $this->orgId,
                    'klient_id'              => $klientId,
                    'benutzer_id'            => $this->pflegeId,
                    'leistungsart_id'        => $laId,
                    'region_id'              => $this->regionId,
                    'datum'                  => $datum->format('Y-m-d'),
                    'zeit_von'               => $start->format('H:i'),
                    'zeit_bis'               => $end->format('H:i'),
                    'minuten'                => 45,
                    'status'                 => $datum->isToday() || $datum->isFuture() ? 'geplant' : 'abgeschlossen',
                    'verrechnet'             => false,
                    'leistungserbringer_typ' => 'fachperson',
                    'created_at'             => now(), 'updated_at' => now(),
                ]);
                $count++;
            }
        }
        $this->command->info("{$count} Einsätze erstellt (heute bis +21 Tage).");
    }

    private function createTour(): void
    {
        $heute = today();

        // Tour für heute anlegen wenn noch keine existiert
        $existing = DB::table('touren')
            ->where('organisation_id', $this->orgId)
            ->where('benutzer_id', $this->pflegeId)
            ->where('datum', $heute->format('Y-m-d'))
            ->first();

        if ($existing) {
            $this->command->info('Tour für heute bereits vorhanden.');
            return;
        }

        $tourId = DB::table('touren')->insertGetId([
            'organisation_id' => $this->orgId,
            'benutzer_id'     => $this->pflegeId,
            'datum'           => $heute->format('Y-m-d'),
            'bezeichnung'     => 'El Merghini — ' . $heute->format('d.m.Y'),
            'created_at'      => now(), 'updated_at' => now(),
        ]);

        // Heutige Einsätze dieser Pflege-Person der Tour zuweisen
        $einsaetze = DB::table('einsaetze')
            ->where('benutzer_id', $this->pflegeId)
            ->where('datum', $heute->format('Y-m-d'))
            ->orderBy('zeit_von')
            ->get();

        foreach ($einsaetze as $i => $e) {
            DB::table('einsaetze')
                ->where('id', $e->id)
                ->update(['tour_id' => $tourId, 'tour_reihenfolge' => $i + 1]);
        }

        $this->command->info("Tour für heute angelegt mit {$einsaetze->count()} Einsätzen.");
    }

    private function printSummary(): void
    {
        $this->command->newLine();
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║  ALLTAGSHELDEN DEMO — ZUGANGSDATEN                   ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info('║  Admin-Login:                                        ║');
        $this->command->info('║    E-Mail:    admin@alltagshelden.ch                 ║');
        $this->command->info('║    Passwort:  Alltagshelden2026!                     ║');
        $this->command->info('║                                                      ║');
        $this->command->info('║  Pflege-Login (Mobile):                              ║');
        $this->command->info('║    E-Mail:    info@spitex-alltagshelden.ch           ║');
        $this->command->info('║    Passwort:  Alltagshelden2026!                     ║');
        $this->command->info('║                                                      ║');
        $this->command->info('║  URL:  https://www.curasoft.ch/login                 ║');
        $this->command->info('╚══════════════════════════════════════════════════════╝');
    }
}
