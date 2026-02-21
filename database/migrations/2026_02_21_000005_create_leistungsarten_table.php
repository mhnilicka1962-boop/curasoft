<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leistungsarten', function (Blueprint $table) {
            $table->id();
            $table->string('bezeichnung');
            $table->boolean('kassenpflichtig')->default(true);
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leistungsarten');
    }
};
