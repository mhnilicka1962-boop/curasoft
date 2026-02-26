<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rechnungs_positionen', function (Blueprint $table) {
            $table->text('beschreibung')->nullable()->after('einheit');
            // leistungstyp_id nullable machen — Tagespauschalen-Positionen haben keinen Leistungstyp
            $table->foreignId('leistungstyp_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rechnungs_positionen', function (Blueprint $table) {
            $table->dropColumn('beschreibung');
            $table->foreignId('leistungstyp_id')->nullable(false)->change();
        });
    }
};
