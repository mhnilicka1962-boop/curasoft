<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Rechnung;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Sprain\SwissQrBill\QrBill;
use Sprain\SwissQrBill\DataGroup\Element\CreditorInformation;
use Sprain\SwissQrBill\DataGroup\Element\StructuredAddress;
use Sprain\SwissQrBill\DataGroup\Element\PaymentAmountInformation;
use Sprain\SwissQrBill\DataGroup\Element\PaymentReference;
use Sprain\SwissQrBill\DataGroup\Element\AdditionalInformation;
use Sprain\SwissQrBill\QrCode\QrCode as SwissQrCode;

class PdfExportService
{
    public function __construct(private Organisation $org) {}

    public function rechnungExportieren(Rechnung $rechnung): string
    {
        $rechnung->loadMissing([
            'klient.region',
            'klient.krankenkassen.krankenkasse',
            'klient.adressen',
            'positionen.leistungstyp.leistungsart',
            'positionen.einsatz.leistungsart',
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

        // Swiss QR-Code generieren (für Seite 2)
        $qrCodeDataUri    = null;
        $qrIbanFormatiert = null;
        $iban = $regionDaten['iban'];

        if ($iban && (float) $rechnung->betrag_total > 0) {
            try {
                $creditorInfo = CreditorInformation::create($iban);

                $qrBill = QrBill::create();
                $qrBill->setCreditorInformation($creditorInfo);
                $qrBill->setCreditor(
                    StructuredAddress::createWithStreet(
                        substr($this->org->name, 0, 70),
                        $this->org->adresse ?? '',
                        null,
                        $this->org->plz ?? '0000',
                        $this->org->ort ?? 'Ort',
                        'CH'
                    )
                );
                $qrBill->setPaymentAmountInformation(
                    PaymentAmountInformation::create('CHF', round((float) $rechnung->betrag_total, 2))
                );
                $qrBill->setPaymentReference(
                    PaymentReference::create(PaymentReference::TYPE_NON)
                );
                $qrBill->setAdditionalInformation(
                    AdditionalInformation::create($rechnung->rechnungsnummer)
                );

                if ($qrBill->isValid()) {
                    $qrCodeDataUri    = $qrBill->getQrCode(SwissQrCode::FILE_FORMAT_PNG)
                        ->getDataUri(SwissQrCode::FILE_FORMAT_PNG);
                    $qrIbanFormatiert = $creditorInfo->getFormattedIban();
                }
            } catch (\Exception $e) {
                // QR-Code nicht kritisch — weiter ohne
            }
        }

        $html = view('pdfs.rechnung', [
            'rechnung'         => $rechnung,
            'org'              => $this->org,
            'regionDaten'      => $regionDaten,
            'logoBase64'       => $logoBase64,
            'logoAusrichtung'  => $this->org->logo_ausrichtung ?? 'links_anschrift_rechts',
            'qrCodeDataUri'    => $qrCodeDataUri,
            'qrIbanFormatiert' => $qrIbanFormatiert,
        ])->render();

        $pdf  = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        $pfad = "pdf_export/{$this->org->id}/rechnung_{$rechnung->rechnungsnummer}.pdf";

        Storage::put($pfad, $pdf->output());
        $rechnung->update(['pdf_pfad' => $pfad]);

        return $pfad;
    }
}
