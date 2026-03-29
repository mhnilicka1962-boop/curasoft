<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Duplikate entfernen: pro (einsatz_id, leistungsart_id) nur den Eintrag mit der höchsten ID behalten
        DB::statement("
            DELETE FROM einsatz_leistungsarten
            WHERE id NOT IN (
                SELECT MAX(id)
                FROM einsatz_leistungsarten
                GROUP BY einsatz_id, leistungsart_id
            )
        ");

        // Unique-Constraint hinzufügen
        Schema::table('einsatz_leistungsarten', function (Blueprint $table) {
            $table->unique(['einsatz_id', 'leistungsart_id'], 'einsatz_leistungsarten_unique');
        });
    }

    public function down(): void
    {
        Schema::table('einsatz_leistungsarten', function (Blueprint $table) {
            $table->dropUnique('einsatz_leistungsarten_unique');
        });
    }
};
