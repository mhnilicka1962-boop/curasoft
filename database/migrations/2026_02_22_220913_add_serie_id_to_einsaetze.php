<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->uuid('serie_id')->nullable()->after('tour_reihenfolge')->index();
        });
    }

    public function down(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->dropColumn('serie_id');
        });
    }
};
