<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klient_beitraege', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->date('gueltig_ab');
            $table->decimal('ansatz_kunde', 8, 2)->default(0);
            $table->decimal('limit_restbetrag_prozent', 5, 2)->default(0);
            $table->decimal('ansatz_spitex', 8, 2)->default(0);
            $table->decimal('kanton_abrechnung', 8, 2)->default(0);
            $table->foreignId('erfasst_von')->nullable()->constrained('benutzer')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klient_beitraege');
    }
};
