<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Klienten: fehlende Stammdaten-Felder
        Schema::table('klienten', function (Blueprint $table) {
            // Anrede
            $table->string('anrede', 20)->nullable()->after('organisation_id');

            // Zuständiger Mitarbeiter
            $table->foreignId('zustaendig_id')->nullable()
                ->after('region_id')
                ->constrained('benutzer')
                ->nullOnDelete();

            // Planungs-Daten
            $table->date('datum_erstkontakt')->nullable()->after('zustaendig_id');
            $table->date('einsatz_geplant_von')->nullable()->after('datum_erstkontakt');
            $table->date('einsatz_geplant_bis')->nullable()->after('einsatz_geplant_von');

            // Persönliches
            $table->string('zivilstand', 50)->nullable()->after('geschlecht');
            $table->smallInteger('anzahl_kinder')->nullable()->after('zivilstand');
            $table->string('notfallnummer', 50)->nullable()->after('telefon');

            // Zahlungskonditionen
            $table->smallInteger('zahlbar_tage')->default(30)->after('krankenkasse_nr');
        });

        // 2. Einsätze: datum_bis für Tagespauschale-Perioden
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->date('datum_bis')->nullable()->after('datum');
        });
    }

    public function down(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->dropColumn('datum_bis');
        });

        Schema::table('klienten', function (Blueprint $table) {
            $table->dropForeign(['zustaendig_id']);
            $table->dropColumn([
                'anrede', 'zustaendig_id', 'datum_erstkontakt',
                'einsatz_geplant_von', 'einsatz_geplant_bis',
                'zivilstand', 'anzahl_kinder', 'notfallnummer', 'zahlbar_tage',
            ]);
        });
    }
};
