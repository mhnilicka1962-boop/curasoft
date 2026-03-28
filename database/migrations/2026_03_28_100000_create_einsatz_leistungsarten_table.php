<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('einsatz_leistungsarten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('einsatz_id')->constrained('einsaetze')->cascadeOnDelete();
            $table->foreignId('leistungsart_id')->constrained('leistungsarten')->restrictOnDelete();
            $table->integer('minuten')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('einsatz_leistungsarten');
    }
};
