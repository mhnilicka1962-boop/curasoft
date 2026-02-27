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
 * Handles synchronisation between Spitex and Bexio accounting.
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
                'remarks'         => 'Spitex ID: ' . $klient->id,
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
    // Zahlungsstatus-Abgleich (Bexio → Spitex)
    // ----------------------------------------------------------------

    /**
     * Prüft den Zahlungsstatus einer einzelnen Rechnung in Bexio.
     * Gibt Array zurück: ['aktualisiert' => bool, 'status' => string, 'fehler' => ?string]
     */
    public function zahlungsstatusAktualisieren(Rechnung $rechnung): array
    {
        if (empty($this->apiKey)) {
            return ['aktualisiert' => false, 'fehler' => 'Kein API-Key konfiguriert.'];
        }

        if (!$rechnung->bexio_rechnung_id) {
            return ['aktualisiert' => false, 'fehler' => 'Noch nicht mit Bexio synchronisiert.'];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(10)
                ->get(self::API_BASE . '/kb_invoice/' . $rechnung->bexio_rechnung_id);

            if (!$response->successful()) {
                return ['aktualisiert' => false, 'fehler' => 'Bexio HTTP ' . $response->status()];
            }

            $daten = $response->json();
            // Bexio kb_item_status_id: 19 = Bezahlt, 16 = Teilbezahlt, 7=Entwurf, 8=Ausstehend, 9=Offen
            $bexioStatusId = $daten['kb_item_status_id'] ?? null;

            if ($bexioStatusId === 19 && $rechnung->status !== 'bezahlt') {
                $rechnung->update([
                    'status'           => 'bezahlt',
                    'bexio_bezahlt_am' => now(),
                ]);
                return ['aktualisiert' => true, 'status' => 'bezahlt'];
            }

            return ['aktualisiert' => false, 'status' => $this->bexioStatusLabel($bexioStatusId)];

        } catch (\Exception $e) {
            Log::error('Bexio Zahlungsstatus-Abfrage fehlgeschlagen', [
                'rechnung_id' => $rechnung->id,
                'error'       => $e->getMessage(),
            ]);
            return ['aktualisiert' => false, 'fehler' => $e->getMessage()];
        }
    }

    /**
     * Prüft den Zahlungsstatus aller Rechnungen einer Collection.
     * Gibt Zusammenfassung zurück: ['aktualisiert' => int, 'fehler' => int]
     */
    public function sammelstatusAktualisieren(\Illuminate\Support\Collection $rechnungen): array
    {
        $aktualisiert = 0;
        $fehler       = 0;

        foreach ($rechnungen as $rechnung) {
            if (!$rechnung->bexio_rechnung_id) continue;
            if ($rechnung->status === 'bezahlt' || $rechnung->status === 'storniert') continue;

            $ergebnis = $this->zahlungsstatusAktualisieren($rechnung);
            if ($ergebnis['aktualisiert']) {
                $aktualisiert++;
            } elseif (isset($ergebnis['fehler'])) {
                $fehler++;
            }
        }

        return ['aktualisiert' => $aktualisiert, 'fehler' => $fehler];
    }

    private function bexioStatusLabel(?int $id): string
    {
        return match($id) {
            7  => 'Entwurf',
            8  => 'Ausstehend',
            9  => 'Offen',
            16 => 'Teilbezahlt',
            19 => 'Bezahlt',
            23 => 'Storniert',
            default => 'Unbekannt (' . $id . ')',
        };
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
