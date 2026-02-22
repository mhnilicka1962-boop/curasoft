<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = [
            // Klienten
            ['klienten',           'organisation_id'],
            ['klienten',           'nachname'],
            ['klienten',           'aktiv'],

            // Benutzer
            ['benutzer',           'organisation_id'],

            // Einsätze
            ['einsaetze',          'organisation_id'],
            ['einsaetze',          'klient_id'],
            ['einsaetze',          'datum'],

            // Rapporte
            ['rapporte',           'organisation_id'],
            ['rapporte',           'klient_id'],
            ['rapporte',           'datum'],

            // Touren
            ['touren',             'organisation_id'],
            ['touren',             'datum'],

            // Rechnungen
            ['rechnungen',         'organisation_id'],
            ['rechnungen',         'klient_id'],

            // Dokumente
            ['dokumente',          'organisation_id'],
            ['dokumente',          'klient_id'],

            // Klient-Unterbeziehungen
            ['klient_adressen',    'klient_id'],
            ['klient_aerzte',      'klient_id'],
            ['klient_krankenkassen','klient_id'],
            ['klient_kontakte',    'klient_id'],
            ['klient_diagnosen',   'klient_id'],
            ['klient_pflegestufen','klient_id'],
            ['klient_beitraege',   'klient_id'],
        ];

        foreach ($indexes as [$table, $column]) {
            $name = "{$table}_{$column}_idx";
            DB::statement("CREATE INDEX IF NOT EXISTS {$name} ON {$table} ({$column})");
        }
    }

    public function down(): void
    {
        $indexes = [
            ['klienten',            'organisation_id'],
            ['klienten',            'nachname'],
            ['klienten',            'aktiv'],
            ['benutzer',            'organisation_id'],
            ['einsaetze',           'organisation_id'],
            ['einsaetze',           'klient_id'],
            ['einsaetze',           'datum'],
            ['rapporte',            'organisation_id'],
            ['rapporte',            'klient_id'],
            ['rapporte',            'datum'],
            ['touren',              'organisation_id'],
            ['touren',              'datum'],
            ['rechnungen',          'organisation_id'],
            ['rechnungen',          'klient_id'],
            ['dokumente',           'organisation_id'],
            ['dokumente',           'klient_id'],
            ['klient_adressen',     'klient_id'],
            ['klient_aerzte',       'klient_id'],
            ['klient_krankenkassen','klient_id'],
            ['klient_kontakte',     'klient_id'],
            ['klient_diagnosen',    'klient_id'],
            ['klient_pflegestufen', 'klient_id'],
            ['klient_beitraege',    'klient_id'],
        ];

        foreach ($indexes as [$table, $column]) {
            DB::statement("DROP INDEX IF EXISTS {$table}_{$column}_idx");
        }
    }
};
