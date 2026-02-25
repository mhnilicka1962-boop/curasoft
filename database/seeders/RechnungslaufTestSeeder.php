<?php

namespace Database\Seeders;

use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\KlientBeitrag;
use App\Models\Leistungsregion;
use App\Models\Organisation;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Test-Seeder für Rechnungslauf-Demo
 * Erstellt 30 neue Test-Klienten + Einsätze für Dez/Jan/Feb
 *
 * Ausführen: php artisan db:seed --class=RechnungslaufTestSeeder
 * Rückgängig: php artisan db:seed --class=RechnungslaufTestSeederCleanup  (oder manuell löschen)
 */
class RechnungslaufTestSeeder extends Seeder
{
    // Verteilung Versandart
    // 30% post, 40% email, 30% manuell (KVG/XML)
    private array $versandartGruppen = [
        'post'    => 9,   // 30%
        'email'   => 12,  // 40%
        'manuell' => 9,   // 30% — diese kriegen versandart_kvg='email' → XML-ZIP
    ];

    private array $nachnamen = [
        'Meier','Müller','Schmid','Keller','Weber','Huber','Fischer','Brunner',
        'Gerber','Zimmermann','Baumann','Moser','Steiner','Frei','Schneider',
        'Bauer','Graf','Roth','Lehmann','Kaufmann','Wirth','Lüthi','Suter',
        'Widmer','Maurer','Hofer','Brun','Wyss','Schäfer','Etter',
    ];

    private array $vornamen = [
        'Hans','Peter','Kurt','Ernst','Rudolf','Helmut','Werner','Walter',
        'Maria','Anna','Ursula','Elisabeth','Ruth','Margrit','Heidi',
        'Franz','Karl','Josef','Heinrich','Otto',
        'Martha','Rosa','Emma','Klara','Gertrud',
        'Fritz','Hugo','Albert','Emil','Max',
    ];

    private array $orte = [
        'Zürich','Bern','Basel','Luzern','Winterthur','St. Gallen',
        'Schaffhausen','Frauenfeld','Aarau','Solothurn','Olten','Zug',
    ];

    // Leistungsart-IDs mit Verteilung: 40% Grundpflege, 35% Hauswirtschaft, 25% Untersuchung
    private array $leistungsartVerteilung = [4,4,4,4, 3,3,3, 2,2, 5];

    public function run(): void
    {
        $org      = Organisation::firstOrFail();
        $benutzer = Benutzer::where('organisation_id', $org->id)->where('aktiv', true)->first();

        if (!$benutzer) {
            $this->command->error('Kein aktiver Benutzer in der Organisation gefunden.');
            return;
        }

        $this->command->info("Organisation: {$org->name}, Benutzer: {$benutzer->nachname}");

        // Region BE sicherstellen
        $region = Region::where('kuerzel', 'BE')->first();
        if (!$region) {
            $this->command->warn('Region BE nicht gefunden — Einsätze ohne Region');
        }

        // Leistungsregionen BE anlegen falls nicht vorhanden
        if ($region) {
            $this->leistungsregionenSicherstellen($region->id);
        }

        $monate = [
            ['2025-12-01', '2025-12-31'],
            ['2026-01-01', '2026-01-31'],
            ['2026-02-01', '2026-02-28'],
        ];

        $erstellt  = 0;
        $einsaetze = 0;
        $idx       = 0;

        foreach ($this->versandartGruppen as $versandart => $anzahl) {
            for ($i = 0; $i < $anzahl; $i++) {
                $nachname = $this->nachnamen[$idx % count($this->nachnamen)];
                $vorname  = $this->vornamen[$idx % count($this->vornamen)];
                $ort      = $this->orte[$idx % count($this->orte)];

                $klient = Klient::create([
                    'organisation_id'    => $org->id,
                    'anrede'             => $i % 2 === 0 ? 'Herr' : 'Frau',
                    'vorname'            => $vorname,
                    'nachname'           => "TEST-{$nachname}",
                    'geburtsdatum'       => Carbon::now()->subYears(rand(70, 95))->startOfYear(),
                    'adresse'            => "Teststrasse " . rand(1, 99),
                    'plz'                => (string) rand(3000, 9999),
                    'ort'                => $ort,
                    'telefon'            => '044 ' . rand(100, 999) . ' ' . rand(10, 99) . ' ' . rand(10, 99),
                    'email'              => $versandart === 'email'
                                            ? strtolower("{$vorname}.test{$idx}@example.ch")
                                            : null,
                    'aktiv'              => true,
                    'region_id'          => $region?->id,
                    'versandart_patient' => $versandart,
                    'versandart_kvg'     => $versandart === 'manuell' ? 'email' : 'manuell',
                    'zahlbar_tage'       => 30,
                ]);

                // Beitrag erfassen (gültig ab 2024-01-01, typische Spitex-Bern-Werte)
                $this->beitragErstellen($klient, $benutzer->id, $versandart);

                // Einsätze für alle 3 Monate
                foreach ($monate as [$von, $bis]) {
                    $einsaetze += $this->einsaetzeErstellen($klient, $benutzer->id, $org->id, $von, $bis, $idx);
                }

                $erstellt++;
                $idx++;
            }
        }

        $this->command->info("Fertig: {$erstellt} Klienten, {$einsaetze} Einsätze erstellt.");
        $this->command->info("TEST-Klienten erkennbar am Prefix 'TEST-' im Nachnamen.");
    }

    /**
     * Leistungsregionen BE anlegen falls nicht vorhanden.
     * Typische Spitex-BE-Tarife 2025.
     */
    private function leistungsregionenSicherstellen(int $regionId): void
    {
        $tarife = [
            // [leistungsart_id, ansatz, kkasse]
            [2, 79.80, 71.80],  // Untersuchung/Behandlung: Patient 8.00
            [3, 57.00,  0.00],  // Hauswirtschaft: KK zahlt nichts
            [4, 68.00, 60.00],  // Grundpflege: Patient 8.00
            [5, 68.00, 60.00],  // Abklärung/Beratung
        ];

        foreach ($tarife as [$laId, $ansatz, $kkasse]) {
            Leistungsregion::firstOrCreate(
                ['leistungsart_id' => $laId, 'region_id' => $regionId, 'gueltig_ab' => '2025-01-01'],
                ['ansatz' => $ansatz, 'kkasse' => $kkasse, 'ansatz_akut' => $ansatz, 'kkasse_akut' => $kkasse]
            );
        }
    }

    /**
     * Beitrag (Selbstbehalt-Ansätze) für Klient erfassen.
     * Werte orientieren sich an typischen Spitex-Bern-Tarifen (2024/2025).
     * Leicht variiert damit Testdaten realistisch wirken.
     */
    private function beitragErstellen(Klient $klient, int $benutzerId, string $versandart): void
    {
        $ansatzKunde = match($versandart) {
            'manuell' => round(rand(0, 5) * 0.5, 2),        // KVG-Klienten: 0–2.50 CHF/h Selbstbehalt
            'email'   => round((rand(80, 130) * 0.1), 2),   // 8.00–13.00 CHF/h
            default   => round((rand(100, 160) * 0.1), 2),  // Post: 10.00–16.00 CHF/h
        };

        $ansatzSpitex  = round(rand(520, 680) * 0.1, 2);  // 52.00–68.00
        $kantonBeitrag = round(rand(50, 120) * 0.1, 2);   // 5.00–12.00

        KlientBeitrag::create([
            'klient_id'                => $klient->id,
            'gueltig_ab'               => '2024-01-01',
            'ansatz_kunde'             => $ansatzKunde,
            'limit_restbetrag_prozent' => 0,
            'ansatz_spitex'            => $ansatzSpitex,
            'kanton_abrechnung'        => $kantonBeitrag,
            'erfasst_von'              => $benutzerId,
        ]);
    }

    private function einsaetzeErstellen(Klient $klient, int $benutzerId, int $orgId, string $von, string $bis, int $seed): int
    {
        $start  = Carbon::parse($von);
        $ende   = Carbon::parse($bis);
        $anzahl = rand(6, 14);  // 6-14 Einsätze pro Monat
        $count  = 0;

        $tage    = (int) $start->diffInDays($ende);
        $abstand = max(1, (int) ($tage / $anzahl));
        $datum   = $start->copy()->addDays(rand(0, 2));

        for ($i = 0; $i < $anzahl && $datum <= $ende; $i++) {
            $stunde  = rand(7, 17);
            $minute  = [0, 15, 30, 45][rand(0, 3)];
            $dauern  = [30, 45, 60, 75, 90, 105, 120][rand(0, 6)];

            $checkin  = $datum->copy()->setTime($stunde, $minute);
            $checkout = $checkin->copy()->addMinutes($dauern);

            // Leistungsart realistisch verteilen
            $laId = $this->leistungsartVerteilung[($seed + $i) % count($this->leistungsartVerteilung)];

            Einsatz::create([
                'organisation_id'  => $orgId,
                'klient_id'        => $klient->id,
                'benutzer_id'      => $benutzerId,
                'leistungsart_id'  => $laId,
                'datum'            => $datum->toDateString(),
                'zeit_von'         => $checkin->format('H:i'),
                'zeit_bis'         => $checkout->format('H:i'),
                'minuten'          => $dauern,
                'status'           => 'abgeschlossen',
                'verrechnet'       => false,
                'checkin_zeit'     => $checkin,
                'checkout_zeit'    => $checkout,
                'checkin_methode'  => 'manuell',
                'checkout_methode' => 'manuell',
            ]);

            $count++;
            $datum->addDays($abstand + rand(0, 1));
        }

        return $count;
    }
}
