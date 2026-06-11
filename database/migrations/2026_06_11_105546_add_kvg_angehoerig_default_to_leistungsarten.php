<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leistungsarten', function (Blueprint $table) {
            $table->decimal('kvg_angehoerig_default', 8, 2)->default(0)->after('kvg_akut_default');
        });
    }

    public function down(): void
    {
        Schema::table('leistungsarten', function (Blueprint $table) {
            $table->dropColumn('kvg_angehoerig_default');
        });
    }
};
