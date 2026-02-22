<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klient_pflegestufen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->foreignId('erfasst_von')->nullable()->constrained('benutzer')->nullOnDelete();
            $table->enum('instrument', ['besa', 'rai_hc', 'ibm', 'manuell'])->default('besa');
            $table->smallInteger('stufe')->nullable();
            $table->decimal('punkte', 6, 2)->nullable();
            $table->date('einstufung_datum');
            $table->date('naechste_pruefung')->nullable();
            $table->text('bemerkung')->nullable();
            $table->timestamps();
        });

        Schema::create('klient_diagnosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->foreignId('erfasst_von')->nullable()->constrained('benutzer')->nullOnDelete();
            $table->foreignId('arzt_id')->nullable()->constrained('aerzte')->nullOnDelete();
            $table->string('icd10_code', 10)->nullable();
            $table->string('icd10_bezeichnung', 255)->nullable();
            $table->enum('diagnose_typ', ['haupt', 'neben', 'einweisung'])->default('neben');
            $table->date('datum_gestellt')->nullable();
            $table->date('datum_bis')->nullable();
            $table->boolean('aktiv')->default(true);
            $table->text('bemerkung')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klient_diagnosen');
        Schema::dropIfExists('klient_pflegestufen');
    }
};
