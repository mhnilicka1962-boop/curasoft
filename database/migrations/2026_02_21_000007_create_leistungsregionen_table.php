<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leistungsregionen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leistungstyp_id')->constrained('leistungstypen')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('regionen')->cascadeOnDelete();
            $table->decimal('ansatz_patient', 10, 2)->default(0);
            $table->decimal('ansatz_kk', 10, 2)->default(0);
            $table->decimal('ansatz_akut_patient', 10, 2)->default(0);
            $table->decimal('ansatz_akut_kk', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leistungsregionen');
    }
};
