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
        Schema::table('leistungsregionen', function (Blueprint $table) {
            $table->decimal('kkasse_angehoerig', 8, 2)->default(0)->after('kkasse_akut');
        });
    }

    public function down(): void
    {
        Schema::table('leistungsregionen', function (Blueprint $table) {
            $table->dropColumn('kkasse_angehoerig');
        });
    }
};
