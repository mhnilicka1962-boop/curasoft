<?php

namespace App\Services;

use App\Models\BexioSync;
use App\Models\Klient;
use App\Models\Organisation;
use App\Models\Rechnung;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Bexio API Integration Service
 *
 * Handles synchronisation between CuraSoft and Bexio accounting.
 * API Docs: https://docs.bexio.com/
 */
class BexioService
{
    private const API_BASE = 'https://api.bexio.com/2.0';

    private string $apiKey;
    private Organisation $org;

    public function __construct(Organisation $org)
    {
        $this->org    = $org;
        $this->apiKey = $org->bexio_api_key ?? '';
    }

    // ----------------------------------------------------------------
    // Verbindungstest
    // ----------------------------------------------------------------

    public function verbindungTesten(): array
    {
        if (empty($this->apiKey)) {
            return ['ok' => false, 'fehler' => 'Kein API-Key konfiguriert.'];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->get(self::API_BASE . '/users/myself');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'ok'   => true,
                    'info' => ($data['firstname'] ?? '') . ' ' . ($data['lastname'] ?? ''),
                ];
            }

            return ['ok' => false, 'fehler' => 'HTTP ' . $response->status() . ': ' . $response->body()];
        } catch (\Exception $e) {
            return ['ok' => false, 'fehler' => $e->getMessage()];
        }
    }

    // ----------------------------------------------------------------
    // Kontakt (Klient → Bexio Kontakt)
    // ----------------------------------------------------------------

    public function kontaktSynchronisieren(Klient $klient): bool
    {
        try {
            $daten = [
                'contact_type_id' => 1, // Privatperson
                'name_1'          => $klient->nachname,
                'name_2'          => $klient->vorname,
                'address'         => $klient->adresse ?? '',
                'postcode'        => $klient->plz ?? '',
                'city'            => $klient->ort ?? '',
                'country_id'      => 1, // Schweiz
                'phone_fixed'     => $klient->telefon ?? '',
                'mail'            => $klient->email ?? '',
                'remarks'         => 'CuraSoft ID: ' . $klient->id,
            ];

            if ($klient->bexio_kontakt_id) {
                // Update
                $response = Http::withToken($this->apiKey)
                    ->put(self::API_BASE . '/contact/' . $klient->bexio_kontakt_id, $daten);
            } else {
                // Create
                $response = Http::withToken($this->apiKey)
                    ->post(self::API_BASE . '/contact', $daten);

                if ($response->successful()) {
                    $klient->update(['bexio_kontakt_id' => $response->json('id')]);
                }
            }

            $this->syncLogSchreiben('Klient', $klient->id, $response->successful(), $response->body());
            return $response->successful();

        } catch (\Exception $e) {
            $this->syncLogSchreiben('Klient', $klient->id, false, $e->getMessage());
            Log::error('Bexio Kontakt-Sync fehlgeschlagen', ['klient_id' => $klient->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // ----------------------------------------------------------------
    // Rechnung (Rechnung → Bexio Rechnung)
    // ----------------------------------------------------------------

    public function rechnungSynchronisieren(Rechnung $rechnung): bool
    {
        try {
            if (!$rechnung->klient?->bexio_kontakt_id) {
                Log::warning('Bexio: Klient hat keine bexio_kontakt_id', ['rechnung_id' => $rechnung->id]);
                return false;
            }

            $positionen = $rechnung->positionen()
                ->with('leistungsart')
                ->get()
                ->map(fn($p) => [
                    'type'        => 'KbPositionArticle',
                    'amount'      => $p->menge,
                    'unit_price'  => $p->betrag / max($p->menge, 1),
                    'discount_in_percent' => 0,
                    'account_id'  => null,
                    'tax_id'      => null,
                    'text'        => $p->leistungsart?->bezeichnung ?? $p->bezeichnung ?? '',
                    'unit_id'     => null,
                ]);

            $daten = [
                'title'           => 'Rechnung ' . $rechnung->rechnung_nr,
                'contact_id'      => $rechnung->klient->bexio_kontakt_id,
                'is_valid_from'   => $rechnung->datum?->format('Y-m-d'),
                'is_valid_to'     => $rechnung->faellig_am?->format('Y-m-d'),
                'currency_id'     => 1, // CHF
                'positions'       => $positionen->toArray(),
            ];

            if ($rechnung->bexio_rechnung_id) {
                $response = Http::withToken($this->apiKey)
                    ->put(self::API_BASE . '/kb_invoice/' . $rechnung->bexio_rechnung_id, $daten);
            } else {
                $response = Http::withToken($this->apiKey)
                    ->post(self::API_BASE . '/kb_invoice', $daten);

                if ($response->successful()) {
                    $rechnung->update(['bexio_rechnung_id' => $response->json('id')]);
                }
            }

            $this->syncLogSchreiben('Rechnung', $rechnung->id, $response->successful(), $response->body());
            return $response->successful();

        } catch (\Exception $e) {
            $this->syncLogSchreiben('Rechnung', $rechnung->id, false, $e->getMessage());
            Log::error('Bexio Rechnung-Sync fehlgeschlagen', ['rechnung_id' => $rechnung->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    // ----------------------------------------------------------------
    // Privat
    // ----------------------------------------------------------------

    private function syncLogSchreiben(string $typ, int $id, bool $ok, string $details): void
    {
        BexioSync::updateOrCreate(
            ['organisation_id' => $this->org->id, 'entity_typ' => $typ, 'entity_id' => $id],
            [
                'bexio_id'     => null,
                'letzter_sync' => now(),
                'sync_status'  => $ok ? 'ok' : 'fehler',
                'fehler_meldung' => $ok ? null : substr($details, 0, 500),
            ]
        );
    }
}
