<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Demo-Daten für Alltagshelden GmbH (Herr El Merghini)
 * 6 Klienten, 3 Pflegepersonen, Touren + Einsätze für nächste 3 Wochen (Mo–Fr)
 * Kann beliebig oft neu ausgeführt werden — löscht vorher alte Demo-Daten.
 */
class AlltagsheldenDemoSeeder extends Seeder
{
    private const YASMINE = 28;
    private const NINA    = 29;
    private const MARC    = 30;
    private const KARIM   = 27;

    public function run(): void
    {
        $orgId = 1;
        $now   = Carbon::now();

        // Passwörter setzen
        DB::table('benutzer')
            ->whereIn('id', [self::KARIM, self::YASMINE, self::NINA, self::MARC])
            ->update(['password' => Hash::make('Alltagshelden2026!')]);

        // Alte Demo-Daten löschen
        $alteKlientIds = DB::table('klienten')
            ->where('organisation_id', $orgId)
            ->whereIn('zustaendig_id', [self::YASMINE, self::NINA, self::MARC])
            ->pluck('id');

        if ($alteKlientIds->isNotEmpty()) {
            DB::table('einsaetze')->whereIn('klient_id', $alteKlientIds)->delete();
        }
        DB::table('einsaetze')
            ->whereIn('benutzer_id', [self::YASMINE, self::NINA, self::MARC])
            ->delete();
        DB::table('touren')
            ->where('organisation_id', $orgId)
            ->whereIn('benutzer_id', [self::YASMINE, self::NINA, self::MARC])
            ->delete();
        if ($alteKlientIds->isNotEmpty()) {
            DB::table('klienten')->whereIn('id', $alteKlientIds)->delete();
        }

        // 6 Klienten anlegen (2 pro Mitarbeiter)
        $klientDaten = [
            self::YASMINE => [
                ['anrede' => 'Frau', 'vorname' => 'Margrit',   'nachname' => 'Schneider',  'geburtsdatum' => '1942-03-15', 'geschlecht' => 'w', 'adresse' => 'Rebbergstrasse 12', 'plz' => '5600', 'ort' => 'Lenzburg',   'telefon' => '062 891 45 67', 'notfallnummer' => '079 456 78 90'],
                ['anrede' => 'Herr', 'vorname' => 'Hans',      'nachname' => 'Müller',     'geburtsdatum' => '1938-07-22', 'geschlecht' => 'm', 'adresse' => 'Bahnhofstrasse 8',  'plz' => '5600', 'ort' => 'Lenzburg',   'telefon' => '062 891 23 45', 'notfallnummer' => '078 123 45 67'],
            ],
            self::NINA => [
                ['anrede' => 'Frau', 'vorname' => 'Elisabeth', 'nachname' => 'Brunner',    'geburtsdatum' => '1945-11-08', 'geschlecht' => 'w', 'adresse' => 'Kirchgasse 3',       'plz' => '5600', 'ort' => 'Lenzburg',   'telefon' => '062 891 78 90', 'notfallnummer' => '076 789 01 23'],
                ['anrede' => 'Herr', 'vorname' => 'Werner',    'nachname' => 'Keller',     'geburtsdatum' => '1940-05-14', 'geschlecht' => 'm', 'adresse' => 'Hauptstrasse 45',    'plz' => '5620', 'ort' => 'Bremgarten', 'telefon' => '056 633 12 34', 'notfallnummer' => '079 234 56 78'],
            ],
            self::MARC => [
                ['anrede' => 'Frau', 'vorname' => 'Rosa',      'nachname' => 'Zimmermann', 'geburtsdatum' => '1948-09-30', 'geschlecht' => 'w', 'adresse' => 'Gartenweg 7',        'plz' => '5620', 'ort' => 'Bremgarten', 'telefon' => '056 633 56 78', 'notfallnummer' => '077 345 67 89'],
                ['anrede' => 'Herr', 'vorname' => 'Fritz',     'nachname' => 'Weber',      'geburtsdatum' => '1936-12-03', 'geschlecht' => 'm', 'adresse' => 'Lindenstrasse 19',   'plz' => '5620', 'ort' => 'Bremgarten', 'telefon' => '056 633 90 12', 'notfallnummer' => '078 456 78 90'],
            ],
        ];

        $mitarbeiterKlienten = [];
        foreach ($klientDaten as $benutzerId => $paare) {
            $ids = [];
            foreach ($paare as $k) {
                $ids[] = DB::table('klienten')->insertGetId(array_merge($k, [
                    'organisation_id'     => $orgId,
                    'zustaendig_id'       => $benutzerId,
                    'region_id'           => 1,
                    'aktiv'               => true,
                    'einsatz_geplant_von' => Carbon::today()->format('Y-m-d'),
                    'einsatz_geplant_bis' => Carbon::today()->addWeeks(3)->format('Y-m-d'),
                    'qr_token'            => Str::random(32),
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]));
            }
            $mitarbeiterKlienten[$benutzerId] = $ids;
        }

        // Touren + Einsätze für nächste 3 Wochen Mo–Fr
        $zeiten = [
            ['von' => '08:00:00', 'bis' => '09:00:00'],
            ['von' => '10:30:00', 'bis' => '11:30:00'],
        ];

        $datum    = Carbon::today();
        $endDatum = Carbon::today()->addWeeks(3);

        while ($datum->lte($endDatum)) {
            if ($datum->isWeekday()) {
                foreach ($mitarbeiterKlienten as $benutzerId => $klientIds) {
                    $istVergangen = $datum->isPast() && !$datum->isToday();

                    $tourId = DB::table('touren')->insertGetId([
                        'organisation_id' => $orgId,
                        'benutzer_id'     => $benutzerId,
                        'datum'           => $datum->format('Y-m-d'),
                        'bezeichnung'     => 'Tour ' . $datum->format('d.m.Y'),
                        'status'          => $istVergangen ? 'abgeschlossen' : 'geplant',
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ]);

                    foreach ($klientIds as $idx => $klientId) {
                        DB::table('einsaetze')->insert([
                            'organisation_id'  => $orgId,
                            'klient_id'        => $klientId,
                            'benutzer_id'      => $benutzerId,
                            'region_id'        => 1,
                            'datum'            => $datum->format('Y-m-d'),
                            'zeit_von'         => $zeiten[$idx]['von'],
                            'zeit_bis'         => $zeiten[$idx]['bis'],
                            'minuten'          => 60,
                            'leistungsart_id'  => 4, // Grundpflege
                            'status'           => $istVergangen ? 'abgeschlossen' : 'geplant',
                            'tour_id'          => $tourId,
                            'tour_reihenfolge' => $idx + 1,
                            'verrechnet'       => false,
                            'created_at'       => $now,
                            'updated_at'       => $now,
                        ]);
                    }
                }
            }
            $datum->addDay();
        }

        $this->command->info('✅ Alltagshelden Demo: 6 Klienten, Touren + Einsätze bis ' . $endDatum->format('d.m.Y'));
    }
}
