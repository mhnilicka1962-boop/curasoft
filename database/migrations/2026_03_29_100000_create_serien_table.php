<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serien', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('organisation_id');
            $table->unsignedBigInteger('klient_id');
            $table->unsignedBigInteger('benutzer_id')->nullable();
            $table->string('rhythmus', 20); // taeglich | woechentlich
            $table->json('wochentage')->nullable(); // [1,3,5] = Mo,Mi,Fr
            $table->json('leistungsarten'); // [{id:1,minuten:30}, ...]
            $table->date('gueltig_ab');
            $table->date('gueltig_bis')->nullable();
            $table->time('zeit_von')->nullable();
            $table->time('zeit_bis')->nullable();
            $table->string('leistungserbringer_typ', 20)->default('fachperson');
            $table->unsignedBigInteger('verordnung_id')->nullable();
            $table->string('bemerkung', 500)->nullable();
            $table->timestamps();

            $table->foreign('klient_id')->references('id')->on('klienten')->onDelete('cascade');
            $table->foreign('benutzer_id')->references('id')->on('benutzer')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serien');
    }
};
