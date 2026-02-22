<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestdatenSeeder extends Seeder
{
    public function run(): void
    {
        // ── Organisation ─────────────────────────────────────────────
        $org = DB::table('organisationen')->first();
        if (!$org) {
            $this->command->error('Keine Organisation gefunden. Bitte zuerst Setup ausführen.');
            return;
        }
        $orgId   = $org->id;
        $adminId = DB::table('benutzer')
            ->where('organisation_id', $orgId)
            ->where('rolle', 'admin')
            ->value('id');

        $this->command->info("Organisation: {$org->name} (ID: {$orgId})");

        // ── Region ───────────────────────────────────────────────────
        $region = DB::table('regionen')->where('kuerzel', 'BE')->first();
        if (!$region) {
            $regionId = DB::table('regionen')->insertGetId([
                'kuerzel'     => 'BE',
                'bezeichnung' => 'Kanton Bern',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } else {
            $regionId = $region->id;
        }

        // ── Leistungsart ─────────────────────────────────────────────
        $leistungsart = DB::table('leistungsarten')
            ->where('bezeichnung', 'Grundpflege')
            ->first();
        if (!$leistungsart) {
            $leistungsartId = DB::table('leistungsarten')->insertGetId([
                'bezeichnung'     => 'Grundpflege',
                'einheit'         => 'minuten',
                'kassenpflichtig' => true,
                'aktiv'           => true,
                'ansatz_default'  => 1.07,
                'kvg_default'     => 0.93,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        } else {
            $leistungsartId = $leistungsart->id;
        }

        // ── Qualifikations-IDs ───────────────────────────────────────
        $qualFage = DB::table('qualifikationen')->where('kuerzel', 'FAGE')->value('id');
        $qualPa   = DB::table('qualifikationen')->where('kuerzel', 'PA')->value('id');
        $qualDn1  = DB::table('qualifikationen')->where('kuerzel', 'DN I')->value('id');
        $qualHf   = DB::table('qualifikationen')->where('kuerzel', 'HF')->value('id');

        $this->command->info("Region: {$regionId}, Leistungsart: {$leistungsartId}");

        // ════════════════════════════════════════════════════════════
        // MITARBEITER
        // ════════════════════════════════════════════════════════════

        // Sandra Huber — Pflege (80%)
        $sandraId = $this->benutzerAnlegen([
            'organisation_id' => $orgId,
            'anrede'          => 'Frau',
            'vorname'         => 'Sandra',
            'nachname'        => 'Huber',
            'geschlecht'      => 'f',
            'geburtsdatum'    => '1988-05-14',
            'nationalitaet'   => 'CH',
            'strasse'         => 'Dorfstrasse 12',
            'plz'             => '3000',
            'ort'             => 'Bern',
            'telefon'         => '031 123 45 67',
            'email'           => 'sandra.huber@test.curasoft',
            'password'        => Hash::make('test1234'),
            'pensum'          => 80,
            'eintrittsdatum'  => '2022-03-01',
            'rolle'           => 'pflege',
            'aktiv'           => true,
        ]);
        foreach (array_filter([$qualFage, $qualPa]) as $qualId) {
            DB::table('benutzer_qualifikation')->updateOrInsert(
                ['benutzer_id' => $sandraId, 'qualifikation_id' => $qualId]
            );
        }

        // Peter Keller — Pflege (100%)
        $peterId = $this->benutzerAnlegen([
            'organisation_id' => $orgId,
            'anrede'          => 'Herr',
            'vorname'         => 'Peter',
            'nachname'        => 'Keller',
            'geschlecht'      => 'm',
            'geburtsdatum'    => '1983-11-22',
            'nationalitaet'   => 'CH',
            'strasse'         => 'Bahnhofstrasse 8',
            'plz'             => '3001',
            'ort'             => 'Bern',
            'telefon'         => '031 234 56 78',
            'email'           => 'peter.keller@test.curasoft',
            'password'        => Hash::make('test1234'),
            'pensum'          => 100,
            'eintrittsdatum'  => '2020-06-01',
            'rolle'           => 'pflege',
            'aktiv'           => true,
        ]);
        foreach (array_filter([$qualDn1, $qualHf]) as $qualId) {
            DB::table('benutzer_qualifikation')->updateOrInsert(
                ['benutzer_id' => $peterId, 'qualifikation_id' => $qualId]
            );
        }

        // Lisa Bauer — Buchhaltung (60%)
        $lisaId = $this->benutzerAnlegen([
            'organisation_id' => $orgId,
            'anrede'          => 'Frau',
            'vorname'         => 'Lisa',
            'nachname'        => 'Bauer',
            'geschlecht'      => 'f',
            'geburtsdatum'    => '1979-08-30',
            'nationalitaet'   => 'CH',
            'strasse'         => 'Hauptgasse 5',
            'plz'             => '3002',
            'ort'             => 'Bern',
            'telefon'         => '031 345 67 89',
            'email'           => 'lisa.bauer@test.curasoft',
            'password'        => Hash::make('test1234'),
            'pensum'          => 60,
            'eintrittsdatum'  => '2021-01-15',
            'rolle'           => 'buchhaltung',
            'aktiv'           => true,
        ]);

        $this->command->info("Mitarbeiter: Sandra ({$sandraId}), Peter ({$peterId}), Lisa ({$lisaId})");

        // ════════════════════════════════════════════════════════════
        // ARZT
        // ════════════════════════════════════════════════════════════
        $arzt = DB::table('aerzte')
            ->where('organisation_id', $orgId)
            ->where('nachname', 'Müller')->where('vorname', 'Hans')
            ->first();
        if (!$arzt) {
            $arztId = DB::table('aerzte')->insertGetId([
                'organisation_id' => $orgId,
                'anrede'          => 'Herr',
                'vorname'         => 'Hans',
                'nachname'        => 'Müller',
                'fachrichtung'    => 'Allgemeinmedizin',
                'praxis_name'     => 'Arztpraxis Müller',
                'adresse'         => 'Praxisweg 3',
                'plz'             => '3000',
                'ort'             => 'Bern',
                'region_id'       => $regionId,
                'telefon'         => '031 456 78 90',
                'aktiv'           => true,
                'created_at'      => now(), 'updated_at' => now(),
            ]);
        } else {
            $arztId = $arzt->id;
        }

        // ════════════════════════════════════════════════════════════
        // KRANKENKASSEN
        // ════════════════════════════════════════════════════════════
        $kkHelsanaId = $this->krankenkasseAnlegen($orgId, [
            'name'    => 'Helsana Versicherungen AG',
            'kuerzel' => 'HELS',
            'ean_nr'  => '7601003000014',
            'adresse' => 'Zürichstrasse 130',
            'plz'     => '8600',
            'ort'     => 'Dübendorf',
            'telefon' => '0800 333 400',
        ]);

        $kkCssId = $this->krankenkasseAnlegen($orgId, [
            'name'    => 'CSS Versicherung AG',
            'kuerzel' => 'CSS',
            'ean_nr'  => '7601003000021',
            'adresse' => 'Tribschenstrasse 21',
            'plz'     => '6005',
            'ort'     => 'Luzern',
            'telefon' => '0844 277 277',
        ]);

        $this->command->info("Krankenkassen: Helsana ({$kkHelsanaId}), CSS ({$kkCssId})");

        // ════════════════════════════════════════════════════════════
        // KLIENTIN: Maria Schmidt
        // ════════════════════════════════════════════════════════════
        $maria = DB::table('klienten')
            ->where('organisation_id', $orgId)
            ->where('nachname', 'Schmidt')->where('vorname', 'Maria')
            ->first();

        if (!$maria) {
            $mariaId = DB::table('klienten')->insertGetId([
                'organisation_id'     => $orgId,
                'anrede'              => 'Frau',
                'vorname'             => 'Maria',
                'nachname'            => 'Schmidt',
                'geschlecht'          => 'w',
                'geburtsdatum'        => '1940-03-15',
                'zivilstand'          => 'verwitwet',
                'adresse'             => 'Rosenweg 7',
                'plz'                 => '3000',
                'ort'                 => 'Bern',
                'region_id'           => $regionId,
                'telefon'             => '031 567 89 01',
                'datum_erstkontakt'   => '2024-01-10',
                'einsatz_geplant_von' => '2024-01-15',
                'aktiv'               => true,
                'qr_token'            => Str::random(32),
                'created_at'          => now(), 'updated_at' => now(),
            ]);
            $this->command->info("Klientin Maria Schmidt erstellt (ID: {$mariaId})");

            // Pflegestufe
            DB::table('klient_pflegestufen')->insert([
                'klient_id'         => $mariaId,
                'erfasst_von'       => $adminId,
                'instrument'        => 'besa',
                'stufe'             => 3,
                'punkte'            => 42.50,
                'einstufung_datum'  => '2024-01-15',
                'naechste_pruefung' => '2025-01-15',
                'bemerkung'         => 'Mittelschwere Pflegebedürftigkeit',
                'created_at'        => now(), 'updated_at' => now(),
            ]);

            // Diagnosen
            DB::table('klient_diagnosen')->insert([
                [
                    'klient_id' => $mariaId, 'erfasst_von' => $adminId, 'arzt_id' => $arztId,
                    'icd10_code' => 'E11', 'icd10_bezeichnung' => 'Diabetes mellitus Typ 2',
                    'diagnose_typ' => 'haupt', 'datum_gestellt' => '2020-05-01', 'aktiv' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'klient_id' => $mariaId, 'erfasst_von' => $adminId, 'arzt_id' => $arztId,
                    'icd10_code' => 'I50', 'icd10_bezeichnung' => 'Herzinsuffizienz',
                    'diagnose_typ' => 'neben', 'datum_gestellt' => '2021-09-12', 'aktiv' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ],
            ]);

            // Arzt
            DB::table('klient_aerzte')->insert([
                'klient_id' => $mariaId, 'arzt_id' => $arztId,
                'rolle' => 'behandelnder', 'hauptarzt' => true, 'gueltig_ab' => '2024-01-01',
                'created_at' => now(), 'updated_at' => now(),
            ]);

            // Krankenkasse
            DB::table('klient_krankenkassen')->insert([
                'klient_id' => $mariaId, 'krankenkasse_id' => $kkHelsanaId,
                'versicherungs_typ' => 'kvg', 'deckungstyp' => 'allgemein',
                'versichertennummer' => '756.1234.5678.90', 'gueltig_ab' => '2024-01-01', 'aktiv' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            // Notfallkontakte
            DB::table('klient_kontakte')->insert([
                [
                    'klient_id' => $mariaId, 'rolle' => 'angehoerig',
                    'anrede' => 'Herr', 'vorname' => 'Thomas', 'nachname' => 'Schmidt',
                    'beziehung' => 'Sohn', 'telefon' => '031 678 90 12', 'telefon_mobil' => '079 123 45 67',
                    'email' => 'thomas.schmidt@example.com', 'bevollmaechtigt' => true, 'aktiv' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'klient_id' => $mariaId, 'rolle' => 'notfallkontakt',
                    'anrede' => 'Frau', 'vorname' => 'Anna', 'nachname' => 'Meier',
                    'beziehung' => 'Tochter', 'telefon' => null, 'telefon_mobil' => '079 234 56 78',
                    'email' => null, 'bevollmaechtigt' => false, 'aktiv' => true,
                    'created_at' => now(), 'updated_at' => now(),
                ],
            ]);

            // Betreuungspersonen
            DB::table('klient_benutzer')->insert([
                ['klient_id' => $mariaId, 'benutzer_id' => $sandraId, 'rolle' => 'hauptbetreuer', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
                ['klient_id' => $mariaId, 'benutzer_id' => $peterId,  'rolle' => 'vertretung',    'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        } else {
            $mariaId = $maria->id;
            $this->command->line("Klientin Maria Schmidt existiert bereits (ID: {$mariaId})");
        }

        // ════════════════════════════════════════════════════════════
        // KLIENT: Hans Brunner
        // ════════════════════════════════════════════════════════════
        $hans = DB::table('klienten')
            ->where('organisation_id', $orgId)
            ->where('nachname', 'Brunner')->where('vorname', 'Hans')
            ->first();

        if (!$hans) {
            $hansId = DB::table('klienten')->insertGetId([
                'organisation_id'     => $orgId,
                'anrede'              => 'Herr',
                'vorname'             => 'Hans',
                'nachname'            => 'Brunner',
                'geschlecht'          => 'm',
                'geburtsdatum'        => '1947-09-08',
                'zivilstand'          => 'verheiratet',
                'adresse'             => 'Gartenweg 3',
                'plz'                 => '3001',
                'ort'                 => 'Bern',
                'region_id'           => $regionId,
                'telefon'             => '031 789 01 23',
                'datum_erstkontakt'   => '2024-06-01',
                'einsatz_geplant_von' => '2024-06-10',
                'aktiv'               => true,
                'qr_token'            => Str::random(32),
                'created_at'          => now(), 'updated_at' => now(),
            ]);
            $this->command->info("Klient Hans Brunner erstellt (ID: {$hansId})");

            DB::table('klient_pflegestufen')->insert([
                'klient_id'         => $hansId,
                'erfasst_von'       => $adminId,
                'instrument'        => 'besa',
                'stufe'             => 2,
                'punkte'            => 28.00,
                'einstufung_datum'  => '2024-06-10',
                'naechste_pruefung' => '2025-06-10',
                'bemerkung'         => 'Leichte bis mittlere Pflegebedürftigkeit, Demenz frühes Stadium',
                'created_at'        => now(), 'updated_at' => now(),
            ]);

            DB::table('klient_diagnosen')->insert([
                'klient_id' => $hansId, 'erfasst_von' => $adminId, 'arzt_id' => $arztId,
                'icd10_code' => 'F00', 'icd10_bezeichnung' => 'Demenz bei Alzheimer-Krankheit',
                'diagnose_typ' => 'haupt', 'datum_gestellt' => '2023-11-20', 'aktiv' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('klient_aerzte')->insert([
                'klient_id' => $hansId, 'arzt_id' => $arztId,
                'rolle' => 'behandelnder', 'hauptarzt' => true, 'gueltig_ab' => '2024-06-01',
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('klient_krankenkassen')->insert([
                'klient_id' => $hansId, 'krankenkasse_id' => $kkCssId,
                'versicherungs_typ' => 'kvg', 'deckungstyp' => 'halbprivat',
                'versichertennummer' => '756.9876.5432.10', 'gueltig_ab' => '2024-06-01', 'aktiv' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('klient_kontakte')->insert([
                'klient_id' => $hansId, 'rolle' => 'angehoerig',
                'anrede' => 'Frau', 'vorname' => 'Ursula', 'nachname' => 'Brunner',
                'beziehung' => 'Ehefrau', 'telefon' => '031 890 12 34', 'telefon_mobil' => '079 345 67 89',
                'email' => 'ursula.brunner@example.com', 'bevollmaechtigt' => true,
                'rechnungen_erhalten' => true, 'aktiv' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            DB::table('klient_benutzer')->insert([
                ['klient_id' => $hansId, 'benutzer_id' => $peterId,   'rolle' => 'hauptbetreuer', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
                ['klient_id' => $hansId, 'benutzer_id' => $sandraId,  'rolle' => 'betreuer',      'aktiv' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        } else {
            $hansId = $hans->id;
            $this->command->line("Klient Hans Brunner existiert bereits (ID: {$hansId})");
        }

        // ════════════════════════════════════════════════════════════
        // EINSÄTZE
        // ════════════════════════════════════════════════════════════
        $einsaetzeExists = DB::table('einsaetze')
            ->where('organisation_id', $orgId)
            ->where('klient_id', $mariaId)
            ->exists();

        if (!$einsaetzeExists) {
            $heute = Carbon::today();

            // Vergangene Einsätze Maria (abgeschlossen, mit Check-in/out)
            for ($i = 5; $i >= 1; $i--) {
                $datum = $heute->copy()->subDays($i * 2);
                DB::table('einsaetze')->insert([
                    'organisation_id'       => $orgId,
                    'klient_id'             => $mariaId,
                    'benutzer_id'           => $sandraId,
                    'leistungsart_id'       => $leistungsartId,
                    'region_id'             => $regionId,
                    'status'                => 'abgeschlossen',
                    'datum'                 => $datum->toDateString(),
                    'zeit_von'              => '08:00:00',
                    'zeit_bis'              => '10:00:00',
                    'minuten'               => 120,
                    'bemerkung'             => 'Morgenpflege',
                    'verrechnet'            => false,
                    'checkin_zeit'          => $datum->copy()->setTime(8, rand(1, 5))->toDateTimeString(),
                    'checkin_methode'       => 'gps',
                    'checkin_distanz_meter' => rand(10, 60),
                    'checkout_zeit'         => $datum->copy()->setTime(10, rand(0, 8))->toDateTimeString(),
                    'checkout_methode'      => 'gps',
                    'checkout_distanz_meter'=> rand(10, 60),
                    'created_at'            => now(), 'updated_at' => now(),
                ]);
            }

            // Vergangene Einsätze Hans (abgeschlossen)
            for ($i = 4; $i >= 1; $i--) {
                $datum = $heute->copy()->subDays($i * 3);
                DB::table('einsaetze')->insert([
                    'organisation_id'       => $orgId,
                    'klient_id'             => $hansId,
                    'benutzer_id'           => $peterId,
                    'leistungsart_id'       => $leistungsartId,
                    'region_id'             => $regionId,
                    'status'                => 'abgeschlossen',
                    'datum'                 => $datum->toDateString(),
                    'zeit_von'              => '09:00:00',
                    'zeit_bis'              => '11:00:00',
                    'minuten'               => 120,
                    'bemerkung'             => 'Pflege + Begleitung',
                    'verrechnet'            => false,
                    'checkin_zeit'          => $datum->copy()->setTime(9, rand(1, 5))->toDateTimeString(),
                    'checkin_methode'       => 'manuell',
                    'checkout_zeit'         => $datum->copy()->setTime(11, rand(0, 12))->toDateTimeString(),
                    'checkout_methode'      => 'manuell',
                    'created_at'            => now(), 'updated_at' => now(),
                ]);
            }

            // Heutige Einsätze (geplant)
            DB::table('einsaetze')->insert([
                [
                    'organisation_id' => $orgId, 'klient_id' => $mariaId, 'benutzer_id' => $sandraId,
                    'leistungsart_id' => $leistungsartId, 'region_id' => $regionId,
                    'status' => 'geplant', 'datum' => $heute->toDateString(),
                    'zeit_von' => '08:00:00', 'zeit_bis' => '10:00:00',
                    'bemerkung' => 'Morgenpflege', 'verrechnet' => false,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'organisation_id' => $orgId, 'klient_id' => $hansId, 'benutzer_id' => $peterId,
                    'leistungsart_id' => $leistungsartId, 'region_id' => $regionId,
                    'status' => 'geplant', 'datum' => $heute->toDateString(),
                    'zeit_von' => '10:30:00', 'zeit_bis' => '12:00:00',
                    'bemerkung' => 'Morgenpflege + Medikamente', 'verrechnet' => false,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'organisation_id' => $orgId, 'klient_id' => $mariaId, 'benutzer_id' => $peterId,
                    'leistungsart_id' => $leistungsartId, 'region_id' => $regionId,
                    'status' => 'geplant', 'datum' => $heute->toDateString(),
                    'zeit_von' => '16:00:00', 'zeit_bis' => '17:30:00',
                    'bemerkung' => 'Abendpflege', 'verrechnet' => false,
                    'created_at' => now(), 'updated_at' => now(),
                ],
            ]);

            // Zukünftige Einsätze
            DB::table('einsaetze')->insert([
                [
                    'organisation_id' => $orgId, 'klient_id' => $mariaId, 'benutzer_id' => $sandraId,
                    'leistungsart_id' => $leistungsartId, 'region_id' => $regionId,
                    'status' => 'geplant', 'datum' => $heute->copy()->addDays(1)->toDateString(),
                    'zeit_von' => '08:00:00', 'zeit_bis' => '10:00:00',
                    'bemerkung' => 'Morgenpflege', 'verrechnet' => false,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'organisation_id' => $orgId, 'klient_id' => $hansId, 'benutzer_id' => $peterId,
                    'leistungsart_id' => $leistungsartId, 'region_id' => $regionId,
                    'status' => 'geplant', 'datum' => $heute->copy()->addDays(2)->toDateString(),
                    'zeit_von' => '09:00:00', 'zeit_bis' => '11:00:00',
                    'bemerkung' => 'Wochenpflege', 'verrechnet' => false,
                    'created_at' => now(), 'updated_at' => now(),
                ],
            ]);

            $this->command->info('Einsätze erstellt.');
        } else {
            $this->command->line('Einsätze existieren bereits, übersprungen.');
        }

        // ════════════════════════════════════════════════════════════
        // RAPPORTE
        // ════════════════════════════════════════════════════════════
        $rapporteExists = DB::table('rapporte')
            ->where('organisation_id', $orgId)
            ->where('klient_id', $mariaId)
            ->exists();

        if (!$rapporteExists) {
            $vergMaria = DB::table('einsaetze')
                ->where('organisation_id', $orgId)
                ->where('klient_id', $mariaId)
                ->where('status', 'abgeschlossen')
                ->orderBy('datum')->limit(3)->get();

            $vergHans = DB::table('einsaetze')
                ->where('organisation_id', $orgId)
                ->where('klient_id', $hansId)
                ->where('status', 'abgeschlossen')
                ->orderBy('datum')->limit(2)->get();

            $inhalte = [
                'pflege'       => 'Frau Schmidt wurde vollständig gepflegt. Körperpflege und Wäsche wurden durchgeführt. Medikamente (Metformin, Ramipril) wurden verabreicht. Allgemeinzustand gut, Stimmung ausgeglichen. Kein Sturzrisiko beobachtet.',
                'verlauf'      => 'Verlaufskontrolle durchgeführt. Blutzucker gemessen: 7.2 mmol/l. Blutdruck 135/82 mmHg. Frau Schmidt klagt über leichte Beinödeme beidseits. Befund telefonisch an Dr. Müller weitergeleitet.',
                'zwischenfall' => 'Frau Schmidt ist beim Aufstehen vom Bett gestürzt. Keine sichtbaren Verletzungen. Sohn Thomas Schmidt telefonisch informiert. Arzt Dr. Müller benachrichtigt. Sturzdokumentation ausgefüllt. Bett auf niedrigste Position gestellt.',
            ];
            $typen = ['pflege', 'verlauf', 'zwischenfall'];

            foreach ($vergMaria as $i => $einsatz) {
                $typ = $typen[$i];
                DB::table('rapporte')->insert([
                    'organisation_id' => $orgId,
                    'klient_id'       => $mariaId,
                    'benutzer_id'     => $sandraId,
                    'einsatz_id'      => $einsatz->id,
                    'datum'           => $einsatz->datum,
                    'zeit_von'        => '08:00',
                    'zeit_bis'        => '10:00',
                    'rapport_typ'     => $typ,
                    'inhalt'          => $inhalte[$typ],
                    'vertraulich'     => $typ === 'zwischenfall',
                    'created_at'      => now(), 'updated_at' => now(),
                ]);
            }

            foreach ($vergHans as $i => $einsatz) {
                DB::table('rapporte')->insert([
                    'organisation_id' => $orgId,
                    'klient_id'       => $hansId,
                    'benutzer_id'     => $peterId,
                    'einsatz_id'      => $einsatz->id,
                    'datum'           => $einsatz->datum,
                    'zeit_von'        => '09:00',
                    'zeit_bis'        => '11:00',
                    'rapport_typ'     => $i === 0 ? 'pflege' : 'medikament',
                    'inhalt'          => $i === 0
                        ? 'Herr Brunner war heute etwas unruhig. Grundpflege vollständig durchgeführt. Spaziergang im Garten gemacht. Mahlzeiten gut eingenommen. Stimmung wechselhaft, aber kooperativ.'
                        : 'Medikamentenvergabe: Donepezil 10mg, Ramipril 5mg, Metformin 850mg. Alle Medikamente eingenommen. Keine Nebenwirkungen beobachtet. Herr Brunner wirkte heute ausgeruht.',
                    'vertraulich'     => false,
                    'created_at'      => now(), 'updated_at' => now(),
                ]);
            }

            $this->command->info('Rapporte erstellt.');
        } else {
            $this->command->line('Rapporte existieren bereits, übersprungen.');
        }

        // ════════════════════════════════════════════════════════════
        // TOUR HEUTE (Sandra)
        // ════════════════════════════════════════════════════════════
        $heute = Carbon::today();
        $tourExists = DB::table('touren')
            ->where('organisation_id', $orgId)
            ->where('datum', $heute->toDateString())
            ->where('benutzer_id', $sandraId)
            ->exists();

        if (!$tourExists) {
            $tourId = DB::table('touren')->insertGetId([
                'organisation_id' => $orgId,
                'benutzer_id'     => $sandraId,
                'datum'           => $heute->toDateString(),
                'bezeichnung'     => 'Tour Sandra — ' . $heute->format('d.m.Y'),
                'status'          => 'geplant',
                'start_zeit'      => '07:45:00',
                'end_zeit'        => '12:00:00',
                'bemerkung'       => 'Morgentour Bern Innenstadt',
                'created_at'      => now(), 'updated_at' => now(),
            ]);

            // Sandras heutige Einsätze der Tour zuordnen
            $sandrasEinsaetze = DB::table('einsaetze')
                ->where('organisation_id', $orgId)
                ->where('benutzer_id', $sandraId)
                ->where('datum', $heute->toDateString())
                ->orderBy('zeit_von')
                ->pluck('id');

            foreach ($sandrasEinsaetze as $reihenfolge => $einsatzId) {
                DB::table('einsaetze')->where('id', $einsatzId)->update([
                    'tour_id'          => $tourId,
                    'tour_reihenfolge' => $reihenfolge + 1,
                    'updated_at'       => now(),
                ]);
            }

            $this->command->info("Tour erstellt (ID: {$tourId}), {$sandrasEinsaetze->count()} Einsätze zugeordnet.");
        } else {
            $this->command->line('Tour existiert bereits, übersprungen.');
        }

        // ════════════════════════════════════════════════════════════
        // RECHNUNG (Entwurf, letzter Monat, Maria Schmidt)
        // ════════════════════════════════════════════════════════════
        $rechnungExists = DB::table('rechnungen')
            ->where('organisation_id', $orgId)
            ->where('klient_id', $mariaId)
            ->exists();

        if (!$rechnungExists) {
            $year    = now()->year;
            $count   = DB::table('rechnungen')->where('organisation_id', $orgId)->count() + 1;
            $rechnr  = 'RE-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $periodeVon = now()->subMonth()->startOfMonth();
            $periodeBis = now()->subMonth()->endOfMonth();

            DB::table('rechnungen')->insert([
                'organisation_id' => $orgId,
                'klient_id'       => $mariaId,
                'rechnungsnummer' => $rechnr,
                'periode_von'     => $periodeVon->toDateString(),
                'periode_bis'     => $periodeBis->toDateString(),
                'rechnungsdatum'  => now()->toDateString(),
                'betrag_patient'  => 180.00,
                'betrag_kk'       => 720.00,
                'betrag_total'    => 900.00,
                'status'          => 'entwurf',
                'created_at'      => now(), 'updated_at' => now(),
            ]);

            $this->command->info("Rechnung {$rechnr} erstellt.");
        } else {
            $this->command->line('Rechnung existiert bereits, übersprungen.');
        }

        // ── Zusammenfassung ──────────────────────────────────────────
        $this->command->newLine();
        $this->command->info('✓ Testdaten erfolgreich angelegt!');
        $this->command->newLine();
        $this->command->table(
            ['Benutzer', 'E-Mail', 'Passwort', 'Rolle'],
            [
                ['Sandra Huber', 'sandra.huber@test.curasoft', 'test1234', 'Pflege'],
                ['Peter Keller', 'peter.keller@test.curasoft', 'test1234', 'Pflege'],
                ['Lisa Bauer',   'lisa.bauer@test.curasoft',   'test1234', 'Buchhaltung'],
            ]
        );
    }

    // ── Hilfsmethoden ─────────────────────────────────────────────

    private function benutzerAnlegen(array $daten): int
    {
        $existing = DB::table('benutzer')->where('email', $daten['email'])->first();
        if ($existing) {
            $this->command->line("Benutzer {$daten['vorname']} {$daten['nachname']} existiert bereits (ID: {$existing->id})");
            return $existing->id;
        }
        $id = DB::table('benutzer')->insertGetId(array_merge($daten, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
        $this->command->info("Benutzer {$daten['vorname']} {$daten['nachname']} erstellt (ID: {$id})");
        return $id;
    }

    private function krankenkasseAnlegen(int $orgId, array $daten): int
    {
        $existing = DB::table('krankenkassen')
            ->where('organisation_id', $orgId)
            ->where('kuerzel', $daten['kuerzel'])
            ->first();
        if ($existing) return $existing->id;

        return DB::table('krankenkassen')->insertGetId(array_merge($daten, [
            'organisation_id' => $orgId,
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]));
    }
}
