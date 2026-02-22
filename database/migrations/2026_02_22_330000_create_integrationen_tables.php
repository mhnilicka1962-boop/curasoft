<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bexio Synchronisierungslog
        Schema::create('bexio_sync', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->string('entity_typ', 50);  // rechnung, kontakt, buchung
            $table->unsignedBigInteger('entity_id');
            $table->unsignedInteger('bexio_id')->nullable();
            $table->timestamp('letzter_sync')->nullable();
            $table->enum('sync_status', ['pending', 'synced', 'fehler'])->default('pending');
            $table->text('fehler_meldung')->nullable();
            $table->timestamps();
            $table->index(['entity_typ', 'entity_id']);
        });

        // Bexio API-Zugangsdaten auf Organisation
        Schema::table('organisationen', function (Blueprint $table) {
            $table->string('bexio_api_key', 255)->nullable()->after('logo_ausrichtung');
            $table->unsignedInteger('bexio_mandant_id')->nullable()->after('bexio_api_key');
        });

        // Bexio-IDs auf bestehenden Tabellen
        Schema::table('klienten', function (Blueprint $table) {
            $table->unsignedInteger('bexio_kontakt_id')->nullable()->after('qr_token');
        });

        Schema::table('rechnungen', function (Blueprint $table) {
            $table->unsignedInteger('bexio_rechnung_id')->nullable();
            $table->timestamp('xml_export_datum')->nullable();
            $table->string('xml_export_pfad', 500)->nullable();
            $table->string('tarmed_fall_nr', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->dropColumn(['bexio_rechnung_id', 'xml_export_datum', 'xml_export_pfad', 'tarmed_fall_nr']);
        });
        Schema::table('klienten', function (Blueprint $table) {
            $table->dropColumn('bexio_kontakt_id');
        });
        Schema::table('organisationen', function (Blueprint $table) {
            $table->dropColumn(['bexio_api_key', 'bexio_mandant_id']);
        });
        Schema::dropIfExists('bexio_sync');
    }
};
