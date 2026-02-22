<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nachrichten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absender_id')->constrained('benutzer')->cascadeOnDelete();
            $table->string('betreff', 200);
            $table->text('inhalt');
            // Optionale Verknüpfung mit Klient oder Einsatz
            $table->string('referenz_typ', 30)->nullable(); // 'klient', 'einsatz'
            $table->unsignedBigInteger('referenz_id')->nullable();
            $table->timestamps();
        });

        Schema::create('nachricht_empfaenger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nachricht_id')->constrained('nachrichten')->cascadeOnDelete();
            $table->foreignId('empfaenger_id')->constrained('benutzer')->cascadeOnDelete();
            $table->timestamp('gelesen_am')->nullable();   // NULL = ungelesen
            $table->boolean('archiviert')->default(false);
            $table->unique(['nachricht_id', 'empfaenger_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nachricht_empfaenger');
        Schema::dropIfExists('nachrichten');
    }
};
