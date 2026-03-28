<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rechnungs_positionen', function (Blueprint $table) {
            $table->foreignId('einsatz_leistungsart_id')
                ->nullable()
                ->after('einsatz_id')
                ->constrained('einsatz_leistungsarten')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rechnungs_positionen', function (Blueprint $table) {
            $table->dropForeign(['einsatz_leistungsart_id']);
            $table->dropColumn('einsatz_leistungsart_id');
        });
    }
};
