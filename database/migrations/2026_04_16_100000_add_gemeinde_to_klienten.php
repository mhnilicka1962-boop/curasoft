<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klienten', function (Blueprint $table) {
            $table->string('gemeinde_name')->nullable()->after('zahlbar_tage');
            $table->string('gemeinde_adresse')->nullable()->after('gemeinde_name');
            $table->string('gemeinde_plz', 10)->nullable()->after('gemeinde_adresse');
            $table->string('gemeinde_ort')->nullable()->after('gemeinde_plz');
            $table->string('gemeinde_email')->nullable()->after('gemeinde_ort');
        });
    }

    public function down(): void
    {
        Schema::table('klienten', function (Blueprint $table) {
            $table->dropColumn(['gemeinde_name', 'gemeinde_adresse', 'gemeinde_plz', 'gemeinde_ort', 'gemeinde_email']);
        });
    }
};
