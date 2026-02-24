<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('einsatz_aktivitaeten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('einsatz_id')->constrained('einsaetze')->onDelete('cascade');
            $table->foreignId('organisation_id')->constrained('organisationen');
            $table->string('kategorie');
            $table->string('aktivitaet');
            $table->unsignedSmallInteger('minuten')->default(5);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('einsatz_aktivitaeten');
    }
};
