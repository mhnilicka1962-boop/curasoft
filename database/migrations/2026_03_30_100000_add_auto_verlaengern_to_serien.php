<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('serien', function (Blueprint $table) {
            $table->boolean('auto_verlaengern')->default(false)->after('gueltig_bis');
        });

        Schema::table('organisationen', function (Blueprint $table) {
            $table->unsignedSmallInteger('einsatz_vorlauf_tage')->default(10)->after('aktiv');
            $table->timestamp('letzter_generierungs_lauf')->nullable()->after('einsatz_vorlauf_tage');
        });
    }

    public function down(): void
    {
        Schema::table('serien', function (Blueprint $table) {
            $table->dropColumn('auto_verlaengern');
        });

        Schema::table('organisationen', function (Blueprint $table) {
            $table->dropColumn(['einsatz_vorlauf_tage', 'letzter_generierungs_lauf']);
        });
    }
};
