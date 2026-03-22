<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->text('admin_kommentar')->nullable()->after('bemerkung');
        });
    }

    public function down(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->dropColumn('admin_kommentar');
        });
    }
};
