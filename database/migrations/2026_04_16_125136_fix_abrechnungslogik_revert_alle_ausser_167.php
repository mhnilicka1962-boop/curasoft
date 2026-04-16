<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Alle zurück auf tiers_garant (rückgängig machen)
        DB::statement("UPDATE rechnungslaeufe SET abrechnungslogik = 'tiers_garant' WHERE id != 167");
        // Nur #167 auf tiers_payant
        DB::statement("UPDATE rechnungslaeufe SET abrechnungslogik = 'tiers_payant' WHERE id = 167");
    }

    public function down(): void {}
};
