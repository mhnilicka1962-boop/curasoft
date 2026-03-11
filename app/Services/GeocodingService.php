<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    // Adresse → Koordinaten via OpenStreetMap Nominatim (kostenlos, kein API-Key)
    public function geocode(string $strasse, string $plz, string $ort, string $land = 'CH'): ?array
    {
        $adresse = trim("$strasse, $plz $ort, $land");

        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'CuraSoft-Spitex/1.0 (spitex@curasoft.ch)'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q'              => $adresse,
                    'format'         => 'json',
                    'limit'          => 1,
                    'countrycodes'   => strtolower($land),
                    'addressdetails' => 0,
                ]);

            $results = $response->json();

            if (!empty($results[0])) {
                return [
                    'lat' => (float) $results[0]['lat'],
                    'lng' => (float) $results[0]['lon'],
                ];
            }
        } catch (\Exception $e) {
            Log::warning("Geocoding fehlgeschlagen für '$adresse': " . $e->getMessage());
        }

        return null;
    }

    // Haversine-Distanz in Metern zwischen zwei Koordinaten
    public static function distanz(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $r  = 6371000;
        $p1 = deg2rad($lat1);
        $p2 = deg2rad($lat2);
        $dp = deg2rad($lat2 - $lat1);
        $dl = deg2rad($lng2 - $lng1);
        $a  = sin($dp / 2) ** 2 + cos($p1) * cos($p2) * sin($dl / 2) ** 2;
        return (int) round($r * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    // Nearest-Neighbor Routenoptimierung
    // Gibt die Einsatz-IDs in optimierter Reihenfolge zurück
    public static function optimiereReihenfolge(array $punkte): array
    {
        // $punkte = [['id' => X, 'lat' => Y, 'lng' => Z], ...]
        if (count($punkte) <= 1) return array_column($punkte, 'id');

        $besucht    = [];
        $reihenfolge = [];
        $aktuell    = $punkte[0]; // Startpunkt = erster Einsatz

        $besucht[$aktuell['id']] = true;
        $reihenfolge[] = $aktuell['id'];

        while (count($reihenfolge) < count($punkte)) {
            $naechster  = null;
            $minDistanz = PHP_INT_MAX;

            foreach ($punkte as $punkt) {
                if (isset($besucht[$punkt['id']])) continue;

                $d = self::distanz($aktuell['lat'], $aktuell['lng'], $punkt['lat'], $punkt['lng']);
                if ($d < $minDistanz) {
                    $minDistanz = $d;
                    $naechster  = $punkt;
                }
            }

            if (!$naechster) break;
            $besucht[$naechster['id']] = true;
            $reihenfolge[] = $naechster['id'];
            $aktuell = $naechster;
        }

        return $reihenfolge;
    }
}
