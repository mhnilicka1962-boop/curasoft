<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('benutzer', function (Blueprint $table) {
            $table->string('einladungs_token', 64)->nullable()->unique()->after('password');
            $table->timestamp('einladungs_token_ablauf')->nullable()->after('einladungs_token');
        });
    }

    public function down(): void
    {
        Schema::table('benutzer', function (Blueprint $table) {
            $table->dropColumn(['einladungs_token', 'einladungs_token_ablauf']);
        });
    }
};
