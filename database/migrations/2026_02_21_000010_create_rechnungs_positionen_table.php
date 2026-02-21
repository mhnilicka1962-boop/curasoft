<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rechnungs_positionen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rechnung_id')->constrained('rechnungen')->cascadeOnDelete();
            $table->foreignId('einsatz_id')->constrained('einsaetze')->cascadeOnDelete();
            $table->foreignId('leistungstyp_id')->constrained('leistungstypen');
            $table->date('datum');
            $table->integer('menge');
            $table->string('einheit', 20);
            $table->decimal('tarif_patient', 10, 2)->default(0);
            $table->decimal('tarif_kk', 10, 2)->default(0);
            $table->decimal('betrag_patient', 10, 2)->default(0);
            $table->decimal('betrag_kk', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rechnungs_positionen');
    }
};
