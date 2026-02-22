<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klienten', function (Blueprint $table) {
            $table->string('qr_token', 32)->unique()->nullable()->after('aktiv');
            $table->decimal('klient_lat', 10, 7)->nullable()->after('qr_token');
            $table->decimal('klient_lng', 10, 7)->nullable()->after('klient_lat');
        });
    }

    public function down(): void
    {
        Schema::table('klienten', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'klient_lat', 'klient_lng']);
        });
    }
};
