<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('krankenkassen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('kuerzel', 30)->nullable();
            $table->string('ean_nr', 20)->nullable();
            $table->string('bag_nr', 20)->nullable();
            $table->string('adresse', 255)->nullable();
            $table->string('plz', 10)->nullable();
            $table->string('ort', 100)->nullable();
            $table->string('telefon', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });

        Schema::create('klient_krankenkassen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->foreignId('krankenkasse_id')->constrained('krankenkassen')->cascadeOnDelete();
            $table->enum('versicherungs_typ', ['kvg', 'vvg'])->default('kvg');
            $table->enum('deckungstyp', ['allgemein', 'halbprivat', 'privat'])->default('allgemein');
            $table->string('versichertennummer', 50)->nullable();
            $table->string('kartennummer', 50)->nullable();
            $table->date('gueltig_ab')->nullable();
            $table->date('gueltig_bis')->nullable();
            $table->boolean('aktiv')->default(true);
            $table->text('bemerkung')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klient_krankenkassen');
        Schema::dropIfExists('krankenkassen');
    }
};
