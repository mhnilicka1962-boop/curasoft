<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisationen', function (Blueprint $table) {
            $table->string('abrechnungslogik', 20)->default('tiers_garant')->after('abrechnungsnummer');
        });
    }

    public function down(): void
    {
        Schema::table('organisationen', function (Blueprint $table) {
            $table->dropColumn('abrechnungslogik');
        });
    }
};
