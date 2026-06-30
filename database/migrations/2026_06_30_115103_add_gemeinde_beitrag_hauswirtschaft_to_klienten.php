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
        Schema::table('klienten', function (Blueprint $table) {
            $table->decimal('gemeinde_beitrag_hauswirtschaft', 8, 2)->default(0)->after('gemeinde_email');
        });
    }

    public function down(): void
    {
        Schema::table('klienten', function (Blueprint $table) {
            $table->dropColumn('gemeinde_beitrag_hauswirtschaft');
        });
    }
};
