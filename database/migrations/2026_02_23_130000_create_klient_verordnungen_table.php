<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klient_verordnungen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->foreignId('arzt_id')->nullable()->constrained('aerzte')->nullOnDelete();
            $table->foreignId('leistungsart_id')->nullable()->constrained('leistungsarten')->nullOnDelete();
            $table->string('verordnungs_nr', 50)->nullable()->comment('Verordnungsnummer des Arztes');
            $table->date('ausgestellt_am')->nullable()->comment('Datum der Verordnung');
            $table->date('gueltig_ab')->nullable();
            $table->date('gueltig_bis')->nullable();
            $table->text('bemerkung')->nullable();
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });

        // Verordnung optional auf Einsatz verknüpfen (für Abrechnung)
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->foreignId('verordnung_id')
                ->nullable()
                ->after('leistungsart_id')
                ->constrained('klient_verordnungen')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->dropConstrainedForeignId('verordnung_id');
        });
        Schema::dropIfExists('klient_verordnungen');
    }
};
