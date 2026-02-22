<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aerzte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->string('anrede', 20)->nullable();
            $table->string('vorname', 100)->nullable();
            $table->string('nachname', 100);
            $table->string('zsr_nr', 20)->nullable();
            $table->string('gln_nr', 20)->nullable();
            $table->string('fachrichtung', 100)->nullable();
            $table->string('praxis_name', 200)->nullable();
            $table->string('adresse', 255)->nullable();
            $table->string('plz', 10)->nullable();
            $table->string('ort', 100)->nullable();
            $table->foreignId('region_id')->nullable()->constrained('regionen')->nullOnDelete();
            $table->string('telefon', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->boolean('aktiv')->default(true);
            $table->timestamps();
        });

        Schema::create('klient_aerzte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->foreignId('arzt_id')->constrained('aerzte')->cascadeOnDelete();
            $table->enum('rolle', ['behandelnder', 'einweisender', 'konsultierender'])->default('behandelnder');
            $table->boolean('hauptarzt')->default(false);
            $table->date('gueltig_ab')->nullable();
            $table->date('gueltig_bis')->nullable();
            $table->text('bemerkung')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klient_aerzte');
        Schema::dropIfExists('aerzte');
    }
};
