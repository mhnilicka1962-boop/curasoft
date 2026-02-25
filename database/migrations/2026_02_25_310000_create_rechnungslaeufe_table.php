<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rechnungslaeufe', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen');
            $table->date('periode_von');
            $table->date('periode_bis');
            $table->string('rechnungstyp', 20);
            $table->decimal('tarif_patient', 8, 4)->default(0);
            $table->decimal('tarif_kk', 8, 4)->default(0);
            $table->integer('anzahl_erstellt')->default(0);
            $table->integer('anzahl_uebersprungen')->default(0);
            $table->string('status', 20)->default('abgeschlossen');
            $table->foreignId('erstellt_von')->constrained('benutzer');
            $table->timestamps();
        });

        Schema::table('rechnungen', function (Blueprint $table) {
            $table->foreignId('rechnungslauf_id')->nullable()
                  ->constrained('rechnungslaeufe')->nullOnDelete()
                  ->after('rechnungstyp');
        });
    }

    public function down(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rechnungslauf_id');
        });
        Schema::dropIfExists('rechnungslaeufe');
    }
};
