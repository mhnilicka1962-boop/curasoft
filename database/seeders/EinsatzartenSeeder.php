<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EinsatzartenSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('leistungstypen')->count() > 0) {
            return;
        }

        // Leistungsarten per Name nachschlagen
        $la = DB::table('leistungsarten')->pluck('id', 'bezeichnung');

        $pauschale   = $la['Pauschale']               ?? null;
        $ub          = $la['Untersuchung Behandlung']  ?? null;
        $hwl         = $la['Hauswirtschaft']           ?? null;
        $gp          = $la['Grundpflege']              ?? null;
        $ab          = $la['Abklärung/Beratung']       ?? null;

        $eintraege = [
            // Pauschale
            ['leistungsart_id' => $pauschale, 'bezeichnung' => 'Tagespauschale'],

            // Untersuchung / Behandlung
            ['leistungsart_id' => $ub, 'bezeichnung' => 'Vitalzeichen (Puls, BD, T, Gewicht)'],
            ['leistungsart_id' => $ub, 'bezeichnung' => 'Blutzucker'],
            ['leistungsart_id' => $ub, 'bezeichnung' => 'Inhalation'],
            ['leistungsart_id' => $ub, 'bezeichnung' => 'Medikamente'],
            ['leistungsart_id' => $ub, 'bezeichnung' => 'Verbandwechsel'],
            ['leistungsart_id' => $ub, 'bezeichnung' => 'Spritzen'],

            // Hauswirtschaft
            ['leistungsart_id' => $hwl, 'bezeichnung' => 'Abklärung und Beratung HWL'],
            ['leistungsart_id' => $hwl, 'bezeichnung' => 'HWL-Leistungen'],

            // Grundpflege
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Grundpflege'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Waschen im Bett'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Waschen am Lavabo'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Duschen'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Intimpflege'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Mobilisation'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Lagern'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Ausscheidung'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Beine einbinden'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Antithrombose Strümpfe'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Betten im Bett'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Dekubitusprophylaxe'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'An-/Auskleiden'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Essen und Trinken'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Mundpflege'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Rasur'],
            ['leistungsart_id' => $gp, 'bezeichnung' => 'Nagelpflege'],

            // Abklärung / Beratung
            ['leistungsart_id' => $ab, 'bezeichnung' => 'Bedarfsanalyse'],
            ['leistungsart_id' => $ab, 'bezeichnung' => 'Beratungsgespräch'],
            ['leistungsart_id' => $ab, 'bezeichnung' => 'Dokumentation'],
            ['leistungsart_id' => $ab, 'bezeichnung' => 'Administration'],
        ];

        $now = now();
        foreach ($eintraege as $e) {
            if (!$e['leistungsart_id']) continue;
            DB::table('leistungstypen')->insert([
                'leistungsart_id' => $e['leistungsart_id'],
                'bezeichnung'     => $e['bezeichnung'],
                'einheit'         => 'minuten',
                'aktiv'           => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }
}
