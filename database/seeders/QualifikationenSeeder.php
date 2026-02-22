<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QualifikationenSeeder extends Seeder
{
    public function run(): void
    {
        $qualifikationen = [
            ['bezeichnung' => 'Fa SRK',                       'kuerzel' => 'Fa SRK',     'sort_order' => 1],
            ['bezeichnung' => 'FAGE',                          'kuerzel' => 'FAGE',       'sort_order' => 2],
            ['bezeichnung' => 'FABE',                          'kuerzel' => 'FABE',       'sort_order' => 3],
            ['bezeichnung' => 'Pflegeassistentin',             'kuerzel' => 'PA',         'sort_order' => 4],
            ['bezeichnung' => 'Pflegehelferin SRK (120 Std.)', 'kuerzel' => 'PH SRK',    'sort_order' => 5],
            ['bezeichnung' => 'DN I',                          'kuerzel' => 'DN I',       'sort_order' => 6],
            ['bezeichnung' => 'DN II',                         'kuerzel' => 'DN II',      'sort_order' => 7],
            ['bezeichnung' => 'HF',                            'kuerzel' => 'HF',         'sort_order' => 8],
            ['bezeichnung' => 'NDS HF Anästhesie',             'kuerzel' => 'NDS Anä',   'sort_order' => 9],
            ['bezeichnung' => 'NDS HF Intensivpflege',         'kuerzel' => 'NDS Int',    'sort_order' => 10],
            ['bezeichnung' => 'NDS HF Notfallpflege',          'kuerzel' => 'NDS Notf',   'sort_order' => 11],
            ['bezeichnung' => 'AKP',                           'kuerzel' => 'AKP',        'sort_order' => 12],
            ['bezeichnung' => 'Psy. Krankenpflege',            'kuerzel' => 'Psy. KP',    'sort_order' => 13],
            ['bezeichnung' => 'Altenpflegerin',                'kuerzel' => 'Altenp.',    'sort_order' => 14],
            ['bezeichnung' => 'Hauspflegerin',                 'kuerzel' => 'Hauspl.',    'sort_order' => 15],
            ['bezeichnung' => 'Betagtenbetreuerin',            'kuerzel' => 'Betagt.',    'sort_order' => 16],
            ['bezeichnung' => 'Putzfrau',                      'kuerzel' => 'Putzfrau',   'sort_order' => 17],
            ['bezeichnung' => 'Nicht definiert',               'kuerzel' => '—',          'sort_order' => 18],
        ];

        foreach ($qualifikationen as $q) {
            DB::table('qualifikationen')->insertOrIgnore(array_merge($q, [
                'aktiv'      => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
