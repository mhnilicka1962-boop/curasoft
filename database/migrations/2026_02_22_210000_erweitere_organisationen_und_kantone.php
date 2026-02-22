<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fehlende Felder in organisationen ergänzen
        Schema::table('organisationen', function (Blueprint $table) {
            // Abrechnungs-IDs (ZSR = Spitex-Zulassungsnummer, für KVG-Abrechnung)
            $table->string('zsr_nr', 20)->nullable()->after('abrechnungsnummer');
            $table->string('mwst_nr', 30)->nullable()->after('zsr_nr');

            // Adresse Ergänzungen
            $table->string('postfach', 50)->nullable()->after('adresse');
            $table->string('adresszusatz')->nullable()->after('postfach');

            // Kontakt
            $table->string('fax', 50)->nullable()->after('telefon');
            $table->string('website')->nullable()->after('email');

            // Bank (Standardwerte, können pro Kanton überschrieben werden)
            $table->string('bank')->nullable();
            $table->string('bankadresse')->nullable();
            $table->string('iban', 30)->nullable();
            $table->string('postcheckkonto', 30)->nullable();

            // Rechnungseinstellungen
            $table->boolean('druck_mit_firmendaten')->default(true);
            $table->enum('rechnungsadresse_position', ['links', 'rechts'])->default('links');
            $table->enum('logo_ausrichtung', [
                'links_anschrift_rechts',
                'rechts_anschrift_links',
                'mitte_anschrift_fusszeile',
            ])->default('links_anschrift_rechts');
        });

        // Pro-Kanton-Einstellungen (ESR, IBAN-Override etc.)
        Schema::create('organisations_regionen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('regionen')->cascadeOnDelete();
            $table->boolean('aktiv')->default(true);         // Org tätig in diesem Kanton

            // Bank-Override pro Kanton (NULL = Haupt-Bankdaten verwenden)
            $table->string('iban', 30)->nullable();
            $table->string('postcheckkonto', 30)->nullable();
            $table->string('bank')->nullable();
            $table->string('bankadresse')->nullable();

            // QR-Rechnung / ESR pro Kanton
            $table->string('esr_teilnehmernr', 20)->nullable();  // ESR-Teilnehmernummer
            $table->string('qr_iban', 30)->nullable();            // QR-IBAN (für QR-Rechnung)
            $table->text('bemerkung')->nullable();

            $table->unique(['organisation_id', 'region_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisations_regionen');

        Schema::table('organisationen', function (Blueprint $table) {
            $table->dropColumn([
                'zsr_nr', 'mwst_nr', 'postfach', 'adresszusatz',
                'fax', 'website', 'bank', 'bankadresse', 'iban', 'postcheckkonto',
                'druck_mit_firmendaten', 'rechnungsadresse_position', 'logo_ausrichtung',
            ]);
        });
    }
};
