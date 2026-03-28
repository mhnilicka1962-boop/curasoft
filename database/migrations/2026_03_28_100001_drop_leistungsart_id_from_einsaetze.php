<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bestehende leistungsart_id-Werte in neue Pivot-Tabelle übertragen
        \DB::statement("
            INSERT INTO einsatz_leistungsarten (einsatz_id, leistungsart_id, minuten, created_at, updated_at)
            SELECT id, leistungsart_id, COALESCE(minuten, 0), NOW(), NOW()
            FROM einsaetze
            WHERE leistungsart_id IS NOT NULL
            ON CONFLICT DO NOTHING
        ");

        Schema::table('einsaetze', function (Blueprint $table) {
            $table->dropForeign(['leistungsart_id']);
            $table->dropColumn('leistungsart_id');
        });
    }

    public function down(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->foreignId('leistungsart_id')->nullable()->constrained('leistungsarten')->nullOnDelete();
        });
    }
};
