<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->foreignId('helfer_id')
                ->nullable()
                ->after('benutzer_id')
                ->constrained('benutzer')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->dropForeign(['helfer_id']);
            $table->dropColumn('helfer_id');
        });
    }
};
