<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Leistungsarten: gültig ab/bis + Default-Ansätze (Vorlage für Kanton-Anlage)
        Schema::table('leistungsarten', function (Blueprint $table) {
            $table->date('gueltig_ab')->nullable()->after('aktiv');
            $table->date('gueltig_bis')->nullable()->after('gueltig_ab');
            $table->decimal('ansatz_default', 10, 2)->default(0)->after('gueltig_bis');
            $table->decimal('kvg_default', 10, 2)->default(0)->after('ansatz_default');
            $table->decimal('ansatz_akut_default', 10, 2)->default(0)->after('kvg_default');
            $table->decimal('kvg_akut_default', 10, 2)->default(0)->after('ansatz_akut_default');
        });

        // Leistungsregionen: Verrechnungs-Flags + MWST
        Schema::table('leistungsregionen', function (Blueprint $table) {
            $table->boolean('verrechnung')->default(true)->after('gueltig_bis');
            $table->boolean('einsatz_minuten')->default(false)->after('verrechnung');
            $table->boolean('einsatz_stunden')->default(true)->after('einsatz_minuten');
            $table->boolean('einsatz_tage')->default(false)->after('einsatz_stunden');
            $table->boolean('mwst')->default(false)->after('einsatz_tage');
        });
    }

    public function down(): void
    {
        Schema::table('leistungsarten', function (Blueprint $table) {
            $table->dropColumn(['gueltig_ab', 'gueltig_bis', 'ansatz_default', 'kvg_default', 'ansatz_akut_default', 'kvg_akut_default']);
        });

        Schema::table('leistungsregionen', function (Blueprint $table) {
            $table->dropColumn(['verrechnung', 'einsatz_minuten', 'einsatz_stunden', 'einsatz_tage', 'mwst']);
        });
    }
};
