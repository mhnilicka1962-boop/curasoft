<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leistungstypen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leistungsart_id')->constrained('leistungsarten')->cascadeOnDelete();
            $table->string('bezeichnung');
            $table->enum('einheit', ['minuten', 'stunden', 'tage'])->default('minuten');
            $table->date('gueltig_ab')->nullable();
            $table->date('gueltig_bis')->nullable();
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leistungstypen');
    }
};
