<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisationen', function (Blueprint $table) {
            $table->string('logo_pfad')->nullable()->after('abrechnungsnummer');
            $table->string('theme_layout')->default('sidebar')->after('logo_pfad');
            $table->string('theme_farbe_primaer', 20)->nullable()->after('theme_layout');
        });
    }

    public function down(): void
    {
        Schema::table('organisationen', function (Blueprint $table) {
            $table->dropColumn(['logo_pfad', 'theme_layout', 'theme_farbe_primaer']);
        });
    }
};
