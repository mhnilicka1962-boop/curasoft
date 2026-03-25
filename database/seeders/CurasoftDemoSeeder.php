<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * CurasoftDemoSeeder — Vollständiger Demo- und Testdatensatz
 *
 * 5 Klienten mit mehreren Leistungsarten:
 *   Brunner   (AG): Grundpflege Mo–Fr + Hauswirtschaft Di/Fr, Sandra
 *   Weber     (AG): UB Injektion Mo–Fr + UB Verband Mo/Mi/Fr, Sandra
 *   Schneider (BE): Grundpflege tägl. + UB Vitalzeichen Mo/Mi/Fr, Peter, tiers_payant
 *   Keller    (BE): Hauswirtschaft Di/Do + Grundpflege Mo/Mi/Fr, Peter
 *   Gerber    (BE): Grundpflege Mo–Fr, Angehörigenpflege Ruth
 *
 * Enthält: einsatz_aktivitaeten, Touren, Rapporte, 4 Rechnungsläufe.
 * Idempotent: kann beliebig oft ausgeführt werden.
 */
class CurasoftDemoSeeder extends Seeder
{
    private int   $orgId;
    private int   $adminId  = 0;
    private array $regionen = []; // kuerzel → id
    private array $la       = []; // mb_strtolower(bezeichnung) → id
    private array $ma       = []; // key → id (Mitarbeiter)
    private array $kl       = []; // key → id (Klienten)

    // Kurznamen → vollständiger LA-Schlüssel in $this->la
    // Schlüssel = mb_strtolower(bezeichnung) aus der DB
    private const LA_MAP = [
        'gp'  => 'grundpflege',
        'ub'  => 'untersuchung behandlung',
        'hwl' => 'hauswirtschaft',
        'ab'  => "abkl\u{00E4}rung/beratung", // abklärung/beratung
    ];

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

        $klienten  = DB::table('klienten')->where('organisation_id', $this->orgId)->count();
        $mitarbeit = DB::table('benutzer')->where('organisation_id', $this->orgId)->count();
        $laeufe    = DB::table('rechnungslaeufe')->where('organisation_id', $this->orgId)->count();
        $this->command->info('CurasoftDemoSeeder abgeschlossen.');
        $this->command->info("  {$klienten} Klienten · {$mitarbeit} Mitarbeiter · {$laeufe} Rechnungsläufe · mehrere Leistungsarten");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BEREINIGUNG
    // ─────────────────────────────────────────────────────────────────────────

    private function bereinigen(): void
    {
        DB::table('rechnungs_positionen')->delete();
        DB::table('rechnungen')->delete();
        DB::table('rechnungslaeufe')->delete();
        DB::table('einsatz_aktivitaeten')->delete();
        DB::table('einsaetze')->delete();
        DB::table('tagespauschalen')->delete();
        DB::table('rapporte')->delete();
        DB::table('touren')->delete();

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
    // LEISTUNGSARTEN LADEN
    // ─────────────────────────────────────────────────────────────────────────

    private function ladenLeistungsarten(): void
    {
        foreach (DB::table('leistungsarten')->where('aktiv', true)->get() as $la) {
            $this->la[mb_strtolower($la->bezeichnung)] = $la->id;
        }
    }

    /** Leistungsart-ID über Kurzname ('gp','ub','hwl','ab') oder vollständigen Schlüssel */
    private function laId(string $key): ?int
    {
        $fullKey = self::LA_MAP[$key] ?? $key;
        if (isset($this->la[$fullKey])) {
            return $this->la[$fullKey];
        }
        // Fallback: Teilstring-Suche
        foreach ($this->la as $k => $id) {
            if (str_contains($k, mb_strtolower($key))) {
                return $id;
            }
        }
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REGIONEN + LEISTUNGSREGIONEN
    // ─────────────────────────────────────────────────────────────────────────

    private function regionen(): void
    {
        foreach (['AG' => 'Aargau', 'BE' => 'Bern'] as $kuerzel => $name) {
            $region = DB::table('regionen')->where('kuerzel', $kuerzel)->first();
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

            if (!DB::table('leistungsregionen')->where('region_id', $id)->exists()) {
                foreach (DB::table('leistungsarten')->where('aktiv', true)->get() as $la) {
                    $isGp = stripos($la->bezeichnung, 'Grundpflege') !== false;
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
                        'verrechnung'     => !$isGp,
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

        // Grundpflege in ALLEN Regionen auf nicht verrechenbar setzen
        $gpId = DB::table('leistungsarten')->where('bezeichnung', 'Grundpflege')->value('id');
        if ($gpId) {
            DB::table('leistungsregionen')
                ->where('leistungsart_id', $gpId)
                ->update(['verrechnung' => false, 'updated_at' => now()]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MITARBEITER
    // ─────────────────────────────────────────────────────────────────────────

    private function mitarbeiter(): void
    {
        $liste = [
            'admin'  => ['email' => 'admin@curasoft-demo.ch',  'vorname' => 'Demo',   'nachname' => 'Admin',   'rolle' => 'admin',  'anstellungsart' => 'fachperson'],
            'sandra' => ['email' => 'sandra@curasoft-demo.ch', 'vorname' => 'Sandra', 'nachname' => 'Meier',   'rolle' => 'pflege', 'anstellungsart' => 'fachperson'],
            'peter'  => ['email' => 'peter@curasoft-demo.ch',  'vorname' => 'Peter',  'nachname' => 'Keller',  'rolle' => 'pflege', 'anstellungsart' => 'fachperson'],
            'anna'   => ['email' => 'anna@curasoft-demo.ch',   'vorname' => 'Anna',   'nachname' => 'Brunner', 'rolle' => 'pflege', 'anstellungsart' => 'fachperson'],
            'ruth'   => ['email' => 'ruth@curasoft-demo.ch',   'vorname' => 'Ruth',   'nachname' => 'Gerber',  'rolle' => 'pflege', 'anstellungsart' => 'angehoerig'],
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
                $this->ma[$key] = $existing->id;
            } else {
                $this->ma[$key] = DB::table('benutzer')->insertGetId([
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

        if (!$this->adminId) {
            $this->adminId = $this->ma['admin'];
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KRANKENKASSEN
    // ─────────────────────────────────────────────────────────────────────────

    private function ladeKrankenkasse(string $suchbegriff): int
    {
        $kk = DB::table('krankenkassen')->where('name', 'ilike', "%{$suchbegriff}%")->first();
        return ($kk ?? DB::table('krankenkassen')->first())?->id ?? 0;
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
        // Elisabeth Brunner — AG, Sandra, GP + HWL
        $id = DB::table('klienten')->insertGetId([
            'organisation_id' => $this->orgId,
            'anrede'          => 'Frau',
            'klient_typ'      => 'patient',
            'vorname'         => 'Elisabeth',
            'nachname'        => 'Brunner',
            'geburtsdatum'    => '1942-03-12',
            'geschlecht'      => 'w',
            'zivilstand'      => 'verwitwet',
            'adresse'         => 'Rosenweg 7',
            'plz'             => '5400',
            'ort'             => 'Baden',
            'telefon'         => '056 444 55 66',
            'notfallnummer'   => '079 333 22 11',
            'region_id'       => $this->regionen['AG'],
            'zustaendig_id'   => $this->ma['sandra'],
            'rechnungstyp'    => 'kombiniert',
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        $this->kl['brunner'] = $id;

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
        DB::table('klient_diagnosen')->insert([
            ['klient_id' => $id, 'icd10_code' => 'I10', 'icd10_bezeichnung' => 'Essentielle Hypertonie',  'diagnose_typ' => 'haupt', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
            ['klient_id' => $id, 'icd10_code' => 'E11', 'icd10_bezeichnung' => 'Diabetes mellitus Typ 2', 'diagnose_typ' => 'neben', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('klient_verordnungen')->insert([
            'klient_id'       => $id,
            'leistungsart_id' => $this->laId('gp'),
            'verordnungs_nr'  => 'VO-2026-0124',
            'gueltig_ab'      => '2026-01-15',
            'gueltig_bis'     => '2026-07-15',
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        DB::table('klient_benutzer')->insert([
            'klient_id'     => $id,
            'benutzer_id'   => $this->ma['sandra'],
            'rolle'         => 'hauptbetreuer',
            'beziehungstyp' => 'fachperson',
            'aktiv'         => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    private function erstelleKlient2(): void
    {
        // Hans Weber — AG, Sandra, UB (Injektion tägl. + Verband Mo/Mi/Fr)
        $id = DB::table('klienten')->insertGetId([
            'organisation_id' => $this->orgId,
            'anrede'          => 'Herr',
            'klient_typ'      => 'patient',
            'vorname'         => 'Hans',
            'nachname'        => 'Weber',
            'geburtsdatum'    => '1947-07-22',
            'geschlecht'      => 'm',
            'zivilstand'      => 'verheiratet',
            'adresse'         => 'Laurenzenvorstadt 15',
            'plz'             => '5000',
            'ort'             => 'Aarau',
            'email'           => 'h.weber@muster.ch',
            'telefon'         => '062 891 23 45',
            'notfallnummer'   => '078 123 45 67',
            'region_id'       => $this->regionen['AG'],
            'zustaendig_id'   => $this->ma['sandra'],
            'rechnungstyp'    => 'kombiniert',
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        $this->kl['weber'] = $id;

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
            ['klient_id' => $id, 'icd10_code' => 'M16', 'icd10_bezeichnung' => 'Koxarthrose',     'diagnose_typ' => 'haupt', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
            ['klient_id' => $id, 'icd10_code' => 'E14', 'icd10_bezeichnung' => 'Diabetes mellitus', 'diagnose_typ' => 'neben', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('klient_verordnungen')->insert([
            'klient_id'       => $id,
            'leistungsart_id' => $this->laId('ub'),
            'verordnungs_nr'  => 'VO-2026-0198',
            'gueltig_ab'      => '2026-01-01',
            'gueltig_bis'     => '2026-12-31',
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        DB::table('klient_benutzer')->insert([
            'klient_id'     => $id,
            'benutzer_id'   => $this->ma['sandra'],
            'rolle'         => 'hauptbetreuer',
            'beziehungstyp' => 'fachperson',
            'aktiv'         => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    private function erstelleKlient3(): void
    {
        // Margrit Schneider — BE, Peter, GP tägl. + UB Mo/Mi/Fr, tiers_payant
        $id = DB::table('klienten')->insertGetId([
            'organisation_id' => $this->orgId,
            'anrede'          => 'Frau',
            'klient_typ'      => 'patient',
            'vorname'         => 'Margrit',
            'nachname'        => 'Schneider',
            'geburtsdatum'    => '1945-11-08',
            'geschlecht'      => 'w',
            'zivilstand'      => 'verwitwet',
            'adresse'         => 'Kramgasse 58',
            'plz'             => '3011',
            'ort'             => 'Bern',
            'email'           => 'm.schneider@muster.ch',
            'telefon'         => '031 311 78 90',
            'notfallnummer'   => '076 789 01 23',
            'region_id'       => $this->regionen['BE'],
            'zustaendig_id'   => $this->ma['peter'],
            'rechnungstyp'    => 'kombiniert',
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        $this->kl['schneider'] = $id;

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
            ['klient_id' => $id, 'icd10_code' => 'G30', 'icd10_bezeichnung' => 'Alzheimer-Krankheit', 'diagnose_typ' => 'haupt', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
            ['klient_id' => $id, 'icd10_code' => 'F32', 'icd10_bezeichnung' => 'Depressive Episode',  'diagnose_typ' => 'neben', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('klient_verordnungen')->insert([
            'klient_id'       => $id,
            'leistungsart_id' => $this->laId('gp'),
            'verordnungs_nr'  => 'VO-2026-0215',
            'gueltig_ab'      => '2026-02-01',
            'gueltig_bis'     => '2026-07-31',
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        DB::table('klient_benutzer')->insert([
            'klient_id'     => $id,
            'benutzer_id'   => $this->ma['peter'],
            'rolle'         => 'hauptbetreuer',
            'beziehungstyp' => 'fachperson',
            'aktiv'         => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    private function erstelleKlient4(): void
    {
        // Werner Keller — BE, Peter, HWL Di/Do + GP Mo/Mi/Fr
        $id = DB::table('klienten')->insertGetId([
            'organisation_id' => $this->orgId,
            'anrede'          => 'Herr',
            'klient_typ'      => 'patient',
            'vorname'         => 'Werner',
            'nachname'        => 'Keller',
            'geburtsdatum'    => '1940-05-14',
            'geschlecht'      => 'm',
            'zivilstand'      => 'verheiratet',
            'adresse'         => 'Hauptgasse 10',
            'plz'             => '3600',
            'ort'             => 'Thun',
            'telefon'         => '033 222 34 56',
            'notfallnummer'   => '079 234 56 78',
            'region_id'       => $this->regionen['BE'],
            'zustaendig_id'   => $this->ma['peter'],
            'rechnungstyp'    => 'kombiniert',
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        $this->kl['keller'] = $id;

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
            ['klient_id' => $id, 'icd10_code' => 'I50', 'icd10_bezeichnung' => 'Herzinsuffizienz',              'diagnose_typ' => 'haupt', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
            ['klient_id' => $id, 'icd10_code' => 'N18', 'icd10_bezeichnung' => 'Chronische Niereninsuffizienz', 'diagnose_typ' => 'neben', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('klient_benutzer')->insert([
            'klient_id'     => $id,
            'benutzer_id'   => $this->ma['peter'],
            'rolle'         => 'hauptbetreuer',
            'beziehungstyp' => 'fachperson',
            'aktiv'         => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    private function erstelleKlient5(): void
    {
        // Josef Gerber — BE, Ruth (Angehörige), GP Mo–Fr
        $id = DB::table('klienten')->insertGetId([
            'organisation_id' => $this->orgId,
            'anrede'          => 'Herr',
            'klient_typ'      => 'pflegebeduerftig',
            'vorname'         => 'Josef',
            'nachname'        => 'Gerber',
            'geburtsdatum'    => '1955-03-18',
            'geschlecht'      => 'm',
            'zivilstand'      => 'verheiratet',
            'adresse'         => 'Gerechtigkeitsgasse 14',
            'plz'             => '3011',
            'ort'             => 'Bern',
            'telefon'         => '031 311 56 78',
            'notfallnummer'   => '079 456 78 90',
            'region_id'       => $this->regionen['BE'],
            'zustaendig_id'   => $this->ma['ruth'],
            'rechnungstyp'    => 'kombiniert',
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        $this->kl['gerber'] = $id;

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
            ['klient_id' => $id, 'icd10_code' => 'M16', 'icd10_bezeichnung' => 'Koxarthrose',        'diagnose_typ' => 'haupt', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
            ['klient_id' => $id, 'icd10_code' => 'Z74', 'icd10_bezeichnung' => 'Pflegebedürftigkeit', 'diagnose_typ' => 'neben', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('klient_benutzer')->insert([
            'klient_id'     => $id,
            'benutzer_id'   => $this->ma['ruth'],
            'rolle'         => 'hauptbetreuer',
            'beziehungstyp' => 'angehoerig_pflegend',
            'aktiv'         => true,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KONTAKTE
    // ─────────────────────────────────────────────────────────────────────────

    private function kontakte(): void
    {
        $liste = [
            'brunner' => [
                ['anrede' => 'Herr', 'vorname' => 'Thomas',  'nachname' => 'Brunner',    'beziehung' => 'Sohn',
                 'telefon' => '079 555 44 33', 'email' => 'thomas.brunner@gmail.com',
                 'bevollmaechtigt' => true, 'rechnungen_erhalten' => true, 'rolle' => 'notfallkontakt'],
            ],
            'weber' => [
                ['anrede' => 'Frau', 'vorname' => 'Margrit', 'nachname' => 'Weber',      'beziehung' => 'Ehefrau',
                 'telefon' => '062 891 23 99', 'email' => null,
                 'bevollmaechtigt' => true, 'rechnungen_erhalten' => true, 'rolle' => 'notfallkontakt'],
            ],
            'schneider' => [
                ['anrede' => 'Frau', 'vorname' => 'Petra',   'nachname' => 'Zimmermann', 'beziehung' => 'Tochter',
                 'telefon' => '076 789 01 23', 'email' => 'petra.zimmermann@bluewin.ch',
                 'bevollmaechtigt' => true, 'rechnungen_erhalten' => true, 'rolle' => 'notfallkontakt'],
                ['anrede' => 'Herr', 'vorname' => 'Daniel',  'nachname' => 'Zimmermann', 'beziehung' => 'Schwiegersohn',
                 'telefon' => '031 765 43 21', 'email' => null,
                 'bevollmaechtigt' => false, 'rechnungen_erhalten' => false, 'rolle' => 'angehoerig'],
            ],
            'keller' => [
                ['anrede' => 'Herr', 'vorname' => 'Beat',    'nachname' => 'Keller',     'beziehung' => 'Sohn',
                 'telefon' => '079 234 56 78', 'email' => 'beat.keller@outlook.com',
                 'bevollmaechtigt' => true, 'rechnungen_erhalten' => true, 'rolle' => 'notfallkontakt'],
            ],
            'gerber' => [
                ['anrede' => 'Frau', 'vorname' => 'Ruth',    'nachname' => 'Gerber',     'beziehung' => 'Tochter',
                 'telefon' => '079 456 78 90', 'email' => 'ruth@curasoft-demo.ch',
                 'bevollmaechtigt' => true, 'rechnungen_erhalten' => true, 'rolle' => 'angehoerig'],
                ['anrede' => 'Herr', 'vorname' => 'Martin',  'nachname' => 'Gerber',     'beziehung' => 'Sohn',
                 'telefon' => '031 456 78 12', 'email' => null,
                 'bevollmaechtigt' => false, 'rechnungen_erhalten' => false, 'rolle' => 'angehoerig'],
            ],
        ];

        foreach ($liste as $klientKey => $kontakte) {
            $klientId = $this->kl[$klientKey];
            foreach ($kontakte as $k) {
                DB::table('klient_kontakte')->insert([
                    'klient_id'           => $klientId,
                    'rolle'               => $k['rolle'],
                    'anrede'              => $k['anrede'],
                    'vorname'             => $k['vorname'],
                    'nachname'            => $k['nachname'],
                    'beziehung'           => $k['beziehung'],
                    'telefon'             => $k['telefon'],
                    'email'               => $k['email'],
                    'bevollmaechtigt'     => $k['bevollmaechtigt'],
                    'rechnungen_erhalten' => $k['rechnungen_erhalten'],
                    'aktiv'               => true,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BEITRÄGE
    // ─────────────────────────────────────────────────────────────────────────

    private function beitraege(): void
    {
        // AG: CHF 98.00/h total, KK 54.60, Patient 43.40
        // BE: CHF 68.00/h total, KK 60.00, Patient  8.00
        $adminId = $this->adminId ?: ($this->ma['admin'] ?? 1);

        $beitraege = [
            'brunner'   => ['region' => 'AG', 'ansatz_kunde' => 43.40, 'ansatz_spitex' => 98.00, 'limit' => 20],
            'weber'     => ['region' => 'AG', 'ansatz_kunde' => 43.40, 'ansatz_spitex' => 98.00, 'limit' => 20],
            'schneider' => ['region' => 'BE', 'ansatz_kunde' =>  8.00, 'ansatz_spitex' => 68.00, 'limit' => 10],
            'keller'    => ['region' => 'BE', 'ansatz_kunde' =>  8.00, 'ansatz_spitex' => 68.00, 'limit' => 10],
            'gerber'    => ['region' => 'BE', 'ansatz_kunde' =>  8.00, 'ansatz_spitex' => 68.00, 'limit' =>  0],
        ];

        foreach ($beitraege as $klientKey => $b) {
            DB::table('klient_beitraege')->insert([
                'klient_id'                => $this->kl[$klientKey],
                'gueltig_ab'               => '2026-01-01',
                'ansatz_kunde'             => $b['ansatz_kunde'],
                'limit_restbetrag_prozent' => $b['limit'],
                'ansatz_spitex'            => $b['ansatz_spitex'],
                'kanton_abrechnung'        => $this->regionen[$b['region']] ?? null,
                'erfasst_von'              => $adminId,
                'created_at'               => now(),
                'updated_at'               => now(),
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EINSÄTZE + TOUREN
    // ─────────────────────────────────────────────────────────────────────────

    private function einsaetzeUndTouren(): void
    {
        $heute          = Carbon::today();
        $vonDat         = $heute->copy()->startOfMonth()->subMonths(4);
        $bisDat         = $heute->copy()->addWeeks(6)->endOfDay();
        $aktuellerMonat = $heute->copy()->startOfMonth();

        $laGp  = $this->laId('gp');
        $laUb  = $this->laId('ub');
        $laHwl = $this->laId('hwl');
        $rAg   = $this->regionen['AG'];
        $rBe   = $this->regionen['BE'];

        // Serien: [klientKey, benutzerId, laId, regionId, wochentage, von, bis, min, kategorie, aktivitaet, mitTour, leTyp]
        $serien = [
            // Brunner: GP Mo–Fr + HWL Di/Fr
            ['brunner',   'sandra', $laGp,  $rAg, [1,2,3,4,5],     '08:00', '08:45', 45, 'Grundpflege',            'Duschen',                             true,  'fachperson'],
            ['brunner',   'sandra', $laHwl, $rAg, [2,5],            '11:00', '11:30', 30, 'Hauswirtschaft',         'HWL-Leistungen',                      true,  'fachperson'],
            // Weber: UB Injektion Mo–Fr + UB Verband Mo/Mi/Fr
            ['weber',     'sandra', $laUb,  $rAg, [1,2,3,4,5],     '09:00', '09:15', 15, 'Untersuchung/Behandlung', 'Injektion subcutan',                  true,  'fachperson'],
            ['weber',     'sandra', $laUb,  $rAg, [1,3,5],          '09:20', '09:40', 20, 'Untersuchung/Behandlung', 'Verbandwechsel',                      true,  'fachperson'],
            // Schneider: GP tägl. + UB Vitalzeichen Mo/Mi/Fr
            ['schneider', 'peter',  $laGp,  $rBe, [1,2,3,4,5,6,0], '09:00', '09:50', 50, 'Grundpflege',            'Grundpflege',                         true,  'fachperson'],
            ['schneider', 'peter',  $laUb,  $rBe, [1,3,5],          '10:00', '10:15', 15, 'Untersuchung/Behandlung', 'Vitalzeichen (Puls, BD, T, Gewicht)', true,  'fachperson'],
            // Keller: HWL Di/Do + GP Mo/Mi/Fr
            ['keller',    'peter',  $laHwl, $rBe, [2,4],            '14:00', '15:00', 60, 'Hauswirtschaft',         'HWL-Leistungen',                      true,  'fachperson'],
            ['keller',    'peter',  $laGp,  $rBe, [1,3,5],          '10:00', '10:25', 25, 'Grundpflege',            'An-/Auskleiden',                      true,  'fachperson'],
            // Gerber: GP Mo–Fr (Angehörige, kein Tour)
            ['gerber',    'ruth',   $laGp,  $rBe, [1,2,3,4,5],     '10:00', '10:35', 35, 'Grundpflege',            'Mobilisation',                        false, 'angehoerig'],
        ];

        $tourenCache = [];

        foreach ($serien as [$klientKey, $benKey, $laId, $regionId, $wochentage, $von, $bis, $min, $kategorie, $aktivitaet, $mitTour, $leTyp]) {
            $klientId   = $this->kl[$klientKey];
            $benutzerId = $this->ma[$benKey];
            $serieId    = (string) Str::uuid();
            $reihenfolge = [];

            $current = $vonDat->copy()->startOfDay();
            while ($current <= $bisDat) {
                if (!in_array((int)$current->dayOfWeek, $wochentage)) {
                    $current->addDay();
                    continue;
                }

                $datumStr      = $current->format('Y-m-d');
                $istVergangen  = $current->lt($heute);
                $istHeute      = $current->isSameDay($heute);
                $abgeschlossen = $istVergangen || $istHeute;
                $status        = $abgeschlossen ? 'abgeschlossen' : 'geplant';
                $verrechnet    = $istVergangen && $current->lt($aktuellerMonat);

                $tourId = $this->holeTourId($tourenCache, $mitTour, $benutzerId, $datumStr, $istVergangen, $istHeute);
                $rkKey  = ($tourId ?? 0) . '_' . $datumStr;
                $reihenfolge[$rkKey] = ($reihenfolge[$rkKey] ?? 0) + 1;

                $eid = DB::table('einsaetze')->insertGetId([
                    'organisation_id'        => $this->orgId,
                    'klient_id'              => $klientId,
                    'benutzer_id'            => $benutzerId,
                    'leistungsart_id'        => $laId,
                    'region_id'              => $regionId,
                    'leistungserbringer_typ' => $leTyp,
                    'serie_id'               => $serieId,
                    'datum'                  => $datumStr,
                    'zeit_von'               => $von,
                    'zeit_bis'               => $bis,
                    'minuten'                => $min,
                    'status'                 => $status,
                    'checkin_zeit'           => $abgeschlossen ? $datumStr . ' ' . $von . ':00' : null,
                    'checkout_zeit'          => $abgeschlossen ? $datumStr . ' ' . $bis . ':00' : null,
                    'checkin_methode'        => $abgeschlossen ? 'manuell' : null,
                    'checkout_methode'       => $abgeschlossen ? 'manuell' : null,
                    'verrechnet'             => $verrechnet,
                    'tour_id'                => $tourId,
                    'tour_reihenfolge'       => $reihenfolge[$rkKey],
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);

                DB::table('einsatz_aktivitaeten')->insert([
                    'einsatz_id'      => $eid,
                    'organisation_id' => $this->orgId,
                    'kategorie'       => $kategorie,
                    'aktivitaet'      => $aktivitaet,
                    'minuten'         => $min,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $current->addDay();
            }
        }

        $this->annaSpringerEinsaetze($tourenCache, $vonDat, $bisDat, $heute, $laGp, $rAg, $aktuellerMonat);
        $this->einmaligeEinsaetze($rAg, $rBe);
    }

    private function holeTourId(array &$cache, bool $mitTour, int $benutzerId, string $datumStr, bool $istVergangen, bool $istHeute): ?int
    {
        if (!$mitTour) return null;

        $tourKey = $benutzerId . '_' . $datumStr;
        if (!isset($cache[$tourKey])) {
            $ben        = DB::table('benutzer')->where('id', $benutzerId)->first();
            $name       = $ben ? $ben->vorname . ' ' . $ben->nachname : 'Tour';
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

    private function annaSpringerEinsaetze(array &$tourenCache, Carbon $vonDat, Carbon $bisDat, Carbon $heute, ?int $laGp, int $regionAg, Carbon $aktuellerMonat): void
    {
        $annaId    = $this->ma['anna'];
        $brunnerId = $this->kl['brunner'];
        $weberId   = $this->kl['weber'];

        // 1. und 3. Dienstag als Vertretung: Woche 1 → Brunner, Woche 3 → Weber
        $current     = $vonDat->copy()->startOfDay();
        $reihenfolge = [];

        while ($current <= $bisDat) {
            if ($current->dayOfWeek !== 2) {
                $current->addDay();
                continue;
            }
            $week = (int) ceil($current->day / 7);
            if ($week !== 1 && $week !== 3) {
                $current->addDay();
                continue;
            }

            $datumStr      = $current->format('Y-m-d');
            $istVergangen  = $current->lt($heute);
            $istHeute      = $current->isSameDay($heute);
            $abgeschlossen = $istVergangen || $istHeute;
            $status        = $abgeschlossen ? 'abgeschlossen' : 'geplant';
            $verrechnet    = $istVergangen && $current->lt($aktuellerMonat);
            $klientId      = ($week === 1) ? $brunnerId : $weberId;
            $von           = ($week === 1) ? '08:00' : '09:00';
            $bis           = ($week === 1) ? '08:45' : '09:15';
            $aktivitaet    = ($week === 1) ? 'Mobilisation' : 'Verbandwechsel';
            $laId          = ($week === 1) ? $laGp : $this->laId('ub');
            $kat           = ($week === 1) ? 'Grundpflege' : 'Untersuchung/Behandlung';

            $tourId = $this->holeTourId($tourenCache, true, $annaId, $datumStr, $istVergangen, $istHeute);
            $rkKey  = ($tourId ?? 0) . '_' . $datumStr;
            $reihenfolge[$rkKey] = ($reihenfolge[$rkKey] ?? 0) + 1;

            $eid = DB::table('einsaetze')->insertGetId([
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
                'checkin_zeit'           => $abgeschlossen ? $datumStr . ' ' . $von . ':00' : null,
                'checkout_zeit'          => $abgeschlossen ? $datumStr . ' ' . $bis . ':00' : null,
                'checkin_methode'        => $abgeschlossen ? 'manuell' : null,
                'checkout_methode'       => $abgeschlossen ? 'manuell' : null,
                'verrechnet'             => $verrechnet,
                'tour_id'                => $tourId,
                'tour_reihenfolge'       => $reihenfolge[$rkKey],
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);

            DB::table('einsatz_aktivitaeten')->insert([
                'einsatz_id'      => $eid,
                'organisation_id' => $this->orgId,
                'kategorie'       => $kat,
                'aktivitaet'      => $aktivitaet,
                'minuten'         => 45,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $current->addDay();
        }
    }

    private function einmaligeEinsaetze(int $regionAg, int $regionBe): void
    {
        $heute    = Carbon::today();
        $laAb     = $this->laId('ab');
        $laGp     = $this->laId('gp');
        $aktMonat = $heute->copy()->startOfMonth();

        $einmalige = [
            ['klient' => 'brunner',   'benutzer' => 'sandra', 'region' => $regionAg,
             'datum' => $heute->copy()->subMonths(4)->addDays(3)->format('Y-m-d'),
             'von' => '10:00', 'bis' => '11:00', 'min' => 60, 'la' => $laAb ?? $laGp,
             'bemerkung' => 'Erstbesuch und Bedarfsabklärung',
             'kat' => 'Abklärung/Beratung', 'akt' => 'Beratungsgespräch'],
            ['klient' => 'brunner',   'benutzer' => 'sandra', 'region' => $regionAg,
             'datum' => $heute->copy()->subWeeks(3)->format('Y-m-d'),
             'von' => '13:00', 'bis' => '14:00', 'min' => 60, 'la' => $laGp,
             'bemerkung' => 'Begleitung Arzttermin Dr. Weber',
             'kat' => 'Grundpflege', 'akt' => 'An-/Auskleiden'],
            ['klient' => 'weber',     'benutzer' => 'sandra', 'region' => $regionAg,
             'datum' => $heute->copy()->subMonths(4)->addDays(5)->format('Y-m-d'),
             'von' => '11:00', 'bis' => '12:00', 'min' => 60, 'la' => $laAb ?? $laGp,
             'bemerkung' => 'Erstbesuch und Bedarfsabklärung',
             'kat' => 'Abklärung/Beratung', 'akt' => 'Beratungsgespräch'],
            ['klient' => 'schneider', 'benutzer' => 'peter',  'region' => $regionBe,
             'datum' => $heute->copy()->subMonths(4)->addDays(2)->format('Y-m-d'),
             'von' => '09:00', 'bis' => '10:30', 'min' => 90, 'la' => $laAb ?? $laGp,
             'bemerkung' => 'Erstbesuch, Assessment und Pflegeplanung',
             'kat' => 'Abklärung/Beratung', 'akt' => 'Bedarfsanalyse'],
            ['klient' => 'keller',    'benutzer' => 'peter',  'region' => $regionBe,
             'datum' => $heute->copy()->subMonths(3)->format('Y-m-d'),
             'von' => '15:00', 'bis' => '16:00', 'min' => 60, 'la' => $laAb ?? $laGp,
             'bemerkung' => 'Erstbesuch',
             'kat' => 'Abklärung/Beratung', 'akt' => 'Beratungsgespräch'],
            ['klient' => 'gerber',    'benutzer' => 'admin',  'region' => $regionBe,
             'datum' => $heute->copy()->subMonths(4)->addDays(7)->format('Y-m-d'),
             'von' => '10:00', 'bis' => '11:00', 'min' => 60, 'la' => $laAb ?? $laGp,
             'bemerkung' => 'Erstbesuch und Abklärung Angehörigenpflege',
             'kat' => 'Abklärung/Beratung', 'akt' => 'Beratungsgespräch'],
        ];

        foreach ($einmalige as $e) {
            $datum = Carbon::parse($e['datum']);
            $eid   = DB::table('einsaetze')->insertGetId([
                'organisation_id'        => $this->orgId,
                'klient_id'              => $this->kl[$e['klient']],
                'benutzer_id'            => $this->ma[$e['benutzer']],
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

            DB::table('einsatz_aktivitaeten')->insert([
                'einsatz_id'      => $eid,
                'organisation_id' => $this->orgId,
                'kategorie'       => $e['kat'],
                'aktivitaet'      => $e['akt'],
                'minuten'         => $e['min'],
                'created_at'      => now(),
                'updated_at'      => now(),
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
                'Frau Brunner in gutem Allgemeinzustand angetroffen. Körperpflege vollständig durchgeführt, beim Ankleiden assistiert. Blutdruck 138/82 mmHg, Puls regelmässig. Klientin war gut gestimmt.',
                'Grundpflege und Hauswirtschaft durchgeführt. Frau Brunner berichtete über leichte Knieschmerzen. Wohnung aufgeräumt, Küche sauber gemacht. Medikamente gerichtet und übergeben.',
            ],
            'weber'     => [
                'Herr Weber war wach und kooperativ. Injektion verabreicht (Insulin 8 IE). Verband gewechselt, Wunde heilt gut. Keine Auffälligkeiten.',
                'Herr Weber klagte über Müdigkeit. Injektion durchgeführt. Vitalzeichen: RR 142/88, Puls 74. Verband Mo/Mi/Fr gewechselt. Hinweis: auf Flüssigkeitszufuhr achten.',
            ],
            'schneider' => [
                'Frau Schneider zeigte sich heute unruhig und zeitweise desorientiert. Grundpflege behutsam durchgeführt. Vitalzeichen: RR 122/78, Puls 68, Temp. 36.8°. Angehörige telefonisch informiert.',
                'Frau Schneider ruhig und freundlich gestimmt. Körperwäsche, Ankleiden und Mundpflege durchgeführt. Vitalzeichen stabil. Hat gut gefrühstückt.',
            ],
            'keller'    => [
                'Herr Keller klagte über leichte Atemnot bei Belastung. Hauswirtschaft durchgeführt. Grundpflege: Kompressionsstrümpfe angelegt. RR 145/90, Puls 78. Arzt informiert.',
                'Herr Keller in gutem Zustand, kooperativ. Wohnung gereinigt, Wäsche gemacht. Grundpflege inkl. Kompressionsstrümpfe. RR 138/86. Keine wesentlichen Veränderungen.',
            ],
            'gerber'    => [
                'Herr Gerber durch Tochter Ruth Gerber gepflegt. Mobilisation und Körperpflege durchgeführt. Herr Gerber in stabilem Zustand, guter Laune.',
                'Grundpflege durch Angehörige Ruth Gerber. Herr Gerber hat Morgengymnastik gemacht. Vitalzeichen unauffällig. Allgemeinzustand gut.',
            ],
        ];

        foreach ($texte as $klientKey => $rapportTexte) {
            $klientId = $this->kl[$klientKey];
            $einsaetze = DB::table('einsaetze')
                ->where('klient_id', $klientId)
                ->where('status', 'abgeschlossen')
                ->orderBy('datum')
                ->limit(2)
                ->get();

            foreach ($einsaetze as $i => $einsatz) {
                DB::table('rapporte')->insert([
                    'organisation_id' => $this->orgId,
                    'klient_id'       => $klientId,
                    'benutzer_id'     => $einsatz->benutzer_id,
                    'einsatz_id'      => $einsatz->id,
                    'datum'           => $einsatz->datum,
                    'inhalt'          => $rapportTexte[$i] ?? $rapportTexte[0],
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
        $adminId     = $this->adminId ?: ($this->ma['admin'] ?? 1);
        $rechnungsNr = 1;

        $perioden = [
            [
                'von'             => $heute->copy()->startOfMonth()->subMonths(4),
                'bis'             => $heute->copy()->startOfMonth()->subMonths(4)->endOfMonth(),
                'lauf_status'     => 'abgeschlossen',
                'rechnung_status' => 'bezahlt',
            ],
            [
                'von'             => $heute->copy()->startOfMonth()->subMonths(3),
                'bis'             => $heute->copy()->startOfMonth()->subMonths(3)->endOfMonth(),
                'lauf_status'     => 'abgeschlossen',
                'rechnung_status' => 'bezahlt',
            ],
            [
                'von'             => $heute->copy()->startOfMonth()->subMonths(2),
                'bis'             => $heute->copy()->startOfMonth()->subMonths(2)->endOfMonth(),
                'lauf_status'     => 'abgeschlossen',
                'rechnung_status' => 'gesendet',
            ],
            [
                'von'             => $heute->copy()->startOfMonth()->subMonth(),
                'bis'             => $heute->copy()->startOfMonth()->subMonth()->endOfMonth(),
                'lauf_status'     => 'abgeschlossen',
                'rechnung_status' => 'entwurf',
            ],
        ];

        foreach ($perioden as $periode) {
            $vonDat = $periode['von'];
            $bisDat = $periode['bis'];

            $laufId = DB::table('rechnungslaeufe')->insertGetId([
                'organisation_id'      => $this->orgId,
                'periode_von'          => $vonDat->format('Y-m-d'),
                'periode_bis'          => $bisDat->format('Y-m-d'),
                'rechnungstyp'         => null,
                'tarif_patient'        => null,
                'tarif_kk'             => null,
                'anzahl_erstellt'      => 0,
                'anzahl_uebersprungen' => 0,
                'status'               => $periode['lauf_status'],
                'erstellt_von'         => $adminId,
                'created_at'           => $bisDat->copy()->addDays(2),
                'updated_at'           => $bisDat->copy()->addDays(2),
            ]);

            $this->erstelleRechnungenFuerLauf($laufId, $vonDat, $bisDat, $periode['rechnung_status'], $rechnungsNr);

            $anzahl = DB::table('rechnungen')->where('rechnungslauf_id', $laufId)->count();
            DB::table('rechnungslaeufe')->where('id', $laufId)->update([
                'anzahl_erstellt' => $anzahl,
                'updated_at'      => now(),
            ]);
        }
    }

    private function erstelleRechnungenFuerLauf(int $laufId, Carbon $vonDat, Carbon $bisDat, string $rechnungStatus, int &$rechnungsNr): void
    {
        // Gerber ausgenommen (Angehörigenpflege, keine KVG-Abrechnung im Demo)
        foreach (['brunner', 'weber', 'schneider', 'keller'] as $klientKey) {
            $klientId = $this->kl[$klientKey];

            $einsaetze = DB::table('einsaetze')
                ->where('klient_id', $klientId)
                ->where('verrechnet', true)
                ->whereBetween('datum', [$vonDat->format('Y-m-d'), $bisDat->format('Y-m-d')])
                ->whereNotNull('checkout_zeit')
                ->get();

            // Fallback: aktuelle Periode — noch nicht verrechnete abgeschlossene Einsätze
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
            $rechnungsNummer = 'RE-' . $vonDat->format('Y') . '-' . str_pad($rechnungsNr, 4, '0', STR_PAD_LEFT);
            $rechnungsNr++;

            $rechnungId = DB::table('rechnungen')->insertGetId([
                'organisation_id'     => $this->orgId,
                'klient_id'           => $klientId,
                'rechnungsnummer'     => $rechnungsNummer,
                'periode_von'         => $vonDat->format('Y-m-d'),
                'periode_bis'         => $bisDat->format('Y-m-d'),
                'rechnungsdatum'      => $bisDat->format('Y-m-d'),
                'betrag_patient'      => 0,
                'betrag_kk'           => 0,
                'betrag_total'        => 0,
                'status'              => $rechnungStatus,
                'rechnungstyp'        => 'kombiniert',
                'rechnungslauf_id'    => $laufId,
                'email_versand_datum' => $rechnungStatus === 'gesendet' ? $bisDat->copy()->addDays(3) : null,
                'email_versand_an'    => $rechnungStatus === 'gesendet' ? $klient->email : null,
                'created_at'          => $bisDat->copy()->addDays(2),
                'updated_at'          => $bisDat->copy()->addDays(2),
            ]);

            $betragPat = 0.0;
            $betragKk  = 0.0;
            $lrCache   = [];

            foreach ($einsaetze as $einsatz) {
                // Tarif per Leistungsart + Region nachschlagen
                $lrKey = $einsatz->leistungsart_id . '_' . $klient->region_id;
                if (!isset($lrCache[$lrKey])) {
                    $lrCache[$lrKey] = DB::table('leistungsregionen')
                        ->where('leistungsart_id', $einsatz->leistungsart_id)
                        ->where('region_id', $klient->region_id)
                        ->orderByDesc('gueltig_ab')
                        ->first();
                }

                $lr = $lrCache[$lrKey];
                if (!$lr) throw new \RuntimeException("Keine Leistungsregion für leistungsart_id={$einsatz->leistungsart_id}, region_id={$klient->region_id}");
                $ansatz = (float) $lr->ansatz;
                $kkasse = (float) $lr->kkasse;
                $minuten = $einsatz->minuten ?? 0;
                $bPat    = round($minuten / 60.0 * max(0, $ansatz - $kkasse), 2);
                $bKk     = round($minuten / 60.0 * $kkasse, 2);

                DB::table('rechnungs_positionen')->insert([
                    'rechnung_id'    => $rechnungId,
                    'einsatz_id'     => $einsatz->id,
                    'datum'          => $einsatz->datum,
                    'menge'          => $minuten,
                    'einheit'        => 'minuten',
                    'beschreibung'   => null,
                    'tarif_patient'  => max(0, $ansatz - $kkasse),
                    'tarif_kk'       => $kkasse,
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
}
