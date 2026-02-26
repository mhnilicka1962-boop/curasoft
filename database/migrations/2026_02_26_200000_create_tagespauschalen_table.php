<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagespauschalen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->string('rechnungstyp', 20); // kvg, klient, gemeinde
            $table->date('datum_von');
            $table->date('datum_bis');
            $table->decimal('ansatz', 10, 4); // CHF/Tag
            $table->text('text')->nullable(); // erscheint auf der Rechnung
            $table->foreignId('erstellt_von')->nullable()->constrained('benutzer')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagespauschalen');
    }
};
