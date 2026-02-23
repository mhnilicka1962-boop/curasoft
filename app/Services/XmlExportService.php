<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Rechnung;
use Illuminate\Support\Facades\Storage;

/**
 * XML-Export Service — Swiss Spitex Rechnungsformat 450.100
 *
 * Spezifikation: forum-datenaustausch.ch / generalInvoiceRequest_450.100.xsd
 *
 * Tarif 311 = KVG Spitex-Pflegeleistungen
 * Einheit: Minuten (KLV Abrechnung im 5-Minuten-Takt)
 */
class XmlExportService
{
    private Organisation $org;

    public function __construct(Organisation $org)
    {
        $this->org = $org;
    }

    /**
     * XML für eine Rechnung generieren und speichern.
     */
    public function rechnungExportieren(Rechnung $rechnung): string
    {
        $rechnung->loadMissing([
            'klient.region',
            'klient.diagnosen',
            'klient.krankenkassen.krankenkasse',
            'positionen.einsatz.verordnung',
            'positionen.einsatz.leistungsart',
        ]);

        if (!$rechnung->klient) {
            throw new \RuntimeException('Klient nicht geladen.');
        }

        $xml = $this->xmlErstellen($rechnung);

        $verzeichnis = "xml_export/{$this->org->id}";
        $dateiname   = "rechnung_{$rechnung->rechnungsnummer}_{$rechnung->rechnungsdatum?->format('Ymd')}.xml";
        $pfad        = "{$verzeichnis}/{$dateiname}";

        Storage::put($pfad, $xml);

        $rechnung->update([
            'xml_export_datum' => now(),
            'xml_export_pfad'  => $pfad,
        ]);

        return $pfad;
    }

    private function xmlErstellen(Rechnung $rechnung): string
    {
        $klient     = $rechnung->klient;
        $org        = $this->org;
        $positionen = $rechnung->positionen;

        // KVG-Krankenkasse des Klienten
        $kkZuweisung = $klient->krankenkassen
            ->where('versicherungs_typ', 'kvg')
            ->where('aktiv', true)
            ->first();
        $kkEan           = $kkZuweisung?->krankenkasse?->ean_nr ?? '';
        $versichertenNr  = $kkZuweisung?->versichertennummer ?? '';
        $tiersPayant     = $kkZuweisung?->tiers_payant ?? true;

        // ZSR kantonal oder global
        $zsrNr = $klient->region_id
            ? ($org->datenFuerRegion($klient->region_id)['zsr_nr'] ?? $org->zsr_nr)
            : $org->zsr_nr;

        $kanton = $klient->region?->kuerzel ?? 'CH';

        // Totale
        $totalKk      = $positionen->sum('betrag_kk');
        $totalPatient = $positionen->sum('betrag_patient');
        $totalGesamt  = $positionen->sum(fn($p) => ($p->betrag_kk ?? 0) + ($p->betrag_patient ?? 0));

        $fälligAm = $rechnung->rechnungsdatum?->addDays(30)->format('Y-m-d') ?? '';

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        // ── Root ──────────────────────────────────────────────────────────────
        $root = $dom->createElement('generalInvoiceRequest');
        $root->setAttribute('xmlns', 'http://www.forum-datenaustausch.ch/invoice');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xsi:schemaLocation', 'http://www.forum-datenaustausch.ch/invoice generalInvoiceRequest_450.100.xsd');
        $root->setAttribute('modus', 'production');
        $root->setAttribute('language', 'de');
        $dom->appendChild($root);

        // ── Payload ───────────────────────────────────────────────────────────
        $payload = $dom->createElement('payload');
        $payload->setAttribute('type', 'invoice');
        $payload->setAttribute('storno', 'false');
        $root->appendChild($payload);

        // ── Invoice Header ────────────────────────────────────────────────────
        $invoice = $dom->createElement('invoice');
        $invoice->setAttribute('request_timestamp', now()->format('c'));
        $invoice->setAttribute('request_id', (string) $rechnung->id);
        $invoice->setAttribute('invoice_id', $rechnung->rechnungsnummer ?? (string) $rechnung->id);
        $payload->appendChild($invoice);

        // ── Body ──────────────────────────────────────────────────────────────
        $body = $dom->createElement('body');
        $body->setAttribute('role_title', 'Leistungserbringer');
        $payload->appendChild($body);

        // ── Tiers payant / Tiers garant ───────────────────────────────────────
        $tiersEl = $dom->createElement($tiersPayant ? 'tiers_payant' : 'tiers_garant');
        $body->appendChild($tiersEl);

        // ── Biller (Rechnungssteller = Spitex-Org) ────────────────────────────
        $biller = $dom->createElement('biller');
        $biller->setAttribute('zsr', $zsrNr ?? '');
        $biller->setAttribute('ean_party', $org->ean_nr ?? '');
        $tiersEl->appendChild($biller);
        $this->adresseAnhaengen($dom, $biller, $org->name, $org->adresse, $org->plz, $org->ort);

        // ── Provider (Leistungserbringer = gleiche Org) ───────────────────────
        // specialty: 37 = Spitex (Fachperson), 39 = Angehörigenpflege (informelle Pflege)
        $hatAngehoerigEinsatz = $positionen->contains(
            fn($p) => ($p->einsatz?->leistungserbringer_typ ?? 'fachperson') === 'angehoerig'
        );
        $specialty = $hatAngehoerigEinsatz ? '39' : '37';

        $provider = $dom->createElement('provider');
        $provider->setAttribute('zsr', $zsrNr ?? '');
        $provider->setAttribute('ean_party', $org->ean_nr ?? '');
        $provider->setAttribute('specialty', $specialty);
        $tiersEl->appendChild($provider);
        $this->adresseAnhaengen($dom, $provider, $org->name, $org->adresse, $org->plz, $org->ort);

        // ── Insurance (Krankenkasse) ──────────────────────────────────────────
        $insurance = $dom->createElement('insurance');
        $insurance->setAttribute('ean_party', $kkEan);
        $tiersEl->appendChild($insurance);

        // ── Patient ───────────────────────────────────────────────────────────
        $einsatzAdresse = $klient->adressen?->firstWhere('adressart', 'einsatzort')
                       ?? $klient->adressen?->first();

        $patient = $dom->createElement('patient');
        $patient->setAttribute('gender', $klient->geschlecht === 'w' ? 'female' : ($klient->geschlecht === 'm' ? 'male' : 'unknown'));
        $patient->setAttribute('birthdate', $klient->geburtsdatum?->format('Y-m-d') ?? '');
        $patient->setAttribute('ssn', $klient->ahv_nr ?? '');           // AHV-Nummer
        $patient->setAttribute('insured_id', $versichertenNr);           // Versichertennummer
        $patient->setAttribute('insurance_id', $versichertenNr);
        $tiersEl->appendChild($patient);

        $person = $dom->createElement('person');
        $patient->appendChild($person);
        $fn = $dom->createElement('familyname');
        $fn->appendChild($dom->createTextNode($this->s($klient->nachname)));
        $person->appendChild($fn);
        $gn = $dom->createElement('givenname');
        $gn->appendChild($dom->createTextNode($this->s($klient->vorname)));
        $person->appendChild($gn);

        // Patientenadresse
        $patPostal = $dom->createElement('postal');
        $patient->appendChild($patPostal);
        $this->postalFelder($dom, $patPostal,
            $einsatzAdresse?->strasse ?? $klient->adresse ?? '',
            $einsatzAdresse?->plz     ?? $klient->plz     ?? '',
            $einsatzAdresse?->ort     ?? $klient->ort      ?? ''
        );

        // ── KVG ───────────────────────────────────────────────────────────────
        $kvg = $dom->createElement('kvg');
        $tiersEl->appendChild($kvg);

        // Treatment (Behandlungsperiode)
        $treatment = $dom->createElement('treatment');
        $treatment->setAttribute('date_begin', $rechnung->periode_von?->format('Y-m-d') ?? '');
        $treatment->setAttribute('date_end',   $rechnung->periode_bis?->format('Y-m-d') ?? '');
        $treatment->setAttribute('canton',     $kanton);
        $treatment->setAttribute('reason',     'disease');
        $treatment->setAttribute('type',       'spitex');
        $kvg->appendChild($treatment);

        // Diagnosen (ICD-10)
        foreach ($klient->diagnosen as $diag) {
            $diagEl = $dom->createElement('diagnosis');
            $diagEl->setAttribute('code',   $diag->icd10_code ?? '');
            $diagEl->setAttribute('system', 'ICD');
            $diagEl->setAttribute('type',   $diag->diagnose_typ === 'haupt' ? 'main' : 'secondary');
            $treatment->appendChild($diagEl);
        }

        // ── Services ──────────────────────────────────────────────────────────
        $services = $dom->createElement('services');
        $kvg->appendChild($services);

        $session = 1;
        foreach ($positionen as $pos) {
            $einsatz = $pos->einsatz;
            $la      = $einsatz?->leistungsart;

            // Minuten ermitteln: aus Einsatz oder Menge
            $minuten = $einsatz?->minuten ?? $pos->menge ?? 0;

            // Tarif pro Minute berechnen (KK-Anteil)
            $tarif_kk_std = (float) ($pos->tarif_kk ?? 0);
            // tarif_kk ist CHF/h → pro Minute = /60
            $unitPrice = $tarif_kk_std > 0 ? round($tarif_kk_std / 60, 4) : 0;

            $service = $dom->createElement('service');
            $service->setAttribute('tariff_type',    '311');
            $service->setAttribute('code',           $la?->tarmed_code ?? '00.0010');
            $service->setAttribute('session',        (string) $session++);
            $service->setAttribute('date_begin',     $pos->datum?->format('Y-m-d') ?? ($rechnung->periode_von?->format('Y-m-d') ?? ''));
            $service->setAttribute('quantity',       (string) $minuten);
            $service->setAttribute('unit',           'min');
            $service->setAttribute('unit_factor',    '1.00');
            $service->setAttribute('unit_price',     number_format($unitPrice, 4, '.', ''));
            $service->setAttribute('amount',         number_format((float) ($pos->betrag_kk ?? 0), 2, '.', ''));
            $service->setAttribute('ean_responsible', $org->ean_nr ?? '');
            $service->setAttribute('ean_provider',    $org->ean_nr ?? '');
            if ($la) {
                $service->setAttribute('name', $this->s($la->bezeichnung));
            }
            // Verordnung referenzieren falls vorhanden
            if ($einsatz?->verordnung_id) {
                $service->setAttribute('obligation', $einsatz->verordnung?->verordnungs_nr ?? '');
            }
            $services->appendChild($service);
        }

        // ── Invoice Info ──────────────────────────────────────────────────────
        $invoiceInfo = $dom->createElement('invoice_info');
        $invoiceInfo->setAttribute('invoice_date',         $rechnung->rechnungsdatum?->format('Y-m-d') ?? '');
        $invoiceInfo->setAttribute('due_date',             $fälligAm);
        $invoiceInfo->setAttribute('amount_due',           number_format($tiersPayant ? $totalKk : $totalGesamt, 2, '.', ''));
        $invoiceInfo->setAttribute('amount_obligations',   number_format($totalGesamt, 2, '.', ''));
        $invoiceInfo->setAttribute('amount_prepaid',       number_format($tiersPayant ? $totalPatient : 0, 2, '.', ''));
        $tiersEl->appendChild($invoiceInfo);

        return $dom->saveXML();
    }

    // ── Hilfsmethoden ─────────────────────────────────────────────────────────

    private function adresseAnhaengen(\DOMDocument $dom, \DOMElement $parent, string $name, ?string $strasse, ?string $plz, ?string $ort): void
    {
        $company = $dom->createElement('company');
        $company->setAttribute('company_name', $this->s($name));
        $parent->appendChild($company);

        $postal = $dom->createElement('postal');
        $company->appendChild($postal);
        $this->postalFelder($dom, $postal, $strasse ?? '', $plz ?? '', $ort ?? '');
    }

    private function postalFelder(\DOMDocument $dom, \DOMElement $parent, string $strasse, string $plz, string $ort): void
    {
        $street = $dom->createElement('street');
        $street->appendChild($dom->createTextNode($this->s($strasse)));
        $parent->appendChild($street);

        $zipEl = $dom->createElement('zip');
        $zipEl->appendChild($dom->createTextNode($this->s($plz)));
        $parent->appendChild($zipEl);

        $city = $dom->createElement('city');
        $city->appendChild($dom->createTextNode($this->s($ort)));
        $parent->appendChild($city);
    }

    private function s(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
