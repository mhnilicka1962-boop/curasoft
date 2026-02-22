<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->foreignId('klient_id')->nullable()->constrained('klienten')->nullOnDelete();
            $table->foreignId('hochgeladen_von')->nullable()->constrained('benutzer')->nullOnDelete();
            $table->enum('dokument_typ', ['pflegeplanung', 'vertrag', 'vollmacht', 'arztzeugnis', 'bericht', 'rechnung_kopie', 'sonstiges'])->default('sonstiges');
            $table->string('bezeichnung', 255);
            $table->string('dateiname', 255);
            $table->string('dateipfad', 500);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedInteger('groesse_bytes')->nullable();
            $table->boolean('vertraulich')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumente');
    }
};
