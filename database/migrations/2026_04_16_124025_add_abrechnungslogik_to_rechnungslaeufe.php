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
        Schema::table('rechnungslaeufe', function (Blueprint $table) {
            $table->string('abrechnungslogik')->default('tiers_garant')->after('organisation_id');
        });
    }

    public function down(): void
    {
        Schema::table('rechnungslaeufe', function (Blueprint $table) {
            $table->dropColumn('abrechnungslogik');
        });
    }
};
