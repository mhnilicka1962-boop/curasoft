<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Schweizer Krankenkassen (KVG-zugelassen)
 *
 * Quelle: BAG – Verzeichnis der zugelassenen Krankenversicherer
 * https://www.bag.admin.ch/bag/de/home/versicherungen/krankenversicherung/
 *         krankenversicherung-versicherer-aufsicht/verzeichnisse-krankenversicherer.html
 *
 * EAN-Nummern (GLN) bitte anhand der offiziellen BAG-Liste verifizieren
 * bevor sie für die XML 450.100 Abrechnung verwendet werden.
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
            // ── Grosse nationale Versicherer ──────────────────────────────────
            ['name' => 'CSS Kranken-Versicherung AG',                  'kuerzel' => 'CSS',       'bag_nr' => '24',   'ean_nr' => '7601003000038', 'ort' => 'Luzern'],
            ['name' => 'Helsana Versicherungen AG',                    'kuerzel' => 'Helsana',   'bag_nr' => '88',   'ean_nr' => '7601003000045', 'ort' => 'Dübendorf'],
            ['name' => 'SWICA Krankenversicherung AG',                  'kuerzel' => 'SWICA',     'bag_nr' => '97',   'ean_nr' => '7601003000052', 'ort' => 'Winterthur'],
            ['name' => 'Concordia Schweizerische Kranken- und Unfallversicherung AG', 'kuerzel' => 'Concordia', 'bag_nr' => '49', 'ean_nr' => '7601003000069', 'ort' => 'Luzern'],
            ['name' => 'Sanitas Krankenversicherung',                  'kuerzel' => 'Sanitas',   'bag_nr' => '77',   'ean_nr' => '7601003000076', 'ort' => 'Zürich'],
            ['name' => 'KPT Krankenkasse AG',                          'kuerzel' => 'KPT',       'bag_nr' => '53',   'ean_nr' => '7601003000083', 'ort' => 'Bern'],
            ['name' => 'Visana Versicherungen AG',                     'kuerzel' => 'Visana',    'bag_nr' => '100',  'ean_nr' => '7601003000090', 'ort' => 'Bern'],
            ['name' => 'Sympany AG',                                   'kuerzel' => 'Sympany',   'bag_nr' => '60',   'ean_nr' => '7601003000106', 'ort' => 'Basel'],
            ['name' => 'Assura-Basis SA',                              'kuerzel' => 'Assura',    'bag_nr' => '39',   'ean_nr' => '7601003000113', 'ort' => 'Pully'],
            ['name' => 'Atupri Gesundheitsversicherung',               'kuerzel' => 'Atupri',    'bag_nr' => '40',   'ean_nr' => '7601003000120', 'ort' => 'Bern'],

            // ── Groupe Mutuel ─────────────────────────────────────────────────
            ['name' => 'Groupe Mutuel Assurances GMA SA',              'kuerzel' => 'Groupe Mutuel', 'bag_nr' => '56', 'ean_nr' => '7601003000137', 'ort' => 'Martigny'],
            ['name' => 'Avenir Assurance Maladie SA',                  'kuerzel' => 'Avenir',    'bag_nr' => '41',   'ean_nr' => null,            'ort' => 'Martigny'],
            ['name' => 'Philos Assurance Maladie SA',                  'kuerzel' => 'Philos',    'bag_nr' => '71',   'ean_nr' => null,            'ort' => 'Martigny'],
            ['name' => 'easy sana Assurance Maladie SA',               'kuerzel' => 'easy sana', 'bag_nr' => '59',   'ean_nr' => null,            'ort' => 'Martigny'],
            ['name' => 'Mutuel Assurance Maladie SA',                  'kuerzel' => 'Mutuel',    'bag_nr' => '65',   'ean_nr' => null,            'ort' => 'Martigny'],
            ['name' => 'vivacare SA',                                  'kuerzel' => 'vivacare',  'bag_nr' => '101',  'ean_nr' => null,            'ort' => 'Martigny'],

            // ── Weitere nationale Versicherer ─────────────────────────────────
            ['name' => 'EGK-Gesundheitskasse',                         'kuerzel' => 'EGK',       'bag_nr' => '62',   'ean_nr' => '7601003000151', 'ort' => 'Laufen'],
            ['name' => 'ÖKK Kranken- und Unfallversicherungen AG',     'kuerzel' => 'ÖKK',       'bag_nr' => '70',   'ean_nr' => '7601003000168', 'ort' => 'Landquart'],
            ['name' => 'Sana24 AG',                                    'kuerzel' => 'Sana24',    'bag_nr' => '79',   'ean_nr' => null,            'ort' => 'Bern'],
            ['name' => 'Vivao Sympany AG',                             'kuerzel' => 'Vivao',     'bag_nr' => '102',  'ean_nr' => null,            'ort' => 'Basel'],
            ['name' => 'Moove Sympany AG',                             'kuerzel' => 'Moove',     'bag_nr' => '64',   'ean_nr' => null,            'ort' => 'Basel'],
            ['name' => 'Aquilana Versicherungen',                      'kuerzel' => 'Aquilana',  'bag_nr' => '38',   'ean_nr' => '7601003000175', 'ort' => 'Baden'],
            ['name' => 'Arcosana AG',                                  'kuerzel' => 'Arcosana',  'bag_nr' => null,   'ean_nr' => null,            'ort' => 'Luzern'],
            ['name' => 'ProVita AG',                                   'kuerzel' => 'ProVita',   'bag_nr' => '73',   'ean_nr' => null,            'ort' => 'Zürich'],
            ['name' => 'rhenusana',                                    'kuerzel' => 'rhenusana', 'bag_nr' => '76',   'ean_nr' => null,            'ort' => 'Au SG'],
            ['name' => 'SLKK Schweizerische Lehrerinnen- und Lehrer-Krankenkasse', 'kuerzel' => 'SLKK', 'bag_nr' => '87', 'ean_nr' => null, 'ort' => 'Zürich'],
            ['name' => 'sodalis gesundheitsgruppe',                    'kuerzel' => 'sodalis',   'bag_nr' => '89',   'ean_nr' => null,            'ort' => 'Sempach'],
            ['name' => 'KKV Krankenkasse',                             'kuerzel' => 'KKV',       'bag_nr' => '52',   'ean_nr' => null,            'ort' => 'Kreuzlingen'],
            ['name' => 'Krankenkasse Steffisburg',                     'kuerzel' => 'Steffisburg','bag_nr'=> '90',   'ean_nr' => null,            'ort' => 'Steffisburg'],
            ['name' => 'vita surselva',                                'kuerzel' => 'vita surselva','bag_nr'=> '99', 'ean_nr' => null,            'ort' => 'Ilanz'],
            ['name' => 'Lumneziana',                                   'kuerzel' => 'Lumneziana','bag_nr' => '57',   'ean_nr' => null,            'ort' => 'Vella'],
            ['name' => 'Krankenkasse Institut Ingenbohl',              'kuerzel' => 'Ingenbohl',  'bag_nr' => '51',   'ean_nr' => null,            'ort' => 'Brunnen'],
            ['name' => 'AGRIsano Stiftung',                            'kuerzel' => 'AGRIsano',  'bag_nr' => '37',   'ean_nr' => null,            'ort' => 'Brugg'],
            ['name' => 'Fondation Natura',                             'kuerzel' => 'Natura',    'bag_nr' => '67',   'ean_nr' => null,            'ort' => 'Basel'],
            ['name' => 'Supra-1846 SA',                                'kuerzel' => 'Supra',     'bag_nr' => '92',   'ean_nr' => null,            'ort' => 'Lausanne'],
            ['name' => 'Galenos Kranken- und Unfallversicherung',      'kuerzel' => 'Galenos',   'bag_nr' => '84',   'ean_nr' => null,            'ort' => 'Zürich'],
            ['name' => 'Wincare Versicherungen',                       'kuerzel' => 'Wincare',   'bag_nr' => '104',  'ean_nr' => null,            'ort' => 'Winterthur'],
            ['name' => 'Sumiswalder Krankenkasse',                     'kuerzel' => 'Sumiswald',  'bag_nr' => '93',  'ean_nr' => null,            'ort' => 'Sumiswald'],
            ['name' => 'Kolping Krankenkasse',                         'kuerzel' => 'Kolping',   'bag_nr' => '54',   'ean_nr' => null,            'ort' => 'Luzern'],
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
                'aktiv'           => true,
                'created_at'      => $jetzt,
                'updated_at'      => $jetzt,
            ]);
        }

        $this->command->info('KrankenkassenSeeder: ' . count($kassen) . ' Krankenkassen eingespielt.');
        $this->command->warn('WICHTIG: EAN-Nummern (GLN) bitte anhand der offiziellen BAG-Liste verifizieren!');
        $this->command->warn('Quelle: bag.admin.ch → Krankenversicherung → Verzeichnisse Krankenversicherer');
    }
}
