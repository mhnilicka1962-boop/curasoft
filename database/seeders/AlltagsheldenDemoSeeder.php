<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Demo-Zugang für Alltagshelden GmbH
 * 1 Admin + 3 Pflegepersonen + 6 Klienten + 6 Einsätze/Tag
 * Ab Montag dieser Woche bis +21 Tage
 *
 * Ausführen: php artisan db:seed --class=AlltagsheldenDemoSeeder
 */
class AlltagsheldenDemoSeeder extends Seeder
{
    private int   $orgId;
    private int   $regionId;
    private int   $adminId;
    private array $pflegeIds = [];
    private array $klienten  = [];
    private array $la        = [];

    public function run(): void
    {
        $org = DB::table('organisationen')->first();
        if (!$org) { $this->command->error('Keine Organisation gefunden.'); return; }
        $this->orgId = $org->id;

        $this->command->info('=== Alltagshelden Demo-Seeder ===');

        $this->setupRegion();
        $this->ladeLeistungsarten();
        $this->createBenutzer();
        $this->createKlienten();
        $this->createEinsaetze();
        $this->createTouren();
        $this->printSummary();
    }

    private function setupRegion(): void
    {
        $region = DB::table('regionen')->where('kuerzel', 'ZH')->first()
            ?? DB::table('regionen')->first();
        if (!$region) { $this->command->error('Keine Region gefunden.'); exit(1); }
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
        $pw  = Hash::make('Alltagshelden2026!');
        $now = now();

        // Admin
        $existing = DB::table('benutzer')->where('email', 'admin@alltagshelden.ch')->first();
        if ($existing) {
            $this->adminId = $existing->id;
            $this->command->info('Admin bereits vorhanden.');
        } else {
            $this->adminId = DB::table('benutzer')->insertGetId([
                'organisation_id' => $this->orgId,
                'anrede' => 'Herr', 'vorname' => 'Karim', 'nachname' => 'El Merghini',
                'email' => 'admin@alltagshelden.ch', 'password' => $pw,
                'rolle' => 'admin', 'aktiv' => true, 'anstellungsart' => 'fachperson',
                'pensum' => 100, 'eintrittsdatum' => today()->format('Y-m-d'),
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $this->command->info("Admin angelegt: admin@alltagshelden.ch");
        }

        // 3 Pflegepersonen
        $pflegePersonen = [
            ['anrede' => 'Frau', 'vorname' => 'Yasmine', 'nachname' => 'El Merghini', 'email' => 'info@spitex-alltagshelden.ch'],
            ['anrede' => 'Frau', 'vorname' => 'Nina',    'nachname' => 'Bosshard',     'email' => 'nina.bosshard@alltagshelden.ch'],
            ['anrede' => 'Herr', 'vorname' => 'Marc',    'nachname' => 'Widmer',       'email' => 'marc.widmer@alltagshelden.ch'],
        ];

        foreach ($pflegePersonen as $p) {
            $existing = DB::table('benutzer')->where('email', $p['email'])->first();
            if ($existing) {
                // Name sicherstellen
                DB::table('benutzer')->where('id', $existing->id)->update([
                    'anrede' => $p['anrede'], 'vorname' => $p['vorname'], 'nachname' => $p['nachname'],
                ]);
                $this->pflegeIds[] = $existing->id;
                $this->command->info("{$p['vorname']} {$p['nachname']} bereits vorhanden.");
            } else {
                $id = DB::table('benutzer')->insertGetId([
                    'organisation_id' => $this->orgId,
                    'anrede' => $p['anrede'], 'vorname' => $p['vorname'], 'nachname' => $p['nachname'],
                    'email' => $p['email'], 'password' => $pw,
                    'rolle' => 'pflege', 'aktiv' => true, 'anstellungsart' => 'fachperson',
                    'pensum' => 100, 'eintrittsdatum' => today()->format('Y-m-d'),
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $this->pflegeIds[] = $id;
                $this->command->info("{$p['vorname']} {$p['nachname']} angelegt: {$p['email']}");
            }
        }
    }

    private function createKlienten(): void
    {
        $kkId = DB::table('krankenkassen')->where('organisation_id', $this->orgId)->value('id');
        $now  = now();

        // 6 Klienten — je 2 pro Pflegeperson
        $liste = [
            ['anrede' => 'Frau', 'vorname' => 'Margrit',   'nachname' => 'Bauer',      'geburtsdatum' => '1942-03-15', 'adresse' => 'Seestrasse 12',     'plz' => '8800', 'ort' => 'Thalwil',    'pflege_idx' => 0],
            ['anrede' => 'Herr', 'vorname' => 'Werner',    'nachname' => 'Hartmann',   'geburtsdatum' => '1938-07-22', 'adresse' => 'Bahnhofstrasse 44', 'plz' => '8953', 'ort' => 'Dietikon',   'pflege_idx' => 0],
            ['anrede' => 'Frau', 'vorname' => 'Hildegard', 'nachname' => 'Brunner',    'geburtsdatum' => '1945-11-08', 'adresse' => 'Kirchweg 7',        'plz' => '8910', 'ort' => 'Affoltern',  'pflege_idx' => 1],
            ['anrede' => 'Herr', 'vorname' => 'Ernst',     'nachname' => 'Zimmermann', 'geburtsdatum' => '1935-05-30', 'adresse' => 'Hauptstrasse 23',   'plz' => '8048', 'ort' => 'Zürich',     'pflege_idx' => 1],
            ['anrede' => 'Frau', 'vorname' => 'Ursula',    'nachname' => 'Meier',      'geburtsdatum' => '1949-09-12', 'adresse' => 'Dorfstrasse 5',     'plz' => '8902', 'ort' => 'Urdorf',     'pflege_idx' => 2],
            ['anrede' => 'Frau', 'vorname' => 'Elisabeth', 'nachname' => 'Keller',     'geburtsdatum' => '1941-01-27', 'adresse' => 'Lindenallee 18',    'plz' => '8046', 'ort' => 'Zürich',     'pflege_idx' => 2],
        ];

        foreach ($liste as $k) {
            $pflegeIdx = $k['pflege_idx'];
            unset($k['pflege_idx']);

            $existing = DB::table('klienten')
                ->where('organisation_id', $this->orgId)
                ->where('nachname', $k['nachname'])->where('vorname', $k['vorname'])->first();

            if ($existing) {
                $this->klienten[] = ['id' => $existing->id, 'pflege_idx' => $pflegeIdx];
                continue;
            }

            $id = DB::table('klienten')->insertGetId(array_merge($k, [
                'organisation_id' => $this->orgId,
                'region_id'       => $this->regionId,
                'zustaendig_id'   => $this->pflegeIds[$pflegeIdx],
                'aktiv'           => true,
                'klient_typ'      => 'patient',
                'created_at'      => $now, 'updated_at' => $now,
            ]));

            if ($kkId) {
                DB::table('klient_krankenkassen')->insert([
                    'klient_id' => $id, 'krankenkasse_id' => $kkId,
                    'versicherungs_typ' => 'kvg', 'tiers_payant' => false, 'aktiv' => true,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
            }

            $this->klienten[] = ['id' => $id, 'pflege_idx' => $pflegeIdx];
        }
        $this->command->info(count($this->klienten) . ' Klienten bereit (2 pro Pflegeperson).');
    }

    private function createEinsaetze(): void
    {
        $laId = $this->la['Grundpflege'] ?? $this->la['Hauswirtschaft'] ?? array_values($this->la)[0] ?? null;
        if (!$laId) { $this->command->warn('Keine Leistungsart — Einsätze übersprungen.'); return; }

        // Zeiten pro Pflegeperson: 2 Einsätze/Tag
        $zeiten = [
            0 => ['07:30', '10:00'],  // Yasmine
            1 => ['08:00', '13:00'],  // Nina
            2 => ['09:00', '14:30'],  // Marc
        ];

        // Ab Montag dieser Woche bis +21 Tage
        $start = today()->startOfWeek(Carbon::MONDAY);
        $end   = today()->addDays(21);
        $count = 0;

        for ($datum = $start->copy(); $datum->lte($end); $datum->addDay()) {
            if ($datum->dayOfWeek === Carbon::SUNDAY) continue;

            foreach ($this->klienten as $k) {
                $pflegeIdx  = $k['pflege_idx'];
                $pflegeId   = $this->pflegeIds[$pflegeIdx];
                $klientId   = $k['id'];
                // Klient 0+1 → pflege 0, je 1 Einsatz = 2 Zeiten verteilt auf beide Klienten
                $zeitIdx    = array_search($klientId, array_column(
                    array_filter($this->klienten, fn($x) => $x['pflege_idx'] === $pflegeIdx),
                    'id'
                ));
                $zeit       = $zeiten[$pflegeIdx][$zeitIdx] ?? $zeiten[$pflegeIdx][0];
                $start_dt   = Carbon::parse($datum->format('Y-m-d') . ' ' . $zeit);
                $end_dt     = $start_dt->copy()->addMinutes(45);

                $exists = DB::table('einsaetze')
                    ->where('klient_id', $klientId)->where('benutzer_id', $pflegeId)
                    ->where('datum', $datum->format('Y-m-d'))->exists();
                if ($exists) continue;

                DB::table('einsaetze')->insert([
                    'organisation_id'        => $this->orgId,
                    'klient_id'              => $klientId,
                    'benutzer_id'            => $pflegeId,
                    'leistungsart_id'        => $laId,
                    'region_id'              => $this->regionId,
                    'datum'                  => $datum->format('Y-m-d'),
                    'zeit_von'               => $start_dt->format('H:i'),
                    'zeit_bis'               => $end_dt->format('H:i'),
                    'minuten'                => 45,
                    'status'                 => $datum->lt(today()) ? 'abgeschlossen' : 'geplant',
                    'verrechnet'             => false,
                    'leistungserbringer_typ' => 'fachperson',
                    'created_at'             => now(), 'updated_at' => now(),
                ]);
                $count++;
            }
        }
        $this->command->info("{$count} Einsätze erstellt (Mo dieser Woche bis +21 Tage, 6/Tag).");
    }

    private function createTouren(): void
    {
        // Tour für heute pro Pflegeperson
        $pflegeNamen = ['Yasmine El Merghini', 'Nina Bosshard', 'Marc Widmer'];
        $tourCount   = 0;

        foreach ($this->pflegeIds as $i => $pflegeId) {
            $existing = DB::table('touren')
                ->where('organisation_id', $this->orgId)
                ->where('benutzer_id', $pflegeId)
                ->where('datum', today()->format('Y-m-d'))->first();

            if ($existing) continue;

            $tourId = DB::table('touren')->insertGetId([
                'organisation_id' => $this->orgId,
                'benutzer_id'     => $pflegeId,
                'datum'           => today()->format('Y-m-d'),
                'bezeichnung'     => $pflegeNamen[$i] . ' — ' . today()->format('d.m.Y'),
                'created_at'      => now(), 'updated_at' => now(),
            ]);

            $einsaetze = DB::table('einsaetze')
                ->where('benutzer_id', $pflegeId)->where('datum', today()->format('Y-m-d'))
                ->orderBy('zeit_von')->get();

            foreach ($einsaetze as $j => $e) {
                DB::table('einsaetze')->where('id', $e->id)
                    ->update(['tour_id' => $tourId, 'tour_reihenfolge' => $j + 1]);
            }
            $tourCount++;
        }
        $this->command->info("{$tourCount} Touren für heute angelegt (je 2 Einsätze pro Tour).");
    }

    private function printSummary(): void
    {
        $this->command->newLine();
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║  ALLTAGSHELDEN DEMO — ZUGANGSDATEN                   ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info('║  Admin:   admin@alltagshelden.ch                     ║');
        $this->command->info('║  Pflege1: info@spitex-alltagshelden.ch               ║');
        $this->command->info('║  Pflege2: nina.bosshard@alltagshelden.ch             ║');
        $this->command->info('║  Pflege3: marc.widmer@alltagshelden.ch               ║');
        $this->command->info('║  Passwort alle: Alltagshelden2026!                   ║');
        $this->command->info('║  URL: https://www.curasoft.ch/login                  ║');
        $this->command->info('╚══════════════════════════════════════════════════════╝');
    }
}
