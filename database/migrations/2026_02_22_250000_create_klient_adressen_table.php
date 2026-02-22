<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klient_adressen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();

            // Typ
            $table->enum('adressart', ['einsatzort', 'rechnung', 'notfall', 'korrespondenz']);
            $table->date('gueltig_ab')->nullable();
            $table->date('gueltig_bis')->nullable();

            // Person (kann abweichen, z.B. Rechnungsempfänger = Sohn)
            $table->string('firma', 100)->nullable();
            $table->string('anrede', 20)->nullable();
            $table->string('vorname', 100)->nullable();
            $table->string('nachname', 100)->nullable();

            // Adresse
            $table->string('strasse', 255)->nullable();
            $table->string('postfach', 50)->nullable();
            $table->string('plz', 10)->nullable();
            $table->string('ort', 100)->nullable();
            $table->foreignId('region_id')->nullable()->constrained('regionen')->nullOnDelete();

            // Kontakt
            $table->string('telefon', 50)->nullable();
            $table->string('telefax', 50)->nullable();
            $table->string('email', 255)->nullable();

            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klient_adressen');
    }
};
