<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klienten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->string('vorname');
            $table->string('nachname');
            $table->date('geburtsdatum')->nullable();
            $table->enum('geschlecht', ['m', 'w', 'x'])->nullable();
            $table->string('adresse')->nullable();
            $table->string('plz', 10)->nullable();
            $table->string('ort')->nullable();
            $table->string('telefon', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('ahv_nr', 20)->nullable();
            $table->string('krankenkasse_name')->nullable();
            $table->string('krankenkasse_nr', 50)->nullable();
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klienten');
    }
};
