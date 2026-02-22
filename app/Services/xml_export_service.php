<?php

namespace App\Services;

use App\Models\Organisation;
use App\Models\Rechnung;
use Illuminate\Support\Facades\Storage;

/**
 * XML-Export Service für Spitex-Abrechnung (Swiss eCare / Tarmed 450.100)
 *
 * Erzeugt eine XML-Datei gemäss Swiss Spitex Rechnungsformat
 * für die elektronische Einreichung bei Krankenkassen.
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
     * Gibt den Dateipfad zurück.
     */
    public function rechnungExportieren(Rechnung $rechnung): string
    {
        $klient = $rechnung->klient;
        if (!$klient) {
            throw new \RuntimeException('Klient nicht geladen.');
        }

        $rechnung->loadMissing(['positionen.leistungsart', 'klient.region']);

        $xml = $this->xmlErstellen($rechnung);

        $verzeichnis = "xml_export/{$this->org->id}";
        $dateiname   = "rechnung_{$rechnung->rechnung_nr}_{$rechnung->datum?->format('Ymd')}.xml";
        $pfad        = "{$verzeichnis}/{$dateiname}";

        Storage::put($pfad, $xml);

        // Pfad und Datum auf Rechnung speichern
        $rechnung->update([
            'xml_export_datum' => now(),
            'xml_export_pfad'  => $pfad,
        ]);

        return $pfad;
    }

    /**
     * XML-Inhalt erstellen (Swiss Spitex 450.100 Format — vereinfacht)
     */
    private function xmlErstellen(Rechnung $rechnung): string
    {
        $klient  = $rechnung->klient;
        $org     = $this->org;
        $positionen = $rechnung->positionen;

        // Absender-ZSR (kantonal oder global)
        $zsrNr = $klient->region_id
            ? ($org->datenFuerRegion($klient->region_id)['zsr_nr'] ?: $org->zsr_nr)
            : $org->zsr_nr;

        $totalBetrag = $positionen->sum('betrag');
        $kkEan = $klient->krankenkassen()
            ->with('krankenkasse')
            ->where('versicherungs_typ', 'kvg')
            ->where('aktiv', true)
            ->first()
            ?->krankenkasse
            ?->ean_nr ?? '';

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $root = $dom->createElement('medicalInvoice');
        $root->setAttribute('xmlns', 'http://www.forum-datenaustausch.ch/invoice');
        $root->setAttribute('xsi:schemaLocation', 'http://www.forum-datenaustausch.ch/invoice generalInvoiceRequest_450.100.xsd');
        $root->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $root->setAttribute('modus', 'production');
        $dom->appendChild($root);

        // Payload
        $payload = $dom->createElement('payload');
        $payload->setAttribute('type', 'invoice');
        $payload->setAttribute('storno', 'false');
        $root->appendChild($payload);

        // Invoice
        $invoice = $dom->createElement('invoice');
        $invoice->setAttribute('request_timestamp', now()->format('c'));
        $invoice->setAttribute('request_id', (string) $rechnung->id);
        $invoice->setAttribute('invoice_id', (string) ($rechnung->rechnung_nr ?? $rechnung->id));
        $payload->appendChild($invoice);

        // Sender (Spitex-Organisation)
        $sender = $dom->createElement('processing');
        $invoice->appendChild($sender);

        $senderParty = $dom->createElement('sender');
        $senderParty->setAttribute('zsr', $zsrNr ?? '');
        $senderParty->setAttribute('ean_party', '');
        $sender->appendChild($senderParty);

        $senderComp = $dom->createElement('company');
        $senderComp->setAttribute('company_name', $org->name);
        $senderComp->setAttribute('postal', $org->adresse ?? '');
        $senderComp->setAttribute('zip', $org->plz ?? '');
        $senderComp->setAttribute('city', $org->ort ?? '');
        $senderParty->appendChild($senderComp);

        // Receiver (Krankenkasse)
        $receiver = $dom->createElement('receiver');
        $receiver->setAttribute('ean_party', $kkEan);
        $sender->appendChild($receiver);

        // Insurance (Patient / Krankenkasse)
        $insurance = $dom->createElement('insurance');
        $insurance->setAttribute('ean_party', $kkEan);
        $payload->appendChild($insurance);

        // Patient
        $patient = $dom->createElement('patient');
        $patient->setAttribute('family_name', $klient->nachname);
        $patient->setAttribute('given_name', $klient->vorname);
        $patient->setAttribute('birthdate', $klient->geburtsdatum?->format('Y-m-d') ?? '');
        $patient->setAttribute('sex', $klient->geschlecht === 'w' ? 'female' : ($klient->geschlecht === 'm' ? 'male' : 'unknown'));
        $patient->setAttribute('ahv_number', $klient->ahv_nr ?? '');
        $patient->setAttribute('insurance_number', '');
        $payload->appendChild($patient);

        // Services
        $services = $dom->createElement('services');
        $payload->appendChild($services);

        foreach ($positionen as $pos) {
            $service = $dom->createElement('service');
            $service->setAttribute('tariff_type', '311'); // Spitex-Tarif
            $service->setAttribute('code', $pos->leistungsart?->tarmed_code ?? '00.0010');
            $service->setAttribute('name', $this->xmlSicher($pos->leistungsart?->bezeichnung ?? 'Leistung'));
            $service->setAttribute('session_date', $rechnung->datum?->format('Y-m-d') ?? '');
            $service->setAttribute('quantity', $pos->menge);
            $service->setAttribute('unit_factor', '1.0');
            $service->setAttribute('unit_price', number_format($pos->betrag / max($pos->menge, 1), 2, '.', ''));
            $service->setAttribute('amount', number_format($pos->betrag, 2, '.', ''));
            $services->appendChild($service);
        }

        // Total
        $totals = $dom->createElement('invoice_info');
        $totals->setAttribute('invoice_date', $rechnung->datum?->format('Y-m-d') ?? '');
        $totals->setAttribute('due_date', $rechnung->faellig_am?->format('Y-m-d') ?? '');
        $totals->setAttribute('amount_due', number_format($totalBetrag, 2, '.', ''));
        $totals->setAttribute('amount_obligations', number_format($totalBetrag, 2, '.', ''));
        $payload->appendChild($totals);

        return $dom->saveXML();
    }

    private function xmlSicher(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
