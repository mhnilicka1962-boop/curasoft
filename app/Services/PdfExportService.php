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
            'positionen.einsatzLeistungsart.leistungsart',
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
        $isTiersPayant     = ($this->org->abrechnungslogik ?? 'tiers_garant') === 'tiers_payant';
        $zeigeRapportblatt = $rapportblattDaten !== null && !$isTiersPayant;
        $zeigeBerechnung   = $rapportblattDaten !== null && $isTiersPayant;

        $klientName = trim(($rechnung->klient->anrede ? $rechnung->klient->anrede . ' ' : '')
            . $rechnung->klient->vorname . ' ' . $rechnung->klient->nachname);

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
            $rbHtml = view('pdfs.rapportblatt', [
                'rechnung'          => $rechnung,
                'org'               => $this->org,
                'regionDaten'       => $regionDaten,
                'klientName'        => $klientName,
                'rapportblattDaten' => $rapportblattDaten,
            ])->render();
            $landscapeBytes = Pdf::loadHTML($rbHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'landscape')->output();
            $finalBytes     = $this->mergePdfs($portraitBytes, $landscapeBytes);
        } elseif ($zeigeBerechnung) {
            $beHtml = view('pdfs.berechnung_anteil', [
                'rechnung'          => $rechnung,
                'klientName'        => $klientName,
                'rapportblattDaten' => $rapportblattDaten,
                'nichtKvgGruppen'   => $this->nichtKvgGruppen($rechnung, $rapportblattDaten),
            ])->render();

            $beilageBytes = Pdf::loadHTML($beHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'portrait')->output();
            $finalBytes   = $this->mergePdfs($portraitBytes, $beilageBytes);
        } else {
            $finalBytes = $portraitBytes;
        }

        // Tagesrapport: Einsätze direkt aus Positionen laden (dedupliziert)
        $einsatzIds = $rechnung->positionen->pluck('einsatz_id')->filter()->unique()->values();
        if ($einsatzIds->isNotEmpty()) {
            $einsaetze = \App\Models\Einsatz::whereIn('id', $einsatzIds)
                ->with(['benutzer', 'einsatzLeistungsarten.leistungsart'])
                ->orderBy('datum')
                ->orderBy('zeit_von')
                ->get();
            $lnHtml = view('pdfs.leistungsnachweis', [
                'rechnung'   => $rechnung,
                'org'        => $this->org,
                'klientName' => $klientName,
                'einsaetze'  => $einsaetze,
            ])->render();
            $lnBytes    = Pdf::loadHTML($lnHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'portrait')->output();
            $finalBytes = $this->mergePdfs($finalBytes, $lnBytes);
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

        $klientName        = trim(($rechnung->klient->anrede ? $rechnung->klient->anrede . ' ' : '') . $rechnung->klient->vorname . ' ' . $rechnung->klient->nachname);
        $rapportblattDaten = $this->rapportblattDaten($rechnung);
        $isTiersPayant     = ($this->org->abrechnungslogik ?? 'tiers_garant') === 'tiers_payant';
        $finalBytes        = $portraitBytes;

        if ($rapportblattDaten !== null && !$isTiersPayant) {
            $rbHtml = view('pdfs.rapportblatt', [
                'rechnung'          => $rechnung,
                'org'               => $this->org,
                'regionDaten'       => $regionDaten,
                'klientName'        => $klientName,
                'rapportblattDaten' => $rapportblattDaten,
            ])->render();
            $finalBytes = $this->mergePdfs($finalBytes, Pdf::loadHTML($rbHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'landscape')->output());
        } elseif ($rapportblattDaten !== null && $isTiersPayant) {
            $beHtml = view('pdfs.berechnung_anteil', [
                'rechnung'          => $rechnung,
                'klientName'        => $klientName,
                'rapportblattDaten' => $rapportblattDaten,
                'nichtKvgGruppen'   => $this->nichtKvgGruppen($rechnung, $rapportblattDaten),
            ])->render();
            $finalBytes = $this->mergePdfs($finalBytes, Pdf::loadHTML($beHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'portrait')->output());
        }

        // Tagesrapport
        $einsatzIds = $rechnung->positionen->pluck('einsatz_id')->filter()->unique()->values();
        if ($einsatzIds->isNotEmpty()) {
            $einsaetze = \App\Models\Einsatz::whereIn('id', $einsatzIds)
                ->with(['benutzer', 'einsatzLeistungsarten.leistungsart'])
                ->orderBy('datum')->orderBy('zeit_von')->get();
            $lnHtml = view('pdfs.leistungsnachweis', [
                'rechnung'   => $rechnung,
                'org'        => $this->org,
                'klientName' => $klientName,
                'einsaetze'  => $einsaetze,
            ])->render();
            $finalBytes = $this->mergePdfs($finalBytes, Pdf::loadHTML($lnHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'portrait')->output());
        }

        return $finalBytes;
    }

    /**
     * Zwei PDF-Byte-Strings (Portrait + Landscape) via FPDI zusammenführen.
     */
    /** Kaufmännische Rundung auf 0.05 CHF */
    private function r5(float $x): float { return round($x * 20) / 20; }

    private function hwGemeindeBetrag(Rechnung $rechnung): float
    {
        $rechnung->klient->loadMissing('aktBeitragHw');
        $beitrag = (float) ($rechnung->klient->aktBeitragHw?->gemeinde_chf_h ?? 0);
        if ($beitrag <= 0) return 0.0;

        $hwId = Leistungsart::where('bezeichnung', 'Hauswirtschaft')->value('id');
        if (!$hwId) return 0.0;

        $total = 0.0;
        foreach ($rechnung->positionen as $pos) {
            $laId = $pos->einsatzLeistungsart?->leistungsart_id ?? $pos->leistungsart_id ?? null;
            if ($laId != $hwId || $pos->einheit !== 'minuten') continue;
            $total += $this->r5((float) $pos->menge / 60 * $beitrag);
        }
        return $this->r5($total);
    }

    /**
     * Gemeinde-Rechnung (Restfinanzierung) als PDF generieren und speichern.
     * Gibt Storage-Pfad zurück.
     */
    public function gemeindeRechnungExportieren(Rechnung $rechnung): string
    {
        $rechnung->loadMissing([
            'klient.aktBeitrag',
            'klient.aktBeitragHw',
            'klient.region',
            'positionen.einsatzLeistungsart.leistungsart',
        ]);

        $rapportDaten = $this->rapportblattDaten($rechnung);
        $hwCHFh       = (float) ($rechnung->klient->aktBeitragHw?->gemeinde_chf_h ?? 0);
        $hwId         = Leistungsart::where('bezeichnung', 'Hauswirtschaft')->value('id');

        // Pro (Datum, Leistungsart) aggregieren
        $posData = [];
        foreach ($rechnung->positionen as $pos) {
            if ($pos->einheit !== 'minuten' || (int) $pos->menge <= 0) continue;
            $laId   = $pos->einsatzLeistungsart?->leistungsart_id ?? $pos->leistungsart_id ?? null;
            if (!$laId) continue;
            $laName = $pos->einsatzLeistungsart?->leistungsart?->bezeichnung ?? '—';
            $d      = $pos->datum->format('Y-m-d');
            if (!isset($posData[$d][$laId])) {
                $posData[$d][$laId] = ['la' => $laName, 'laId' => $laId, 'min' => 0, 'vollkosten' => 0.0, 'kk' => 0.0, 'gemeinde' => 0.0];
            }
            $posData[$d][$laId]['min']       += (int) $pos->menge;
            $posData[$d][$laId]['vollkosten'] += (float) $pos->betrag_patient + (float) $pos->betrag_kk;
            $posData[$d][$laId]['kk']         += (float) $pos->betrag_kk;
        }

        // KVG-Gemeinde pro Tag aus rapportblattDaten — verteilen nach Netto-Anteil
        if ($rapportDaten) {
            foreach ($rapportDaten['tage'] as $tag) {
                $d          = $tag['datum']->format('Y-m-d');
                $dayGemeinde = (float) $tag['gemeinde'];
                if ($dayGemeinde <= 0 || empty($posData[$d])) continue;
                $dayNetto = 0.0;
                foreach ($posData[$d] as $laId => $la) {
                    if ($laId != $hwId) $dayNetto += max(0, $la['vollkosten'] - $la['kk']);
                }
                if ($dayNetto <= 0) continue;
                foreach ($posData[$d] as $laId => &$la) {
                    if ($laId != $hwId) {
                        $netto = max(0, $la['vollkosten'] - $la['kk']);
                        $la['gemeinde'] = $this->r5($dayGemeinde * ($netto / $dayNetto));
                    }
                }
                unset($la);
            }
        }

        // Hauswirtschaft-Gemeinde direkt berechnen
        if ($hwId && $hwCHFh > 0) {
            foreach ($posData as $d => &$dayData) {
                if (isset($dayData[$hwId])) {
                    $dayData[$hwId]['gemeinde'] = $this->r5($dayData[$hwId]['min'] / 60 * $hwCHFh);
                }
            }
            unset($dayData);
        }

        // Flache Zeilen-Liste (sortiert nach Datum, dann LA-Name)
        $zeilen = [];
        ksort($posData);
        foreach ($posData as $d => $dayData) {
            uasort($dayData, fn($a, $b) => strcmp($a['la'], $b['la']));
            foreach ($dayData as $row) {
                $zeilen[] = array_merge(['datum' => \Carbon\Carbon::parse($d)], $row);
            }
        }

        // Totals pro Leistungsart
        $laTotals = [];
        foreach ($zeilen as $z) {
            $la = $z['la'];
            if (!isset($laTotals[$la])) $laTotals[$la] = ['la' => $la, 'min' => 0, 'vollkosten' => 0.0, 'kk' => 0.0, 'gemeinde' => 0.0];
            $laTotals[$la]['min']       += $z['min'];
            $laTotals[$la]['vollkosten'] = $this->r5($laTotals[$la]['vollkosten'] + $z['vollkosten']);
            $laTotals[$la]['kk']         = $this->r5($laTotals[$la]['kk'] + $z['kk']);
            $laTotals[$la]['gemeinde']   = $this->r5($laTotals[$la]['gemeinde'] + $z['gemeinde']);
        }

        $totalGemeinde = $this->r5(array_sum(array_column($laTotals, 'gemeinde')));
        $summen = ['gemeinde' => $totalGemeinde];

        $html = view('pdfs.gemeinde_rechnung', [
            'rechnung'  => $rechnung,
            'org'       => $this->org,
            'zeilen'    => $zeilen,
            'laTotals'  => array_values($laTotals),
            'tage'      => [], // legacy — nicht mehr verwendet
            'summen'   => $summen,
        ])->render();

        $pdf  = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html, 'UTF-8')
            ->setPaper('A4', 'portrait');

        $pfad = 'pdf_export/' . $this->org->id . '/gemeinde_' . $rechnung->rechnungsnummer . '.pdf';
        \Illuminate\Support\Facades\Storage::put($pfad, $pdf->output());

        // betrag_gemeinde auf Rechnung speichern (KVG + Hauswirtschaft)
        $rechnung->update(['betrag_gemeinde' => $summen['gemeinde']]);

        return $pfad;
    }

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
    private function nichtKvgGruppen(Rechnung $rechnung, array $rapportblattDaten): array
    {
        $kvgLaIds = collect($rapportblattDaten['tarife'] ?? [])->pluck('id')->filter()->all();

        $gruppen = [];
        foreach ($rechnung->positionen as $pos) {
            if ($pos->menge <= 0) continue;
            $laId = $pos->einsatzLeistungsart?->leistungsart_id ?? $pos->leistungsart_id ?? null;
            if ($laId === null || in_array($laId, $kvgLaIds)) continue;

            $bezeichnung = $pos->einsatzLeistungsart?->leistungsart?->bezeichnung
                        ?? $pos->beschreibung
                        ?? '—';

            if (!isset($gruppen[$bezeichnung])) {
                $gruppen[$bezeichnung] = ['bezeichnung' => $bezeichnung, 'menge' => 0, 'betrag' => 0.0];
            }
            $gruppen[$bezeichnung]['menge']  += (float) $pos->menge;
            $gruppen[$bezeichnung]['betrag'] += (float) $pos->betrag_patient + (float) $pos->betrag_kk;
        }
        return array_values($gruppen);
    }

    public function rapportblattDaten(Rechnung $rechnung): ?array
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
            // Angehörige: KK zahlt alles via kkasse_angehoerig — kein Gemeinde-Anteil
            $typ = ($pos->leistungserbringer_typ ?? null)
                ?? ($pos->einsatz?->leistungserbringer_typ ?? 'fachperson');
            if ($typ === 'angehoerig') continue;

            $laId = $pos->einsatzLeistungsart?->leistungsart_id ?? $pos->leistungsart_id ?? null;
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
            'positionen.einsatzLeistungsart.leistungsart',
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
        $isTiersPayant     = ($this->org->abrechnungslogik ?? 'tiers_garant') === 'tiers_payant';
        $zeigeRapportblatt = $rapportblattDaten !== null && !$isTiersPayant;
        $zeigeBerechnung   = $rapportblattDaten !== null && $isTiersPayant;

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

        $klientName  = trim(($rechnung->klient->anrede ? $rechnung->klient->anrede . ' ' : '')
            . $rechnung->klient->vorname . ' ' . $rechnung->klient->nachname);
        $finalBytes  = $portraitBytes;

        if ($zeigeRapportblatt) {
            $rbRegionDaten = $rechnung->klient->region_id
                ? $this->org->datenFuerRegion($rechnung->klient->region_id)
                : ['zsr_nr' => ''];

            $rbHtml = view('pdfs.rapportblatt', [
                'rechnung'          => $rechnung,
                'org'               => $this->org,
                'regionDaten'       => $rbRegionDaten,
                'klientName'        => $klientName,
                'rapportblattDaten' => $rapportblattDaten,
            ])->render();

            $finalBytes = $this->mergePdfs($finalBytes,
                Pdf::loadHTML($rbHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'landscape')->output()
            );
        } elseif ($zeigeBerechnung) {
            $beHtml = view('pdfs.berechnung_anteil', [
                'rechnung'          => $rechnung,
                'klientName'        => $klientName,
                'rapportblattDaten' => $rapportblattDaten,
                'nichtKvgGruppen'   => $this->nichtKvgGruppen($rechnung, $rapportblattDaten),
            ])->render();

            $finalBytes = $this->mergePdfs($finalBytes,
                Pdf::loadHTML($beHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'portrait')->output()
            );
        }

        // Tagesrapport (Seite 3)
        $einsatzIds = $rechnung->positionen->pluck('einsatz_id')->filter()->unique()->values();
        if ($einsatzIds->isNotEmpty()) {
            $einsaetze = \App\Models\Einsatz::whereIn('id', $einsatzIds)
                ->with(['benutzer', 'einsatzLeistungsarten.leistungsart'])
                ->orderBy('datum')->orderBy('zeit_von')->get();
            $lnHtml = view('pdfs.leistungsnachweis', [
                'rechnung'   => $rechnung,
                'org'        => $this->org,
                'klientName' => $klientName,
                'einsaetze'  => $einsaetze,
            ])->render();
            $finalBytes = $this->mergePdfs($finalBytes,
                Pdf::loadHTML($lnHtml)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'portrait')->output()
            );
        }

        return $finalBytes;
    }
}
