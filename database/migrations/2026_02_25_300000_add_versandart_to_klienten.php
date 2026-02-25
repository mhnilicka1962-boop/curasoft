<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klienten', function (Blueprint $table) {
            $table->string('versandart_patient', 20)->default('post')->after('zahlbar_tage');
            $table->string('versandart_kvg', 20)->default('manuell')->after('versandart_patient');
        });
    }

    public function down(): void
    {
        Schema::table('klienten', function (Blueprint $table) {
            $table->dropColumn(['versandart_patient', 'versandart_kvg']);
        });
    }
};
