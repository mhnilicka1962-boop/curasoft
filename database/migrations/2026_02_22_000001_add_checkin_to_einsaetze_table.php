<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            // Check-in
            $table->timestamp('checkin_zeit')->nullable()->after('verrechnet');
            $table->decimal('checkin_lat', 10, 7)->nullable()->after('checkin_zeit');
            $table->decimal('checkin_lng', 10, 7)->nullable()->after('checkin_lat');
            $table->string('checkin_methode', 10)->nullable()->after('checkin_lng'); // qr, gps, manuell
            $table->integer('checkin_distanz_meter')->nullable()->after('checkin_methode');

            // Check-out
            $table->timestamp('checkout_zeit')->nullable()->after('checkin_distanz_meter');
            $table->decimal('checkout_lat', 10, 7)->nullable()->after('checkout_zeit');
            $table->decimal('checkout_lng', 10, 7)->nullable()->after('checkout_lat');
            $table->string('checkout_methode', 10)->nullable()->after('checkout_lng');
            $table->integer('checkout_distanz_meter')->nullable()->after('checkout_methode');
        });
    }

    public function down(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->dropColumn([
                'checkin_zeit', 'checkin_lat', 'checkin_lng', 'checkin_methode', 'checkin_distanz_meter',
                'checkout_zeit', 'checkout_lat', 'checkout_lng', 'checkout_methode', 'checkout_distanz_meter',
            ]);
        });
    }
};
