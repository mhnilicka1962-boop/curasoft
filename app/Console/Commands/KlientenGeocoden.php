<?php

namespace App\Console\Commands;

use App\Models\Klient;
use App\Services\GeocodingService;
use Illuminate\Console\Command;

class KlientenGeocoden extends Command
{
    protected $signature   = 'klienten:geocoden {--force : Auch bereits geocodierte Klienten neu geocoden}';
    protected $description = 'Koordinaten für alle Klienten via OpenStreetMap Nominatim holen';

    public function handle(GeocodingService $geo): int
    {
        $query = Klient::whereNotNull('adresse')->whereNotNull('plz')->whereNotNull('ort');

        if (!$this->option('force')) {
            $query->whereNull('klient_lat');
        }

        $klienten = $query->get();

        if ($klienten->isEmpty()) {
            $this->info('Alle Klienten haben bereits Koordinaten.');
            return 0;
        }

        $this->info("Geocode {$klienten->count()} Klienten...");
        $bar = $this->output->createProgressBar($klienten->count());
        $bar->start();

        $ok = 0; $fehler = 0;

        foreach ($klienten as $klient) {
            $coords = $geo->geocode($klient->adresse, $klient->plz, $klient->ort);

            if ($coords) {
                $klient->update(['klient_lat' => $coords['lat'], 'klient_lng' => $coords['lng']]);
                $ok++;
            } else {
                $fehler++;
            }

            $bar->advance();
            // Nominatim Rate-Limit: max 1 Request/Sekunde
            usleep(1100000);
        }

        $bar->finish();
        $this->newLine();
        $this->info("✓ $ok geocodet, $fehler fehlgeschlagen.");

        return 0;
    }
}
