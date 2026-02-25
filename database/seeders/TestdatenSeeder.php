<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestdatenSeeder extends Seeder
{
    private int   $orgId;
    private int   $adminId;
    private int   $regionId;
    private array $pfleger      = []; // nachname → id (10 Fachpersonen)
    private array $angehoerige  = []; // nachname → id (3 pflegende Angehörige)
    private int   $lisaId;
    private array $klienten     = []; // nachname → id (20 Klienten)
    private array $aerzte       = []; // nachname → id (5 Ärzte)
    private array $kk           = []; // kuerzel  → id (Krankenkassen)
    private array $la           = []; // bezeichnung → id (Leistungsarten)

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

        $this->orgId   = $org->id;
        $this->adminId = DB::table('benutzer')
            ->where('organisation_id', $this->orgId)
            ->where('rolle', 'admin')
            ->value('id');

        $this->command->info("Organisation: {$org->name} (ID: {$this->orgId})");

        $this->setupRegion();
        $this->setupLeistungsarten();
        $this->setupKrankenkassen();
        $this->createMitarbeiter();
        $this->createPflegendeAngehoerige();
        $this->createAerzte();
        $this->createKlienten();
        $this->createVerordnungen();
        $this->createEinsaetze();
        $this->createRapporte();
        $this->createTouren();
        $this->createRechnungen();
        $this->printSummary();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SETUP
    // ─────────────────────────────────────────────────────────────────────────

    private function setupRegion(): void
    {
        $region = DB::table('regionen')->where('kuerzel', 'BE')->first();
        if ($region) {
            $this->regionId = $region->id;
        } else {
            $this->regionId = DB::table('regionen')->insertGetId([
                'kuerzel'     => 'BE',
                'bezeichnung' => 'Kanton Bern',
                'created_at'  => now(), 'updated_at' => now(),
            ]);
            $this->command->info('Region BE angelegt.');
        }
    }

    private function setupLeistungsarten(): void
    {
        foreach (DB::table('leistungsarten')->where('aktiv', true)->get() as $la) {
            $this->la[$la->bezeichnung] = $la->id;
        }
        $this->command->info('Leistungsarten geladen: ' . implode(', ', array_keys($this->la)));
    }

    private function setupKrankenkassen(): void
    {
        $liste = [
            ['name' => 'Helsana Versicherungen AG',   'kuerzel' => 'HELS',  'ean_nr' => '7601003000014', 'adresse' => 'Zürichstrasse 130',    'plz' => '8600', 'ort' => 'Dübendorf',  'telefon' => '0800 333 400'],
            ['name' => 'CSS Versicherung AG',          'kuerzel' => 'CSS',   'ean_nr' => '7601003000021', 'adresse' => 'Tribschenstrasse 21',   'plz' => '6005', 'ort' => 'Luzern',     'telefon' => '0844 277 277'],
            ['name' => 'SWICA Krankenversicherung',    'kuerzel' => 'SWICA', 'ean_nr' => '7601003000038', 'adresse' => 'Römerstrasse 38',       'plz' => '8401', 'ort' => 'Winterthur', 'telefon' => '0800 80 90 80'],
            ['name' => 'Sanitas Krankenversicherung',  'kuerzel' => 'SANI',  'ean_nr' => '7601003000045', 'adresse' => 'Jägerstrasse 3',        'plz' => '8004', 'ort' => 'Zürich',     'telefon' => '044 298 98 98'],
            ['name' => 'Concordia Versicherungen',     'kuerzel' => 'CONC',  'ean_nr' => '7601003000052', 'adresse' => 'Bundesplatz 15',        'plz' => '6002', 'ort' => 'Luzern',     'telefon' => '041 228 02 02'],
        ];

        foreach ($liste as $kk) {
            $existing = DB::table('krankenkassen')
                ->where('organisation_id', $this->orgId)
                ->where('kuerzel', $kk['kuerzel'])
                ->first();
            if ($existing) {
                $this->kk[$kk['kuerzel']] = $existing->id;
            } else {
                $this->kk[$kk['kuerzel']] = DB::table('krankenkassen')->insertGetId(array_merge($kk, [
                    'organisation_id' => $this->orgId,
                    'aktiv'           => true,
                    'created_at'      => now(), 'updated_at' => now(),
                ]));
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MITARBEITER (10 Fachpersonen + 1 Buchhaltung)
    // ─────────────────────────────────────────────────────────────────────────

    private function createMitarbeiter(): void
    {
        // Qualifikationen laden
        $qual = [];
        foreach (['FAGE', 'PA', 'DN I', 'DN II', 'HF', 'PH SRK'] as $k) {
            $q = DB::table('qualifikationen')->where('kuerzel', $k)->first();
            if ($q) $qual[$k] = $q->id;
        }

        // gln / nareg_nr: Demo-Werte (fiktiv) — im Echtbetrieb aus nareg.admin.ch eintragen
        $liste = [
            ['anrede'=>'Frau', 'vorname'=>'Sandra',  'nachname'=>'Huber',      'geschlecht'=>'f', 'geburtsdatum'=>'1988-05-14',
             'strasse'=>'Dorfstrasse 12',         'plz'=>'3000', 'ort'=>'Bern', 'telefon'=>'031 123 45 67',
             'email'=>'sandra.huber@test.spitex',      'pensum'=>80,  'eintrittsdatum'=>'2022-03-01',
             'gln'=>'7601003901001', 'nareg_nr'=>'80001001', 'quals'=>['FAGE','PA']],
            ['anrede'=>'Herr', 'vorname'=>'Peter',   'nachname'=>'Keller',      'geschlecht'=>'m', 'geburtsdatum'=>'1983-11-22',
             'strasse'=>'Bahnhofstrasse 8',        'plz'=>'3001', 'ort'=>'Bern', 'telefon'=>'031 234 56 78',
             'email'=>'peter.keller@test.spitex',       'pensum'=>100, 'eintrittsdatum'=>'2020-06-01',
             'gln'=>'7601003901002', 'nareg_nr'=>'80001002', 'quals'=>['DN I','HF']],
            ['anrede'=>'Frau', 'vorname'=>'Monika',  'nachname'=>'Leuthold',    'geschlecht'=>'f', 'geburtsdatum'=>'1985-02-18',
             'strasse'=>'Kirchgasse 4',            'plz'=>'3012', 'ort'=>'Bern', 'telefon'=>'031 345 11 22',
             'email'=>'monika.leuthold@test.spitex',    'pensum'=>90,  'eintrittsdatum'=>'2021-09-01',
             'gln'=>'7601003901003', 'nareg_nr'=>'80001003', 'quals'=>['DN I']],
            ['anrede'=>'Herr', 'vorname'=>'Beat',    'nachname'=>'Zimmermann',  'geschlecht'=>'m', 'geburtsdatum'=>'1979-07-03',
             'strasse'=>'Lindengasse 17',          'plz'=>'3004', 'ort'=>'Bern', 'telefon'=>'031 456 22 33',
             'email'=>'beat.zimmermann@test.spitex',    'pensum'=>100, 'eintrittsdatum'=>'2019-01-15',
             'gln'=>'7601003901004', 'nareg_nr'=>'80001004', 'quals'=>['HF','DN II']],
            ['anrede'=>'Frau', 'vorname'=>'Claudia', 'nachname'=>'Roth',        'geschlecht'=>'f', 'geburtsdatum'=>'1991-10-25',
             'strasse'=>'Marktgasse 9',            'plz'=>'3011', 'ort'=>'Bern', 'telefon'=>'031 567 33 44',
             'email'=>'claudia.roth@test.spitex',       'pensum'=>80,  'eintrittsdatum'=>'2023-02-01',
             'gln'=>'7601003901005', 'nareg_nr'=>'80001005', 'quals'=>['FAGE']],
            ['anrede'=>'Herr', 'vorname'=>'Thomas',  'nachname'=>'Brunner',     'geschlecht'=>'m', 'geburtsdatum'=>'1987-04-12',
             'strasse'=>'Spitalgasse 22',          'plz'=>'3005', 'ort'=>'Bern', 'telefon'=>'031 678 44 55',
             'email'=>'thomas.brunner@test.spitex',     'pensum'=>70,  'eintrittsdatum'=>'2022-11-01',
             'gln'=>'7601003901006', 'nareg_nr'=>'80001006', 'quals'=>['PA']],
            ['anrede'=>'Frau', 'vorname'=>'Ursula',  'nachname'=>'Streit',      'geschlecht'=>'f', 'geburtsdatum'=>'1975-12-08',
             'strasse'=>'Effingerstrasse 34',      'plz'=>'3008', 'ort'=>'Bern', 'telefon'=>'031 789 55 66',
             'email'=>'ursula.streit@test.spitex',      'pensum'=>100, 'eintrittsdatum'=>'2018-06-01',
             'gln'=>'7601003901007', 'nareg_nr'=>'80001007', 'quals'=>['DN I','DN II']],
            ['anrede'=>'Herr', 'vorname'=>'Marco',   'nachname'=>'Steiner',     'geschlecht'=>'m', 'geburtsdatum'=>'1990-06-30',
             'strasse'=>'Schwarztorstrasse 11',    'plz'=>'3007', 'ort'=>'Bern', 'telefon'=>'031 890 66 77',
             'email'=>'marco.steiner@test.spitex',      'pensum'=>90,  'eintrittsdatum'=>'2021-04-01',
             'gln'=>'7601003901008', 'nareg_nr'=>'80001008', 'quals'=>['HF']],
            ['anrede'=>'Frau', 'vorname'=>'Andrea',  'nachname'=>'Maurer',      'geschlecht'=>'f', 'geburtsdatum'=>'1993-08-15',
             'strasse'=>'Weissenbühlweg 3',        'plz'=>'3007', 'ort'=>'Bern', 'telefon'=>'031 901 77 88',
             'email'=>'andrea.maurer@test.spitex',      'pensum'=>60,  'eintrittsdatum'=>'2023-08-01',
             'gln'=>'7601003901009', 'nareg_nr'=>'80001009', 'quals'=>['FAGE','PH SRK']],
            ['anrede'=>'Herr', 'vorname'=>'Daniel',  'nachname'=>'Fehr',        'geschlecht'=>'m', 'geburtsdatum'=>'1982-03-21',
             'strasse'=>'Könizstrasse 44',         'plz'=>'3008', 'ort'=>'Bern', 'telefon'=>'031 012 88 99',
             'email'=>'daniel.fehr@test.spitex',        'pensum'=>100, 'eintrittsdatum'=>'2020-01-01',
             'gln'=>'7601003901010', 'nareg_nr'=>'80001010', 'quals'=>['PA','FAGE']],
        ];

        foreach ($liste as $data) {
            $quals = $data['quals'];
            unset($data['quals']);
            $id = $this->benutzerAnlegen(array_merge($data, [
                'organisation_id' => $this->orgId,
                'password'        => Hash::make('test1234'),
                'rolle'           => 'pflege',
                'aktiv'           => true,
                'anstellungsart'  => 'fachperson',
            ]));
            $this->pfleger[$data['nachname']] = $id;
            foreach ($quals as $k) {
                if (isset($qual[$k])) {
                    DB::table('benutzer_qualifikation')->updateOrInsert(
                        ['benutzer_id' => $id, 'qualifikation_id' => $qual[$k]]
                    );
                }
            }
        }

        // Lisa Bauer — Buchhaltung
        $this->lisaId = $this->benutzerAnlegen([
            'organisation_id' => $this->orgId,
            'anrede'          => 'Frau', 'vorname' => 'Lisa', 'nachname' => 'Bauer',
            'geschlecht'      => 'f',    'geburtsdatum' => '1979-08-30',
            'strasse'         => 'Hauptgasse 5', 'plz' => '3002', 'ort' => 'Bern',
            'telefon'         => '031 345 67 89', 'email' => 'lisa.bauer@test.spitex',
            'password'        => Hash::make('test1234'), 'pensum' => 60,
            'eintrittsdatum'  => '2021-01-15', 'rolle' => 'buchhaltung',
            'aktiv'           => true, 'anstellungsart' => 'fachperson',
        ]);

        $this->command->info('Mitarbeiter: ' . count($this->pfleger) . ' Pflegefachpersonen + Lisa Bauer (Buchhaltung)');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PFLEGENDE ANGEHÖRIGE (3) — rolle=pflege, anstellungsart=angehoerig
    // Erscheinen NICHT in der Tourenplanung
    // ─────────────────────────────────────────────────────────────────────────

    private function createPflegendeAngehoerige(): void
    {
        $liste = [
            ['anrede'=>'Frau', 'vorname'=>'Ruth',    'nachname'=>'Gerber',   'geschlecht'=>'f', 'geburtsdatum'=>'1965-03-12',
             'strasse'=>'Gartenweg 5',   'plz'=>'3001', 'ort'=>'Bern', 'telefon'=>'079 111 22 33',
             'email'=>'ruth.gerber@test.spitex'],
            ['anrede'=>'Frau', 'vorname'=>'Franziska','nachname'=>'Käser',   'geschlecht'=>'f', 'geburtsdatum'=>'1958-07-24',
             'strasse'=>'Dorfplatz 2',   'plz'=>'3006', 'ort'=>'Bern', 'telefon'=>'079 222 33 44',
             'email'=>'franziska.kaeser@test.spitex'],
            ['anrede'=>'Herr', 'vorname'=>'Stefan',  'nachname'=>'Schneider','geschlecht'=>'m', 'geburtsdatum'=>'1962-11-05',
             'strasse'=>'Riedweg 8',     'plz'=>'3013', 'ort'=>'Bern', 'telefon'=>'079 333 44 55',
             'email'=>'stefan.schneider@test.spitex'],
        ];

        foreach ($liste as $data) {
            $id = $this->benutzerAnlegen(array_merge($data, [
                'organisation_id' => $this->orgId,
                'password'        => Hash::make('test1234'),
                'rolle'           => 'pflege',
                'aktiv'           => true,
                'anstellungsart'  => 'angehoerig',
            ]));
            $this->angehoerige[$data['nachname']] = $id;
        }

        $this->command->info('Pflegende Angehörige: ' . count($this->angehoerige));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ÄRZTE (5)
    // ─────────────────────────────────────────────────────────────────────────

    private function createAerzte(): void
    {
        $liste = [
            ['anrede'=>'Herr', 'vorname'=>'Hans',   'nachname'=>'Müller',  'fachrichtung'=>'Allgemeinmedizin', 'praxis_name'=>'Arztpraxis Müller',     'adresse'=>'Praxisweg 3',       'plz'=>'3000', 'ort'=>'Bern', 'telefon'=>'031 100 10 10'],
            ['anrede'=>'Frau', 'vorname'=>'Andrea', 'nachname'=>'Weber',   'fachrichtung'=>'Neurologie',       'praxis_name'=>'Neurologie Praxis Weber','adresse'=>'Neuweg 14',         'plz'=>'3010', 'ort'=>'Bern', 'telefon'=>'031 200 20 20'],
            ['anrede'=>'Herr', 'vorname'=>'Martin', 'nachname'=>'Fischer', 'fachrichtung'=>'Kardiologie',      'praxis_name'=>'Kardiologie Bern',       'adresse'=>'Herzgasse 7',       'plz'=>'3011', 'ort'=>'Bern', 'telefon'=>'031 300 30 30'],
            ['anrede'=>'Frau', 'vorname'=>'Sabine', 'nachname'=>'Huber',   'fachrichtung'=>'Geriatrie',        'praxis_name'=>'Geriatrische Praxis',    'adresse'=>'Seniorengasse 1',   'plz'=>'3012', 'ort'=>'Bern', 'telefon'=>'031 400 40 40'],
            ['anrede'=>'Herr', 'vorname'=>'Klaus',  'nachname'=>'Meier',   'fachrichtung'=>'Onkologie',        'praxis_name'=>'Onkologie Praxis Meier', 'adresse'=>'Krebsweg 19',      'plz'=>'3008', 'ort'=>'Bern', 'telefon'=>'031 500 50 50'],
        ];

        foreach ($liste as $data) {
            $existing = DB::table('aerzte')
                ->where('organisation_id', $this->orgId)
                ->where('nachname', $data['nachname'])
                ->where('vorname', $data['vorname'])
                ->first();
            if ($existing) {
                $this->aerzte[$data['nachname']] = $existing->id;
            } else {
                $this->aerzte[$data['nachname']] = DB::table('aerzte')->insertGetId(array_merge($data, [
                    'organisation_id' => $this->orgId,
                    'region_id'       => $this->regionId,
                    'aktiv'           => true,
                    'created_at'      => now(), 'updated_at' => now(),
                ]));
            }
        }

        $this->command->info('Ärzte: ' . count($this->aerzte));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // KLIENTEN (20)
    // ─────────────────────────────────────────────────────────────────────────

    private function createKlienten(): void
    {
        // Leistungsart-IDs
        $laGrund    = $this->la['Grundpflege']          ?? array_values($this->la)[0];
        $laBehandl  = $this->la['Untersuchung Behandlung'] ?? $laGrund;
        $laHauswirt = $this->la['Hauswirtschaft']        ?? $laGrund;
        $laBeratung = $this->la['Abklärung/Beratung']   ?? $laGrund;

        // ┌─────────────────────────────────────────────────────────────────┐
        // │ Felder: anrede, vorname, nachname, geschlecht, geburtsdatum,    │
        // │  zivilstand, adresse, plz, ort, telefon,                        │
        // │  arzt (nachname), kk (kuerzel), deckungstyp, tiers_payant,      │
        // │  besa_stufe, besa_punkte, klient_typ, zustaendig (nachname),    │
        // │  datum_erstkontakt, einsatz_geplant_von,                        │
        // │  diagnosen [[code, bezeichnung, typ, datum], ...],              │
        // │  kontakt [anrede, vorname, nachname, beziehung, tel, mobil]     │
        // └─────────────────────────────────────────────────────────────────┘

        $klientenListe = [
            [
                'anrede'=>'Frau','vorname'=>'Maria','nachname'=>'Schmidt','geschlecht'=>'w',
                'geburtsdatum'=>'1940-03-15','zivilstand'=>'verwitwet',
                'adresse'=>'Rosenweg 7','plz'=>'3000','ort'=>'Bern','telefon'=>'031 567 89 01',
                'arzt'=>'Müller','kk'=>'HELS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>3,'besa_punkte'=>42.50,'klient_typ'=>'patient','zustaendig'=>'Huber',
                'datum_erstkontakt'=>'2024-01-10','einsatz_geplant_von'=>'2024-01-15',
                'diagnosen'=>[['E11','Diabetes mellitus Typ 2','haupt','2020-05-01'],['I50','Herzinsuffizienz','neben','2021-09-12']],
                'kontakt'=>['Herr','Thomas','Schmidt','Sohn','031 678 90 12','079 123 45 67'],
            ],
            [
                'anrede'=>'Herr','vorname'=>'Hans','nachname'=>'Brunner','geschlecht'=>'m',
                'geburtsdatum'=>'1947-09-08','zivilstand'=>'verheiratet',
                'adresse'=>'Gartenweg 3','plz'=>'3001','ort'=>'Bern','telefon'=>'031 789 01 23',
                'arzt'=>'Weber','kk'=>'CSS','deckungstyp'=>'halbprivat','tiers_payant'=>false,
                'besa_stufe'=>2,'besa_punkte'=>28.00,'klient_typ'=>'patient','zustaendig'=>'Keller',
                'datum_erstkontakt'=>'2024-06-01','einsatz_geplant_von'=>'2024-06-10',
                'diagnosen'=>[['F00','Demenz bei Alzheimer-Krankheit','haupt','2023-11-20']],
                'kontakt'=>['Frau','Ursula','Brunner','Ehefrau','031 890 12 34','079 345 67 89'],
            ],
            [
                'anrede'=>'Frau','vorname'=>'Rosa','nachname'=>'Frei','geschlecht'=>'w',
                'geburtsdatum'=>'1933-07-22','zivilstand'=>'verwitwet',
                'adresse'=>'Blumenweg 15','plz'=>'3002','ort'=>'Bern','telefon'=>'031 234 11 22',
                'arzt'=>'Huber','kk'=>'HELS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>4,'besa_punkte'=>58.00,'klient_typ'=>'patient','zustaendig'=>'Leuthold',
                'datum_erstkontakt'=>'2023-08-01','einsatz_geplant_von'=>'2023-08-15',
                'diagnosen'=>[['M80','Osteoporose mit pathologischer Fraktur','haupt','2023-06-01'],['Z96.6','Hüftgelenksprothese','neben','2023-07-15']],
                'kontakt'=>['Frau','Brigitte','Frei','Tochter',null,'079 456 78 90'],
            ],
            [
                'anrede'=>'Herr','vorname'=>'Werner','nachname'=>'Müller','geschlecht'=>'m',
                'geburtsdatum'=>'1946-12-05','zivilstand'=>'geschieden',
                'adresse'=>'Parkweg 21','plz'=>'3003','ort'=>'Bern','telefon'=>'031 345 22 33',
                'arzt'=>'Weber','kk'=>'SWICA','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>3,'besa_punkte'=>38.50,'klient_typ'=>'patient','zustaendig'=>'Zimmermann',
                'datum_erstkontakt'=>'2023-01-15','einsatz_geplant_von'=>'2023-02-01',
                'diagnosen'=>[['G20','Parkinson-Krankheit','haupt','2022-03-10'],['F02.3','Demenz bei Parkinson-Krankheit','neben','2024-01-01']],
                'kontakt'=>['Frau','Silvia','Müller','Schwester','031 456 33 44',null],
            ],
            [
                // Pflegender Angehöriger: Stefan Schneider
                'anrede'=>'Frau','vorname'=>'Hedwig','nachname'=>'Schneider','geschlecht'=>'w',
                'geburtsdatum'=>'1936-04-18','zivilstand'=>'verwitwet',
                'adresse'=>'Mühleweg 8','plz'=>'3004','ort'=>'Bern','telefon'=>'031 456 33 44',
                'arzt'=>'Huber','kk'=>'CSS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>5,'besa_punkte'=>72.00,'klient_typ'=>'pflegebeduerftig','zustaendig'=>'Streit',
                'datum_erstkontakt'=>'2022-06-01','einsatz_geplant_von'=>'2022-07-01',
                'diagnosen'=>[['F01','Vaskuläre Demenz','haupt','2021-05-20'],['I10','Essentielle Hypertonie','neben','2015-01-01']],
                'kontakt'=>['Herr','Stefan','Schneider','Sohn','079 333 44 55',null],
            ],
            [
                'anrede'=>'Herr','vorname'=>'Ernst','nachname'=>'Zimmermann','geschlecht'=>'m',
                'geburtsdatum'=>'1942-01-30','zivilstand'=>'verheiratet',
                'adresse'=>'Waldweg 12','plz'=>'3005','ort'=>'Bern','telefon'=>'031 567 44 55',
                'arzt'=>'Fischer','kk'=>'HELS','deckungstyp'=>'halbprivat','tiers_payant'=>false,
                'besa_stufe'=>2,'besa_punkte'=>22.00,'klient_typ'=>'patient','zustaendig'=>'Roth',
                'datum_erstkontakt'=>'2024-03-01','einsatz_geplant_von'=>'2024-03-15',
                'diagnosen'=>[['J44','Chronisch obstruktive Lungenkrankheit','haupt','2019-08-15'],['J45','Asthma bronchiale','neben','2021-01-01']],
                'kontakt'=>['Frau','Martha','Zimmermann','Ehefrau','031 678 55 66',null],
            ],
            [
                'anrede'=>'Frau','vorname'=>'Lisbeth','nachname'=>'Studer','geschlecht'=>'w',
                'geburtsdatum'=>'1945-09-14','zivilstand'=>'ledig',
                'adresse'=>'Länggasse 6','plz'=>'3012','ort'=>'Bern','telefon'=>'031 678 55 66',
                'arzt'=>'Weber','kk'=>'SWICA','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>3,'besa_punkte'=>35.00,'klient_typ'=>'patient','zustaendig'=>'Brunner',
                'datum_erstkontakt'=>'2023-10-01','einsatz_geplant_von'=>'2023-10-15',
                'diagnosen'=>[['G35','Multiple Sklerose','haupt','2010-02-14']],
                'kontakt'=>['Frau','Petra','Studer','Schwester',null,'079 567 89 01'],
            ],
            [
                // Pflegender Angehöriger: Ruth Gerber
                'anrede'=>'Herr','vorname'=>'Otto','nachname'=>'Gerber','geschlecht'=>'m',
                'geburtsdatum'=>'1951-06-12','zivilstand'=>'verwitwet',
                'adresse'=>'Gartenweg 5','plz'=>'3001','ort'=>'Bern','telefon'=>'031 789 66 77',
                'arzt'=>'Fischer','kk'=>'CSS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>3,'besa_punkte'=>40.00,'klient_typ'=>'pflegebeduerftig','zustaendig'=>'Maurer',
                'datum_erstkontakt'=>'2023-01-01','einsatz_geplant_von'=>'2023-01-15',
                'diagnosen'=>[['I63','Hirninfarkt','haupt','2022-11-03'],['G81','Hemiplegie','neben','2022-11-03']],
                'kontakt'=>['Frau','Ruth','Gerber','Tochter','079 111 22 33',null],
            ],
            [
                'anrede'=>'Frau','vorname'=>'Katharina','nachname'=>'Moser','geschlecht'=>'w',
                'geburtsdatum'=>'1938-11-28','zivilstand'=>'verwitwet',
                'adresse'=>'Rosenstrasse 33','plz'=>'3006','ort'=>'Bern','telefon'=>'031 890 77 88',
                'arzt'=>'Huber','kk'=>'SWICA','deckungstyp'=>'allgemein','tiers_payant'=>false,
                'besa_stufe'=>2,'besa_punkte'=>18.00,'klient_typ'=>'patient','zustaendig'=>'Fehr',
                'datum_erstkontakt'=>'2023-05-01','einsatz_geplant_von'=>'2023-05-15',
                'diagnosen'=>[['M81','Osteoporose ohne pathologische Fraktur','haupt','2018-04-01'],['F32','Depressive Episode','neben','2020-10-01']],
                'kontakt'=>['Herr','Beat','Moser','Sohn','031 901 88 99',null],
            ],
            [
                'anrede'=>'Herr','vorname'=>'Albert','nachname'=>'Steiner','geschlecht'=>'m',
                'geburtsdatum'=>'1953-02-07','zivilstand'=>'verheiratet',
                'adresse'=>'Sonnenweg 18','plz'=>'3007','ort'=>'Bern','telefon'=>'031 901 88 99',
                'arzt'=>'Müller','kk'=>'HELS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>1,'besa_punkte'=>10.00,'klient_typ'=>'patient','zustaendig'=>'Huber',
                'datum_erstkontakt'=>'2024-09-01','einsatz_geplant_von'=>'2024-09-15',
                'diagnosen'=>[['E10','Diabetes mellitus Typ 1','haupt','2000-01-01']],
                'kontakt'=>['Frau','Heidi','Steiner','Ehefrau','031 012 99 00',null],
            ],
            [
                'anrede'=>'Frau','vorname'=>'Margrit','nachname'=>'Bühler','geschlecht'=>'w',
                'geburtsdatum'=>'1931-05-03','zivilstand'=>'verwitwet',
                'adresse'=>'Kirchfeldstrasse 45','plz'=>'3004','ort'=>'Bern','telefon'=>'031 123 99 88',
                'arzt'=>'Meier','kk'=>'CSS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>5,'besa_punkte'=>78.00,'klient_typ'=>'patient','zustaendig'=>'Streit',
                'datum_erstkontakt'=>'2024-08-15','einsatz_geplant_von'=>'2024-09-01',
                'diagnosen'=>[['C34','Bösartige Neubildung der Bronchien und Lunge','haupt','2024-06-01'],['Z51.5','Palliativversorgung','neben','2024-08-01']],
                'kontakt'=>['Frau','Anita','Bühler','Tochter',null,'079 678 90 12'],
            ],
            [
                // Pflegender Angehöriger: Franziska Käser
                'anrede'=>'Herr','vorname'=>'Fritz','nachname'=>'Käser','geschlecht'=>'m',
                'geburtsdatum'=>'1956-08-19','zivilstand'=>'verheiratet',
                'adresse'=>'Dorfplatz 2','plz'=>'3006','ort'=>'Bern','telefon'=>'031 234 88 77',
                'arzt'=>'Fischer','kk'=>'HELS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>3,'besa_punkte'=>33.00,'klient_typ'=>'pflegebeduerftig','zustaendig'=>'Steiner',
                'datum_erstkontakt'=>'2023-03-01','einsatz_geplant_von'=>'2023-04-01',
                'diagnosen'=>[['J44','Chronisch obstruktive Lungenkrankheit','haupt','2018-03-01'],['I50','Herzinsuffizienz','neben','2022-01-01']],
                'kontakt'=>['Frau','Franziska','Käser','Ehefrau','079 222 33 44',null],
            ],
            [
                'anrede'=>'Frau','vorname'=>'Helene','nachname'=>'Ryser','geschlecht'=>'w',
                'geburtsdatum'=>'1944-12-01','zivilstand'=>'verwitwet',
                'adresse'=>'Riedweg 12','plz'=>'3013','ort'=>'Bern','telefon'=>'031 345 77 66',
                'arzt'=>'Huber','kk'=>'SWICA','deckungstyp'=>'allgemein','tiers_payant'=>false,
                'besa_stufe'=>2,'besa_punkte'=>20.00,'klient_typ'=>'patient','zustaendig'=>'Keller',
                'datum_erstkontakt'=>'2024-11-01','einsatz_geplant_von'=>'2024-11-15',
                'diagnosen'=>[['Z96.6','Hüftgelenksprothese beidseits','haupt','2024-10-15']],
                'kontakt'=>['Herr','Kurt','Ryser','Sohn','031 456 66 55',null],
            ],
            [
                'anrede'=>'Herr','vorname'=>'Paul','nachname'=>'Zürcher','geschlecht'=>'m',
                'geburtsdatum'=>'1949-03-27','zivilstand'=>'verheiratet',
                'adresse'=>'Bernstrasse 56','plz'=>'3006','ort'=>'Bern','telefon'=>'031 456 66 55',
                'arzt'=>'Müller','kk'=>'CSS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>2,'besa_punkte'=>16.00,'klient_typ'=>'patient','zustaendig'=>'Roth',
                'datum_erstkontakt'=>'2024-04-01','einsatz_geplant_von'=>'2024-04-15',
                'diagnosen'=>[['M06','Seropositive rheumatoide Arthritis','haupt','2015-07-01']],
                'kontakt'=>['Frau','Regula','Zürcher','Ehefrau','079 789 01 23',null],
            ],
            [
                'anrede'=>'Frau','vorname'=>'Ida','nachname'=>'Stauffer','geschlecht'=>'w',
                'geburtsdatum'=>'1940-07-11','zivilstand'=>'verwitwet',
                'adresse'=>'Amthausgasse 3','plz'=>'3011','ort'=>'Bern','telefon'=>'031 567 55 44',
                'arzt'=>'Huber','kk'=>'HELS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>4,'besa_punkte'=>62.00,'klient_typ'=>'patient','zustaendig'=>'Zimmermann',
                'datum_erstkontakt'=>'2023-02-01','einsatz_geplant_von'=>'2023-03-01',
                'diagnosen'=>[['F00','Demenz bei Alzheimer-Krankheit','haupt','2022-09-01'],['R32','Inkontinenz, nicht näher bezeichnet','neben','2023-01-01']],
                'kontakt'=>['Herr','Hans','Stauffer','Sohn','031 678 44 33',null],
            ],
            [
                'anrede'=>'Herr','vorname'=>'Jakob','nachname'=>'Huber','geschlecht'=>'m',
                'geburtsdatum'=>'1947-10-16','zivilstand'=>'verheiratet',
                'adresse'=>'Gerechtigkeitsgasse 7','plz'=>'3011','ort'=>'Bern','telefon'=>'031 678 44 33',
                'arzt'=>'Weber','kk'=>'SWICA','deckungstyp'=>'halbprivat','tiers_payant'=>false,
                'besa_stufe'=>3,'besa_punkte'=>36.00,'klient_typ'=>'patient','zustaendig'=>'Leuthold',
                'datum_erstkontakt'=>'2023-07-01','einsatz_geplant_von'=>'2023-07-15',
                'diagnosen'=>[['G20','Parkinson-Krankheit','haupt','2020-06-01'],['E11','Diabetes mellitus Typ 2','neben','2016-01-01']],
                'kontakt'=>['Frau','Vreni','Huber','Ehefrau','031 789 33 22',null],
            ],
            [
                'anrede'=>'Frau','vorname'=>'Gertrud','nachname'=>'Lanz','geschlecht'=>'w',
                'geburtsdatum'=>'1934-02-14','zivilstand'=>'verwitwet',
                'adresse'=>'Postgasse 9','plz'=>'3011','ort'=>'Bern','telefon'=>'031 789 33 22',
                'arzt'=>'Huber','kk'=>'CSS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>4,'besa_punkte'=>55.00,'klient_typ'=>'patient','zustaendig'=>'Steiner',
                'datum_erstkontakt'=>'2025-10-01','einsatz_geplant_von'=>'2025-10-15',
                'diagnosen'=>[['W18','Sturz auf gleicher Ebene','haupt','2025-09-10'],['M80','Osteoporose mit pathologischer Fraktur','neben','2025-09-10']],
                'kontakt'=>['Herr','Peter','Lanz','Sohn',null,'079 890 12 34'],
            ],
            [
                'anrede'=>'Herr','vorname'=>'Heinrich','nachname'=>'Wyss','geschlecht'=>'m',
                'geburtsdatum'=>'1952-05-23','zivilstand'=>'verheiratet',
                'adresse'=>'Nydeggstalden 4','plz'=>'3011','ort'=>'Bern','telefon'=>'031 890 22 11',
                'arzt'=>'Weber','kk'=>'HELS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>2,'besa_punkte'=>14.00,'klient_typ'=>'patient','zustaendig'=>'Brunner',
                'datum_erstkontakt'=>'2024-07-01','einsatz_geplant_von'=>'2024-07-15',
                'diagnosen'=>[['G35','Multiple Sklerose, schubförmig','haupt','2018-06-01']],
                'kontakt'=>['Frau','Monika','Wyss','Ehefrau','031 901 11 00',null],
            ],
            [
                'anrede'=>'Frau','vorname'=>'Auguste','nachname'=>'Berger','geschlecht'=>'w',
                'geburtsdatum'=>'1937-09-30','zivilstand'=>'verwitwet',
                'adresse'=>'Junkernstrasse 52','plz'=>'3011','ort'=>'Bern','telefon'=>'031 901 11 00',
                'arzt'=>'Meier','kk'=>'CSS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>5,'besa_punkte'=>80.00,'klient_typ'=>'patient','zustaendig'=>'Streit',
                'datum_erstkontakt'=>'2024-11-15','einsatz_geplant_von'=>'2024-12-01',
                'diagnosen'=>[['C50','Bösartige Neubildung der Brustdrüse','haupt','2023-04-01'],['Z51.5','Palliativversorgung','neben','2024-11-01']],
                'kontakt'=>['Herr','Rolf','Berger','Sohn',null,'079 901 23 45'],
            ],
            [
                'anrede'=>'Frau','vorname'=>'Therese','nachname'=>'Nussbaum','geschlecht'=>'w',
                'geburtsdatum'=>'1943-11-08','zivilstand'=>'verwitwet',
                'adresse'=>'Kramgasse 14','plz'=>'3011','ort'=>'Bern','telefon'=>'031 012 00 99',
                'arzt'=>'Huber','kk'=>'HELS','deckungstyp'=>'allgemein','tiers_payant'=>true,
                'besa_stufe'=>3,'besa_punkte'=>32.00,'klient_typ'=>'patient','zustaendig'=>'Maurer',
                'datum_erstkontakt'=>'2023-04-01','einsatz_geplant_von'=>'2023-05-01',
                'diagnosen'=>[['F00','Demenz bei Alzheimer-Krankheit','haupt','2023-03-01'],['I10','Essentielle Hypertonie','neben','2010-01-01']],
                'kontakt'=>['Frau','Barbara','Nussbaum','Tochter','031 123 98 87',null],
            ],
        ];

        foreach ($klientenListe as $data) {
            $diagnosen         = $data['diagnosen'];
            $kontakt           = $data['kontakt'];
            $arztKey           = $data['arzt'];
            $kkKuerzel         = $data['kk'];
            $deckungstyp       = $data['deckungstyp'];
            $tiersPayant       = $data['tiers_payant'];
            $besaStufe         = $data['besa_stufe'];
            $besaPunkte        = $data['besa_punkte'];
            $klientTyp         = $data['klient_typ'];
            $zustaendig        = $data['zustaendig'];
            $datumErstkontakt  = $data['datum_erstkontakt'];
            $einsatzGeplantVon = $data['einsatz_geplant_von'];

            unset($data['diagnosen'], $data['kontakt'], $data['arzt'], $data['kk'],
                  $data['deckungstyp'], $data['tiers_payant'], $data['besa_stufe'],
                  $data['besa_punkte'], $data['klient_typ'], $data['zustaendig'],
                  $data['datum_erstkontakt'], $data['einsatz_geplant_von']);

            $zustaendigId = $this->pfleger[$zustaendig] ?? null;
            $arztId       = $this->aerzte[$arztKey] ?? null;
            $kkId         = $this->kk[$kkKuerzel] ?? null;

            $existing = DB::table('klienten')
                ->where('organisation_id', $this->orgId)
                ->where('nachname', $data['nachname'])
                ->where('vorname', $data['vorname'])
                ->first();

            if ($existing) {
                $this->klienten[$data['nachname']] = $existing->id;
                $this->command->line("Klient {$data['vorname']} {$data['nachname']} existiert (ID: {$existing->id})");
                continue;
            }

            $klientId = DB::table('klienten')->insertGetId(array_merge($data, [
                'organisation_id'     => $this->orgId,
                'region_id'           => $this->regionId,
                'zustaendig_id'       => $zustaendigId,
                'klient_typ'          => $klientTyp,
                'datum_erstkontakt'   => $datumErstkontakt,
                'einsatz_geplant_von' => $einsatzGeplantVon,
                'aktiv'               => true,
                'qr_token'            => Str::random(32),
                'created_at'          => now(), 'updated_at' => now(),
            ]));

            $this->klienten[$data['nachname']] = $klientId;

            // Pflegestufe
            DB::table('klient_pflegestufen')->insert([
                'klient_id'         => $klientId,
                'erfasst_von'       => $this->adminId,
                'instrument'        => 'besa',
                'stufe'             => $besaStufe,
                'punkte'            => $besaPunkte,
                'einstufung_datum'  => now()->subMonths(rand(3, 18))->toDateString(),
                'naechste_pruefung' => now()->addMonths(rand(4, 14))->toDateString(),
                'created_at'        => now(), 'updated_at' => now(),
            ]);

            // Diagnosen
            foreach ($diagnosen as $d) {
                DB::table('klient_diagnosen')->insert([
                    'klient_id'         => $klientId,
                    'erfasst_von'       => $this->adminId,
                    'arzt_id'           => $arztId,
                    'icd10_code'        => $d[0],
                    'icd10_bezeichnung' => $d[1],
                    'diagnose_typ'      => $d[2],
                    'datum_gestellt'    => $d[3],
                    'aktiv'             => true,
                    'created_at'        => now(), 'updated_at' => now(),
                ]);
            }

            // Arzt
            if ($arztId) {
                DB::table('klient_aerzte')->insert([
                    'klient_id'  => $klientId,
                    'arzt_id'    => $arztId,
                    'rolle'      => 'behandelnder',
                    'hauptarzt'  => true,
                    'gueltig_ab' => $datumErstkontakt,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            // Krankenkasse
            if ($kkId) {
                DB::table('klient_krankenkassen')->insert([
                    'klient_id'          => $klientId,
                    'krankenkasse_id'    => $kkId,
                    'versicherungs_typ'  => 'kvg',
                    'deckungstyp'        => $deckungstyp,
                    'tiers_payant'       => $tiersPayant,
                    'versichertennummer' => '756.' . rand(1000, 9999) . '.' . rand(1000, 9999) . '.' . rand(10, 99),
                    'gueltig_ab'         => $datumErstkontakt,
                    'aktiv'              => true,
                    'created_at'         => now(), 'updated_at' => now(),
                ]);
            }

            // Kontaktperson
            [$kAnrede, $kVorname, $kNachname, $kBeziehung, $kTel, $kMobil] = $kontakt;
            DB::table('klient_kontakte')->insert([
                'klient_id'      => $klientId,
                'rolle'          => 'notfallkontakt',
                'anrede'         => $kAnrede,
                'vorname'        => $kVorname,
                'nachname'       => $kNachname,
                'beziehung'      => $kBeziehung,
                'telefon'        => $kTel,
                'telefon_mobil'  => $kMobil,
                'bevollmaechtigt'=> true,
                'aktiv'          => true,
                'created_at'     => now(), 'updated_at' => now(),
            ]);

            $this->command->info("Klient {$data['vorname']} {$data['nachname']} erstellt (ID: {$klientId})");
        }

        // Pflegende Angehörige ihren Klienten zuweisen
        $this->assignAngehoerige();
    }

    private function assignAngehoerige(): void
    {
        // Stefan Schneider → Hedwig Schneider
        if (isset($this->angehoerige['Schneider'], $this->klienten['Schneider'])) {
            DB::table('klient_benutzer')->updateOrInsert(
                ['klient_id' => $this->klienten['Schneider'], 'benutzer_id' => $this->angehoerige['Schneider']],
                ['rolle' => 'hauptbetreuer', 'beziehungstyp' => 'angehoerig_pflegend', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
        // Ruth Gerber → Otto Gerber
        if (isset($this->angehoerige['Gerber'], $this->klienten['Gerber'])) {
            DB::table('klient_benutzer')->updateOrInsert(
                ['klient_id' => $this->klienten['Gerber'], 'benutzer_id' => $this->angehoerige['Gerber']],
                ['rolle' => 'hauptbetreuer', 'beziehungstyp' => 'angehoerig_pflegend', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
        // Franziska Käser → Fritz Käser
        if (isset($this->angehoerige['Käser'], $this->klienten['Käser'])) {
            DB::table('klient_benutzer')->updateOrInsert(
                ['klient_id' => $this->klienten['Käser'], 'benutzer_id' => $this->angehoerige['Käser']],
                ['rolle' => 'hauptbetreuer', 'beziehungstyp' => 'angehoerig_pflegend', 'aktiv' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VERORDNUNGEN (für ausgewählte Klienten)
    // ─────────────────────────────────────────────────────────────────────────

    private function createVerordnungen(): void
    {
        $laGrund   = $this->la['Grundpflege']             ?? array_values($this->la)[0];
        $laBehandl = $this->la['Untersuchung Behandlung'] ?? $laGrund;

        $liste = [
            // klient, arzt, laId, nr, ausgestellt, ab, bis
            ['Schmidt',  'Müller',  $laGrund,   'VO-2024-0112', '2024-01-08', '2024-01-15', '2024-07-15'],
            ['Schmidt',  'Müller',  $laGrund,   'VO-2024-0441', '2024-07-10', '2024-07-15', '2025-01-15'],
            ['Brunner',  'Weber',   $laBehandl, 'VO-2024-0287', '2024-05-28', '2024-06-10', '2025-06-10'],
            ['Frei',     'Huber',   $laGrund,   'VO-2023-1102', '2023-08-01', '2023-08-15', '2024-08-15'],
            ['Bühler',   'Meier',   $laBehandl, 'VO-2024-0933', '2024-08-10', '2024-09-01', '2025-03-01'],
            ['Stauffer', 'Huber',   $laGrund,   'VO-2023-0412', '2023-02-25', '2023-03-01', '2024-03-01'],
            ['Berger',   'Meier',   $laBehandl, 'VO-2024-1241', '2024-11-10', '2024-12-01', '2025-06-01'],
            ['Huber',    'Weber',   $laBehandl, 'VO-2023-0889', '2023-07-05', '2023-07-15', '2024-07-15'],
        ];

        foreach ($liste as [$klientNachname, $arztNachname, $laId, $nr, $ausgestellt, $ab, $bis]) {
            if (!isset($this->klienten[$klientNachname])) continue;

            $exists = DB::table('klient_verordnungen')
                ->where('klient_id', $this->klienten[$klientNachname])
                ->where('verordnungs_nr', $nr)
                ->exists();
            if ($exists) continue;

            DB::table('klient_verordnungen')->insert([
                'klient_id'       => $this->klienten[$klientNachname],
                'arzt_id'         => $this->aerzte[$arztNachname] ?? null,
                'leistungsart_id' => $laId,
                'verordnungs_nr'  => $nr,
                'ausgestellt_am'  => $ausgestellt,
                'gueltig_ab'      => $ab,
                'gueltig_bis'     => $bis,
                'aktiv'           => (strtotime($bis) >= strtotime('today')),
                'created_at'      => now(), 'updated_at' => now(),
            ]);
        }

        $this->command->info('Verordnungen erstellt: ' . count($liste));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EINSÄTZE
    // ─────────────────────────────────────────────────────────────────────────

    private function createEinsaetze(): void
    {
        $count = DB::table('einsaetze')->where('organisation_id', $this->orgId)->count();
        if ($count > 0) {
            $this->command->line("Einsätze vorhanden ({$count}), übersprungen.");
            return;
        }

        $laGrund    = $this->la['Grundpflege']             ?? array_values($this->la)[0];
        $laBehandl  = $this->la['Untersuchung Behandlung'] ?? $laGrund;
        $laHauswirt = $this->la['Hauswirtschaft']          ?? $laGrund;
        $laBeratung = $this->la['Abklärung/Beratung']      ?? $laGrund;

        // Einsatzplan pro Klient: [benutzerNachname, leistungsartId, zeitVon, zeitBis, leistungserbringer_typ]
        // Angehörige (Gerber, Käser, Schneider) werden über $this->angehoerige nachgeschlagen
        $plan = [
            'Schmidt'    => ['Huber',        $laGrund,    '08:00', '10:00', 'fachperson'],
            'Brunner'    => ['Keller',        $laBehandl,  '09:00', '11:00', 'fachperson'],
            'Frei'       => ['Leuthold',      $laGrund,    '07:30', '09:30', 'fachperson'],
            'Müller'     => ['Zimmermann',    $laBehandl,  '10:00', '11:30', 'fachperson'],
            'Schneider'  => ['Schneider',     $laGrund,    '08:30', '09:30', 'angehoerig'],
            'Zimmermann' => ['Roth',          $laHauswirt, '14:00', '15:30', 'fachperson'],
            'Studer'     => ['Brunner',       $laBehandl,  '11:00', '12:00', 'fachperson'],
            'Gerber'     => ['Gerber',        $laGrund,    '09:00', '10:00', 'angehoerig'],
            'Moser'      => ['Fehr',          $laHauswirt, '13:30', '15:00', 'fachperson'],
            'Steiner'    => ['Huber',         $laGrund,    '15:00', '15:30', 'fachperson'],
            'Bühler'     => ['Streit',        $laBehandl,  '08:00', '10:00', 'fachperson'],
            'Käser'      => ['Käser',         $laGrund,    '08:00', '09:00', 'angehoerig'],
            'Ryser'      => ['Keller',        $laGrund,    '16:00', '17:30', 'fachperson'],
            'Zürcher'    => ['Roth',          $laHauswirt, '13:00', '14:00', 'fachperson'],
            'Stauffer'   => ['Zimmermann',    $laGrund,    '08:00', '10:30', 'fachperson'],
            'Huber'      => ['Leuthold',      $laBehandl,  '09:30', '11:30', 'fachperson'],
            'Lanz'       => ['Steiner',       $laGrund,    '07:30', '09:30', 'fachperson'],
            'Wyss'       => ['Brunner',       $laHauswirt, '14:30', '16:00', 'fachperson'],
            'Berger'     => ['Streit',        $laBehandl,  '10:00', '12:00', 'fachperson'],
            'Nussbaum'   => ['Maurer',        $laGrund,    '08:30', '10:00', 'fachperson'],
        ];

        // Besuchshäufigkeit pro Woche (nach BESA-Stufe)
        $besaWoche = [
            'Schmidt'=>3,'Brunner'=>2,'Frei'=>4,'Müller'=>3,'Schneider'=>5,
            'Zimmermann'=>2,'Studer'=>3,'Gerber'=>3,'Moser'=>2,'Steiner'=>1,
            'Bühler'=>5,'Käser'=>3,'Ryser'=>2,'Zürcher'=>2,'Stauffer'=>4,
            'Huber'=>3,'Lanz'=>4,'Wyss'=>2,'Berger'=>5,'Nussbaum'=>3,
        ];

        // Wochentage-Rotation nach Frequenz (1=Mo … 5=Fr nach ISO)
        $wtMap = [
            1 => [3],
            2 => [1, 4],
            3 => [1, 3, 5],
            4 => [1, 2, 4, 5],
            5 => [1, 2, 3, 4, 5],
        ];

        $heute   = Carbon::today();
        $inserted = 0;

        // ── Historische Einsätze (30 Tage) ───────────────────────────────────
        for ($d = 30; $d >= 1; $d--) {
            $datum = $heute->copy()->subDays($d);
            $wt    = (int) $datum->dayOfWeekIso; // 1=Mo

            foreach ($plan as $klient => [$pfleger, $laId, $von, $bis, $erbTyp]) {
                $freq = $besaWoche[$klient] ?? 2;
                if (!in_array($wt, $wtMap[$freq] ?? [1, 3])) continue;
                if (!isset($this->klienten[$klient])) continue;

                $benutzerId = $erbTyp === 'angehoerig'
                    ? ($this->angehoerige[$pfleger] ?? null)
                    : ($this->pfleger[$pfleger]     ?? null);
                if (!$benutzerId) continue;

                DB::table('einsaetze')->insert([
                    'organisation_id'        => $this->orgId,
                    'klient_id'              => $this->klienten[$klient],
                    'benutzer_id'            => $benutzerId,
                    'leistungsart_id'        => $laId,
                    'region_id'              => $this->regionId,
                    'status'                 => 'abgeschlossen',
                    'datum'                  => $datum->toDateString(),
                    'zeit_von'               => $von . ':00',
                    'zeit_bis'               => $bis . ':00',
                    'minuten'                => $this->minutenZwischen($von, $bis),
                    'bemerkung'              => $this->bemerkung($klient),
                    'verrechnet'             => ($d > 14),
                    'leistungserbringer_typ' => $erbTyp,
                    'checkin_zeit'           => $datum->copy()->setTimeFromTimeString($von)->addMinutes(rand(1, 8))->toDateTimeString(),
                    'checkin_methode'        => $erbTyp === 'angehoerig' ? 'manuell' : ($d % 3 === 0 ? 'gps' : 'qr'),
                    'checkin_distanz_meter'  => $erbTyp === 'angehoerig' ? null : rand(5, 80),
                    'checkout_zeit'          => $datum->copy()->setTimeFromTimeString($bis)->addMinutes(rand(0, 10))->toDateTimeString(),
                    'checkout_methode'       => $erbTyp === 'angehoerig' ? 'manuell' : ($d % 3 === 0 ? 'gps' : 'qr'),
                    'checkout_distanz_meter' => $erbTyp === 'angehoerig' ? null : rand(5, 80),
                    'created_at'             => now(), 'updated_at' => now(),
                ]);
                $inserted++;
            }
        }

        // ── Heutige Einsätze (geplant) ───────────────────────────────────────
        foreach ($plan as $klient => [$pfleger, $laId, $von, $bis, $erbTyp]) {
            if (!isset($this->klienten[$klient])) continue;
            $benutzerId = $erbTyp === 'angehoerig'
                ? ($this->angehoerige[$pfleger] ?? null)
                : ($this->pfleger[$pfleger]     ?? null);
            if (!$benutzerId) continue;

            DB::table('einsaetze')->insert([
                'organisation_id'        => $this->orgId,
                'klient_id'              => $this->klienten[$klient],
                'benutzer_id'            => $benutzerId,
                'leistungsart_id'        => $laId,
                'region_id'              => $this->regionId,
                'status'                 => 'geplant',
                'datum'                  => $heute->toDateString(),
                'zeit_von'               => $von . ':00',
                'zeit_bis'               => $bis . ':00',
                'minuten'                => $this->minutenZwischen($von, $bis),
                'bemerkung'              => $this->bemerkung($klient),
                'verrechnet'             => false,
                'leistungserbringer_typ' => $erbTyp,
                'created_at'             => now(), 'updated_at' => now(),
            ]);
            $inserted++;
        }

        // ── Zukünftige Einsätze (14 Tage) mit serie_id — nur Fachpersonen ───
        $serieId = (string) Str::uuid();
        for ($d = 1; $d <= 14; $d++) {
            $datum = $heute->copy()->addDays($d);
            $wt    = (int) $datum->dayOfWeekIso;

            foreach ($plan as $klient => [$pfleger, $laId, $von, $bis, $erbTyp]) {
                if ($erbTyp === 'angehoerig') continue;
                $freq = $besaWoche[$klient] ?? 2;
                if (!in_array($wt, $wtMap[$freq] ?? [1, 3])) continue;
                if (!isset($this->klienten[$klient])) continue;
                $benutzerId = $this->pfleger[$pfleger] ?? null;
                if (!$benutzerId) continue;

                DB::table('einsaetze')->insert([
                    'organisation_id'        => $this->orgId,
                    'klient_id'              => $this->klienten[$klient],
                    'benutzer_id'            => $benutzerId,
                    'leistungsart_id'        => $laId,
                    'region_id'              => $this->regionId,
                    'status'                 => 'geplant',
                    'datum'                  => $datum->toDateString(),
                    'zeit_von'               => $von . ':00',
                    'zeit_bis'               => $bis . ':00',
                    'minuten'                => $this->minutenZwischen($von, $bis),
                    'bemerkung'              => $this->bemerkung($klient),
                    'verrechnet'             => false,
                    'leistungserbringer_typ' => 'fachperson',
                    'serie_id'               => $serieId,
                    'created_at'             => now(), 'updated_at' => now(),
                ]);
                $inserted++;
            }
        }

        $this->command->info("Einsätze erstellt: {$inserted}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RAPPORTE (~33% der abgeschlossenen Einsätze)
    // ─────────────────────────────────────────────────────────────────────────

    private function createRapporte(): void
    {
        $count = DB::table('rapporte')->where('organisation_id', $this->orgId)->count();
        if ($count > 0) {
            $this->command->line("Rapporte vorhanden ({$count}), übersprungen.");
            return;
        }

        $einsaetze = DB::table('einsaetze')
            ->where('organisation_id', $this->orgId)
            ->where('status', 'abgeschlossen')
            ->get();

        $typen    = ['pflege', 'pflege', 'pflege', 'verlauf', 'medikament', 'zwischenfall', 'information'];
        $inserted = 0;

        foreach ($einsaetze as $i => $e) {
            if ($i % 3 !== 0) continue;
            $typ           = $typen[array_rand($typen)];
            $klientNachname = DB::table('klienten')->where('id', $e->klient_id)->value('nachname') ?? 'Patient';

            DB::table('rapporte')->insert([
                'organisation_id' => $this->orgId,
                'klient_id'       => $e->klient_id,
                'benutzer_id'     => $e->benutzer_id,
                'einsatz_id'      => $e->id,
                'datum'           => $e->datum,
                'zeit_von'        => $e->zeit_von,
                'zeit_bis'        => $e->zeit_bis,
                'rapport_typ'     => $typ,
                'inhalt'          => $this->rapportInhalt($typ, $klientNachname),
                'vertraulich'     => ($typ === 'zwischenfall'),
                'created_at'      => now(), 'updated_at' => now(),
            ]);
            $inserted++;
        }

        $this->command->info("Rapporte erstellt: {$inserted}");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TOUREN (heute + morgen, je 3 Mitarbeiter)
    // Angehörige erscheinen NICHT in Touren
    // ─────────────────────────────────────────────────────────────────────────

    private function createTouren(): void
    {
        $heute  = Carbon::today();
        $morgen = $heute->copy()->addDay();

        $config = [
            [$heute,  'Huber',      '07:45', '13:00', 'Morgentour Sandra — Innenstadt'],
            [$heute,  'Keller',     '08:00', '14:00', 'Morgentour Peter — Länggasse'],
            [$heute,  'Leuthold',   '08:30', '13:30', 'Morgentour Monika — Mattenhof'],
            [$morgen, 'Zimmermann', '07:45', '13:00', 'Morgentour Beat — Innenstadt'],
            [$morgen, 'Roth',       '08:00', '12:30', 'Morgentour Claudia — Bümpliz'],
            [$morgen, 'Streit',     '08:00', '14:00', 'Palliatívtour Ursula'],
        ];

        foreach ($config as [$datum, $nachname, $startZeit, $endZeit, $bezeichnung]) {
            $benutzerId = $this->pfleger[$nachname] ?? null;
            if (!$benutzerId) continue;

            $exists = DB::table('touren')
                ->where('organisation_id', $this->orgId)
                ->where('datum', $datum->toDateString())
                ->where('benutzer_id', $benutzerId)
                ->exists();
            if ($exists) continue;

            $tourId = DB::table('touren')->insertGetId([
                'organisation_id' => $this->orgId,
                'benutzer_id'     => $benutzerId,
                'datum'           => $datum->toDateString(),
                'bezeichnung'     => $bezeichnung,
                'status'          => 'geplant',
                'start_zeit'      => $startZeit . ':00',
                'end_zeit'        => $endZeit . ':00',
                'created_at'      => now(), 'updated_at' => now(),
            ]);

            // Geplante Einsätze dieses Mitarbeiters der Tour zuordnen
            $einsaetze = DB::table('einsaetze')
                ->where('organisation_id', $this->orgId)
                ->where('benutzer_id', $benutzerId)
                ->where('datum', $datum->toDateString())
                ->where('status', 'geplant')
                ->whereNull('tour_id')
                ->orderBy('zeit_von')
                ->pluck('id');

            foreach ($einsaetze as $seq => $eid) {
                DB::table('einsaetze')->where('id', $eid)->update([
                    'tour_id'          => $tourId,
                    'tour_reihenfolge' => $seq + 1,
                    'updated_at'       => now(),
                ]);
            }
        }

        $this->command->info('Touren erstellt: ' . count($config));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RECHNUNGEN (5 in verschiedenen Zuständen)
    // ─────────────────────────────────────────────────────────────────────────

    private function createRechnungen(): void
    {
        $count = DB::table('rechnungen')->where('organisation_id', $this->orgId)->count();
        if ($count > 0) {
            $this->command->line("Rechnungen vorhanden ({$count}), übersprungen.");
            return;
        }

        $liste = [
            // [klient, monateBack, betragKK, betragPatient, status]
            ['Schmidt',  -3,  720.00,  180.00, 'bezahlt'],
            ['Bühler',   -2,  960.00,  240.00, 'gesendet'],
            ['Stauffer', -2,  840.00,  210.00, 'gesendet'],
            ['Brunner',  -1,  480.00,  120.00, 'entwurf'],
            ['Berger',   -1, 1080.00,  270.00, 'entwurf'],
        ];

        foreach ($liste as [$nachname, $monateBack, $betragKK, $betragPatient, $status]) {
            if (!isset($this->klienten[$nachname])) continue;

            $laufNr    = DB::table('rechnungen')->where('organisation_id', $this->orgId)->count() + 1;
            $rechnNr   = 'RE-' . now()->year . '-' . str_pad($laufNr, 4, '0', STR_PAD_LEFT);
            $periodeVon = now()->addMonths($monateBack)->startOfMonth();
            $periodeBis = now()->addMonths($monateBack)->endOfMonth();

            DB::table('rechnungen')->insert([
                'organisation_id' => $this->orgId,
                'klient_id'       => $this->klienten[$nachname],
                'rechnungsnummer' => $rechnNr,
                'periode_von'     => $periodeVon->toDateString(),
                'periode_bis'     => $periodeBis->toDateString(),
                'rechnungsdatum'  => $periodeBis->copy()->addDays(5)->toDateString(),
                'betrag_kk'       => $betragKK,
                'betrag_patient'  => $betragPatient,
                'betrag_total'    => $betragKK + $betragPatient,
                'status'          => $status,
                'created_at'      => now(), 'updated_at' => now(),
            ]);
        }

        $this->command->info('Rechnungen erstellt: ' . count($liste));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER
    // ─────────────────────────────────────────────────────────────────────────

    private function benutzerAnlegen(array $daten): int
    {
        $existing = DB::table('benutzer')->where('email', $daten['email'])->first();
        if ($existing) {
            $this->command->line("Benutzer {$daten['email']} existiert (ID: {$existing->id})");
            return $existing->id;
        }
        return DB::table('benutzer')->insertGetId(array_merge($daten, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    private function minutenZwischen(string $von, string $bis): int
    {
        [$h1, $m1] = explode(':', $von);
        [$h2, $m2] = explode(':', $bis);
        return ((int)$h2 * 60 + (int)$m2) - ((int)$h1 * 60 + (int)$m1);
    }

    private function bemerkung(string $klient): string
    {
        $map = [
            'Schmidt'    => 'Morgenpflege, Medikamente Metformin + Ramipril',
            'Brunner'    => 'Pflege + Begleitung, ruhige Stimmung',
            'Frei'       => 'Mobilisation nach Hüft-OP, Gehübungen',
            'Müller'     => 'Parkinson-Pflege, Zittern heute stärker',
            'Schneider'  => 'Angehörigenpflege (Stefan Schneider vor Ort)',
            'Zimmermann' => 'Hauswirtschaft, Mahlzeiten vorbereiten',
            'Studer'     => 'MS-Pflege, Fatigue ausgeprägt',
            'Gerber'     => 'Angehörigenpflege (Ruth Gerber vor Ort)',
            'Moser'      => 'Hauswirtschaft + Einkauf',
            'Steiner'    => 'Blutzuckerkontrolle, Insulinvergabe',
            'Bühler'     => 'Palliativpflege, Schmerzmanagement',
            'Käser'      => 'Angehörigenpflege (Franziska Käser vor Ort)',
            'Ryser'      => 'Post-OP-Nachsorge, Verbandswechsel',
            'Zürcher'    => 'Hauswirtschaft, Gelenkpflege',
            'Stauffer'   => 'Demenzpflege, Inkontinenzversorgung',
            'Huber'      => 'Parkinson + Diabetes, Medikamente',
            'Lanz'       => 'Sturzfolge-Betreuung, Mobilisation',
            'Wyss'       => 'MS-Pflege, Haushaltsunterstützung',
            'Berger'     => 'Palliativpflege, Schmerzmittel verabreicht',
            'Nussbaum'   => 'Demenzpflege, Orientierungshilfe',
        ];
        return $map[$klient] ?? 'Routinepflege';
    }

    private function rapportInhalt(string $typ, string $nachname): string
    {
        return match ($typ) {
            'pflege'       => "{$nachname}: Körperpflege, Ankleiden und Lagerung vollständig durchgeführt. Medikamente verabreicht. Allgemeinzustand stabil, Stimmung ausgeglichen. Kein Sturzrisiko beobachtet.",
            'verlauf'      => "Verlaufskontrolle {$nachname}: Vitalzeichen im Normbereich. Blutdruck 132/80 mmHg. Keine neuen Beschwerden. Kooperativ und ansprechbar.",
            'medikament'   => "Medikamentenvergabe {$nachname}: Alle Medikamente planmässig eingenommen. Keine Unverträglichkeiten beobachtet. Dokumentation vollständig.",
            'zwischenfall' => "{$nachname}: Sturzereignis beim Aufstehen. Keine sichtbaren Verletzungen. Hausarzt telefonisch informiert. Angehörige benachrichtigt. Sturzdokumentation ausgefüllt.",
            'information'  => "{$nachname}: Zeitwunsch für nächsten Besuch vermerkt. Besondere Hinweise an Folgebetreuer weitergegeben.",
            default        => "Routinebericht {$nachname}.",
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ZUSAMMENFASSUNG
    // ─────────────────────────────────────────────────────────────────────────

    private function printSummary(): void
    {
        $this->command->newLine();
        $this->command->info('✓ Testdaten erfolgreich angelegt!');
        $this->command->newLine();

        $rows = [];
        foreach ($this->pfleger as $name => $id) {
            $email = DB::table('benutzer')->find($id)?->email ?? '-';
            $rows[] = [$name, $email, 'test1234', 'pflege', 'Fachperson'];
        }
        foreach ($this->angehoerige as $name => $id) {
            $email = DB::table('benutzer')->find($id)?->email ?? '-';
            $rows[] = [$name, $email, 'test1234', 'pflege', 'Pflegender Angehöriger'];
        }
        $rows[] = ['Bauer Lisa', 'lisa.bauer@test.spitex', 'test1234', 'buchhaltung', 'Buchhaltung'];

        $this->command->table(['Name', 'E-Mail', 'Passwort', 'Rolle', 'Typ'], $rows);

        $this->command->newLine();
        $this->command->line('Klienten:  ' . count($this->klienten) . ' (davon 3 mit pflegendem Angehörigem)');
        $this->command->line('Ärzte:     ' . count($this->aerzte));
        $einsatzCount = DB::table('einsaetze')->where('organisation_id', $this->orgId)->count();
        $this->command->line("Einsätze:  {$einsatzCount}");
        $rapportCount = DB::table('rapporte')->where('organisation_id', $this->orgId)->count();
        $this->command->line("Rapporte:  {$rapportCount}");
    }
}
