<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('einsaetze', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->foreignId('benutzer_id')->constrained('benutzer')->cascadeOnDelete();
            $table->foreignId('leistungstyp_id')->constrained('leistungstypen');
            $table->foreignId('region_id')->constrained('regionen');
            $table->date('datum');
            $table->time('zeit_von')->nullable();
            $table->time('zeit_bis')->nullable();
            $table->integer('minuten')->nullable();
            $table->text('bemerkung')->nullable();
            $table->boolean('verrechnet')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('einsaetze');
    }
};
