<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('touren', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->foreignId('benutzer_id')->nullable()->constrained('benutzer')->nullOnDelete();
            $table->date('datum');
            $table->string('bezeichnung', 100)->nullable();
            $table->enum('status', ['geplant', 'gestartet', 'abgeschlossen'])->default('geplant');
            $table->time('start_zeit')->nullable();
            $table->time('end_zeit')->nullable();
            $table->text('bemerkung')->nullable();
            $table->timestamps();
        });

        Schema::table('einsaetze', function (Blueprint $table) {
            $table->foreignId('tour_id')->nullable()->after('status')->constrained('touren')->nullOnDelete();
            $table->smallInteger('tour_reihenfolge')->nullable()->after('tour_id');
        });
    }

    public function down(): void
    {
        Schema::table('einsaetze', function (Blueprint $table) {
            $table->dropForeign(['tour_id']);
            $table->dropColumn(['tour_id', 'tour_reihenfolge']);
        });
        Schema::dropIfExists('touren');
    }
};
