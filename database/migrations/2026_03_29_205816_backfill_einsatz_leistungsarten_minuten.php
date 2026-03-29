<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Aktivitätsname → leistungsart_id via leistungstypen
        $ltMap = DB::table('leistungstypen')->pluck('leistungsart_id', 'bezeichnung');

        // Alle einsatz_leistungsarten mit minuten=0
        $einsatzIds = DB::table('einsatz_leistungsarten')
            ->where('minuten', 0)
            ->distinct()
            ->pluck('einsatz_id');

        foreach ($einsatzIds as $einsatzId) {
            $aktivitaeten = DB::table('einsatz_aktivitaeten')
                ->where('einsatz_id', $einsatzId)
                ->get(['aktivitaet', 'minuten']);

            $summen = [];
            foreach ($aktivitaeten as $akt) {
                $laId = $ltMap[$akt->aktivitaet] ?? null;
                if (!$laId) continue;
                $summen[$laId] = ($summen[$laId] ?? 0) + $akt->minuten;
            }

            foreach ($summen as $laId => $min) {
                DB::table('einsatz_leistungsarten')
                    ->where('einsatz_id', $einsatzId)
                    ->where('leistungsart_id', $laId)
                    ->update(['minuten' => $min]);
            }
        }
    }

    public function down(): void
    {
        // Nicht reversibel
    }
};
