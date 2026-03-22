<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Leistungsarten sicherstellen (falls fehlend)
        $leistungsarten = [
            ['bezeichnung' => 'Pauschale',            'einheit' => 'tage',    'kassenpflichtig' => false],
            ['bezeichnung' => 'Untersuchung Behandlung', 'einheit' => 'minuten', 'kassenpflichtig' => true],
            ['bezeichnung' => 'Hauswirtschaft',       'einheit' => 'minuten', 'kassenpflichtig' => false],
            ['bezeichnung' => 'Grundpflege',          'einheit' => 'minuten', 'kassenpflichtig' => true],
            ['bezeichnung' => 'Abklärung/Beratung',   'einheit' => 'minuten', 'kassenpflichtig' => true],
        ];

        foreach ($leistungsarten as $la) {
            if (!DB::table('leistungsarten')->where('bezeichnung', $la['bezeichnung'])->exists()) {
                DB::table('leistungsarten')->insert([
                    'bezeichnung'      => $la['bezeichnung'],
                    'einheit'          => $la['einheit'],
                    'kassenpflichtig'  => $la['kassenpflichtig'],
                    'aktiv'            => true,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }

        // IDs nachschlagen
        $la = DB::table('leistungsarten')->pluck('id', 'bezeichnung');

        $pauschale = $la['Pauschale']               ?? null;
        $ub        = $la['Untersuchung Behandlung'] ?? null;
        $hwl       = $la['Hauswirtschaft']          ?? null;
        $gp        = $la['Grundpflege']             ?? null;
        $ab        = $la['Abklärung/Beratung']      ?? null;

        // Leistungstypen sicherstellen (nur fehlende einfügen)
        $eintraege = [
            [$pauschale, 'Tagespauschale'],
            [$ub,        'Blutzucker'],
            [$ub,        'Inhalation'],
            [$ub,        'Injektion subcutan'],
            [$ub,        'Medikamente richten'],
            [$ub,        'Verbandwechsel'],
            [$ub,        'Vitalzeichen (Puls, BD, T, Gewicht)'],
            [$hwl,       'Abklärung und Beratung HWL'],
            [$hwl,       'HWL-Leistungen'],
            [$gp,        'An-/Auskleiden'],
            [$gp,        'Antithrombose Strümpfe'],
            [$gp,        'Ausscheidung'],
            [$gp,        'Beine einbinden'],
            [$gp,        'Betten im Bett'],
            [$gp,        'Dekubitusprophylaxe'],
            [$gp,        'Duschen'],
            [$gp,        'Essen und Trinken'],
            [$gp,        'Grundpflege'],
            [$gp,        'Intimpflege'],
            [$gp,        'Lagern'],
            [$gp,        'Medikamente abgeben'],
            [$gp,        'Mobilisation'],
            [$gp,        'Mundpflege'],
            [$gp,        'Nagelpflege'],
            [$gp,        'Rasur'],
            [$gp,        'Waschen am Lavabo'],
            [$gp,        'Waschen im Bett'],
            [$ab,        'Bedarfsanalyse'],
            [$ab,        'Beratungsgespräch'],
            [$ab,        'Dokumentation'],
            [$ab,        'Administration'],
        ];

        foreach ($eintraege as [$laId, $bezeichnung]) {
            if (!$laId) continue;
            if (!DB::table('leistungstypen')->where('leistungsart_id', $laId)->where('bezeichnung', $bezeichnung)->exists()) {
                DB::table('leistungstypen')->insert([
                    'leistungsart_id' => $laId,
                    'bezeichnung'     => $bezeichnung,
                    'einheit'         => 'minuten',
                    'aktiv'           => true,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Absichtlich leer — Stammdaten werden nicht rückgängig gemacht
    }
};
