<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leistungsarten', function (Blueprint $table) {
            $table->string('tarmed_code', 20)->nullable()
                ->after('kvg_akut_default')
                ->comment('TARMED/Tarif 311 Code für XML 450.100 Abrechnung (z.B. 00.0010)');
        });
    }

    public function down(): void
    {
        Schema::table('leistungsarten', function (Blueprint $table) {
            $table->dropColumn('tarmed_code');
        });
    }
};
