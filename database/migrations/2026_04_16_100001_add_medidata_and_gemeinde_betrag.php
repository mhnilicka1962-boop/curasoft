<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MediData-Zugangsdaten auf Organisationen
        Schema::table('organisationen', function (Blueprint $table) {
            $table->string('medidata_url')->nullable()->after('bexio_mandant_id');
            $table->string('medidata_username')->nullable()->after('medidata_url');
            $table->text('medidata_passwort')->nullable()->after('medidata_username');
        });

        // Gemeinde-Betrag + Versandstatus auf Rechnungen
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->decimal('betrag_gemeinde', 10, 2)->default(0)->after('betrag_kk');
            $table->string('gemeinde_versand_datum')->nullable()->after('betrag_gemeinde');
            $table->string('gemeinde_versand_an')->nullable()->after('gemeinde_versand_datum');
            $table->string('gemeinde_fehler')->nullable()->after('gemeinde_versand_an');
            $table->string('medidata_versand_datum')->nullable()->after('gemeinde_fehler');
            $table->string('medidata_fehler')->nullable()->after('medidata_versand_datum');
        });
    }

    public function down(): void
    {
        Schema::table('organisationen', function (Blueprint $table) {
            $table->dropColumn(['medidata_url', 'medidata_username', 'medidata_passwort']);
        });
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->dropColumn(['betrag_gemeinde', 'gemeinde_versand_datum', 'gemeinde_versand_an',
                'gemeinde_fehler', 'medidata_versand_datum', 'medidata_fehler']);
        });
    }
};
