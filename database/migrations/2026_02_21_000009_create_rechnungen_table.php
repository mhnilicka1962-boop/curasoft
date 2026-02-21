<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rechnungen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->string('rechnungsnummer', 50)->unique();
            $table->date('periode_von');
            $table->date('periode_bis');
            $table->date('rechnungsdatum');
            $table->decimal('betrag_patient', 10, 2)->default(0);
            $table->decimal('betrag_kk', 10, 2)->default(0);
            $table->decimal('betrag_total', 10, 2)->default(0);
            $table->enum('status', ['entwurf', 'gesendet', 'bezahlt', 'storniert'])->default('entwurf');
            $table->string('pdf_pfad')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rechnungen');
    }
};
