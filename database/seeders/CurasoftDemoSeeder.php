<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * CurasoftDemoSeeder — Generischer Vorführ-Datensatz für www.curasoft.ch
 *
 * 5 Klienten:
 *   1. Elisabeth Brunner   — Grundpflege, AG, Mo–Fr, Sandra
 *   2. Hans Weber          — Grundpflege, AG, Mo/Mi/Fr, Sandra
 *   3. Margrit Schneider   — Grundpflege, BE, täglich, Peter, tiers_payant, Verordnung
 *   4. Werner Keller       — Grundpflege, BE, Di/Do, Peter
 *   5. Josef Gerber        — Grundpflege, BE, Mo/Mi/Fr, Ruth (Angehörige)
 *
 * 5 Mitarbeiter + 2 Rechnungsläufe.
 * Idempotent: Kann beliebig oft neu ausgeführt werden.
 */
class CurasoftDemoSeeder extends Seeder
{
    private int   $orgId;
    private int   $adminId;
    private array $regionen    = []; // kuerzel → id
    private array $la          = []; // bezeichnung-key → id
    private array $kk          = []; // name-key → id
    private array $mitarbeiter = []; // key → id
    private array $klienten    = []; // key → id

    // ─────────────────────────────────────────────────────────────────────────
    // MAIN
    // ─────────────────────────────────────────────────────────────────────────

    public function run(): void
    {
        $org = DB::table('organisationen')->first();
        if (!$org) {
            $this->command->error('Keine Organisation gefunden. Zuerst Setup ausführen.');
            return;
        }
        $this->orgId = $org->id;

        DB::transaction(function () {
            $this->bereinigen();
            $this->organisationUpdaten();
            $this->ladenLeistungsarten();
            $this->regionen();
            $this->mitarbeiter();
            $this->klienten();
            $this->kontakte();
            $this->beitraege();
            $this->einsaetzeUndTouren();
            $this->rapporte();
            $this->rechnungslaeufe();
        });

        $this->command->info('CurasoftDemoSeeder erfolgreich durchgelaufen.');
        $this->command->info('  5 Klienten, 5 Mitarbeiter, 2 Rechnungsläufe.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BEREINIGUNG
    // ─────────────────────────────────────────────────────────────────────────

    private function bereinigen(): void
    {
        // Reihenfolge beachtet FK-Abhängigkeiten
        DB::table('rechnungs_positionen')->delete();
        DB::table('rechnungen')->delete();
        DB::table('rechnungslaeufe')->delete();
        DB::table('einsatz_aktivitaeten')->delete();
        DB::table('einsaetze')->delete();
        DB::table('tagespauschalen')->delete();
        DB::table('rapporte')->delete();
        DB::table('touren')->delete();

        // Klient-Unterrelationen
        DB::table('klient_benutzer')->delete();
        DB::table('klient_krankenkassen')->delete();
        DB::table('klient_diagnosen')->delete();
        DB::table('klient_verordnungen')->delete();
        DB::table('klient_adressen')->delete();
        DB::table('klient_aerzte')->delete();
        DB::table('klient_kontakte')->delete();
        DB::table('klient_pflegestufen')->delete();
        DB::table('klient_beitraege')->delete();

        DB::table('klienten')->delete();

        // Benutzer ausser mhn@itjob.ch
        $mhn = DB::table('benutzer')->where('email', 'mhn@itjob.ch')->first();
        $this->adminId = $mhn ? $mhn->id : 0;

        DB::table('benutzer_qualifikation')
            ->when($this->adminId, fn($q) => $q->where('benutzer_id', '!=', $this->adminId))
            ->delete();
        DB::table('benutzer_leistungsarten')
            ->when($this->adminId, fn($q) => $q->where('benutzer_id', '!=', $this->adminId))
            ->delete();
        DB::table('benutzer')
            ->where('email', '!=', 'mhn@itjob.ch')
            ->delete();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ORGANISATION
    // ─────────────────────────────────────────────────────────────────────────

    private function organisationUpdaten(): void
    {
        DB::table('organisationen')->where('id', $this->orgId)->update([
            'name'       => 'CuraSoft Demo Spitex AG',
            'adresse'    => 'Bahnhofstrasse 1',
            'plz'        => '5000',
            'ort'        => 'Aarau',
            'kanton'     => 'AG',
            'telefon'    => '062 123 45 67',
            'email'      => 'info@curasoft-demo.ch',
            'website'    => 'https://www.curasoft.ch',
            'iban'       => 'CH56 0483 5012 3456 7800 9',
            'updated_at' => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LEISTUNGSARTEN laden
    // ─────────────────────────────────────────────────────────────────────────

    private function ladenLeistungsarten(): void
    {
        $las = DB::table('leistungsarten')
            ->where('aktiv', true)
            ->get();

        foreach ($las as $la) {
            $key = mb_strtolower($la->bezeichnung);
            $this->la[$key] = $la->id;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REGIONEN + LEISTUNGSREGIONEN
    // ─────────────────────────────────────────────────────────────────────────

    private function regionen(): void
    {
        foreach (['AG' => 'Aargau', 'BE' => 'Bern'] as $kuerzel => $name) {
            $region = DB::table('regionen')
                ->where('kuerzel', $kuerzel)
                ->first();

            if (!$region) {
                $id = DB::table('regionen')->insertGetId([
                    'bezeichnung' => $name,
                    'kuerzel'     => $kuerzel,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            } else {
                $id = $region->id;
            }

            $this->regionen[$kuerzel] = $id;

            // Leistungsregionen prüfen und ggf. anlegen
            $hatLR = DB::table('leistungsregionen')
                ->where('region_id', $id)
                ->exists();

            if (!$hatLR) {
                $las = DB::table('leistungsarten')
                    ->where('aktiv', true)
                    ->get();

                foreach ($las as $la) {
                    DB::table('leistungsregionen')->insert([
                        'leistungsart_id' => $la->id,
                        'region_id'       => $id,
                        'gueltig_ab'      => '2026-01-01',
                        'gueltig_bis'     => null,
                        'ansatz'          => $la->ansatz_default ?? 1.05,
                        'kkasse'          => $la->kvg_default ?? 0.84,
                        'ansatz_akut'     => $la->ansatz_akut_default ?? 0,
                        'kkasse_akut'     => $la->kvg_akut_default ?? 0,
                        'kassenpflichtig' => true,
                        'verrechnung'     => true,
                        'einsatz_minuten' => false,
                        'einsatz_stunden' => true,
                        'einsatz_tage'    => false,
                        'mwst'            => false,
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MITARBEITER
    // ─────────────────────────────────────────────────────────────────────────

    private function mitarbeiter(): void
    {
        $liste = [
            'admin'  => ['email' => 'admin@curasoft-demo.ch',  'vorname' => 'Demo',   'nachname' => 'Admin',   'rolle' => 'admin',   'anstellungsart' => 'fachperson'],
            'sandra' => ['email' => 'sandra@curasoft-demo.ch', 'vorname' => 'Sandra', 'nachname' => 'Meier',   'rolle' => 'pflege',  'anstellungsart' => 'fachperson'],
            'peter'  => ['email' => 'peter@curasoft-demo.ch',  'vorname' => 'Peter',  'nachname' => 'Keller',  'rolle' => 'pflege',  'anstellungsart' => 'fachperson'],
            'anna'   => ['email' => 'anna@curasoft-demo.ch',   'vorname' => 'Anna',   'nachname' => 'Brunner', 'rolle' => 'pflege',  'anstellungsart' => 'fachperson'],
            'ruth'   => ['email' => 'ruth@curasoft-demo.ch',   'vorname' => 'Ruth',   'nachname' => 'Gerber',  'rolle' => 'pflege',  'anstellungsart' => 'angehoerig'],
        ];

        foreach ($liste as $key => $data) {
            $existing = DB::table('benutzer')->where('email', $data['email'])->first();
            if ($existing) {
                DB::table('benutzer')->where('id', $existing->id)->update([
                    'organisation_id' => $this->orgId,
                    'vorname'         => $data['vorname'],
                    'nachname'        => $data['nachname'],
                    'rolle'           => $data['rolle'],
                    'anstellungsart'  => $data['anstellungsart'],
                    'aktiv'           => true,
                    'password'        => Hash::make('Demo2026!'),
                    'updated_at'      => now(),
                ]);
                $this->mitarbeiter[$key] = $existing->id;
            } else {
                $this->mitarbeiter[$key] = DB::table('benutzer')->insertGetId([
                    'organisation_id' => $this->orgId,
                    'email'           => $data['email'],
                    'vorname'         => $data['vorname'],
                    'nachname'        => $data['nachname'],
                    'rolle'           => $data['rolle'],
                    'anstellungsart'  => $data['anstellungsart'],
                    'aktiv'           => true,
                    'password'        => Hash::make('Demo2026!'),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        // adminId auf Demo-Admin setzen (für Rechnungslauf erstellt_von)
        if (!$this->adminId) {
            $this->adminId = $this->mitarbeiter['admin'];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KRANKENKASSEN laden
    // ─────────────────────────────────────────────────────────────────────────

    private function ladeKrankenkasse(string $suchbegriff): int
    {
        $kk = DB::table('krankenkassen')
            ->where('name', 'ilike', "%{$suchbegriff}%")
            ->first();

        if (!$kk) {
            $kk = DB::table('krankenkassen')->first();
        }

        return $kk ? $kk->id : 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KLIENTEN
    // ─────────────────────────────────────────────────────────────────────────

    private function klienten(): void
    {
        $this->erstelleKlient1();
        $this->erstelleKlient2();
        $this->erstelleKlient3();
        $this->erstelleKlient4();
        $this->erstelleKlient5();
    }

    private function erstelleKlient1(): void
    {
        // Elisabeth Brunner — AG, Sandra, Mo–Fr
        $id = DB::table('klienten')->insertGetId([
            'organisation_id'  => $this->orgId,
            'anrede'           => 'Frau',
            'klient_typ'       => 'patient',
            'vorname'          => 'Elisabeth',
            'nachname'         => 'Brunner',
            'geburtsdatum'     => '1942-03-12',
            'geschlecht'       => 'w',
            'zivilstand'       => 'verwitwet',
            'adresse'          => 'Rosenweg 7',
            'plz'              => '5400',
            'ort'              => 'Baden',
            'telefon'          => '056 444 55 66',
            'notfallnummer'    => '079 333 22 11',
            'region_id'        => $this->regionen['AG'],
            'zustaendig_id'    => $this->mitarbeiter['sandra'],
            'rechnungstyp'     => 'kombiniert',
            'aktiv'            => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $this->klienten['brunner'] = $id;

        // KK
        $kkId = $this->ladeKrankenkasse('CSS');
        if ($kkId) {
            DB::table('klient_krankenkassen')->insert([
                'klient_id'          => $id,
                'krankenkasse_id'    => $kkId,
                'versicherungs_typ'  => 'kvg',
                'deckungstyp'        => 'allgemein',
                'versichertennummer' => '756.4321.8765.09',
                'tiers_payant'       => false,
                'gueltig_ab'         => '2026-01-01',
                'aktiv'              => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        // Diagnosen
        DB::table('klient_diagnosen')->insert([
            ['klient_id' => $id, 'icd10_code' => 'I10', 'icd10_bezeichnung' => 'Essentielle Hypertonie',    'diagnose_typ' => 'haupt', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
            ['klient_id' => $id, 'icd10_code' => 'E11', 'icd10_bezeichnung' => 'Diabetes mellitus Typ 2',   'diagnose_typ' => 'neben', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Verordnung
        $laId = $this->laId('grundpflege');
        DB::table('klient_verordnungen')->insert([
            'klient_id'       => $id,
            'leistungsart_id' => $laId,
            'verordnungs_nr'  => 'VO-2026-0124',
            'gueltig_ab'      => '2026-01-15',
            'gueltig_bis'     => '2026-07-15',
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // klient_benutzer
        DB::table('klient_benutzer')->insert([
            'klient_id'    => $id,
            'benutzer_id'  => $this->mitarbeiter['sandra'],
            'rolle'        => 'hauptbetreuer',
            'beziehungstyp'=> 'fachperson',
            'aktiv'        => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function erstelleKlient2(): void
    {
        // Hans Weber — AG, Sandra, Mo/Mi/Fr
        $id = DB::table('klienten')->insertGetId([
            'organisation_id'  => $this->orgId,
            'anrede'           => 'Herr',
            'klient_typ'       => 'patient',
            'vorname'          => 'Hans',
            'nachname'         => 'Weber',
            'geburtsdatum'     => '1947-07-22',
            'geschlecht'       => 'm',
            'zivilstand'       => 'verheiratet',
            'adresse'          => 'Laurenzenvorstadt 15',
            'plz'              => '5000',
            'ort'              => 'Aarau',
            'telefon'          => '062 891 23 45',
            'notfallnummer'    => '078 123 45 67',
            'region_id'        => $this->regionen['AG'],
            'zustaendig_id'    => $this->mitarbeiter['sandra'],
            'rechnungstyp'     => 'kombiniert',
            'aktiv'            => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $this->klienten['weber'] = $id;

        $kkId = $this->ladeKrankenkasse('Helsana');
        if ($kkId) {
            DB::table('klient_krankenkassen')->insert([
                'klient_id'          => $id,
                'krankenkasse_id'    => $kkId,
                'versicherungs_typ'  => 'kvg',
                'deckungstyp'        => 'allgemein',
                'versichertennummer' => '756.5678.9012.34',
                'tiers_payant'       => false,
                'gueltig_ab'         => '2026-01-01',
                'aktiv'              => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        DB::table('klient_diagnosen')->insert([
            ['klient_id' => $id, 'icd10_code' => 'M16', 'icd10_bezeichnung' => 'Koxarthrose', 'diagnose_typ' => 'haupt', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('klient_benutzer')->insert([
            'klient_id'    => $id,
            'benutzer_id'  => $this->mitarbeiter['sandra'],
            'rolle'        => 'hauptbetreuer',
            'beziehungstyp'=> 'fachperson',
            'aktiv'        => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function erstelleKlient3(): void
    {
        // Margrit Schneider — BE, Peter, täglich, tiers_payant
        $id = DB::table('klienten')->insertGetId([
            'organisation_id'  => $this->orgId,
            'anrede'           => 'Frau',
            'klient_typ'       => 'patient',
            'vorname'          => 'Margrit',
            'nachname'         => 'Schneider',
            'geburtsdatum'     => '1945-11-08',
            'geschlecht'       => 'w',
            'zivilstand'       => 'verwitwet',
            'adresse'          => 'Kramgasse 58',
            'plz'              => '3011',
            'ort'              => 'Bern',
            'telefon'          => '031 311 78 90',
            'notfallnummer'    => '076 789 01 23',
            'region_id'        => $this->regionen['BE'],
            'zustaendig_id'    => $this->mitarbeiter['peter'],
            'rechnungstyp'     => 'kombiniert',
            'aktiv'            => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $this->klienten['schneider'] = $id;

        $kkId = $this->ladeKrankenkasse('Sanitas');
        if ($kkId) {
            DB::table('klient_krankenkassen')->insert([
                'klient_id'          => $id,
                'krankenkasse_id'    => $kkId,
                'versicherungs_typ'  => 'kvg',
                'deckungstyp'        => 'allgemein',
                'versichertennummer' => '756.6789.0123.45',
                'tiers_payant'       => true,
                'gueltig_ab'         => '2026-01-01',
                'aktiv'              => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        DB::table('klient_diagnosen')->insert([
            ['klient_id' => $id, 'icd10_code' => 'G30', 'icd10_bezeichnung' => 'Alzheimer-Krankheit',  'diagnose_typ' => 'haupt', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
            ['klient_id' => $id, 'icd10_code' => 'F32', 'icd10_bezeichnung' => 'Depressive Episode',   'diagnose_typ' => 'neben', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $laId = $this->laId('grundpflege');
        DB::table('klient_verordnungen')->insert([
            'klient_id'       => $id,
            'leistungsart_id' => $laId,
            'verordnungs_nr'  => 'VO-2026-0215',
            'gueltig_ab'      => '2026-02-01',
            'gueltig_bis'     => '2026-07-31',
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        DB::table('klient_benutzer')->insert([
            'klient_id'    => $id,
            'benutzer_id'  => $this->mitarbeiter['peter'],
            'rolle'        => 'hauptbetreuer',
            'beziehungstyp'=> 'fachperson',
            'aktiv'        => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function erstelleKlient4(): void
    {
        // Werner Keller — BE, Peter, Di/Do
        $id = DB::table('klienten')->insertGetId([
            'organisation_id'  => $this->orgId,
            'anrede'           => 'Herr',
            'klient_typ'       => 'patient',
            'vorname'          => 'Werner',
            'nachname'         => 'Keller',
            'geburtsdatum'     => '1940-05-14',
            'geschlecht'       => 'm',
            'zivilstand'       => 'verheiratet',
            'adresse'          => 'Hauptgasse 10',
            'plz'              => '3600',
            'ort'              => 'Thun',
            'telefon'          => '033 222 34 56',
            'notfallnummer'    => '079 234 56 78',
            'region_id'        => $this->regionen['BE'],
            'zustaendig_id'    => $this->mitarbeiter['peter'],
            'rechnungstyp'     => 'kombiniert',
            'aktiv'            => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $this->klienten['keller'] = $id;

        $kkId = $this->ladeKrankenkasse('SWICA');
        if ($kkId) {
            DB::table('klient_krankenkassen')->insert([
                'klient_id'          => $id,
                'krankenkasse_id'    => $kkId,
                'versicherungs_typ'  => 'kvg',
                'deckungstyp'        => 'allgemein',
                'versichertennummer' => '756.7890.1234.56',
                'tiers_payant'       => false,
                'gueltig_ab'         => '2026-01-01',
                'aktiv'              => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        DB::table('klient_diagnosen')->insert([
            ['klient_id' => $id, 'icd10_code' => 'I50', 'icd10_bezeichnung' => 'Herzinsuffizienz', 'diagnose_typ' => 'haupt', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('klient_benutzer')->insert([
            'klient_id'    => $id,
            'benutzer_id'  => $this->mitarbeiter['peter'],
            'rolle'        => 'hauptbetreuer',
            'beziehungstyp'=> 'fachperson',
            'aktiv'        => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function erstelleKlient5(): void
    {
        // Josef Gerber — BE, Ruth (Angehörige), Mo/Mi/Fr
        $id = DB::table('klienten')->insertGetId([
            'organisation_id'  => $this->orgId,
            'anrede'           => 'Herr',
            'klient_typ'       => 'pflegebeduerftig',
            'vorname'          => 'Josef',
            'nachname'         => 'Gerber',
            'geburtsdatum'     => '1955-03-18',
            'geschlecht'       => 'm',
            'zivilstand'       => 'verheiratet',
            'adresse'          => 'Gerechtigkeitsgasse 14',
            'plz'              => '3011',
            'ort'              => 'Bern',
            'telefon'          => '031 311 56 78',
            'notfallnummer'    => '079 456 78 90',
            'region_id'        => $this->regionen['BE'],
            'zustaendig_id'    => $this->mitarbeiter['ruth'],
            'rechnungstyp'     => 'kombiniert',
            'aktiv'            => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
        $this->klienten['gerber'] = $id;

        $kkId = $this->ladeKrankenkasse('Concordia');
        if ($kkId) {
            DB::table('klient_krankenkassen')->insert([
                'klient_id'          => $id,
                'krankenkasse_id'    => $kkId,
                'versicherungs_typ'  => 'kvg',
                'deckungstyp'        => 'allgemein',
                'versichertennummer' => '756.8901.2345.67',
                'tiers_payant'       => false,
                'gueltig_ab'         => '2026-01-01',
                'aktiv'              => true,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }

        DB::table('klient_diagnosen')->insert([
            ['klient_id' => $id, 'icd10_code' => 'M16', 'icd10_bezeichnung' => 'Koxarthrose', 'diagnose_typ' => 'haupt', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::table('klient_benutzer')->insert([
            'klient_id'    => $id,
            'benutzer_id'  => $this->mitarbeiter['ruth'],
            'rolle'        => 'hauptbetreuer',
            'beziehungstyp'=> 'angehoerig_pflegend',
            'aktiv'        => true,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EINSÄTZE + TOUREN
    // ─────────────────────────────────────────────────────────────────────────

    private function einsaetzeUndTouren(): void
    {
        $heute              = Carbon::today();
        $vonDat             = $heute->copy()->startOfMonth()->subMonths(4); // ca. 1. Nov/Dez
        $bisDat             = $heute->copy()->addWeeks(6)->endOfDay();      // ca. Ende April
        $aktuellerMonat     = $heute->copy()->startOfMonth();

        $laGrundpflege = $this->laId('grundpflege');
        $regionAg      = $this->regionen['AG'];
        $regionBe      = $this->regionen['BE'];

        $konfigurationen = [
            'brunner'   => ['benutzer' => 'sandra', 'wochentage' => [1,2,3,4,5],       'von' => '08:00', 'bis' => '08:45', 'min' => 45, 'region' => $regionAg, 'tour' => true,  'le_typ' => 'fachperson'],
            'weber'     => ['benutzer' => 'sandra', 'wochentage' => [1,3,5],            'von' => '09:30', 'bis' => '10:15', 'min' => 45, 'region' => $regionAg, 'tour' => true,  'le_typ' => 'fachperson'],
            'schneider' => ['benutzer' => 'peter',  'wochentage' => [1,2,3,4,5,6,0],   'von' => '09:00', 'bis' => '10:00', 'min' => 60, 'region' => $regionBe, 'tour' => true,  'le_typ' => 'fachperson'],
            'keller'    => ['benutzer' => 'peter',  'wochentage' => [2,4],              'von' => '14:00', 'bis' => '14:30', 'min' => 30, 'region' => $regionBe, 'tour' => true,  'le_typ' => 'fachperson'],
            'gerber'    => ['benutzer' => 'ruth',   'wochentage' => [1,3,5],            'von' => '10:00', 'bis' => '10:30', 'min' => 30, 'region' => $regionBe, 'tour' => false, 'le_typ' => 'angehoerig'],
        ];

        // Touren-Cache: "benutzer_id_datum" => tour_id
        $tourenCache = [];

        foreach ($konfigurationen as $klientKey => $cfg) {
            $klientId   = $this->klienten[$klientKey];
            $benutzerId = $this->mitarbeiter[$cfg['benutzer']];
            $serieId    = (string) Str::uuid();
            $reihenfolge = [];

            $current = $vonDat->copy()->startOfDay();

            while ($current <= $bisDat) {
                $wochentag = (int) $current->dayOfWeek;
                if (!in_array($wochentag, $cfg['wochentage'])) {
                    $current->addDay();
                    continue;
                }

                $datumStr     = $current->format('Y-m-d');
                $istVergangen = $current->lt($heute);
                $istHeute     = $current->isSameDay($heute);
                $status       = ($istVergangen || $istHeute) ? 'abgeschlossen' : 'geplant';

                // verrechnet = true für alle abgeschlossenen Einsätze aus vergangenen Monaten
                $verrechnet = $istVergangen && $current->lt($aktuellerMonat);

                $tourId  = $this->holeTourId($tourenCache, $cfg['tour'], $benutzerId, $datumStr, $istVergangen, $istHeute);
                $rkKey   = ($tourId ?? 0) . '_' . $datumStr;
                $reihenfolge[$rkKey] = ($reihenfolge[$rkKey] ?? 0) + 1;

                DB::table('einsaetze')->insert([
                    'organisation_id'        => $this->orgId,
                    'klient_id'              => $klientId,
                    'benutzer_id'            => $benutzerId,
                    'leistungsart_id'        => $laGrundpflege,
                    'region_id'              => $cfg['region'],
                    'leistungserbringer_typ' => $cfg['le_typ'],
                    'serie_id'               => $serieId,
                    'datum'                  => $datumStr,
                    'zeit_von'               => $cfg['von'],
                    'zeit_bis'               => $cfg['bis'],
                    'minuten'                => $cfg['min'],
                    'status'                 => $status,
                    'checkin_zeit'           => ($istVergangen || $istHeute) ? $datumStr . ' ' . $cfg['von'] . ':00' : null,
                    'checkout_zeit'          => ($istVergangen || $istHeute) ? $datumStr . ' ' . $cfg['bis'] . ':00' : null,
                    'checkin_methode'        => ($istVergangen || $istHeute) ? 'manuell' : null,
                    'checkout_methode'       => ($istVergangen || $istHeute) ? 'manuell' : null,
                    'verrechnet'             => $verrechnet,
                    'tour_id'                => $tourId,
                    'tour_reihenfolge'       => $reihenfolge[$rkKey],
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);

                $current->addDay();
            }
        }

        $this->annaSpringerEinsaetze($tourenCache, $vonDat, $bisDat, $heute, $laGrundpflege, $regionAg, $aktuellerMonat);
        $this->einmaligeEinsaetze($laGrundpflege, $regionAg, $regionBe);
    }

    private function holeTourId(array &$cache, bool $mitTour, int $benutzerId, string $datumStr, bool $istVergangen, bool $istHeute): ?int
    {
        if (!$mitTour) return null;

        $tourKey = $benutzerId . '_' . $datumStr;
        if (!isset($cache[$tourKey])) {
            $bBenutzer  = DB::table('benutzer')->where('id', $benutzerId)->first();
            $name       = $bBenutzer ? $bBenutzer->vorname . ' ' . $bBenutzer->nachname : 'Tour';
            $tourStatus = (!$istVergangen && !$istHeute) ? 'geplant' : ($istHeute ? 'gestartet' : 'abgeschlossen');
            $cache[$tourKey] = DB::table('touren')->insertGetId([
                'organisation_id' => $this->orgId,
                'benutzer_id'     => $benutzerId,
                'datum'           => $datumStr,
                'bezeichnung'     => $name . ' — ' . Carbon::parse($datumStr)->format('d.m.Y'),
                'status'          => $tourStatus,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
        return $cache[$tourKey];
    }

    private function annaSpringerEinsaetze(array &$tourenCache, Carbon $vonDat, Carbon $bisDat, Carbon $heute, ?int $laId, int $regionAg, Carbon $aktuellerMonat): void
    {
        $annaId    = $this->mitarbeiter['anna'];
        $brunnerId = $this->klienten['brunner'];
        $weberId   = $this->klienten['weber'];

        // 1. und 3. Dienstag als Vertretung: Woche 1 → Brunner, Woche 3 → Weber
        $current     = $vonDat->copy()->startOfDay();
        $reihenfolge = [];

        while ($current <= $bisDat) {
            if ($current->dayOfWeek !== 2) { // nur Dienstag
                $current->addDay();
                continue;
            }
            $week = (int) ceil($current->day / 7);
            if ($week !== 1 && $week !== 3) {
                $current->addDay();
                continue;
            }

            $datumStr     = $current->format('Y-m-d');
            $istVergangen = $current->lt($heute);
            $istHeute     = $current->isSameDay($heute);
            $status       = ($istVergangen || $istHeute) ? 'abgeschlossen' : 'geplant';
            $verrechnet   = $istVergangen && $current->lt($aktuellerMonat);
            $klientId     = ($week === 1) ? $brunnerId : $weberId;
            $von          = ($week === 1) ? '08:00' : '09:30';
            $bis          = ($week === 1) ? '08:45' : '10:15';

            $tourId = $this->holeTourId($tourenCache, true, $annaId, $datumStr, $istVergangen, $istHeute);
            $rkKey  = ($tourId ?? 0) . '_' . $datumStr;
            $reihenfolge[$rkKey] = ($reihenfolge[$rkKey] ?? 0) + 1;

            DB::table('einsaetze')->insert([
                'organisation_id'        => $this->orgId,
                'klient_id'              => $klientId,
                'benutzer_id'            => $annaId,
                'leistungsart_id'        => $laId,
                'region_id'              => $regionAg,
                'leistungserbringer_typ' => 'fachperson',
                'serie_id'               => null,
                'datum'                  => $datumStr,
                'zeit_von'               => $von,
                'zeit_bis'               => $bis,
                'minuten'                => 45,
                'status'                 => $status,
                'checkin_zeit'           => ($istVergangen || $istHeute) ? $datumStr . ' ' . $von . ':00' : null,
                'checkout_zeit'          => ($istVergangen || $istHeute) ? $datumStr . ' ' . $bis . ':00' : null,
                'checkin_methode'        => ($istVergangen || $istHeute) ? 'manuell' : null,
                'checkout_methode'       => ($istVergangen || $istHeute) ? 'manuell' : null,
                'verrechnet'             => $verrechnet,
                'tour_id'                => $tourId,
                'tour_reihenfolge'       => $reihenfolge[$rkKey],
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);

            $current->addDay();
        }
    }

    private function einmaligeEinsaetze(?int $laGrundpflege, int $regionAg, int $regionBe): void
    {
        $heute   = Carbon::today();
        $laAbkl  = $this->laId('abklaerung') ?? $laGrundpflege;
        $aktMonat = $heute->copy()->startOfMonth();

        $einmalige = [
            ['klient' => 'brunner',   'benutzer' => 'sandra', 'region' => $regionAg,
             'datum' => $heute->copy()->subMonths(4)->addDays(3)->format('Y-m-d'),
             'von' => '10:00', 'bis' => '11:00', 'min' => 60, 'la' => $laAbkl,
             'bemerkung' => 'Erstbesuch und Bedarfsabklärung'],
            ['klient' => 'brunner',   'benutzer' => 'sandra', 'region' => $regionAg,
             'datum' => $heute->copy()->subWeeks(3)->format('Y-m-d'),
             'von' => '13:00', 'bis' => '14:00', 'min' => 60, 'la' => $laGrundpflege,
             'bemerkung' => 'Begleitung Arzttermin Dr. Weber'],
            ['klient' => 'weber',     'benutzer' => 'sandra', 'region' => $regionAg,
             'datum' => $heute->copy()->subMonths(4)->addDays(5)->format('Y-m-d'),
             'von' => '11:00', 'bis' => '12:00', 'min' => 60, 'la' => $laAbkl,
             'bemerkung' => 'Erstbesuch und Bedarfsabklärung'],
            ['klient' => 'schneider', 'benutzer' => 'peter',  'region' => $regionBe,
             'datum' => $heute->copy()->subMonths(4)->addDays(2)->format('Y-m-d'),
             'von' => '09:00', 'bis' => '10:30', 'min' => 90, 'la' => $laAbkl,
             'bemerkung' => 'Erstbesuch, Assessment und Pflegeplanung'],
            ['klient' => 'keller',    'benutzer' => 'peter',  'region' => $regionBe,
             'datum' => $heute->copy()->subMonths(3)->format('Y-m-d'),
             'von' => '15:00', 'bis' => '16:00', 'min' => 60, 'la' => $laAbkl,
             'bemerkung' => 'Erstbesuch'],
            ['klient' => 'gerber',    'benutzer' => 'admin',  'region' => $regionBe,
             'datum' => $heute->copy()->subMonths(4)->addDays(7)->format('Y-m-d'),
             'von' => '10:00', 'bis' => '11:00', 'min' => 60, 'la' => $laAbkl,
             'bemerkung' => 'Erstbesuch und Abklärung Angehörigenpflege'],
        ];

        foreach ($einmalige as $e) {
            $datum = Carbon::parse($e['datum']);
            DB::table('einsaetze')->insert([
                'organisation_id'        => $this->orgId,
                'klient_id'              => $this->klienten[$e['klient']],
                'benutzer_id'            => $this->mitarbeiter[$e['benutzer']],
                'leistungsart_id'        => $e['la'],
                'region_id'              => $e['region'],
                'leistungserbringer_typ' => 'fachperson',
                'serie_id'               => null,
                'datum'                  => $e['datum'],
                'zeit_von'               => $e['von'],
                'zeit_bis'               => $e['bis'],
                'minuten'                => $e['min'],
                'bemerkung'              => $e['bemerkung'],
                'status'                 => 'abgeschlossen',
                'checkin_zeit'           => $e['datum'] . ' ' . $e['von'] . ':00',
                'checkout_zeit'          => $e['datum'] . ' ' . $e['bis'] . ':00',
                'checkin_methode'        => 'manuell',
                'checkout_methode'       => 'manuell',
                'verrechnet'             => $datum->lt($aktMonat),
                'tour_id'                => null,
                'tour_reihenfolge'       => null,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RAPPORTE
    // ─────────────────────────────────────────────────────────────────────────

    private function rapporte(): void
    {
        $texte = [
            'brunner'   => [
                'Frau Brunner in gutem Allgemeinzustand angetroffen. Körperpflege vollständig durchgeführt, beim Ankleiden assistiert. Blutdruck 138/82 mmHg, Puls regelmässig. Klientin war gut gestimmt und hat gut geschlafen.',
                'Grundpflege durchgeführt inkl. Ganzkörperwäsche. Frau Brunner berichtete über leichte Knieschmerzen beim Aufstehen. Medikamente gerichtet und übergeben. Wohnung gut gepflegt.',
            ],
            'weber'     => [
                'Herr Weber war wach und kooperativ. Grundpflege inkl. Rasur durchgeführt. Medikamente gerichtet und verabreicht. Keine Auffälligkeiten.',
                'Herr Weber klagte über Müdigkeit. Grundpflege vollständig durchgeführt. RR 142/88, Puls 74. Hinweis an nächste Pflegeperson: auf Flüssigkeitszufuhr achten.',
            ],
            'schneider' => [
                'Frau Schneider zeigte sich heute unruhig und zeitweise desorientiert. Grundpflege behutsam durchgeführt. Angehörige telefonisch informiert. Vitalzeichen stabil.',
                'Frau Schneider ruhig und freundlich gestimmt. Körperwäsche, Ankleiden und Mundpflege durchgeführt. Hat gut gefrühstückt. Situation stabil.',
            ],
            'keller'    => [
                'Herr Keller klagte über leichte Atemnot bei Belastung. Grundpflege durchgeführt, Kompressionsstrümpfe angelegt. Puls 78, RR 145/90. Arzt informiert.',
                'Herr Keller in gutem Zustand, kooperativ. Grundpflege und Kompressionsstrümpfe. RR 138/86. Keine wesentlichen Veränderungen.',
            ],
            'gerber'    => [
                'Herr Gerber durch Tochter Ruth Gerber gepflegt. Mobilisation und Körperpflege durchgeführt. Herr Gerber in stabilem Zustand.',
                'Grundpflege durch Angehörige Ruth Gerber. Herr Gerber gut gelaunt, hat Morgengymnastik gemacht. Vitalzeichen unauffällig.',
            ],
        ];

        foreach ($texte as $klientKey => $rapportTexte) {
            $klientId = $this->klienten[$klientKey];

            // Vergangene Einsätze suchen (die ersten 2)
            $einsaetze = DB::table('einsaetze')
                ->where('klient_id', $klientId)
                ->where('status', 'abgeschlossen')
                ->orderBy('datum')
                ->limit(2)
                ->get();

            foreach ($einsaetze as $i => $einsatz) {
                $text = $rapportTexte[$i] ?? $rapportTexte[0];
                DB::table('rapporte')->insert([
                    'organisation_id' => $this->orgId,
                    'klient_id'       => $klientId,
                    'benutzer_id'     => $einsatz->benutzer_id,
                    'einsatz_id'      => $einsatz->id,
                    'datum'           => $einsatz->datum,
                    'inhalt'          => $text,
                    'rapport_typ'     => 'pflege',
                    'vertraulich'     => false,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RECHNUNGSLÄUFE
    // ─────────────────────────────────────────────────────────────────────────

    private function rechnungslaeufe(): void
    {
        $heute       = Carbon::today();
        $adminId     = $this->adminId ?: ($this->mitarbeiter['admin'] ?? 1);
        $rechnungsNr = 1;

        // ── 4 Perioden: -3M (bezahlt), -2M (gesendet), -1M (abgeschlossen), aktuell (entwurf)
        $perioden = [
            [
                'von'    => $heute->copy()->startOfMonth()->subMonths(3),
                'bis'    => $heute->copy()->startOfMonth()->subMonths(3)->endOfMonth(),
                'status' => 'abgeschlossen',
                'rechnung_status' => 'bezahlt',
            ],
            [
                'von'    => $heute->copy()->startOfMonth()->subMonths(2),
                'bis'    => $heute->copy()->startOfMonth()->subMonths(2)->endOfMonth(),
                'status' => 'abgeschlossen',
                'rechnung_status' => 'gesendet',
            ],
            [
                'von'    => $heute->copy()->startOfMonth()->subMonth(),
                'bis'    => $heute->copy()->startOfMonth()->subMonth()->endOfMonth(),
                'status' => 'abgeschlossen',
                'rechnung_status' => 'entwurf',
            ],
            [
                'von'    => $heute->copy()->startOfMonth(),
                'bis'    => $heute->copy(),
                'status' => 'entwurf',
                'rechnung_status' => 'entwurf',
            ],
        ];

        foreach ($perioden as $periode) {
            $vonDat  = $periode['von'];
            $bisDat  = $periode['bis'];
            $laufStatus = $periode['status'];
            $rechnungStatus = $periode['rechnung_status'];

            $laufId = DB::table('rechnungslaeufe')->insertGetId([
                'organisation_id'      => $this->orgId,
                'periode_von'          => $vonDat->format('Y-m-d'),
                'periode_bis'          => $bisDat->format('Y-m-d'),
                'rechnungstyp'         => null,
                'tarif_patient'        => null,
                'tarif_kk'             => null,
                'anzahl_erstellt'      => 0,
                'anzahl_uebersprungen' => 0,
                'status'               => $laufStatus,
                'erstellt_von'         => $adminId,
                'created_at'           => $bisDat->copy()->addDays(2),
                'updated_at'           => $bisDat->copy()->addDays(2),
            ]);

            $this->erstelleRechnungenFuerLauf($laufId, $vonDat, $bisDat, $rechnungStatus, $rechnungsNr);

            $anzahl = DB::table('rechnungen')->where('rechnungslauf_id', $laufId)->count();
            DB::table('rechnungslaeufe')->where('id', $laufId)->update([
                'anzahl_erstellt' => $anzahl,
                'updated_at'      => now(),
            ]);
        }
    }

    private function erstelleRechnungenFuerLauf(int $laufId, Carbon $vonDat, Carbon $bisDat, string $rechnungStatus, int &$rechnungsNr): void
    {
        $klienten = ['brunner', 'weber', 'schneider', 'keller'];

        foreach ($klienten as $klientKey) {
            $klientId = $this->klienten[$klientKey];

            $einsaetze = DB::table('einsaetze')
                ->where('klient_id', $klientId)
                ->where('verrechnet', true)
                ->whereBetween('datum', [$vonDat->format('Y-m-d'), $bisDat->format('Y-m-d')])
                ->whereNotNull('checkout_zeit')
                ->get();

            // Für den aktuellen Monat (entwurf): noch nicht verrechnete abgeschlossene Einsätze
            if ($einsaetze->isEmpty()) {
                $einsaetze = DB::table('einsaetze')
                    ->where('klient_id', $klientId)
                    ->where('verrechnet', false)
                    ->where('status', 'abgeschlossen')
                    ->whereBetween('datum', [$vonDat->format('Y-m-d'), $bisDat->format('Y-m-d')])
                    ->whereNotNull('checkout_zeit')
                    ->get();
            }

            if ($einsaetze->isEmpty()) continue;

            $klient = DB::table('klienten')->where('id', $klientId)->first();
            $lr     = DB::table('leistungsregionen')
                ->where('region_id', $klient->region_id)
                ->orderByDesc('gueltig_ab')
                ->first();

            $ansatzStd = $lr ? (float) $lr->ansatz : 63.0;
            $kkasseStd = $lr ? (float) $lr->kkasse : 50.40;
            $betragPat = 0.0;
            $betragKk  = 0.0;

            $rechnungsNummer = 'RE-' . $vonDat->format('Y') . '-' . str_pad($rechnungsNr, 4, '0', STR_PAD_LEFT);
            $rechnungsNr++;

            $rechnungId = DB::table('rechnungen')->insertGetId([
                'organisation_id'  => $this->orgId,
                'klient_id'        => $klientId,
                'rechnungsnummer'  => $rechnungsNummer,
                'periode_von'      => $vonDat->format('Y-m-d'),
                'periode_bis'      => $bisDat->format('Y-m-d'),
                'rechnungsdatum'   => $bisDat->format('Y-m-d'),
                'betrag_patient'   => 0,
                'betrag_kk'        => 0,
                'betrag_total'     => 0,
                'status'           => $rechnungStatus,
                'rechnungstyp'     => 'kombiniert',
                'rechnungslauf_id' => $laufId,
                'email_versand_datum' => $rechnungStatus === 'gesendet' ? $bisDat->copy()->addDays(3) : null,
                'email_versand_an'    => $rechnungStatus === 'gesendet' ? $klient->email : null,
                'created_at'       => $bisDat->copy()->addDays(2),
                'updated_at'       => $bisDat->copy()->addDays(2),
            ]);

            foreach ($einsaetze as $einsatz) {
                $minuten = $einsatz->minuten ?? 0;
                $bPat    = round($minuten / 60.0 * max(0, $ansatzStd - $kkasseStd), 2);
                $bKk     = round($minuten / 60.0 * $kkasseStd, 2);

                DB::table('rechnungs_positionen')->insert([
                    'rechnung_id'    => $rechnungId,
                    'einsatz_id'     => $einsatz->id,
                    'datum'          => $einsatz->datum,
                    'menge'          => $minuten,
                    'einheit'        => 'minuten',
                    'beschreibung'   => null,
                    'tarif_patient'  => max(0, $ansatzStd - $kkasseStd),
                    'tarif_kk'       => $kkasseStd,
                    'betrag_patient' => $bPat,
                    'betrag_kk'      => $bKk,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                $betragPat += $bPat;
                $betragKk  += $bKk;
            }

            DB::table('rechnungen')->where('id', $rechnungId)->update([
                'betrag_patient' => round($betragPat, 2),
                'betrag_kk'      => round($betragKk, 2),
                'betrag_total'   => round($betragPat + $betragKk, 2),
                'updated_at'     => now(),
            ]);
        }
    }


    // ─────────────────────────────────────────────────────────────────────────
    // KONTAKTE
    // ─────────────────────────────────────────────────────────────────────────

    private function kontakte(): void
    {
        $liste = [
            'brunner' => [
                ['anrede' => 'Herr',  'vorname' => 'Thomas',  'nachname' => 'Brunner',    'beziehung' => 'Sohn',
                 'telefon' => '079 555 44 33', 'email' => 'thomas.brunner@gmail.com',
                 'bevollmaechtigt' => true, 'rechnungen_erhalten' => true,
                 'rolle' => 'notfallkontakt'],
            ],
            'weber' => [
                ['anrede' => 'Frau',  'vorname' => 'Margrit', 'nachname' => 'Weber',      'beziehung' => 'Ehefrau',
                 'telefon' => '062 891 23 99', 'email' => null,
                 'bevollmaechtigt' => true, 'rechnungen_erhalten' => true,
                 'rolle' => 'notfallkontakt'],
            ],
            'schneider' => [
                ['anrede' => 'Frau',  'vorname' => 'Petra',   'nachname' => 'Zimmermann', 'beziehung' => 'Tochter',
                 'telefon' => '076 789 01 23', 'email' => 'petra.zimmermann@bluewin.ch',
                 'bevollmaechtigt' => true, 'rechnungen_erhalten' => true,
                 'rolle' => 'notfallkontakt'],
                ['anrede' => 'Herr',  'vorname' => 'Daniel',  'nachname' => 'Zimmermann', 'beziehung' => 'Schwiegersohn',
                 'telefon' => '031 765 43 21', 'email' => null,
                 'bevollmaechtigt' => false, 'rechnungen_erhalten' => false,
                 'rolle' => 'angehoerig'],
            ],
            'keller' => [
                ['anrede' => 'Herr',  'vorname' => 'Beat',    'nachname' => 'Keller',     'beziehung' => 'Sohn',
                 'telefon' => '079 234 56 78', 'email' => 'beat.keller@outlook.com',
                 'bevollmaechtigt' => true, 'rechnungen_erhalten' => true,
                 'rolle' => 'notfallkontakt'],
            ],
            'gerber' => [
                ['anrede' => 'Frau',  'vorname' => 'Ruth',    'nachname' => 'Gerber',     'beziehung' => 'Tochter',
                 'telefon' => '079 456 78 90', 'email' => 'ruth@curasoft-demo.ch',
                 'bevollmaechtigt' => true, 'rechnungen_erhalten' => true,
                 'rolle' => 'angehoerig'],
                ['anrede' => 'Herr',  'vorname' => 'Martin',  'nachname' => 'Gerber',     'beziehung' => 'Sohn',
                 'telefon' => '031 456 78 12', 'email' => null,
                 'bevollmaechtigt' => false, 'rechnungen_erhalten' => false,
                 'rolle' => 'angehoerig'],
            ],
        ];

        foreach ($liste as $klientKey => $kontakte) {
            $klientId = $this->klienten[$klientKey];
            foreach ($kontakte as $k) {
                DB::table('klient_kontakte')->insert([
                    'klient_id'          => $klientId,
                    'rolle'              => $k['rolle'],
                    'anrede'             => $k['anrede'],
                    'vorname'            => $k['vorname'],
                    'nachname'           => $k['nachname'],
                    'beziehung'          => $k['beziehung'],
                    'telefon'            => $k['telefon'],
                    'email'              => $k['email'],
                    'bevollmaechtigt'    => $k['bevollmaechtigt'],
                    'rechnungen_erhalten'=> $k['rechnungen_erhalten'],
                    'aktiv'              => true,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BEITRÄGE (Patientenanteil pro Klient + Kanton)
    // ─────────────────────────────────────────────────────────────────────────

    private function beitraege(): void
    {
        // Echte Tarife aus leistungsregionen (Grundpflege):
        //   AG: CHF 98.00/h total, KK 54.60, Patient 43.40
        //   BE: CHF 68.00/h total, KK 60.00, Patient  8.00
        $adminId = $this->adminId ?: ($this->mitarbeiter['admin'] ?? 1);

        $beitraege = [
            'brunner'   => ['region' => 'AG', 'ansatz_kunde' => 43.40, 'ansatz_spitex' => 98.00, 'limit' => 20],
            'weber'     => ['region' => 'AG', 'ansatz_kunde' => 43.40, 'ansatz_spitex' => 98.00, 'limit' => 20],
            'schneider' => ['region' => 'BE', 'ansatz_kunde' =>  8.00, 'ansatz_spitex' => 68.00, 'limit' => 10],
            'keller'    => ['region' => 'BE', 'ansatz_kunde' =>  8.00, 'ansatz_spitex' => 68.00, 'limit' => 10],
            'gerber'    => ['region' => 'BE', 'ansatz_kunde' =>  8.00, 'ansatz_spitex' => 68.00, 'limit' =>  0],
        ];

        foreach ($beitraege as $klientKey => $b) {
            DB::table('klient_beitraege')->insert([
                'klient_id'              => $this->klienten[$klientKey],
                'gueltig_ab'             => '2026-01-01',
                'ansatz_kunde'           => $b['ansatz_kunde'],
                'limit_restbetrag_prozent' => $b['limit'],
                'ansatz_spitex'          => $b['ansatz_spitex'],
                'kanton_abrechnung'      => $this->regionen[$b['region']] ?? null,
                'erfasst_von'            => $adminId,
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HILFSMETHODEN
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Leistungsart-ID für einen Schlüssel (case-insensitive Teilstring-Suche).
     */
    private function laId(string $suchbegriff): ?int
    {
        foreach ($this->la as $key => $id) {
            if (str_contains($key, mb_strtolower($suchbegriff))) {
                return $id;
            }
        }

        // Fallback: Direkt in DB suchen
        $la = DB::table('leistungsarten')
            ->where('aktiv', true)
            ->where('bezeichnung', 'ilike', "%{$suchbegriff}%")
            ->first();

        return $la ? $la->id : null;
    }
}
