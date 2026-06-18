<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bedarfsanalysen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->foreignId('klient_id')->nullable()->constrained('klienten')->nullOnDelete();
            $table->foreignId('erstellt_von')->nullable()->constrained('benutzer')->nullOnDelete();
            $table->enum('status', ['entwurf', 'abgeschlossen'])->default('entwurf');
            $table->unsignedTinyInteger('aktueller_schritt')->default(1);
            $table->timestamp('abgeschlossen_am')->nullable();

            // SEITE 1 — Personalien
            $table->date('datum_analyse')->nullable();
            $table->string('ort_analyse')->nullable();
            $table->string('anrede', 20)->nullable();
            $table->string('vorname')->nullable();
            $table->string('nachname')->nullable();
            $table->string('strasse')->nullable();
            $table->string('plz', 10)->nullable();
            $table->string('ort')->nullable();
            $table->string('telefon', 50)->nullable();
            $table->string('mobile', 50)->nullable();
            $table->date('geburtsdatum')->nullable();
            $table->string('heimatort')->nullable();
            $table->string('konfession')->nullable();
            $table->string('zivilstand', 50)->nullable();
            $table->string('nationalitaet', 100)->nullable();
            $table->string('ahv_nr', 20)->nullable();
            // Ansprechperson 1
            $table->string('ap1_name')->nullable();
            $table->string('ap1_vorname')->nullable();
            $table->string('ap1_strasse')->nullable();
            $table->string('ap1_plz', 10)->nullable();
            $table->string('ap1_ort')->nullable();
            $table->string('ap1_beziehung', 50)->nullable();
            $table->text('ap1_bemerkung')->nullable();
            $table->string('ap1_telefon', 50)->nullable();
            $table->string('ap1_mobile', 50)->nullable();
            $table->boolean('ap1_vormund')->default(false);
            $table->string('ap1_erreichbarkeit', 20)->nullable();
            $table->time('ap1_erreichbarkeit_von')->nullable();
            $table->time('ap1_erreichbarkeit_bis')->nullable();
            // Ansprechperson 2
            $table->string('ap2_name')->nullable();
            $table->string('ap2_vorname')->nullable();
            $table->string('ap2_strasse')->nullable();
            $table->string('ap2_plz', 10)->nullable();
            $table->string('ap2_ort')->nullable();
            $table->string('ap2_beziehung', 50)->nullable();
            $table->text('ap2_bemerkung')->nullable();
            $table->string('ap2_telefon', 50)->nullable();
            $table->string('ap2_mobile', 50)->nullable();
            $table->boolean('ap2_vormund')->default(false);
            $table->string('ap2_erreichbarkeit', 20)->nullable();
            $table->time('ap2_erreichbarkeit_von')->nullable();
            $table->time('ap2_erreichbarkeit_bis')->nullable();

            // SEITE 2 — Versicherung & Details
            $table->string('kvg_krankenkasse')->nullable();
            $table->string('kvg_anschrift')->nullable();
            $table->boolean('vvg_vorhanden')->default(false);
            $table->string('vvg_deckungstyp', 20)->nullable();
            $table->boolean('pflegeversicherung')->default(false);
            $table->string('pflegeversicherung_name')->nullable();
            $table->string('zweite_krankenkasse')->nullable();
            $table->string('zweite_krankenkasse_anschrift')->nullable();
            $table->text('haushaltshilfe')->nullable();
            $table->text('versicherung_bemerkungen')->nullable();
            $table->string('aufnahmegrund', 20)->nullable();
            $table->string('hilflosenentschaedigung', 20)->nullable();
            $table->text('rechnungsadresse')->nullable();
            $table->boolean('vorauszahlung')->default(false);
            $table->string('zustaendiger_arzt')->nullable();
            $table->unsignedSmallInteger('personen_haushalt')->nullable();
            $table->unsignedSmallInteger('personen_betreuungsbed')->nullable();
            $table->decimal('gewicht_kg', 5, 1)->nullable();

            // SEITE 3 — Medizin & Pflegestufe
            $table->text('diagnosen_text')->nullable();
            $table->boolean('medikamente_liste')->default(false);
            $table->text('mobilitaet')->nullable();
            $table->text('hilfsmittel')->nullable();
            $table->text('hobbies')->nullable();
            $table->string('pflegestufe', 30)->nullable();

            // SEITE 4 — Verpflegung & Pflegedienst
            $table->boolean('wunschkost')->default(false);
            $table->string('wunschkost_details')->nullable();
            $table->boolean('pflegedienst_aktuell')->default(false);
            $table->string('pflegedienst_name')->nullable();
            $table->text('pflegedienst_aufgaben')->nullable();
            $table->string('pflegedienst_frequenz')->nullable();
            $table->boolean('pflegedienst_abbestellen')->default(false);
            $table->boolean('raucher')->nullable();

            // SEITE 5 — Wohnverhältnisse & Admin
            $table->string('wohntyp', 20)->nullable();
            $table->unsignedSmallInteger('anzahl_zimmer')->nullable();
            $table->boolean('lift')->default(false);
            $table->boolean('treppe')->default(false);
            $table->unsignedSmallInteger('treppe_stufen')->nullable();
            $table->text('klinik')->nullable();
            $table->boolean('patientenverfuegung')->default(false);
            $table->boolean('haustiere')->default(false);
            $table->string('haustiere_details')->nullable();
            $table->date('eintrittstermin')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bedarfsanalysen');
    }
};
