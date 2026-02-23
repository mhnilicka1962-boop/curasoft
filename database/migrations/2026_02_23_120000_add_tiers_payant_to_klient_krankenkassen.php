<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klient_krankenkassen', function (Blueprint $table) {
            // Tiers payant = KK zahlt direkt an Spitex (Standard in CH)
            // Tiers garant = Klient zahlt, holt sich Geld von KK zurück
            $table->boolean('tiers_payant')->default(true)->after('versicherungs_typ')
                ->comment('true = Tiers payant (KK zahlt direkt), false = Tiers garant (Klient zahlt)');
        });
    }

    public function down(): void
    {
        Schema::table('klient_krankenkassen', function (Blueprint $table) {
            $table->dropColumn('tiers_payant');
        });
    }
};
