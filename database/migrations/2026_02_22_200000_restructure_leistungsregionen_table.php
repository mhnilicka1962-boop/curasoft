<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabelle neu erstellen (war leer, falsche Struktur)
        Schema::drop('leistungsregionen');

        Schema::create('leistungsregionen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leistungsart_id')->constrained('leistungsarten')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('regionen')->cascadeOnDelete();
            $table->decimal('ansatz', 10, 2)->default(0);       // Gesamtansatz
            $table->decimal('kkasse', 10, 2)->default(0);       // KK-Anteil
            $table->decimal('ansatz_akut', 10, 2)->default(0);  // Akut-Ansatz
            $table->decimal('kkasse_akut', 10, 2)->default(0);  // Akut KK-Anteil
            $table->boolean('kassenpflichtig')->default(true);
            $table->date('gueltig_ab')->nullable();
            $table->date('gueltig_bis')->nullable();
            $table->unique(['leistungsart_id', 'region_id']);
            $table->timestamps();
        });

        // einheit zu leistungsarten hinzufügen
        Schema::table('leistungsarten', function (Blueprint $table) {
            $table->enum('einheit', ['minuten', 'stunden', 'tage'])->default('minuten')->after('kassenpflichtig');
        });
    }

    public function down(): void
    {
        Schema::drop('leistungsregionen');

        Schema::create('leistungsregionen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leistungstyp_id')->constrained('leistungstypen')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('regionen')->cascadeOnDelete();
            $table->decimal('ansatz_patient', 10, 2)->default(0);
            $table->decimal('ansatz_kk', 10, 2)->default(0);
            $table->decimal('ansatz_akut_patient', 10, 2)->default(0);
            $table->decimal('ansatz_akut_kk', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('leistungsarten', function (Blueprint $table) {
            $table->dropColumn('einheit');
        });
    }
};
