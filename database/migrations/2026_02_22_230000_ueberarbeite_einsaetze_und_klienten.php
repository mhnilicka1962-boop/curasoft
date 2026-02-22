<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Klienten: Region/Kanton für Tarif-Zuordnung
        Schema::table('klienten', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()
                ->after('ort')
                ->constrained('regionen')
                ->nullOnDelete();
        });

        // 2. Einsätze: Struktur korrigieren
        Schema::table('einsaetze', function (Blueprint $table) {
            // leistungstyp_id war Platzhalter auf falscher Ebene → entfernen
            $table->dropForeign(['leistungstyp_id']);
            $table->dropColumn('leistungstyp_id');

            // Korrekte Ebene: Leistungsart (Grundpflege, HWL, …)
            $table->foreignId('leistungsart_id')->nullable()
                ->after('benutzer_id')
                ->constrained('leistungsarten')
                ->nullOnDelete();

            // region_id nullable machen (war NOT NULL mit Platzhalter 1)
            $table->foreignId('region_id')->nullable()->change();

            // Status-Workflow
            $table->enum('status', ['geplant', 'aktiv', 'abgeschlossen', 'storniert'])
                ->default('geplant')
                ->after('leistungsart_id');
        });
    }

    public function down(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropForeign(['leistungsart_id']);
            $table->dropColumn('leistungsart_id');
            $table->foreignId('leistungstyp_id')->constrained('leistungstypen');
        });

        Schema::table('klienten', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
        });
    }
};
