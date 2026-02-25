<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Schweizer Krankenkassen (KVG-zugelassen)
 *
 * Quellen:
 * - BAG Verzeichnis: bag.admin.ch → Krankenversicherung → Verzeichnisse Krankenversicherer
 * - MediData Versichererliste v1.4 (gültig ab 01.07.2024)
 * - Refdata Partner-Datenbank: refdata.ch/de/partner/abfrage/partner-refdatabase-gln
 *
 * WICHTIG — Zwei verschiedene GLN-Typen:
 * - ean_nr (ean_party im XML) = Teilnehmer-GLN der einzelnen Kasse
 * - Empfänger-GLN (transport to) = wohin MediData die Rechnung leitet (oft Konzern-GLN)
 *   → Die Empfänger-GLN wird im XML-Transport-Header gesetzt (noch nicht implementiert)
 *   → ean_nr hier = Teilnehmer-GLN für XML-Body (ean_party Attribut)
 *
 * Stand: 2026-02-25 — Fusionen berücksichtigt:
 * - easy sana + Supra-1846 → Groupe Mutuel (01.01.2025)
 * - Moove Sympany + Kolping → Vivao Sympany (01.01.2024)
 * - ProVita → SWICA (01.01.2024)
 * - vivacare → Galenos (01.01.2026)
 */
class KrankenkassenSeeder extends Seeder
{
    public function run(): void
    {
        $orgId = DB::table('organisationen')->value('id');
        if (!$orgId) return;

        // Nur einspielen wenn noch keine KK vorhanden
        if (DB::table('krankenkassen')->where('organisation_id', $orgId)->exists()) {
            $this->command->info('KrankenkassenSeeder: Bereits vorhanden — übersprungen.');
            return;
        }

        $kassen = [
            // ── Grosse nationale Versicherer ─────────────────────────────────────────────────────────────────────
            // HINWEIS: ean_nr = Teilnehmer-GLN (ean_party im XML). Empfänger-GLN (MediData-Transport) kann abweichen.
            // CSS Empfänger-GLN laut MediData v1.4: 7601003001082 (Routing-Header)
            ['name' => 'CSS Kranken-Versicherung AG',                  'kuerzel' => 'CSS',       'bag_nr' => '24',   'ean_nr' => '7601003000038', 'ort' => 'Luzern'],
            ['name' => 'Helsana Versicherungen AG',                    'kuerzel' => 'Helsana',   'bag_nr' => '88',   'ean_nr' => '7601003000045', 'ort' => 'Dübendorf'],
            ['name' => 'SWICA Krankenversicherung AG',                  'kuerzel' => 'SWICA',     'bag_nr' => '97',   'ean_nr' => '7601003000052', 'ort' => 'Winterthur'],
            ['name' => 'Concordia Schweizerische Kranken- und Unfallversicherung AG', 'kuerzel' => 'Concordia', 'bag_nr' => '49', 'ean_nr' => '7601003000069', 'ort' => 'Luzern'],
            // Sanitas Empfänger-GLN laut MediData v1.4: 7601003002294
            ['name' => 'Sanitas Krankenversicherung',                  'kuerzel' => 'Sanitas',   'bag_nr' => '77',   'ean_nr' => '7601003000076', 'ort' => 'Zürich'],
            ['name' => 'KPT Krankenkasse AG',                          'kuerzel' => 'KPT',       'bag_nr' => '53',   'ean_nr' => '7601003000083', 'ort' => 'Bern'],
            ['name' => 'Visana Versicherungen AG',                     'kuerzel' => 'Visana',    'bag_nr' => '100',  'ean_nr' => '7601003000090', 'ort' => 'Bern'],
            ['name' => 'Sympany AG',                                   'kuerzel' => 'Sympany',   'bag_nr' => '60',   'ean_nr' => '7601003000106', 'ort' => 'Basel'],
            ['name' => 'Assura-Basis SA',                              'kuerzel' => 'Assura',    'bag_nr' => '39',   'ean_nr' => '7601003000113', 'ort' => 'Pully'],
            ['name' => 'Atupri Gesundheitsversicherung',               'kuerzel' => 'Atupri',    'bag_nr' => '40',   'ean_nr' => '7601003000120', 'ort' => 'Bern'],

            // ── Groupe Mutuel Gruppe ──────────────────────────────────────────────────────────────────────────────
            // ALLE Groupe-Mutuel-Kassen: Empfänger-GLN (MediData-Transport) = 7601003002980
            // easy sana → Avenir fusioniert 01.01.2025; Supra-1846 → Mutuel fusioniert 01.01.2025
            ['name' => 'Groupe Mutuel Assurances GMA SA',              'kuerzel' => 'Groupe Mutuel', 'bag_nr' => '56', 'ean_nr' => '7601003002980', 'ort' => 'Martigny'],
            ['name' => 'Avenir Assurance Maladie SA',                  'kuerzel' => 'Avenir',    'bag_nr' => '41',   'ean_nr' => '7601003002980', 'ort' => 'Martigny'],
            ['name' => 'Philos Assurance Maladie SA',                  'kuerzel' => 'Philos',    'bag_nr' => '71',   'ean_nr' => '7601003002980', 'ort' => 'Martigny'],
            ['name' => 'easy sana Assurance Maladie SA',               'kuerzel' => 'easy sana', 'bag_nr' => '59',   'ean_nr' => '7601003002980', 'ort' => 'Martigny', 'aktiv' => false], // fusioniert in Avenir 01.01.2025
            ['name' => 'Mutuel Assurance Maladie SA',                  'kuerzel' => 'Mutuel',    'bag_nr' => '65',   'ean_nr' => '7601003002980', 'ort' => 'Martigny'],
            ['name' => 'Supra-1846 SA',                                'kuerzel' => 'Supra',     'bag_nr' => '92',   'ean_nr' => '7601003002980', 'ort' => 'Lausanne', 'aktiv' => false], // fusioniert in Mutuel 01.01.2025

            // ── Sympany Gruppe ────────────────────────────────────────────────────────────────────────────────────
            // Moove + Kolping fusioniert in Vivao Sympany 01.01.2024
            ['name' => 'Vivao Sympany AG',                             'kuerzel' => 'Vivao',     'bag_nr' => '102',  'ean_nr' => '7601003000207', 'ort' => 'Basel'],
            ['name' => 'Moove Sympany AG',                             'kuerzel' => 'Moove',     'bag_nr' => '64',   'ean_nr' => '7601003000351', 'ort' => 'Basel',   'aktiv' => false], // fusioniert in Vivao 01.01.2024
            ['name' => 'Kolping Krankenkasse',                         'kuerzel' => 'Kolping',   'bag_nr' => '54',   'ean_nr' => '7601003002256', 'ort' => 'Luzern',  'aktiv' => false], // fusioniert in Vivao 01.01.2024

            // ── Weitere nationale Versicherer ─────────────────────────────────────────────────────────────────────
            ['name' => 'EGK-Gesundheitskasse',                         'kuerzel' => 'EGK',       'bag_nr' => '62',   'ean_nr' => '7601003000151', 'ort' => 'Laufen'],
            // ÖKK Empfänger-GLN laut MediData v1.4: 7601003000894
            ['name' => 'ÖKK Kranken- und Unfallversicherungen AG',     'kuerzel' => 'ÖKK',       'bag_nr' => '70',   'ean_nr' => '7601003000168', 'ort' => 'Landquart'],
            ['name' => 'Sana24 AG',                                    'kuerzel' => 'Sana24',    'bag_nr' => '79',   'ean_nr' => '7601003010220', 'ort' => 'Bern'],    // Visana-Gruppe
            ['name' => 'Aquilana Versicherungen',                      'kuerzel' => 'Aquilana',  'bag_nr' => '38',   'ean_nr' => '7601003000175', 'ort' => 'Baden'],
            ['name' => 'Arcosana AG',                                  'kuerzel' => 'Arcosana',  'bag_nr' => null,   'ean_nr' => '7601003001082', 'ort' => 'Luzern'],  // CSS-Gruppe, Empfänger = CSS-GLN
            ['name' => 'ProVita AG',                                   'kuerzel' => 'ProVita',   'bag_nr' => '73',   'ean_nr' => '7601003000052', 'ort' => 'Zürich',  'aktiv' => false], // in SWICA integriert 01.01.2024
            ['name' => 'vivacare SA',                                  'kuerzel' => 'vivacare',  'bag_nr' => '101',  'ean_nr' => '7601003011098', 'ort' => 'Martigny', 'aktiv' => false], // in Galenos integriert 01.01.2026
            ['name' => 'Galenos Kranken- und Unfallversicherung',      'kuerzel' => 'Galenos',   'bag_nr' => '84',   'ean_nr' => '7601003000054', 'ort' => 'Zürich'],  // Visana-Gruppe; inkl. vivacare ab 2026
            ['name' => 'Wincare Versicherungen',                       'kuerzel' => 'Wincare',   'bag_nr' => '104',  'ean_nr' => '7601003002423', 'ort' => 'Winterthur'],

            // ── Regionale / kleine Versicherer ───────────────────────────────────────────────────────────────────
            ['name' => 'rhenusana',                                    'kuerzel' => 'rhenusana', 'bag_nr' => '76',   'ean_nr' => '7601003000788', 'ort' => 'Au SG'],
            ['name' => 'SLKK Schweizerische Lehrerinnen- und Lehrer-Krankenkasse', 'kuerzel' => 'SLKK', 'bag_nr' => '87', 'ean_nr' => '7601003002263', 'ort' => 'Zürich'],
            ['name' => 'sodalis gesundheitsgruppe',                    'kuerzel' => 'sodalis',   'bag_nr' => '89',   'ean_nr' => '7601003001891', 'ort' => 'Sempach'],
            ['name' => 'AGRIsano Stiftung',                            'kuerzel' => 'AGRIsano',  'bag_nr' => '37',   'ean_nr' => '7601003000436', 'ort' => 'Brugg'],
            ['name' => 'Sumiswalder Krankenkasse',                     'kuerzel' => 'Sumiswald', 'bag_nr' => '93',   'ean_nr' => '7601003001686', 'ort' => 'Sumiswald'],
            ['name' => 'Krankenkasse Institut Ingenbohl',              'kuerzel' => 'Ingenbohl', 'bag_nr' => '51',   'ean_nr' => '7601003002140', 'ort' => 'Brunnen'],

            // ── Sehr kleine / regionale Kassen (GLN nicht verifiziert) ───────────────────────────────────────────
            // Bitte über refdata.ch/de/partner/abfrage/partner-refdatabase-gln verifizieren
            ['name' => 'KKV Krankenkasse',                             'kuerzel' => 'KKV',       'bag_nr' => '52',   'ean_nr' => null,            'ort' => 'Kreuzlingen'],
            ['name' => 'Krankenkasse Steffisburg',                     'kuerzel' => 'Steffisburg','bag_nr'=> '90',   'ean_nr' => null,            'ort' => 'Steffisburg'],
            ['name' => 'vita surselva',                                'kuerzel' => 'vita surselva','bag_nr'=> '99', 'ean_nr' => null,            'ort' => 'Ilanz'],
            ['name' => 'Lumneziana',                                   'kuerzel' => 'Lumneziana','bag_nr' => '57',   'ean_nr' => null,            'ort' => 'Vella'],
            ['name' => 'Fondation Natura',                             'kuerzel' => 'Natura',    'bag_nr' => '67',   'ean_nr' => null,            'ort' => 'Basel'],
        ];

        $jetzt = now();
        foreach ($kassen as $k) {
            DB::table('krankenkassen')->insert([
                'organisation_id' => $orgId,
                'name'            => $k['name'],
                'kuerzel'         => $k['kuerzel'],
                'bag_nr'          => $k['bag_nr'] ?? null,
                'ean_nr'          => $k['ean_nr'] ?? null,
                'ort'             => $k['ort'] ?? null,
                'aktiv'           => $k['aktiv'] ?? true,
                'created_at'      => $jetzt,
                'updated_at'      => $jetzt,
            ]);
        }

        $this->command->info('KrankenkassenSeeder: ' . count($kassen) . ' Krankenkassen eingespielt.');
        $this->command->warn('HINWEIS: Fusions-Kassen (easy sana, Supra, Moove, Kolping, ProVita, vivacare) als inaktiv markiert.');
        $this->command->warn('PRÜFEN: KKV, Steffisburg, vita surselva, Lumneziana, Fondation Natura — GLN fehlt noch.');
        $this->command->warn('Verifizieren: refdata.ch/de/partner/abfrage/partner-refdatabase-gln');
    }
}
