<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * GLN = Global Location Number (GS1, 13-stellig, beginnt mit 7601003... in der Schweiz)
 *
 * organisationen.gln  — GLN der Spitex-Organisation (von GS1 Schweiz)
 *                        Pflichtfeld im XML 450.100: biller.ean_party + provider.ean_party
 *
 * benutzer.gln        — GLN der Pflegefachperson aus dem NAREG-Register
 *                        (nareg.admin.ch — Nationales Register der Gesundheitsberufe)
 *                        Pflichtfeld im XML 450.100: service.ean_responsible pro Leistungsposition
 *
 * benutzer.nareg_nr   — NAREG-Registernummer (z.B. "80012345") — für interne Kontrolle
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisationen', function (Blueprint $table) {
            $table->string('gln', 13)->nullable()->after('zsr_nr')
                ->comment('GLN der Organisation (GS1 Schweiz, 13-stellig)');
        });

        Schema::table('benutzer', function (Blueprint $table) {
            $table->string('gln', 13)->nullable()->after('ahv_nr')
                ->comment('GLN aus NAREG-Register (nareg.admin.ch)');
            $table->string('nareg_nr', 20)->nullable()->after('gln')
                ->comment('NAREG-Registernummer (Pflegefachpersonen)');
        });
    }

    public function down(): void
    {
        Schema::table('organisationen', function (Blueprint $table) {
            $table->dropColumn('gln');
        });

        Schema::table('benutzer', function (Blueprint $table) {
            $table->dropColumn(['gln', 'nareg_nr']);
        });
    }
};
