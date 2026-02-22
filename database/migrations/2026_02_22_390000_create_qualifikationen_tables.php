<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qualifikationen', function (Blueprint $table) {
            $table->id();
            $table->string('bezeichnung', 100);
            $table->string('kuerzel', 40)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });

        Schema::create('benutzer_qualifikation', function (Blueprint $table) {
            $table->foreignId('benutzer_id')->constrained('benutzer')->cascadeOnDelete();
            $table->foreignId('qualifikation_id')->constrained('qualifikationen')->cascadeOnDelete();
            $table->primary(['benutzer_id', 'qualifikation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benutzer_qualifikation');
        Schema::dropIfExists('qualifikationen');
    }
};
