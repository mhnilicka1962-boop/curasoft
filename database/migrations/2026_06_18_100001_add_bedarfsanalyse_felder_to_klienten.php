<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klienten', function (Blueprint $table) {
            $table->string('mobile', 50)->nullable()->after('telefon');
            $table->string('heimatort')->nullable()->after('ahv_nr');
            $table->string('konfession')->nullable()->after('heimatort');
            $table->string('nationalitaet', 100)->nullable()->after('konfession');
            $table->decimal('gewicht_kg', 5, 1)->nullable()->after('nationalitaet');
            $table->text('mobilitaet')->nullable()->after('gewicht_kg');
            $table->text('hilfsmittel')->nullable()->after('mobilitaet');
            $table->text('hobbies')->nullable()->after('hilfsmittel');
            $table->string('aufnahmegrund', 20)->nullable()->after('hobbies');
            $table->string('hilflosenentschaedigung', 20)->nullable()->after('aufnahmegrund');
            $table->boolean('pflegeversicherung')->default(false)->after('hilflosenentschaedigung');
            $table->string('pflegeversicherung_name')->nullable()->after('pflegeversicherung');
            $table->boolean('vorauszahlung')->default(false)->after('pflegeversicherung_name');
            $table->unsignedSmallInteger('personen_haushalt')->nullable()->after('vorauszahlung');
            $table->unsignedSmallInteger('personen_betreuungsbed')->nullable()->after('personen_haushalt');
            $table->boolean('wunschkost')->default(false)->after('personen_betreuungsbed');
            $table->string('wunschkost_details')->nullable()->after('wunschkost');
            $table->boolean('pflegedienst_aktuell')->default(false)->after('wunschkost_details');
            $table->string('pflegedienst_name')->nullable()->after('pflegedienst_aktuell');
            $table->text('pflegedienst_aufgaben')->nullable()->after('pflegedienst_name');
            $table->string('pflegedienst_frequenz')->nullable()->after('pflegedienst_aufgaben');
            $table->boolean('pflegedienst_abbestellen')->default(false)->after('pflegedienst_frequenz');
            $table->boolean('raucher')->nullable()->after('pflegedienst_abbestellen');
            $table->string('wohntyp', 20)->nullable()->after('raucher');
            $table->unsignedSmallInteger('anzahl_zimmer')->nullable()->after('wohntyp');
            $table->boolean('lift')->default(false)->after('anzahl_zimmer');
            $table->boolean('treppe')->default(false)->after('lift');
            $table->unsignedSmallInteger('treppe_stufen')->nullable()->after('treppe');
            $table->text('klinik')->nullable()->after('treppe_stufen');
            $table->boolean('patientenverfuegung')->default(false)->after('klinik');
            $table->boolean('haustiere')->default(false)->after('patientenverfuegung');
            $table->string('haustiere_details')->nullable()->after('haustiere');
            $table->string('pflegestufe_curapflege', 30)->nullable()->after('haustiere_details');
        });
    }

    public function down(): void
    {
        Schema::table('klienten', function (Blueprint $table) {
            $table->dropColumn([
                'mobile','heimatort','konfession','nationalitaet','gewicht_kg',
                'mobilitaet','hilfsmittel','hobbies','aufnahmegrund','hilflosenentschaedigung',
                'pflegeversicherung','pflegeversicherung_name','vorauszahlung',
                'personen_haushalt','personen_betreuungsbed','wunschkost','wunschkost_details',
                'pflegedienst_aktuell','pflegedienst_name','pflegedienst_aufgaben',
                'pflegedienst_frequenz','pflegedienst_abbestellen','raucher',
                'wohntyp','anzahl_zimmer','lift','treppe','treppe_stufen',
                'klinik','patientenverfuegung','haustiere','haustiere_details','pflegestufe_curapflege',
            ]);
        });
    }
};
