<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klient_kontakte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->enum('rolle', ['angehoerig', 'gesetzlicher_vertreter', 'rechnungsempfaenger', 'notfallkontakt', 'sonstige'])->default('angehoerig');
            $table->string('anrede', 20)->nullable();
            $table->string('vorname', 100)->nullable();
            $table->string('nachname', 100)->nullable();
            $table->string('firma', 100)->nullable();
            $table->string('beziehung', 100)->nullable();
            $table->string('adresse', 255)->nullable();
            $table->string('plz', 10)->nullable();
            $table->string('ort', 100)->nullable();
            $table->foreignId('region_id')->nullable()->constrained('regionen')->nullOnDelete();
            $table->string('telefon', 50)->nullable();
            $table->string('telefon_mobil', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->boolean('bevollmaechtigt')->default(false);
            $table->boolean('rechnungen_erhalten')->default(false);
            $table->boolean('aktiv')->default(true);
            $table->text('bemerkung')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klient_kontakte');
    }
};
