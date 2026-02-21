<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisationen', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('adresse')->nullable();
            $table->string('plz', 10)->nullable();
            $table->string('ort')->nullable();
            $table->string('kanton', 2)->nullable();
            $table->string('telefon', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('abrechnungsnummer', 50)->nullable();
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisationen');
    }
};
