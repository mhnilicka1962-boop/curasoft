<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 9pt;
        color: #1a1a1a;
        line-height: 1.45;
    }
    .seite {
        padding: 15mm 20mm 20mm 20mm;
    }

    /* ── Header ─────────────────────────────────────────── */
    .kopf-logo {
        max-height: 16mm;
        max-width: 60mm;
        margin-bottom: 3mm;
        display: block;
    }
    .kopf-firma-name {
        font-size: 9.5pt;
        font-weight: bold;
        color: #1e40af;
    }
    .kopf-firma-detail {
        font-size: 7.5pt;
        color: #555;
        line-height: 1.4;
        margin-bottom: 3mm;
    }

    /* ── Anschrift ───────────────────────────────────────── */
    .anschrift {
        font-size: 10pt;
        line-height: 1.75;
        margin-bottom: 8mm;
    }

    /* ── Rechnungsnummer ─────────────────────────────────── */
    .re-nr {
        font-size: 14pt;
        font-weight: bold;
        font-family: DejaVu Sans Mono, monospace;
        color: #1e40af;
        margin-bottom: 4mm;
    }

    /* ── Info-Block ──────────────────────────────────────── */
    table.info-block {
        font-size: 8.5pt;
        line-height: 1.7;
        margin-bottom: 6mm;
        border-collapse: collapse;
    }
    table.info-block td.lbl {
        color: #666;
        padding-right: 6mm;
        white-space: nowrap;
        vertical-align: top;
    }
    table.info-block td.val {
        vertical-align: top;
    }

    /* ── RECHNUNG Titel ──────────────────────────────────── */
    .rechnung-titel {
        font-size: 15pt;
        font-weight: bold;
        color: #1e40af;
        letter-spacing: 1pt;
        border-bottom: 1pt solid #1e40af;
        padding-bottom: 2mm;
        margin-bottom: 4mm;
    }

    /* ── Positionen Tabelle ──────────────────────────────── */
    table.positionen {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 5mm;
        font-size: 8pt;
    }
    table.positionen thead tr {
        background: #1e40af;
        color: #fff;
    }
    table.positionen thead th {
        padding: 2.5mm 3mm;
        text-align: left;
        font-weight: bold;
        font-size: 7.5pt;
        letter-spacing: 0.3pt;
    }
    table.positionen thead th.rechts {
        text-align: right;
    }
    table.positionen tbody tr {
        border-bottom: 0.3pt solid #e5e7eb;
    }
    table.positionen tbody tr:nth-child(even) {
        background: #f8fafc;
    }
    table.positionen tbody td {
        padding: 2mm 3mm;
        vertical-align: middle;
    }
    table.positionen tbody td.rechts {
        text-align: right;
        font-family: DejaVu Sans Mono, monospace;
        font-size: 7.5pt;
    }

    /* ── Totals ──────────────────────────────────────────── */
    .totals-block {
        margin-left: 50%;
        margin-bottom: 6mm;
    }
    table.totals {
        width: 100%;
        border-collapse: collapse;
        font-size: 8.5pt;
    }
    table.totals td {
        padding: 1.5mm 3mm;
    }
    table.totals td.rechts {
        text-align: right;
        font-family: DejaVu Sans Mono, monospace;
    }
    .total-zeile {
        font-weight: bold;
        border-top: 1pt solid #1e40af;
        font-size: 10pt;
        color: #1e40af;
    }

    /* ── Zahlungsinfo ────────────────────────────────────── */
    .zahlung-block {
        border-top: 0.5pt solid #d1d5db;
        padding-top: 4mm;
        margin-bottom: 6mm;
    }
    .zahlung-titel {
        font-size: 8pt;
        font-weight: bold;
        color: #374151;
        margin-bottom: 2mm;
    }
    .zahlung-grid {
        display: table;
        width: 100%;
    }
    .zahlung-col {
        display: table-cell;
        width: 50%;
        vertical-align: top;
        font-size: 8pt;
        line-height: 1.7;
    }
    .iban {
        font-family: DejaVu Sans Mono, monospace;
        font-size: 8.5pt;
        letter-spacing: 0.5pt;
        font-weight: bold;
    }

    /* ── Fusszeile ───────────────────────────────────────── */
    .fusszeile {
        position: fixed;
        bottom: 8mm;
        left: 20mm;
        right: 20mm;
        border-top: 0.3pt solid #d1d5db;
        padding-top: 2mm;
        font-size: 7pt;
        color: #9ca3af;
        text-align: center;
    }

    /* ── QR-Zahlteil (Seite 2) ───────────────────────────── */
    .qr-seite {
        margin: 0;
        padding: 0;
        page-break-before: always;
    }
    .qr-trennlinie {
        text-align: center;
        font-size: 8pt;
        color: #000;
        border-bottom: 0.75pt solid #000;
        padding: 3mm 0;
        margin-bottom: 0;
    }
    .qr-wrap {
        display: table;
        width: 100%;
        border-collapse: collapse;
    }
    .qr-empfang {
        display: table-cell;
        width: 62mm;
        border-right: 0.75pt solid #000;
        padding: 5mm;
        vertical-align: top;
        font-size: 8pt;
    }
    .qr-zahlteil {
        display: table-cell;
        width: 148mm;
        padding: 5mm;
        vertical-align: top;
    }
    .qr-h1 {
        font-size: 11pt;
        font-weight: bold;
        margin-bottom: 5mm;
    }
    .qr-h2 {
        font-size: 6pt;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 1mm;
        margin-top: 3mm;
        color: #000;
    }
    .qr-zahlteil .qr-h2 {
        font-size: 8pt;
        margin-top: 4mm;
    }
    .qr-text-small { font-size: 8pt; line-height: 1.4; }
    .qr-text-large { font-size: 10pt; line-height: 1.4; }
    .qr-inner { display: table; width: 100%; }
    .qr-inner-links {
        display: table-cell;
        width: 51mm;
        vertical-align: top;
    }
    .qr-inner-rechts {
        display: table-cell;
        vertical-align: top;
        padding-left: 5mm;
    }
    .qr-annnahme {
        margin-top: 8mm;
        text-align: right;
        font-size: 6pt;
        font-weight: bold;
        text-transform: uppercase;
    }
</style>
</head>
<body>

@php
    $nurKK       = in_array($rechnung->rechnungstyp ?? 'kombiniert', ['kvg']);
    $nurPatient  = in_array($rechnung->rechnungstyp ?? 'kombiniert', ['klient', 'gemeinde']);
    $beide       = !$nurKK && !$nurPatient;
    $kkLabel     = $rechnung->rechnungstyp === 'gemeinde' ? 'Gemeinde' : 'KK';
    $zahlbarTage = $rechnung->klient->zahlbar_tage ?? 30;
    $klientName  = trim(($rechnung->klient->anrede ? $rechnung->klient->anrede . ' ' : '')
                   . $rechnung->klient->vorname . ' ' . $rechnung->klient->nachname);
@endphp

<div class="seite">

    {{-- ── 1. Header: Logo ODER Firma-Angaben (max 5 Zeilen) ── --}}
    @if($logoBase64)
        <img src="{{ $logoBase64 }}" class="kopf-logo" alt="Logo">
    @else
        <div class="kopf-firma-name">{{ $org->name }}</div>
        @php
            $firmaZeilen = array_filter([
                trim(($org->adresse ?? '') . ($org->plz || $org->ort ? ', ' . $org->plz . ' ' . $org->ort : '')),
                $org->telefon ? 'Tel. ' . $org->telefon : null,
                $org->email ?? null,
                $regionDaten['zsr_nr'] ? 'ZSR: ' . $regionDaten['zsr_nr'] : null,
                $org->mwst_nr ? 'MWST: ' . $org->mwst_nr : null,
            ]);
        @endphp
        <div class="kopf-firma-detail">
            @foreach(array_values($firmaZeilen) as $zeile)
                {{ $zeile }}<br>
            @endforeach
        </div>
    @endif

    {{-- ── 2. Anschrift (an wen die Rechnung geht, 4 Zeilen) ── --}}
    <div class="anschrift">
        @if($rechnung->rechnungstyp === 'kvg' && $rechnung->klient->krankenkassen->isNotEmpty())
            @php $kk = $rechnung->klient->krankenkassen->first(); @endphp
            <strong>{{ $kk->name }}</strong><br>
            @if($kk->adresse){{ $kk->adresse }}<br>@endif
            {{ $kk->plz }} {{ $kk->ort }}
        @else
            @if($rechnung->klient->anrede){{ $rechnung->klient->anrede }}<br>@endif
            <strong>{{ $rechnung->klient->vorname }} {{ $rechnung->klient->nachname }}</strong><br>
            @if($rechnung->klient->adresse){{ $rechnung->klient->adresse }}<br>@endif
            {{ $rechnung->klient->plz }} {{ $rechnung->klient->ort }}
        @endif
    </div>

    {{-- ── 3. Rechnungsnummer ──────────────────────────────── --}}
    <div class="re-nr">{{ $rechnung->rechnungsnummer }}</div>

    {{-- ── 4. Info-Block ───────────────────────────────────── --}}
    <table class="info-block">
        <tr>
            <td class="lbl">Datum Rechnung</td>
            <td class="val">{{ $rechnung->rechnungsdatum->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Versanddatum</td>
            <td class="val">{{ $rechnung->rechnungsdatum->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Leistungen von</td>
            <td class="val">{{ $rechnung->periode_von->format('d.m.Y') }} bis {{ $rechnung->periode_bis->format('d.m.Y') }}</td>
        </tr>
        @if($regionDaten['zsr_nr'])
        <tr>
            <td class="lbl">ZSR-Nr.</td>
            <td class="val">{{ $regionDaten['zsr_nr'] }}</td>
        </tr>
        @endif
        <tr>
            <td class="lbl">Klient</td>
            <td class="val">
                {{ $rechnung->klient->id }}, {{ $klientName }}<br>
                {{ $rechnung->klient->adresse }}, {{ $rechnung->klient->plz }} {{ $rechnung->klient->ort }}
                @if($rechnung->klient->geburtsdatum)
                <br>Geburtsdatum {{ $rechnung->klient->geburtsdatum->format('d.m.Y') }}
                @endif
            </td>
        </tr>
    </table>

    {{-- ── 5. Titel ────────────────────────────────────────── --}}
    <div class="rechnung-titel">
        @if($rechnung->rechnungstyp === 'gemeinde')GEMEINDERECHNUNG
        @else RECHNUNG
        @endif
    </div>

    {{-- ── 6. Positionen (Detail) ──────────────────────────── --}}
    <table class="positionen">
        <thead>
            <tr>
                <th style="width:11%">Datum</th>
                <th style="width:{{ $beide ? '27%' : '40%' }}">Leistung</th>
                <th class="rechts" style="width:8%">Min.</th>
                @if($beide || $nurPatient)
                <th class="rechts" style="width:12%">Tarif Pat.</th>
                <th class="rechts" style="width:13%">Betrag Pat.</th>
                @endif
                @if($beide || $nurKK)
                <th class="rechts" style="width:12%">Tarif {{ $kkLabel }}</th>
                <th class="rechts" style="width:13%">Betrag {{ $kkLabel }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($rechnung->positionen->sortBy('datum') as $pos)
            <tr>
                <td style="font-size:7.5pt;">{{ $pos->datum->format('d.m.Y') }}</td>
                <td>{{ $pos->leistungstyp?->bezeichnung ?? $pos->leistungstyp?->leistungsart?->bezeichnung ?? 'Pflege- und Betreuungsleistung' }}</td>
                <td class="rechts">{{ $pos->menge }}</td>
                @if($beide || $nurPatient)
                <td class="rechts">{{ number_format($pos->tarif_patient, 2, '.', "'") }}</td>
                <td class="rechts">{{ number_format($pos->betrag_patient, 2, '.', "'") }}</td>
                @endif
                @if($beide || $nurKK)
                <td class="rechts">{{ number_format($pos->tarif_kk, 2, '.', "'") }}</td>
                <td class="rechts">{{ number_format($pos->betrag_kk, 2, '.', "'") }}</td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── 7. Totals ───────────────────────────────────────── --}}
    <div class="totals-block">
        <table class="totals">
            @if($beide || $nurPatient)
            <tr>
                <td>Summe Patientenanteil</td>
                <td class="rechts">CHF {{ number_format($rechnung->betrag_patient, 2, '.', "'") }}</td>
            </tr>
            @endif
            @if($beide || $nurKK)
            <tr>
                <td>Summe {{ $kkLabel }}</td>
                <td class="rechts">CHF {{ number_format($rechnung->betrag_kk, 2, '.', "'") }}</td>
            </tr>
            @endif
            <tr class="total-zeile">
                <td>TOTAL</td>
                <td class="rechts">CHF {{ number_format($rechnung->betrag_total, 2, '.', "'") }}</td>
            </tr>
            <tr style="font-weight:bold; font-size:9pt; color:#1a1a1a; border-top:0.5pt solid #d1d5db;">
                <td style="padding-top:2mm;">Unser Guthaben CHF – zahlbar bis {{ $rechnung->rechnungsdatum->addDays($zahlbarTage)->format('d.m.Y') }}</td>
                <td class="rechts" style="padding-top:2mm;">{{ number_format($rechnung->betrag_total, 2, '.', "'") }}</td>
            </tr>
        </table>
    </div>

    {{-- ── 8. Zahlungsinfo ─────────────────────────────────── --}}
    @if(!$qrCodeDataUri && ($regionDaten['iban'] || $regionDaten['postcheckkonto']))
    <div class="zahlung-block">
        <div class="zahlung-titel">Zahlungsanweisung</div>
        <div class="zahlung-grid">
            <div class="zahlung-col">
                @if($regionDaten['iban'])
                    IBAN: <span class="iban">{{ $qrIbanFormatiert ?? $regionDaten['iban'] }}</span><br>
                @endif
                @if($regionDaten['bank']){{ $regionDaten['bank'] }}<br>@endif
                @if($regionDaten['bankadresse']){{ $regionDaten['bankadresse'] }}<br>@endif
                @if($regionDaten['postcheckkonto'])Postkonto: {{ $regionDaten['postcheckkonto'] }}<br>@endif
            </div>
            <div class="zahlung-col">
                Zahlbar: netto {{ $zahlbarTage }} Tage<br>
                Bitte Rechnungsnummer angeben:<br>
                <strong style="font-family: DejaVu Sans Mono, monospace;">{{ $rechnung->rechnungsnummer }}</strong>
            </div>
        </div>
    </div>
    @elseif($qrCodeDataUri)
    <div class="zahlung-block">
        <div style="font-size: 8.5pt;">
            Bitte benutzen Sie den beiliegenden Einzahlungsschein.
        </div>
    </div>
    @endif

</div>

{{-- ── Fusszeile Seite 1 ─────────────────────────────────── --}}
<div class="fusszeile">
    {{ $org->name }}
    @if($org->adresse) · {{ $org->adresse }}, {{ $org->plz }} {{ $org->ort }}@endif
    @if($org->telefon) · Tel. {{ $org->telefon }}@endif
    @if($org->email) · {{ $org->email }}@endif
</div>

{{-- ── Seite 2: QR-Zahlteil ───────────────────────────────── --}}
@if($qrCodeDataUri)
<div class="qr-seite">
    <div class="qr-trennlinie">Vor der Einzahlung abzutrennen ✂</div>
    <div class="qr-wrap">

        {{-- Empfangsschein (links, 62mm) --}}
        <div class="qr-empfang">
            <div class="qr-h1">Empfangsschein</div>
            <div class="qr-h2">Konto / Zahlbar an</div>
            <div class="qr-text-small">
                {{ $qrIbanFormatiert }}<br>
                {{ $org->name }}<br>
                @if($org->adresse){{ $org->adresse }}<br>@endif
                {{ $org->plz }} {{ $org->ort }}
            </div>
            <div class="qr-h2" style="margin-top: 4mm;">Zahlungspflichtiger</div>
            <div class="qr-text-small">
                {{ $rechnung->klient->vorname }} {{ $rechnung->klient->nachname }}<br>
                @if($rechnung->klient->adresse){{ $rechnung->klient->adresse }}<br>@endif
                {{ $rechnung->klient->plz }} {{ $rechnung->klient->ort }}
            </div>
            <div style="margin-top: 5mm; display: table; width: 100%;">
                <div style="display: table-row;">
                    <div style="display: table-cell; font-size: 6pt; font-weight: bold; text-transform: uppercase; padding-right: 5mm;">Währung</div>
                    <div style="display: table-cell; font-size: 6pt; font-weight: bold; text-transform: uppercase;">Betrag</div>
                </div>
                <div style="display: table-row;">
                    <div style="display: table-cell; font-size: 8pt; font-weight: bold; padding-right: 5mm;">CHF</div>
                    <div style="display: table-cell; font-size: 8pt; font-weight: bold;">{{ number_format($rechnung->betrag_total, 2, '.', "'") }}</div>
                </div>
            </div>
            <div class="qr-annnahme">Annahmestelle</div>
        </div>

        {{-- Zahlteil (rechts, 148mm) --}}
        <div class="qr-zahlteil">
            <div class="qr-h1">Zahlteil</div>
            <div class="qr-inner">
                <div class="qr-inner-links">
                    <img src="{{ $qrCodeDataUri }}" style="width: 46mm; height: 46mm; display: block;">
                    <div style="margin-top: 5mm; display: table; width: 100%;">
                        <div style="display: table-row;">
                            <div style="display: table-cell; font-size: 8pt; font-weight: bold; text-transform: uppercase; padding-right: 4mm;">Währung</div>
                            <div style="display: table-cell; font-size: 8pt; font-weight: bold; text-transform: uppercase;">Betrag</div>
                        </div>
                        <div style="display: table-row;">
                            <div style="display: table-cell; font-size: 10pt; font-weight: bold; padding-right: 4mm;">CHF</div>
                            <div style="display: table-cell; font-size: 10pt; font-weight: bold;">{{ number_format($rechnung->betrag_total, 2, '.', "'") }}</div>
                        </div>
                    </div>
                </div>
                <div class="qr-inner-rechts">
                    <div class="qr-h2" style="margin-top: 0;">Konto / Zahlbar an</div>
                    <div class="qr-text-large">
                        {{ $qrIbanFormatiert }}<br>
                        {{ $org->name }}<br>
                        @if($org->adresse){{ $org->adresse }}<br>@endif
                        {{ $org->plz }} {{ $org->ort }}
                    </div>
                    <div class="qr-h2">Zusätzliche Informationen</div>
                    <div class="qr-text-large">{{ $rechnung->rechnungsnummer }}</div>
                    <div class="qr-h2">Zahlungspflichtiger</div>
                    <div class="qr-text-large">
                        {{ $rechnung->klient->vorname }} {{ $rechnung->klient->nachname }}<br>
                        @if($rechnung->klient->adresse){{ $rechnung->klient->adresse }}<br>@endif
                        {{ $rechnung->klient->plz }} {{ $rechnung->klient->ort }}
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endif

</body>
</html>
