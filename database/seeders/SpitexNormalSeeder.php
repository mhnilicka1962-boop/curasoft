<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Testdaten: kleines Normal-Spitex ohne Tagespauschale.
 * 5 Klienten (2 AG, 3 BE), 5 Pflegende + 1 Buchhaltung.
 * Einsätze Dez 2025 – Feb 2026 abgerechnet, März 2026 laufend.
 * Idempotent — kann beliebig oft neu ausgeführt werden.
 */
class SpitexNormalSeeder extends Seeder
{
    private const KLIENT_IDS  = [315, 316, 317, 318, 319];
    private const ORG_ID      = 1;
    private const ADMIN_ID    = 1;

    // region_id pro Klient
    private const KLIENT_REGION = [315 => 2, 316 => 2, 317 => 1, 318 => 1, 319 => 1];

    // Tarife eingefroren (aus leistungsregionen)
    private const TARIFE = [
        1 => [ // BE
            2 => ['ansatz' => 79.80, 'kkasse' => 71.80, 'bez' => 'Untersuchung/Behandlung'],
            3 => ['ansatz' => 57.00, 'kkasse' =>  0.00, 'bez' => 'Hauswirtschaft'],
            4 => ['ansatz' => 68.00, 'kkasse' => 60.00, 'bez' => 'Grundpflege'],
            5 => ['ansatz' => 68.00, 'kkasse' => 60.00, 'bez' => 'Abklärung/Beratung'],
        ],
        2 => [ // AG
            2 => ['ansatz' => 92.00, 'kkasse' => 65.40, 'bez' => 'Untersuchung/Behandlung'],
            3 => ['ansatz' => 44.00, 'kkasse' =>  0.00, 'bez' => 'Hauswirtschaft'],
            4 => ['ansatz' => 98.00, 'kkasse' => 54.60, 'bez' => 'Grundpflege'],
            5 => ['ansatz' => 96.50, 'kkasse' => 79.80, 'bez' => 'Abklärung/Beratung'],
        ],
    ];

    /**
     * Einsatz-Muster pro Klient.
     * [laId, ltId, benutzerId, minuten, wochentage (0=So..6=Sa), nurAm5ten, startStunde, startMinute]
     */
    private const MUSTER = [
        315 => [ // Elisabeth Brunner — AG — GP täglich + HWL Di+Fr
            [4, 10, 123, 45, [1,2,3,4,5,6], false, 8,  0],  // GP Mo–Sa 08:00
            [3,  9, 126, 30, [2,5],         false, 9,  0],  // HWL Di+Fr 09:00
        ],
        316 => [ // Hans Weber — AG — Injektion täglich + Verbandwechsel Mo/Mi/Fr
            [2, 31, 124, 15, [0,1,2,3,4,5,6], false, 8,  0],  // UB Injektion täglich 08:00
            [2,  6, 124, 20, [1,3,5],          false, 8, 30],  // UB Verband Mo/Mi/Fr 08:30
        ],
        317 => [ // Margrit Schneider — BE — GP Mo–Sa + Vitalzeichen Mo/Mi/Fr
            [4, 13, 127, 50, [1,2,3,4,5,6], false, 9,  0],  // GP Mo–Sa 09:00
            [2,  2, 127, 15, [1,3,5],        false, 10, 0],  // UB Vital Mo/Mi/Fr 10:00
        ],
        318 => [ // Werner Keller — BE — HWL Di/Do/Sa + GP Mo/Mi/Fr
            [3,  9, 125, 60, [2,4,6], false, 14, 0],  // HWL Di/Do/Sa 14:00
            [4, 10, 125, 25, [1,3,5], false,  8, 0],  // GP Mo/Mi/Fr 08:00
        ],
        319 => [ // Josef Gerber — BE — GP Mo–Fr + Beratung monatlich am 5.
            [4, 22, 125, 35, [1,2,3,4,5], false, 10, 0],  // GP Mo–Fr 10:00
            [5, 28, 127, 60, [],           true,  15, 0],  // AB am 5. des Monats 15:00
        ],
    ];

    public function run(): void
    {
        // ── Cleanup ───────────────────────────────────────────────────────────
        $rechnungIds = DB::table('rechnungen')
            ->whereIn('klient_id', self::KLIENT_IDS)
            ->pluck('id');

        $laufIds = DB::table('rechnungen')
            ->whereIn('klient_id', self::KLIENT_IDS)
            ->whereNotNull('rechnungslauf_id')
            ->pluck('rechnungslauf_id')
            ->unique();

        DB::table('rechnungs_positionen')->whereIn('rechnung_id', $rechnungIds)->delete();
        DB::table('rechnungen')->whereIn('klient_id', self::KLIENT_IDS)->delete();

        foreach ($laufIds as $lid) {
            if (DB::table('rechnungen')->where('rechnungslauf_id', $lid)->count() === 0) {
                DB::table('rechnungslaeufe')->where('id', $lid)->delete();
            }
        }

        DB::table('einsaetze')->whereIn('klient_id', self::KLIENT_IDS)->delete();

        // ── Einsätze erstellen ────────────────────────────────────────────────
        $now = now();

        $perioden = [
            0 => ['von' => '2025-12-01', 'bis' => '2025-12-31', 'verrechnet' => true],
            1 => ['von' => '2026-01-01', 'bis' => '2026-01-31', 'verrechnet' => true],
            2 => ['von' => '2026-02-01', 'bis' => '2026-02-28', 'verrechnet' => true],
            3 => ['von' => '2026-03-01', 'bis' => '2026-03-19', 'verrechnet' => false],
        ];

        // Sammle Einsatz-IDs pro Periode/Klient/LA für Rechnungen
        // $idx[periIdx][klientId][laId][] = ['id', 'menge', 'datum']
        $idx = [];

        foreach (self::KLIENT_IDS as $klientId) {
            $regionId = self::KLIENT_REGION[$klientId];

            foreach ($perioden as $pi => $peri) {
                $date = Carbon::parse($peri['von']);
                $end  = Carbon::parse($peri['bis']);

                while ($date <= $end) {
                    $dow = $date->dayOfWeek;

                    foreach (self::MUSTER[$klientId] as $m) {
                        [$laId, $ltId, $benutzerId, $min, $days, $nurAm5, $h, $mi] = $m;

                        $soll = $nurAm5
                            ? ($date->day === 5 && $dow >= 1 && $dow <= 5)
                            : in_array($dow, $days);

                        if (! $soll) {
                            continue;
                        }

                        $von = $date->copy()->setHour($h)->setMinute($mi)->setSecond(0);
                        $bis = $von->copy()->addMinutes($min);

                        $eid = DB::table('einsaetze')->insertGetId([
                            'organisation_id'        => self::ORG_ID,
                            'klient_id'              => $klientId,
                            'benutzer_id'            => $benutzerId,
                            'region_id'              => $regionId,
                            'leistungsart_id'        => $laId,
                            'datum'                  => $date->toDateString(),
                            'zeit_von'               => $von->format('H:i'),
                            'zeit_bis'               => $bis->format('H:i'),
                            'checkin_zeit'           => $von->toDateTimeString(),
                            'checkout_zeit'          => $bis->toDateTimeString(),
                            'checkin_methode'        => 'vor_ort',
                            'checkout_methode'       => 'vor_ort',
                            'minuten'                => $min,
                            'status'                 => 'abgeschlossen',
                            'verrechnet'             => $peri['verrechnet'],
                            'leistungserbringer_typ' => 'fachperson',
                            'created_at'             => $now,
                            'updated_at'             => $now,
                        ]);

                        $idx[$pi][$klientId][$laId][] = [
                            'id'    => $eid,
                            'menge' => $min,
                            'datum' => $date->toDateString(),
                        ];
                    }

                    $date->addDay();
                }
            }
        }

        // ── Rechnungsläufe + Rechnungen für Perioden 0–2 ─────────────────────
        $laufConfig = [
            0 => ['status_lauf' => 'abgeschlossen', 'status_rech' => 'bezahlt',  'rd' => '2026-01-05'],
            1 => ['status_lauf' => 'abgeschlossen', 'status_rech' => 'gesendet', 'rd' => '2026-02-05'],
            2 => ['status_lauf' => 'entwurf',       'status_rech' => 'entwurf',  'rd' => '2026-03-05'],
        ];

        // Nächste freie Rechnungsnummer ermitteln
        $lastNr = DB::table('rechnungen')
            ->where('rechnungsnummer', 'like', 'RE-20%')
            ->max('rechnungsnummer');
        $nextNr = 1;
        if ($lastNr && preg_match('/RE-\d{4}-(\d+)$/', $lastNr, $m)) {
            $nextNr = (int) $m[1] + 1;
        }

        foreach ([0, 1, 2] as $pi) {
            $peri = $perioden[$pi];
            $cfg  = $laufConfig[$pi];
            $jahr = Carbon::parse($peri['von'])->year;

            $laufId = DB::table('rechnungslaeufe')->insertGetId([
                'organisation_id'      => self::ORG_ID,
                'periode_von'          => $peri['von'],
                'periode_bis'          => $peri['bis'],
                'anzahl_erstellt'      => count(self::KLIENT_IDS),
                'anzahl_uebersprungen' => 0,
                'status'               => $cfg['status_lauf'],
                'erstellt_von'         => self::ADMIN_ID,
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            foreach (self::KLIENT_IDS as $klientId) {
                $regionId  = self::KLIENT_REGION[$klientId];
                $tarife    = self::TARIFE[$regionId];
                $laData    = $idx[$pi][$klientId] ?? [];

                if (empty($laData)) {
                    continue;
                }

                // Beträge summieren
                $bPatient = 0.0;
                $bKk      = 0.0;
                foreach ($laData as $laId => $einsaetze) {
                    $t = $tarife[$laId];
                    foreach ($einsaetze as $e) {
                        $bPatient += $e['menge'] / 60 * $t['ansatz'];
                        $bKk      += $e['menge'] / 60 * $t['kkasse'];
                    }
                }

                $rechNr = 'RE-' . $jahr . '-' . str_pad($nextNr, 4, '0', STR_PAD_LEFT);
                $nextNr++;

                $rechnungId = DB::table('rechnungen')->insertGetId([
                    'organisation_id' => self::ORG_ID,
                    'klient_id'       => $klientId,
                    'rechnungsnummer' => $rechNr,
                    'rechnungslauf_id'=> $laufId,
                    'periode_von'     => $peri['von'],
                    'periode_bis'     => $peri['bis'],
                    'rechnungsdatum'  => $cfg['rd'],
                    'betrag_patient'  => round($bPatient, 2),
                    'betrag_kk'       => round($bKk, 2),
                    'betrag_total'    => round($bPatient + $bKk, 2),
                    'status'          => $cfg['status_rech'],
                    'rechnungstyp'    => 'kombiniert',
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);

                // Positionen — eine pro Einsatz
                foreach ($laData as $laId => $einsaetze) {
                    $t = $tarife[$laId];
                    foreach ($einsaetze as $e) {
                        DB::table('rechnungs_positionen')->insert([
                            'rechnung_id'   => $rechnungId,
                            'einsatz_id'    => $e['id'],
                            'datum'         => $e['datum'],
                            'menge'         => $e['menge'],
                            'einheit'       => 'min',
                            'tarif_patient' => $t['ansatz'],
                            'tarif_kk'      => $t['kkasse'],
                            'betrag_patient'=> round($e['menge'] / 60 * $t['ansatz'], 2),
                            'betrag_kk'     => round($e['menge'] / 60 * $t['kkasse'], 2),
                            'beschreibung'  => $t['bez'] . ' — ' . Carbon::parse($e['datum'])->format('d.m.Y'),
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ]);
                    }
                }
            }
        }

        $this->command->info('SpitexNormalSeeder: 5 Klienten, 4 Perioden, 3 Rechnungsläufe — fertig.');
    }
}
