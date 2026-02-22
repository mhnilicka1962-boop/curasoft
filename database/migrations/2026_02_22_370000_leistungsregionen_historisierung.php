<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Unique-Constraint (leistungsart_id, region_id) entfernen
        // → erlaubt mehrere Einträge pro Kombination mit verschiedenen gueltig_ab-Daten
        DB::statement('ALTER TABLE leistungsregionen DROP CONSTRAINT IF EXISTS leistungsregionen_leistungsart_id_region_id_unique');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE leistungsregionen ADD CONSTRAINT leistungsregionen_leistungsart_id_region_id_unique UNIQUE (leistungsart_id, region_id)');
    }
};
