<?php

namespace App\Services;

use App\Models\Leistungsart;
use App\Models\Leistungsregion;
use App\Models\Organisation;
use App\Models\Rechnung;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
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
            'klient.aktBeitrag',
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

        $rapportblattDaten = $this->rapportblattDaten($rechnung);
        $zeigeRapportblatt = $rapportblattDaten !== null
            && ($this->org->abrechnungslogik ?? 'tiers_garant') !== 'tiers_payant';

        $html = view('pdfs.rechnung', [
            'rechnung'         => $rechnung,
            'org'              => $this->org,
            'regionDaten'      => $regionDaten,
            'logoBase64'       => $logoBase64,
            'logoAusrichtung'  => $this->org->logo_ausrichtung ?? 'links_anschrift_rechts',
            'qrCodeDataUri'    => $qrCodeDataUri,
            'qrIbanFormatiert' => $qrIbanFormatiert,
        ])->render();

        $portraitBytes = Pdf::loadHTML($html)->setPaper('A4', 'portrait')->output();

        if ($zeigeRapportblatt) {
            $klientName = trim(($rechnung->klient->anrede ? $rechnung->klient->anrede . ' ' : '')
                . $rechnung->klient->vorname . ' ' . $rechnung->klient->nachname);

            $rbHtml = view('pdfs.rapportblatt', [
                'rechnung'          => $rechnung,
                'org'               => $this->org,
                'regionDaten'       => $regionDaten,
                'klientName'        => $klientName,
                'rapportblattDaten' => $rapportblattDaten,
            ])->render();

            $landscapeBytes = Pdf::loadHTML($rbHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'landscape')->output();
            $finalBytes     = $this->mergePdfs($portraitBytes, $landscapeBytes);
        } else {
            $finalBytes = $portraitBytes;
        }

        $pfad = "pdf_export/{$this->org->id}/rechnung_{$rechnung->rechnungsnummer}.pdf";
        Storage::put($pfad, $finalBytes);
        $rechnung->update(['pdf_pfad' => $pfad]);

        return $pfad;
    }

    /**
     * Provisorische Vorschau-PDF für eine nicht gespeicherte Rechnung.
     * Gibt die PDF-Bytes zurück (kein Speichern, kein QR-Code).
     */
    public function provisorischExportieren(Rechnung $rechnung): string
    {
        $logoBase64 = null;
        if ($this->org->logo_pfad) {
            $logoPfad = public_path($this->org->logo_pfad);
            if (file_exists($logoPfad)) {
                $mime       = mime_content_type($logoPfad);
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPfad));
            }
        }

        $regionDaten = $rechnung->klient->region_id
            ? $this->org->datenFuerRegion($rechnung->klient->region_id)
            : ['zsr_nr' => $this->org->zsr_nr ?? '', 'iban' => '', 'bank' => '', 'bankadresse' => '', 'postcheckkonto' => '', 'esr' => '', 'qr_iban' => ''];

        $html = view('pdfs.rechnung', [
            'rechnung'         => $rechnung,
            'org'              => $this->org,
            'regionDaten'      => $regionDaten,
            'logoBase64'       => $logoBase64,
            'logoAusrichtung'  => $this->org->logo_ausrichtung ?? 'links_anschrift_rechts',
            'qrCodeDataUri'    => null,
            'qrIbanFormatiert' => null,
            'provisorisch'     => true,
        ])->render();

        $portraitBytes = Pdf::loadHTML($html)->setPaper('A4', 'portrait')->output();

        $rapportblattDaten = $this->rapportblattDaten($rechnung);
        if ($rapportblattDaten !== null) {
            $klientName = trim($rechnung->klient->vorname . ' ' . $rechnung->klient->nachname);
            $rbHtml = view('pdfs.rapportblatt', [
                'rechnung'          => $rechnung,
                'org'               => $this->org,
                'regionDaten'       => $regionDaten,
                'klientName'        => $klientName,
                'rapportblattDaten' => $rapportblattDaten,
            ])->render();
            $landscapeBytes = Pdf::loadHTML($rbHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'landscape')->output();
            return $this->mergePdfs($portraitBytes, $landscapeBytes);
        }

        return $portraitBytes;
    }

    /**
     * Zwei PDF-Byte-Strings (Portrait + Landscape) via FPDI zusammenführen.
     */
    /** Kaufmännische Rundung auf 0.05 CHF */
    private function r5(float $x): float { return round($x * 20) / 20; }

    private function mergePdfs(string $portraitBytes, string $landscapeBytes): string
    {
        // Temp-Dateien — FPDI benötigt Dateipfade
        $tmpA = tempnam(sys_get_temp_dir(), 'pdf_a_');
        $tmpB = tempnam(sys_get_temp_dir(), 'pdf_b_');
        file_put_contents($tmpA, $portraitBytes);
        file_put_contents($tmpB, $landscapeBytes);

        try {
            $fpdi = new Fpdi();
            $fpdi->SetAutoPageBreak(false);

            // Seiten aus Portrait-PDF
            $countA = $fpdi->setSourceFile($tmpA);
            for ($i = 1; $i <= $countA; $i++) {
                $tpl = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($tpl);
                $fpdi->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
                $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
            }

            // Seiten aus Landscape-PDF
            $countB = $fpdi->setSourceFile($tmpB);
            for ($i = 1; $i <= $countB; $i++) {
                $tpl = $fpdi->importPage($i);
                $size = $fpdi->getTemplateSize($tpl);
                $fpdi->AddPage($size['width'] > $size['height'] ? 'L' : 'P', [$size['width'], $size['height']]);
                $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
            }

            return $fpdi->Output('', 'S');
        } finally {
            @unlink($tmpA);
            @unlink($tmpB);
        }
    }

    /**
     * Daten für Rapportblatt aufbereiten.
     * Alle Tage der Periode werden zurückgegeben (auch leere).
     * Gibt null zurück wenn nicht zutreffend (Pauschale, kein Kanton).
     */
    private function rapportblattDaten(Rechnung $rechnung): ?array
    {
        $positionen = $rechnung->positionen;
        if ($positionen->isEmpty()) return null;
        if ($positionen->every(fn($p) => in_array($p->einheit, ['tage', 'pauschal']))) return null;

        $regionId = $rechnung->klient->region_id;
        if (!$regionId) return null;

        $keys = [
            'abkl' => 'Abklärung/Beratung',
            'unt'  => 'Untersuchung Behandlung',
            'gp'   => 'Grundpflege',
        ];

        // Leistungsarten-IDs
        $laMap = Leistungsart::whereIn('bezeichnung', array_values($keys))->pluck('id', 'bezeichnung');

        // Neueste Leistungsregion-Tarife pro Leistungsart × Region
        $tarife = [];
        foreach ($keys as $k => $name) {
            $laId = $laMap[$name] ?? null;
            $lr   = $laId
                ? Leistungsregion::where('leistungsart_id', $laId)
                    ->where('region_id', $regionId)
                    ->orderByDesc('gueltig_ab')
                    ->first()
                : null;
            $tarife[$k] = [
                'id'     => $laId,
                'ansatz' => $lr ? (float)$lr->ansatz : 0.0,
                'kkasse' => $lr ? (float)$lr->kkasse : 0.0,
            ];
        }

        // aktBeitrag
        $rechnung->klient->loadMissing('aktBeitrag');
        $beitrag      = $rechnung->klient->aktBeitrag;
        $ansatzKunde  = $beitrag ? (float)$beitrag->ansatz_kunde  : 0.0;
        $limitProzent = $beitrag ? (float)$beitrag->limit_restbetrag_prozent : 100.0;

        // Positionen nach Datum × Leistungsart aggregieren
        $tagesMin = [];
        foreach ($positionen as $pos) {
            $laId = $pos->einsatz?->leistungsart_id ?? null;
            if (!$laId) continue;
            $key = null;
            foreach ($keys as $k => $name) {
                if ($tarife[$k]['id'] == $laId) { $key = $k; break; }
            }
            if (!$key) continue;
            $dateStr = $pos->datum->format('Y-m-d');
            $tagesMin[$dateStr] = ($tagesMin[$dateStr] ?? ['abkl' => 0, 'unt' => 0, 'gp' => 0]);
            $tagesMin[$dateStr][$key] += (int)$pos->menge;
        }

        // Alle Tage der Periode (auch leere)
        $tage   = [];
        $summen = array_fill_keys([
            'abkl_min','unt_min','gp_min',
            'taxe_abkl','taxe_unt','taxe_gp',
            'kvg_abkl','kvg_unt','kvg_gp',
            'netto','pat','gemeinde',
        ], 0.0);

        $current = $rechnung->periode_von->copy()->startOfDay();
        $end     = $rechnung->periode_bis->copy()->startOfDay();

        while ($current <= $end) {
            $dateStr  = $current->format('Y-m-d');
            $minuten  = $tagesMin[$dateStr] ?? ['abkl' => 0, 'unt' => 0, 'gp' => 0];

            $taxeAbkl = $this->r5($minuten['abkl'] * $tarife['abkl']['ansatz'] / 60);
            $taxeUnt  = $this->r5($minuten['unt']  * $tarife['unt']['ansatz']  / 60);
            $taxeGp   = $this->r5($minuten['gp']   * $tarife['gp']['ansatz']   / 60);
            $kvgAbkl  = $this->r5($minuten['abkl'] * $tarife['abkl']['kkasse'] / 60);
            $kvgUnt   = $this->r5($minuten['unt']  * $tarife['unt']['kkasse']  / 60);
            $kvgGp    = $this->r5($minuten['gp']   * $tarife['gp']['kkasse']   / 60);
            $netto    = $this->r5(($taxeAbkl + $taxeUnt + $taxeGp) - ($kvgAbkl + $kvgUnt + $kvgGp));

            $limitBetrag = $this->r5($limitProzent / 100 * $netto);
            $patLimit    = $ansatzKunde > 0 && $limitBetrag < $ansatzKunde;
            $pat         = $ansatzKunde > 0 ? ($patLimit ? $limitBetrag : $ansatzKunde) : 0.0;
            $pat         = max(0.0, min($this->r5($pat), $netto));
            $gemeinde    = max(0.0, $this->r5($netto - $pat));

            $tage[] = [
                'datum'     => $current->copy(),
                'abkl_min'  => $minuten['abkl'],
                'unt_min'   => $minuten['unt'],
                'gp_min'    => $minuten['gp'],
                'taxe_abkl' => $taxeAbkl,
                'taxe_unt'  => $taxeUnt,
                'taxe_gp'   => $taxeGp,
                'kvg_abkl'  => $kvgAbkl,
                'kvg_unt'   => $kvgUnt,
                'kvg_gp'    => $kvgGp,
                'netto'     => $netto,
                'pat'       => $pat,
                'gemeinde'  => $gemeinde,
                'pat_limit' => $patLimit,
            ];

            $summen['abkl_min']  += $minuten['abkl'];
            $summen['unt_min']   += $minuten['unt'];
            $summen['gp_min']    += $minuten['gp'];
            $summen['taxe_abkl'] += $taxeAbkl;
            $summen['taxe_unt']  += $taxeUnt;
            $summen['taxe_gp']   += $taxeGp;
            $summen['kvg_abkl']  += $kvgAbkl;
            $summen['kvg_unt']   += $kvgUnt;
            $summen['kvg_gp']    += $kvgGp;
            $summen['netto']     += $netto;
            $summen['pat']       += $pat;
            $summen['gemeinde']  += $gemeinde;

            $current->addDay();
        }

        foreach (['taxe_abkl','taxe_unt','taxe_gp','kvg_abkl','kvg_unt','kvg_gp','netto','pat','gemeinde'] as $f) {
            $summen[$f] = $this->r5($summen[$f]);
        }

        return [
            'tage'    => $tage,
            'tarife'  => $tarife,
            'beitrag' => ['ansatz_kunde' => $ansatzKunde, 'limit_prozent' => $limitProzent],
            'summen'  => $summen,
        ];
    }

    /**
     * PDF direkt als String rendern — ohne speichern, für Vorschau.
     */
    public function rechnungAlsPdfString(Rechnung $rechnung): string
    {
        $rechnung->loadMissing([
            'klient.region',
            'klient.krankenkassen.krankenkasse',
            'klient.adressen',
            'klient.aktBeitrag',
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

        $logoBase64 = null;
        if ($this->org->logo_pfad) {
            $logoPfad = public_path($this->org->logo_pfad);
            if (file_exists($logoPfad)) {
                $mime       = mime_content_type($logoPfad);
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPfad));
            }
        }

        $rapportblattDaten = $this->rapportblattDaten($rechnung);
        $zeigeRapportblatt = $rapportblattDaten !== null
            && ($this->org->abrechnungslogik ?? 'tiers_garant') !== 'tiers_payant';

        $html = view('pdfs.rechnung', [
            'rechnung'         => $rechnung,
            'org'              => $this->org,
            'regionDaten'      => $regionDaten,
            'logoBase64'       => $logoBase64,
            'logoAusrichtung'  => $this->org->logo_ausrichtung ?? 'links_anschrift_rechts',
            'qrCodeDataUri'    => null,
            'qrIbanFormatiert' => null,
        ])->render();

        $portraitBytes = Pdf::loadHTML($html)->setPaper('A4', 'portrait')->output();

        if ($zeigeRapportblatt) {
            $klientName = trim(($rechnung->klient->anrede ? $rechnung->klient->anrede . ' ' : '')
                . $rechnung->klient->vorname . ' ' . $rechnung->klient->nachname);

            $regionDaten = $rechnung->klient->region_id
                ? $this->org->datenFuerRegion($rechnung->klient->region_id)
                : ['zsr_nr' => ''];

            $rbHtml = view('pdfs.rapportblatt', [
                'rechnung'          => $rechnung,
                'org'               => $this->org,
                'regionDaten'       => $regionDaten,
                'klientName'        => $klientName,
                'rapportblattDaten' => $rapportblattDaten,
            ])->render();

            $landscapeBytes = Pdf::loadHTML($rbHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'landscape')->output();
            return $this->mergePdfs($portraitBytes, $landscapeBytes);
        }

        return $portraitBytes;
    }
}
