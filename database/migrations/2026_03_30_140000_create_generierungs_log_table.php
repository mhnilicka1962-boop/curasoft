<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generierungs_log', function (Blueprint $table) {
            $table->id();
            $table->timestamp('ausgefuehrt_at');
            $table->unsignedSmallInteger('einsaetze_generiert')->default(0);
            $table->unsignedSmallInteger('fehler')->default(0);
            $table->string('via', 10)->default('auto'); // auto | manuell
            $table->text('meldung')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generierungs_log');
    }
};
