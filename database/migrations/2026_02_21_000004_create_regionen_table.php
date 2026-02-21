<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regionen', function (Blueprint $table) {
            $table->id();
            $table->string('kuerzel', 4)->unique();
            $table->string('bezeichnung');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regionen');
    }
};
