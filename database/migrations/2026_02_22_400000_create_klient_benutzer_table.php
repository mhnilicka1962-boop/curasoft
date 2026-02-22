<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klient_benutzer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->foreignId('benutzer_id')->constrained('benutzer')->cascadeOnDelete();
            $table->enum('rolle', ['hauptbetreuer', 'betreuer', 'vertretung'])->default('betreuer');
            $table->boolean('aktiv')->default(true);
            $table->text('bemerkung')->nullable();
            $table->timestamps();

            $table->unique(['klient_id', 'benutzer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klient_benutzer');
    }
};
