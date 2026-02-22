<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisations_regionen', function (Blueprint $table) {
            // ZSR-Nr. pro Kanton (NULL = Haupt-ZSR der Firma)
            $table->string('zsr_nr', 20)->nullable()->after('aktiv');
        });
    }

    public function down(): void
    {
        Schema::table('organisations_regionen', function (Blueprint $table) {
            $table->dropColumn('zsr_nr');
        });
    }
};
