<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Angehörigenpflege — fehlende Felder für vollständige Abbildung
 *
 * 1. klienten.klient_typ          — patient | pflegebeduerftig | angehoerig
 * 2. klient_benutzer.beziehungstyp — fachperson | angehoerig_pflegend | freiwillig
 * 3. benutzer.anstellungsart       — fachperson | angehoerig | freiwillig | praktikum
 * 4. einsaetze.leistungserbringer_typ — fachperson | angehoerig  (KVG-Abrechnung)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Klient-Typ
        Schema::table('klienten', function (Blueprint $table) {
            $table->string('klient_typ', 30)->default('patient')
                ->after('anrede')
                ->comment('patient | pflegebeduerftig | angehoerig');
        });

        // 2. Beziehungstyp auf Klient-Benutzer-Verknüpfung
        Schema::table('klient_benutzer', function (Blueprint $table) {
            $table->string('beziehungstyp', 30)->nullable()
                ->after('rolle')
                ->comment('fachperson | angehoerig_pflegend | freiwillig');
        });

        // 3. Anstellungsart auf Benutzer
        Schema::table('benutzer', function (Blueprint $table) {
            $table->string('anstellungsart', 30)->default('fachperson')
                ->after('rolle')
                ->comment('fachperson | angehoerig | freiwillig | praktikum');
        });

        // 4. Leistungserbringer-Typ auf Einsatz (KVG-relevant)
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->string('leistungserbringer_typ', 30)->default('fachperson')
                ->after('verordnung_id')
                ->comment('fachperson | angehoerig — bestimmt KVG-Abrechenbarkeit');
        });
    }

    public function down(): void
    {
        Schema::table('klienten',      fn($t) => $t->dropColumn('klient_typ'));
        Schema::table('klient_benutzer', fn($t) => $t->dropColumn('beziehungstyp'));
        Schema::table('benutzer',      fn($t) => $t->dropColumn('anstellungsart'));
        Schema::table('einsaetze',     fn($t) => $t->dropColumn('leistungserbringer_typ'));
    }
};
