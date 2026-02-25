<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * einsatz_id nullable machen — erlaubt manuell erfasste oder importierte Positionen
 * ohne direkte Einsatz-Verknüpfung (z.B. Pauschalrechnungen, Altdaten-Import).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rechnungs_positionen', function (Blueprint $table) {
            $table->foreignId('einsatz_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rechnungs_positionen', function (Blueprint $table) {
            $table->foreignId('einsatz_id')->nullable(false)->change();
        });
    }
};
