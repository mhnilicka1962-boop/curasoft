<?php

namespace Database\Seeders;

use App\Models\Rechnung;
use App\Models\RechnungsPosition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Erstellt realistische Demo-Rechnungen für Testzwecke.
 * Bestehende Rechnungen werden NICHT gelöscht.
 * Kann mehrfach ausgeführt werden (idempotent via rechnungsnummer-Check).
 *
 * php artisan db:seed --class=DemoRechnungenSeeder
 */
class DemoRechnungenSeeder extends Seeder
{
    // Org ID (einzige Organisation)
    private int $orgId = 1;

    // Klient-IDs die wir für die Demo-Rechnungen verwenden
    private array $klientIds = [1, 2, 3, 4, 5, 6, 7, 8];

    // Typische Spitex-Tarife (CHF/Std.)
    private array $tarife = [
        ['patient' => 15.35, 'kk' => 54.60],  // Standard KVG
        ['patient' => 0.00,  'kk' => 65.40],  // KVG-only (Tiers payant)
        ['patient' => 12.80, 'kk' => 48.20],  // Grundpflege
        ['patient' => 32.50, 'kk' => 0.00],   // Selbstzahler
    ];

    public function run(): void
    {
        $demoRechnungen = $this->demoRechnungenDaten();

        $erstellt = 0;
        foreach ($demoRechnungen as $data) {
            // Idempotent: nur anlegen wenn Nummer noch nicht existiert
            if (Rechnung::where('rechnungsnummer', $data['nr'])->exists()) {
                continue;
            }

            $rechnung = Rechnung::create([
                'organisation_id' => $this->orgId,
                'klient_id'       => $data['klient_id'],
                'rechnungsnummer' => $data['nr'],
                'periode_von'     => $data['von'],
                'periode_bis'     => $data['bis'],
                'rechnungsdatum'  => $data['datum'],
                'status'          => $data['status'],
                'rechnungstyp'    => $data['typ'],
                'betrag_patient'  => 0,
                'betrag_kk'       => 0,
                'betrag_total'    => 0,
            ]);

            $this->erstellePositionen($rechnung, $data['positionen'], $data['typ']);
            $rechnung->load('positionen');
            $rechnung->berechneTotale();

            $erstellt++;
        }

        $this->command->info("DemoRechnungenSeeder: {$erstellt} neue Rechnungen erstellt.");
    }

    private function erstellePositionen(Rechnung $rechnung, array $positionen, string $typ): void
    {
        $nurKK      = $typ === 'kvg';
        $nurPatient = in_array($typ, ['klient', 'gemeinde']);

        foreach ($positionen as $pos) {
            $tarif  = $this->tarife[$pos['tarifIdx']];
            $menge  = $pos['minuten'];
            $stunden = $menge / 60;

            $tarifPatient = $nurKK      ? 0.00 : $tarif['patient'];
            $tarifKk      = $nurPatient ? 0.00 : $tarif['kk'];

            RechnungsPosition::create([
                'rechnung_id'    => $rechnung->id,
                'einsatz_id'     => null,
                'datum'          => $pos['datum'],
                'menge'          => $menge,
                'einheit'        => 'minuten',
                'tarif_patient'  => $tarifPatient,
                'tarif_kk'       => $tarifKk,
                'betrag_patient' => round($stunden * $tarifPatient, 2),
                'betrag_kk'      => round($stunden * $tarifKk, 2),
            ]);
        }
    }

    /**
     * Demo-Rechnungen: Verschiedene Monate, Typen und Status.
     * Nummernschema: RE-JAHR-NNNN (ab 0010 um keine Kollision mit echten)
     */
    private function demoRechnungenDaten(): array
    {
        return [
            // ── 2025 – Archiv-Rechnungen (alle bezahlt) ──────────────────────
            [
                'nr'        => 'RE-2025-0001',
                'klient_id' => 1,
                'von'       => '2025-01-01', 'bis' => '2025-01-31',
                'datum'     => '2025-02-03',
                'status'    => 'bezahlt',
                'typ'       => 'kombiniert',
                'positionen' => $this->monatsPositionen('2025-01', 3, 0),
            ],
            [
                'nr'        => 'RE-2025-0002',
                'klient_id' => 2,
                'von'       => '2025-01-01', 'bis' => '2025-01-31',
                'datum'     => '2025-02-05',
                'status'    => 'bezahlt',
                'typ'       => 'kvg',
                'positionen' => $this->monatsPositionen('2025-01', 4, 1),
            ],
            [
                'nr'        => 'RE-2025-0003',
                'klient_id' => 3,
                'von'       => '2025-02-01', 'bis' => '2025-02-28',
                'datum'     => '2025-03-04',
                'status'    => 'bezahlt',
                'typ'       => 'klient',
                'positionen' => $this->monatsPositionen('2025-02', 3, 3),
            ],
            [
                'nr'        => 'RE-2025-0004',
                'klient_id' => 4,
                'von'       => '2025-02-01', 'bis' => '2025-02-28',
                'datum'     => '2025-03-06',
                'status'    => 'bezahlt',
                'typ'       => 'kombiniert',
                'positionen' => $this->monatsPositionen('2025-02', 5, 2),
            ],
            [
                'nr'        => 'RE-2025-0005',
                'klient_id' => 5,
                'von'       => '2025-03-01', 'bis' => '2025-03-31',
                'datum'     => '2025-04-02',
                'status'    => 'bezahlt',
                'typ'       => 'gemeinde',
                'positionen' => $this->monatsPositionen('2025-03', 4, 0),
            ],
            [
                'nr'        => 'RE-2025-0006',
                'klient_id' => 1,
                'von'       => '2025-03-01', 'bis' => '2025-03-31',
                'datum'     => '2025-04-04',
                'status'    => 'bezahlt',
                'typ'       => 'kvg',
                'positionen' => $this->monatsPositionen('2025-03', 6, 1),
            ],
            [
                'nr'        => 'RE-2025-0007',
                'klient_id' => 6,
                'von'       => '2025-04-01', 'bis' => '2025-04-30',
                'datum'     => '2025-05-05',
                'status'    => 'bezahlt',
                'typ'       => 'kombiniert',
                'positionen' => $this->monatsPositionen('2025-04', 4, 0),
            ],
            [
                'nr'        => 'RE-2025-0008',
                'klient_id' => 7,
                'von'       => '2025-05-01', 'bis' => '2025-05-31',
                'datum'     => '2025-06-03',
                'status'    => 'bezahlt',
                'typ'       => 'kombiniert',
                'positionen' => $this->monatsPositionen('2025-05', 5, 2),
            ],
            [
                'nr'        => 'RE-2025-0009',
                'klient_id' => 8,
                'von'       => '2025-06-01', 'bis' => '2025-06-30',
                'datum'     => '2025-07-02',
                'status'    => 'bezahlt',
                'typ'       => 'kvg',
                'positionen' => $this->monatsPositionen('2025-06', 4, 1),
            ],
            [
                'nr'        => 'RE-2025-0010',
                'klient_id' => 2,
                'von'       => '2025-07-01', 'bis' => '2025-07-31',
                'datum'     => '2025-08-04',
                'status'    => 'bezahlt',
                'typ'       => 'kombiniert',
                'positionen' => $this->monatsPositionen('2025-07', 5, 0),
            ],

            // ── 2025 – Storniert ──────────────────────────────────────────────
            [
                'nr'        => 'RE-2025-0011',
                'klient_id' => 3,
                'von'       => '2025-08-01', 'bis' => '2025-08-31',
                'datum'     => '2025-09-03',
                'status'    => 'storniert',
                'typ'       => 'kombiniert',
                'positionen' => $this->monatsPositionen('2025-08', 3, 0),
            ],

            // ── Q4 2025 – bezahlt ─────────────────────────────────────────────
            [
                'nr'        => 'RE-2025-0012',
                'klient_id' => 4,
                'von'       => '2025-09-01', 'bis' => '2025-09-30',
                'datum'     => '2025-10-06',
                'status'    => 'bezahlt',
                'typ'       => 'kvg',
                'positionen' => $this->monatsPositionen('2025-09', 4, 1),
            ],
            [
                'nr'        => 'RE-2025-0013',
                'klient_id' => 5,
                'von'       => '2025-10-01', 'bis' => '2025-10-31',
                'datum'     => '2025-11-04',
                'status'    => 'bezahlt',
                'typ'       => 'gemeinde',
                'positionen' => $this->monatsPositionen('2025-10', 5, 2),
            ],
            [
                'nr'        => 'RE-2025-0014',
                'klient_id' => 6,
                'von'       => '2025-11-01', 'bis' => '2025-11-30',
                'datum'     => '2025-12-03',
                'status'    => 'bezahlt',
                'typ'       => 'kombiniert',
                'positionen' => $this->monatsPositionen('2025-11', 6, 0),
            ],
            [
                'nr'        => 'RE-2025-0015',
                'klient_id' => 7,
                'von'       => '2025-12-01', 'bis' => '2025-12-31',
                'datum'     => '2026-01-05',
                'status'    => 'bezahlt',
                'typ'       => 'klient',
                'positionen' => $this->monatsPositionen('2025-12', 4, 3),
            ],

            // ── 2026 – Gesendet (ausstehend) ──────────────────────────────────
            [
                'nr'        => 'RE-2026-0010',
                'klient_id' => 1,
                'von'       => '2026-01-01', 'bis' => '2026-01-31',
                'datum'     => '2026-02-03',
                'status'    => 'gesendet',
                'typ'       => 'kvg',
                'positionen' => $this->monatsPositionen('2026-01', 5, 1),
            ],
            [
                'nr'        => 'RE-2026-0011',
                'klient_id' => 3,
                'von'       => '2026-01-01', 'bis' => '2026-01-31',
                'datum'     => '2026-02-05',
                'status'    => 'gesendet',
                'typ'       => 'kombiniert',
                'positionen' => $this->monatsPositionen('2026-01', 4, 0),
            ],
            [
                'nr'        => 'RE-2026-0012',
                'klient_id' => 5,
                'von'       => '2026-01-01', 'bis' => '2026-01-31',
                'datum'     => '2026-02-06',
                'status'    => 'gesendet',
                'typ'       => 'gemeinde',
                'positionen' => $this->monatsPositionen('2026-01', 3, 2),
            ],
            [
                'nr'        => 'RE-2026-0013',
                'klient_id' => 7,
                'von'       => '2026-01-01', 'bis' => '2026-01-31',
                'datum'     => '2026-02-07',
                'status'    => 'gesendet',
                'typ'       => 'klient',
                'positionen' => $this->monatsPositionen('2026-01', 4, 3),
            ],
        ];
    }

    /**
     * Erzeugt mehrere Positionen verteilt über den Monat.
     */
    private function monatsPositionen(string $monat, int $anzahl, int $tarifIdx): array
    {
        $positionen = [];
        $tage = [2, 5, 8, 12, 15, 18, 22, 25, 28];
        $minuten = [45, 60, 75, 90, 60, 45, 90, 75, 60];

        for ($i = 0; $i < $anzahl && $i < count($tage); $i++) {
            $tag = str_pad($tage[$i], 2, '0', STR_PAD_LEFT);
            $positionen[] = [
                'datum'    => "{$monat}-{$tag}",
                'minuten'  => $minuten[$i],
                'tarifIdx' => $tarifIdx,
            ];
        }

        return $positionen;
    }
}
