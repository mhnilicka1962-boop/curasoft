<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benutzer_leistungsarten', function (Blueprint $table) {
            $table->unsignedBigInteger('benutzer_id');
            $table->unsignedBigInteger('leistungsart_id');
            $table->primary(['benutzer_id', 'leistungsart_id']);
            $table->foreign('benutzer_id')->references('id')->on('benutzer')->onDelete('cascade');
            $table->foreign('leistungsart_id')->references('id')->on('leistungsarten')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benutzer_leistungsarten');
    }
};
