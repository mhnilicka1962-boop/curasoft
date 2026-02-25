<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Rechnung;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfExportService
{
    public function __construct(private Organisation $org) {}

    public function rechnungExportieren(Rechnung $rechnung): string
    {
        $rechnung->loadMissing([
            'klient.region',
            'positionen.leistungstyp.leistungsart',
        ]);

        $regionDaten = $rechnung->klient->region_id
            ? $this->org->datenFuerRegion($rechnung->klient->region_id)
            : [
                'zsr_nr'         => $this->org->zsr_nr ?? '',
                'bank'           => $this->org->bank ?? '',
                'bankadresse'    => $this->org->bankadresse ?? '',
                'iban'           => $this->org->iban ?? '',
                'postcheckkonto' => $this->org->postcheckkonto ?? '',
                'esr'            => '',
                'qr_iban'        => '',
            ];

        // Logo als Base64 einbetten (DomPDF kann keine externen HTTP-URLs laden)
        $logoBase64 = null;
        if ($this->org->logo_pfad) {
            $logoPfad = public_path($this->org->logo_pfad);
            if (file_exists($logoPfad)) {
                $mime       = mime_content_type($logoPfad);
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPfad));
            }
        }

        $html = view('pdfs.rechnung', [
            'rechnung'    => $rechnung,
            'org'         => $this->org,
            'regionDaten' => $regionDaten,
            'logoBase64'  => $logoBase64,
        ])->render();

        $pdf  = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        $pfad = "pdf_export/{$this->org->id}/rechnung_{$rechnung->rechnungsnummer}.pdf";

        Storage::put($pfad, $pdf->output());
        $rechnung->update(['pdf_pfad' => $pfad]);

        return $pfad;
    }
}
