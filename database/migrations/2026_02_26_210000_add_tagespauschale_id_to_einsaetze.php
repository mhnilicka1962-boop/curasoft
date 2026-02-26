<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->foreignId('tagespauschale_id')
                ->nullable()
                ->after('datum_bis')
                ->constrained('tagespauschalen')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->dropForeign(['tagespauschale_id']);
            $table->dropColumn('tagespauschale_id');
        });
    }
};
