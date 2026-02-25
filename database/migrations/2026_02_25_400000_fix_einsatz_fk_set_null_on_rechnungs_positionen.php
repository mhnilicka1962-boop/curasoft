<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * einsatz_id FK auf SET NULL ändern.
 *
 * Grund: Wenn ein Einsatz gelöscht wird (z.B. Korrektur), darf die bereits
 * erstellte Rechnungsposition NICHT mitgelöscht werden — Tarife, Beträge
 * und Minuten sind in der Position eingefroren und müssen 10 Jahre
 * nachvollziehbar bleiben (OR + MWSt-Pflicht).
 *
 * CASCADE DELETE war ein Versehen in der ursprünglichen Migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rechnungs_positionen', function (Blueprint $table) {
            $table->dropForeign(['einsatz_id']);
            $table->foreign('einsatz_id')
                  ->references('id')
                  ->on('einsaetze')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rechnungs_positionen', function (Blueprint $table) {
            $table->dropForeign(['einsatz_id']);
            $table->foreign('einsatz_id')
                  ->references('id')
                  ->on('einsaetze')
                  ->cascadeOnDelete();
        });
    }
};
