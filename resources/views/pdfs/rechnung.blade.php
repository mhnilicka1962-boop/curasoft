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

    /* ── Fusszeile ──────────────────────────────────────── */
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
<div class="seite">

    {{-- ── Kopfzeile ─────────────────────────────────── --}}
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

    {{-- ── Rechnungs-Titel ─────────────────────────────── --}}
    <div class="titel-block">
        <div class="titel">RECHNUNG</div>
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
                <div class="meta-wert">{{ $rechnung->rechnungsdatum->addDays($rechnung->klient->zahlbar_tage ?? 30)->format('d.m.Y') }}</div>
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
                <th style="width:30%">Leistung</th>
                <th class="rechts" style="width:9%">Min.</th>
                <th class="rechts" style="width:12%">Tarif Pat.</th>
                <th class="rechts" style="width:12%">Betrag Pat.</th>
                <th class="rechts" style="width:12%">Tarif KK</th>
                <th class="rechts" style="width:13%">Betrag KK</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rechnung->positionen->sortBy('datum') as $pos)
            <tr>
                <td>{{ $pos->datum->format('d.m.Y') }}</td>
                <td>{{ $pos->leistungstyp?->bezeichnung ?? $pos->leistungstyp?->leistungsart?->bezeichnung ?? '—' }}</td>
                <td class="rechts">{{ $pos->menge }}</td>
                <td class="rechts">{{ number_format($pos->tarif_patient, 2, '.', "'") }}</td>
                <td class="rechts">{{ number_format($pos->betrag_patient, 2, '.', "'") }}</td>
                <td class="rechts">{{ number_format($pos->tarif_kk, 2, '.', "'") }}</td>
                <td class="rechts">{{ number_format($pos->betrag_kk, 2, '.', "'") }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Totals ──────────────────────────────────────── --}}
    <div class="totals-block">
        <table class="totals">
            <tr>
                <td>Summe Patientenanteil</td>
                <td class="rechts">CHF {{ number_format($rechnung->betrag_patient, 2, '.', "'") }}</td>
            </tr>
            <tr>
                <td>Summe Krankenversicherung</td>
                <td class="rechts">CHF {{ number_format($rechnung->betrag_kk, 2, '.', "'") }}</td>
            </tr>
            <tr class="total-zeile">
                <td>TOTAL</td>
                <td class="rechts">CHF {{ number_format($rechnung->betrag_total, 2, '.', "'") }}</td>
            </tr>
        </table>
    </div>

    {{-- ── Zahlungsinfo ────────────────────────────────── --}}
    @if($regionDaten['iban'] || $regionDaten['postcheckkonto'])
    <div class="zahlung-block">
        <div class="zahlung-titel">Zahlungsanweisung</div>
        <div class="zahlung-grid">
            <div class="zahlung-col">
                @if($regionDaten['iban'])
                    IBAN: <span class="iban">{{ $regionDaten['iban'] }}</span><br>
                @endif
                @if($regionDaten['bank'])
                    {{ $regionDaten['bank'] }}<br>
                @endif
                @if($regionDaten['bankadresse'])
                    {{ $regionDaten['bankadresse'] }}<br>
                @endif
                @if($regionDaten['postcheckkonto'])
                    Postkonto: {{ $regionDaten['postcheckkonto'] }}<br>
                @endif
            </div>
            <div class="zahlung-col">
                Zahlbar: netto {{ $rechnung->klient->zahlbar_tage ?? 30 }} Tage<br>
                Bitte Rechnungsnummer angeben:<br>
                <strong style="font-family: DejaVu Sans Mono, monospace;">{{ $rechnung->rechnungsnummer }}</strong>
            </div>
        </div>
    </div>
    @endif

</div>

{{-- ── Fusszeile ────────────────────────────────────────── --}}
<div class="fusszeile">
    {{ $org->name }}
    @if($org->adresse) · {{ $org->adresse }}, {{ $org->plz }} {{ $org->ort }}@endif
    @if($org->telefon) · Tel. {{ $org->telefon }}@endif
    @if($org->email) · {{ $org->email }}@endif
</div>

</body>
</html>
