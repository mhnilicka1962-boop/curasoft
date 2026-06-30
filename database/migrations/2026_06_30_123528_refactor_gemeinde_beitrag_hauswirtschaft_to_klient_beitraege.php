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
        Schema::table('klient_beitraege', function (Blueprint $table) {
            $table->string('typ', 20)->default('kvg')->after('klient_id');
            $table->decimal('gemeinde_chf_h', 8, 2)->nullable()->after('kanton_abrechnung');
        });

        Schema::table('klienten', function (Blueprint $table) {
            $table->dropColumn('gemeinde_beitrag_hauswirtschaft');
        });
    }

    public function down(): void
    {
        Schema::table('klient_beitraege', function (Blueprint $table) {
            $table->dropColumn(['typ', 'gemeinde_chf_h']);
        });

        Schema::table('klienten', function (Blueprint $table) {
            $table->decimal('gemeinde_beitrag_hauswirtschaft', 8, 2)->default(0);
        });
    }
};
