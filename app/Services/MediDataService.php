<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Rechnung;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MediDataService
{
    public function __construct(private Organisation $org) {}

    /**
     * XML-Rechnung zu MediData hochladen.
     * Gibt true bei Erfolg zurück, wirft Exception bei Fehler.
     */
    public function xmlHochladen(Rechnung $rechnung, string $xmlPfad): bool
    {
        if (empty($this->org->medidata_url)) {
            throw new \RuntimeException('Keine MediData-URL konfiguriert (Firma → MediData).');
        }
        if (empty($this->org->medidata_username)) {
            throw new \RuntimeException('Kein MediData-Benutzername konfiguriert.');
        }
        if (empty($this->org->medidata_passwort)) {
            throw new \RuntimeException('Kein MediData-Passwort konfiguriert.');
        }

        $xmlInhalt = Storage::get($xmlPfad);
        if (!$xmlInhalt) {
            throw new \RuntimeException("XML-Datei nicht gefunden: {$xmlPfad}");
        }

        $response = Http::withBasicAuth($this->org->medidata_username, $this->org->medidata_passwort)
            ->withHeaders(['Content-Type' => 'application/xml'])
            ->withOptions(['verify' => true, 'timeout' => 30])
            ->send('POST', $this->org->medidata_url, ['body' => $xmlInhalt]);

        if ($response->failed()) {
            throw new \RuntimeException(
                "MediData Fehler {$response->status()}: " . mb_substr($response->body(), 0, 300)
            );
        }

        return true;
    }
}
