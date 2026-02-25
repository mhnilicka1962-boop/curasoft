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
        padding: 18mm 18mm 15mm 25mm;
    }

    /* ── Kopfzeile ─────────────────────────────────────── */
    .kopf {
        display: table;
        width: 100%;
        margin-bottom: 8mm;
    }
    .kopf-links {
        display: table-cell;
        width: 55%;
        vertical-align: top;
    }
    .kopf-rechts {
        display: table-cell;
        width: 45%;
        vertical-align: top;
        text-align: right;
    }
    .kopf-mitte {
        display: table-cell;
        width: 100%;
        text-align: center;
        vertical-align: top;
    }
    .org-name {
        font-size: 13pt;
        font-weight: bold;
        color: #1e40af;
        margin-bottom: 2mm;
    }
    .org-detail {
        font-size: 8pt;
        color: #555;
        line-height: 1.5;
    }
    .logo {
        max-height: 18mm;
        max-width: 55mm;
    }
    .logo-mitte {
        max-height: 20mm;
        max-width: 80mm;
    }

    /* ── Rechnungs-Titel ────────────────────────────────── */
    .titel-block {
        margin-bottom: 6mm;
        border-bottom: 0.5pt solid #d1d5db;
        padding-bottom: 4mm;
    }
    .titel {
        font-size: 16pt;
        font-weight: bold;
        color: #1e40af;
        letter-spacing: 1pt;
        margin-bottom: 3mm;
    }
    .meta-grid {
        display: table;
        width: 100%;
    }
    .meta-col {
        display: table-cell;
        width: 33%;
    }
    .meta-label {
        font-size: 7pt;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5pt;
    }
    .meta-wert {
        font-size: 9pt;
        font-weight: bold;
    }

    /* ── Adressen ───────────────────────────────────────── */
    .adressen {
        display: table;
        width: 100%;
        margin-bottom: 6mm;
    }
    .adresse-block {
        display: table-cell;
        width: 50%;
        vertical-align: top;
    }
    .adresse-titel {
        font-size: 7pt;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5pt;
        margin-bottom: 1.5mm;
        border-bottom: 0.3pt solid #e5e7eb;
        padding-bottom: 1mm;
    }
    .adresse-inhalt {
        font-size: 9pt;
        line-height: 1.6;
    }

    /* ── Periode ────────────────────────────────────────── */
    .periode-zeile {
        background: #eff6ff;
        border: 0.5pt solid #bfdbfe;
        border-radius: 2pt;
        padding: 2.5mm 4mm;
        font-size: 8.5pt;
        margin-bottom: 5mm;
        color: #1e40af;
    }

    /* ── Positionen Tabelle ─────────────────────────────── */
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

    /* ── Totals ─────────────────────────────────────────── */
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

    /* ── Zahlungsinfo ───────────────────────────────────── */
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

    /* ── Fusszeile (Seite 1) ────────────────────────────── */
    .fusszeile {
        position: fixed;
        bottom: 8mm;
        left: 25mm;
        right: 18mm;
        border-top: 0.3pt solid #d1d5db;
        padding-top: 2mm;
        font-size: 7pt;
        color: #9ca3af;
        text-align: center;
    }

    /* ── QR-Zahlteil (Seite 2) ──────────────────────────── */
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
    .qr-text-small {
        font-size: 8pt;
        line-height: 1.4;
    }
    .qr-text-large {
        font-size: 10pt;
        line-height: 1.4;
    }
    .qr-inner {
        display: table;
        width: 100%;
    }
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
    .qr-betrag-row {
        display: table;
        width: 100%;
        margin-top: 5mm;
    }
    .qr-betrag-col {
        display: table-cell;
        vertical-align: top;
    }
    .qr-box {
        border: 0.75pt solid #000;
        width: 52mm;
        height: 20mm;
    }
    .qr-annnahme {
        margin-top: 8mm;
        text-align: right;
        font-size: 6pt;
        font-weight: bold;
        text-transform: uppercase;
    }

    /* ── Kein Logo Placeholder ──────────────────────────── */
    .org-kuerzel {
        font-size: 20pt;
        font-weight: bold;
        color: #dbeafe;
        letter-spacing: 2pt;
    }
</style>
</head>
<body>

@php
    $nurKK      = in_array($rechnung->rechnungstyp ?? 'kombiniert', ['kvg']);
    $nurPatient = in_array($rechnung->rechnungstyp ?? 'kombiniert', ['klient', 'gemeinde']);
    $beide      = !$nurKK && !$nurPatient;
    $kkLabel    = $rechnung->rechnungstyp === 'gemeinde' ? 'Gemeinde' : 'KK';
    $zahlbarTage = $rechnung->klient->zahlbar_tage ?? 30;
@endphp

<div class="seite">

    {{-- ── Kopfzeile je nach logo_ausrichtung ──────────── --}}
    @if($logoAusrichtung === 'mitte_anschrift_fusszeile')
    {{-- Logo zentriert, Adresse in Fusszeile --}}
    <div class="kopf">
        <div class="kopf-mitte">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="logo-mitte" alt="Logo">
            @else
                <div class="org-name">{{ $org->name }}</div>
            @endif
        </div>
    </div>

    @elseif($logoAusrichtung === 'rechts_anschrift_links')
    {{-- Logo rechts, Angaben links --}}
    <div class="kopf">
        <div class="kopf-links">
            <div class="org-name">{{ $org->name }}</div>
            <div class="org-detail">
                @if($org->adresse){{ $org->adresse }}<br>@endif
                @if($org->plz || $org->ort){{ $org->plz }} {{ $org->ort }}<br>@endif
                @if($org->telefon)Tel. {{ $org->telefon }}<br>@endif
                @if($org->email){{ $org->email }}<br>@endif
                @if($regionDaten['zsr_nr'])ZSR: {{ $regionDaten['zsr_nr'] }}<br>@endif
                @if($org->mwst_nr)MWST: {{ $org->mwst_nr }}@endif
            </div>
        </div>
        <div class="kopf-rechts">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="logo" alt="Logo">
            @else
                <div class="org-kuerzel">{{ strtoupper(substr($org->name, 0, 4)) }}</div>
            @endif
        </div>
    </div>

    @else
    {{-- Standard: Logo links, Angaben rechts (links_anschrift_rechts) --}}
    <div class="kopf">
        <div class="kopf-links">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="logo" alt="Logo">
            @else
                <div class="org-kuerzel">{{ strtoupper(substr($org->name, 0, 4)) }}</div>
            @endif
        </div>
        <div class="kopf-rechts">
            <div class="org-name">{{ $org->name }}</div>
            <div class="org-detail">
                @if($org->adresse){{ $org->adresse }}<br>@endif
                @if($org->plz || $org->ort){{ $org->plz }} {{ $org->ort }}<br>@endif
                @if($org->telefon)Tel. {{ $org->telefon }}<br>@endif
                @if($org->email){{ $org->email }}<br>@endif
                @if($regionDaten['zsr_nr'])ZSR: {{ $regionDaten['zsr_nr'] }}<br>@endif
                @if($org->mwst_nr)MWST: {{ $org->mwst_nr }}@endif
            </div>
        </div>
    </div>
    @endif

    {{-- ── Rechnungs-Titel ─────────────────────────────── --}}
    <div class="titel-block">
        <div class="titel">
            @if($rechnung->rechnungstyp === 'kvg') RECHNUNG AN KRANKENKASSE
            @elseif($rechnung->rechnungstyp === 'gemeinde') GEMEINDERECHNUNG
            @else RECHNUNG
            @endif
        </div>
        <div class="meta-grid">
            <div class="meta-col">
                <div class="meta-label">Rechnungsnummer</div>
                <div class="meta-wert" style="font-family: DejaVu Sans Mono, monospace;">{{ $rechnung->rechnungsnummer }}</div>
            </div>
            <div class="meta-col">
                <div class="meta-label">Rechnungsdatum</div>
                <div class="meta-wert">{{ $rechnung->rechnungsdatum->format('d.m.Y') }}</div>
            </div>
            <div class="meta-col">
                <div class="meta-label">Zahlbar bis</div>
                <div class="meta-wert">{{ $rechnung->rechnungsdatum->addDays($zahlbarTage)->format('d.m.Y') }}</div>
            </div>
        </div>
    </div>

    {{-- ── Adressen ─────────────────────────────────────── --}}
    <div class="adressen">
        <div class="adresse-block">
            <div class="adresse-titel">Rechnungsempfänger</div>
            <div class="adresse-inhalt">
                @if($rechnung->klient->anrede)<strong>{{ $rechnung->klient->anrede }}</strong><br>@endif
                <strong>{{ $rechnung->klient->vorname }} {{ $rechnung->klient->nachname }}</strong><br>
                @if($rechnung->klient->adresse){{ $rechnung->klient->adresse }}<br>@endif
                {{ $rechnung->klient->plz }} {{ $rechnung->klient->ort }}
                @if($rechnung->klient->geburtsdatum)<br><span style="color:#888; font-size:7.5pt;">geb. {{ $rechnung->klient->geburtsdatum->format('d.m.Y') }}</span>@endif
            </div>
        </div>
        <div class="adresse-block">
            <div class="adresse-titel">Leistungserbringer</div>
            <div class="adresse-inhalt">
                <strong>{{ $org->name }}</strong><br>
                @if($org->adresse){{ $org->adresse }}<br>@endif
                @if($org->plz || $org->ort){{ $org->plz }} {{ $org->ort }}@endif
            </div>
        </div>
    </div>

    {{-- ── Abrechnungsperiode ───────────────────────────── --}}
    <div class="periode-zeile">
        Abrechnungsperiode: <strong>{{ $rechnung->periode_von->format('d.m.Y') }}</strong>
        bis <strong>{{ $rechnung->periode_bis->format('d.m.Y') }}</strong>
        @if($rechnung->klient->region)
            &nbsp;·&nbsp; Kanton: <strong>{{ $rechnung->klient->region->kuerzel }}</strong>
        @endif
    </div>

    {{-- ── Positionen ──────────────────────────────────── --}}
    <table class="positionen">
        <thead>
            <tr>
                <th style="width:12%">Datum</th>
                <th style="width:{{ $beide ? '25%' : '38%' }}">Leistung</th>
                <th class="rechts" style="width:9%">Min.</th>
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
                <td>{{ $pos->datum->format('d.m.Y') }}</td>
                <td>{{ $pos->leistungstyp?->bezeichnung ?? $pos->leistungstyp?->leistungsart?->bezeichnung ?? '—' }}</td>
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

    {{-- ── Totals ──────────────────────────────────────── --}}
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
        </table>
    </div>

    {{-- ── Zahlungsinfo (ohne QR) ──────────────────────── --}}
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
        <div class="zahlung-titel">Zahlungsanweisung</div>
        <div style="font-size: 8pt; color: #555;">
            Zahlung per QR-Rechnung — siehe Seite 2 (Zahlteil / Empfangsschein).<br>
            Bitte Rechnungsnummer <strong>{{ $rechnung->rechnungsnummer }}</strong> angeben.
            Zahlbar netto {{ $zahlbarTage }} Tage.
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

{{-- ── Seite 2: QR-Zahlteil (Swiss QR-Rechnung) ──────────── --}}
@if($qrCodeDataUri)
<div class="qr-seite">

    {{-- Trennlinie --}}
    <div class="qr-trennlinie">Vor der Einzahlung abzutrennen ✂</div>

    {{-- Zahlteil + Empfangsschein --}}
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
                {{-- Links: QR-Code + Währung/Betrag --}}
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

                {{-- Rechts: Infos --}}
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
