<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->string('email_fehler', 500)->nullable()->after('email_versand_an');
        });
    }

    public function down(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->dropColumn('email_fehler');
        });
    }
};
