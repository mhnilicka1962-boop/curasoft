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
    line-height: 1.5;
}
.seite { padding: 15mm 20mm 25mm 20mm; }

/* ── Kopfzeile ───────────────────────────────────────────── */
.kopf { display: table; width: 100%; margin-bottom: 6mm; }
.kopf-links { display: table-cell; vertical-align: middle; width: 50%; }
.kopf-rechts {
    display: table-cell; vertical-align: middle; width: 50%;
    text-align: right; font-size: 7pt; line-height: 1.5; color: #555;
}
.kopf-logo { max-height: 12mm; max-width: 50mm; display: block; }
.kopf-firma { font-size: 8pt; font-weight: bold; color: #1a1a1a; }

/* ── Anschrift ───────────────────────────────────────────── */
/* margin-top positioniert Anschrift ins Couvert-Fenster (ca. 50mm ab Seitenrand) */
.anschrift { font-size: 8.5pt; line-height: 1.5; margin-top: 10mm; margin-bottom: 14mm; min-height: 16mm; }

/* ── Rechnung-Titel ──────────────────────────────────────── */
.rechnung-nr { font-size: 9.5pt; font-weight: bold; margin-bottom: 3mm; }

/* ── Info-Block ──────────────────────────────────────────── */
table.info { font-size: 7pt; border-collapse: collapse; margin-bottom: 4mm; line-height: 1.2; }
table.info td.lbl {
    color: #555; padding-right: 6mm; padding-bottom: 0;
    white-space: nowrap; min-width: 26mm; vertical-align: top;
}
table.info td.val { padding-bottom: 0; vertical-align: top; }

/* ── Abschnitt ───────────────────────────────────────────── */
.abschnitt { margin-bottom: 4mm; }
.abschnitt-kopf {
    font-size: 6.5pt; font-weight: bold; text-transform: uppercase;
    color: #999; letter-spacing: 0.5pt;
    border-top: 0.4pt solid #ccc; padding-top: 1.5mm; margin-bottom: 2mm;
}
table.abschnitt-info { font-size: 8pt; border-collapse: collapse; }
table.abschnitt-info td.lbl {
    color: #555; padding-right: 8mm; padding-bottom: 0.75mm;
    min-width: 34mm; vertical-align: top;
}
table.abschnitt-info td.val { padding-bottom: 0.75mm; }

/* ── Positionen ──────────────────────────────────────────── */
table.positionen { width: 100%; border-collapse: collapse; margin-bottom: 5mm; font-size: 8pt; }
table.positionen thead tr { border-bottom: 0.75pt solid #333; }
table.positionen thead th {
    padding: 2mm 2.5mm; text-align: left;
    font-weight: bold; font-size: 7.5pt; color: #333;
}
table.positionen thead th.r { text-align: right; }
table.positionen tbody tr { border-bottom: 0.3pt solid #eee; }
table.positionen tbody tr:nth-child(even) { background: #fafafa; }
table.positionen tbody td { padding: 1.5mm 2.5mm; vertical-align: middle; }
table.positionen tbody td.r {
    text-align: right;
    font-family: DejaVu Sans Mono, monospace;
    font-size: 7.5pt;
}

/* ── Totals ──────────────────────────────────────────────── */
.totals-block { margin-left: 55%; margin-bottom: 6mm; }
table.totals { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
table.totals td { padding: 1.5mm 2.5mm; }
table.totals td.r { text-align: right; font-family: DejaVu Sans Mono, monospace; }
.total-zeile { font-weight: bold; border-top: 1pt solid #333; }
.total-zeile td { padding-top: 2mm; font-size: 10pt; }
.zahlbar-zeile td {
    border-top: 0.5pt solid #bbb; padding-top: 2mm;
    font-size: 8.5pt; font-weight: bold;
}

/* ── Zahlungsinfo ────────────────────────────────────────── */
.zahlung-block { margin-top: 3mm; font-size: 8pt; line-height: 1.7; color: #333; }

/* ── Fusszeile ───────────────────────────────────────────── */
.fusszeile {
    position: fixed; bottom: 8mm; left: 20mm; right: 20mm;
    border-top: 0.3pt solid #d1d5db; padding-top: 2mm;
    font-size: 7pt; color: #9ca3af; text-align: center;
}

/* ── QR-Zahlteil (Seite 2) ───────────────────────────────── */
.qr-seite { margin: 0; padding: 0; page-break-before: always; }
.qr-trennlinie {
    text-align: center; font-size: 8pt; color: #000;
    border-bottom: 0.75pt solid #000; padding: 3mm 0;
}
.qr-wrap { display: table; width: 100%; border-collapse: collapse; }
.qr-empfang {
    display: table-cell; width: 62mm;
    border-right: 0.75pt solid #000; padding: 5mm;
    vertical-align: top; font-size: 8pt;
}
.qr-zahlteil { display: table-cell; width: 148mm; padding: 5mm; vertical-align: top; }
.qr-h1 { font-size: 11pt; font-weight: bold; margin-bottom: 5mm; }
.qr-h2 { font-size: 6pt; font-weight: bold; text-transform: uppercase; margin-bottom: 1mm; margin-top: 3mm; }
.qr-zahlteil .qr-h2 { font-size: 8pt; margin-top: 4mm; }
.qr-text-small { font-size: 8pt; line-height: 1.4; }
.qr-text-large { font-size: 10pt; line-height: 1.4; }
.qr-inner { display: table; width: 100%; }
.qr-inner-links { display: table-cell; width: 51mm; vertical-align: top; }
.qr-inner-rechts { display: table-cell; vertical-align: top; padding-left: 5mm; }
.qr-annahme { margin-top: 8mm; text-align: right; font-size: 6pt; font-weight: bold; text-transform: uppercase; }
</style>
</head>
<body>

@php
    $nurKK       = $rechnung->rechnungstyp === 'kvg';
    $nurPatient  = in_array($rechnung->rechnungstyp, ['klient', 'gemeinde']);
    $beide       = !$nurKK && !$nurPatient;
    $kkLabel     = $rechnung->rechnungstyp === 'gemeinde' ? 'Gemeinde' : 'KK';
    $zahlbarTage = $rechnung->klient->zahlbar_tage ?? 30;
    $zahlbarBis  = $rechnung->rechnungsdatum->addDays($zahlbarTage)->format('d.m.Y');
    $klientName  = trim(($rechnung->klient->anrede ? $rechnung->klient->anrede . ' ' : '')
                   . $rechnung->klient->vorname . ' ' . $rechnung->klient->nachname);

    // Anschrift: KVG→KK, sonst Rechnungsadresse > Einsatzadresse > Hauptadresse
    $adrRechnung = $rechnung->klient->adressen->firstWhere('adressart', 'rechnung');
    $adrEinsatz  = $rechnung->klient->adressen->firstWhere('adressart', 'einsatzort');

    if ($nurKK && $rechnung->klient->krankenkassen->isNotEmpty()) {
        $kk = $rechnung->klient->krankenkassen->first();
        $anschriftAnrede = null;
        $anschriftName   = $kk->krankenkasse->name ?? '';
        $anschriftStr    = $kk->krankenkasse->adresse ?? '';
        $anschriftOrt    = trim(($kk->krankenkasse->plz ?? '') . ' ' . ($kk->krankenkasse->ort ?? ''));
    } elseif ($adrRechnung) {
        $anschriftAnrede = null;
        $anschriftName   = $adrRechnung->nachname ?? $klientName;
        $anschriftStr    = $adrRechnung->strasse ?? '';
        $anschriftOrt    = trim(($adrRechnung->plz ?? '') . ' ' . ($adrRechnung->ort ?? ''));
    } elseif ($adrEinsatz) {
        $anschriftAnrede = $rechnung->klient->anrede;
        $anschriftName   = $klientName;
        $anschriftStr    = $adrEinsatz->strasse ?? '';
        $anschriftOrt    = trim(($adrEinsatz->plz ?? '') . ' ' . ($adrEinsatz->ort ?? ''));
    } else {
        $anschriftAnrede = $rechnung->klient->anrede;
        $anschriftName   = $klientName;
        $anschriftStr    = $rechnung->klient->adresse ?? '';
        $anschriftOrt    = trim(($rechnung->klient->plz ?? '') . ' ' . ($rechnung->klient->ort ?? ''));
    }

    // Positionen: Pauschale (alle einheit=tage) vs. Einzelleistungen
    $positionen  = $rechnung->positionen->sortBy('datum');
    $istPauschale = $positionen->isNotEmpty() && $positionen->every(fn($p) => $p->einheit === 'tage');
@endphp

<div class="seite">

    {{-- 1. Kopfzeile ───────────────────────────────────────── --}}
    <div class="kopf">
        <div class="kopf-links">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="kopf-logo" alt="Logo">
            @else
                <div class="kopf-firma">{{ $org->name }}</div>
            @endif
        </div>
        <div class="kopf-rechts">
            @if(!$logoBase64){{ $org->name }} · @endif{{ $org->adresse }}, {{ $org->plz }} {{ $org->ort }}<br>
            Tel. {{ $org->telefon }}@if($org->email) · {{ $org->email }}@endif
        </div>
    </div>

    {{-- 3. Anschrift ────────────────────────────────────────── --}}
    <div class="anschrift">
        @if($anschriftAnrede ?? null)<span>{{ $anschriftAnrede }}</span><br>@endif
        <strong>{{ $anschriftName }}</strong><br>
        @if($anschriftStr){{ $anschriftStr }}<br>@endif
        {{ $anschriftOrt }}
    </div>

    {{-- 4. Rechnung-Titel + Nummer ──────────────────────────── --}}
    <div class="rechnung-nr">
        {{ $rechnung->rechnungstyp === 'gemeinde' ? 'GEMEINDERECHNUNG' : 'RECHNUNG' }} — Nr. {{ $rechnung->rechnungsnummer }}
    </div>

    {{-- 5. Info-Block ───────────────────────────────────────── --}}
    <table class="info">
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
            <td class="val">{{ $rechnung->periode_von->format('d.m.Y') }} – {{ $rechnung->periode_bis->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">ZSR-Nr.</td>
            <td class="val">{{ $regionDaten['zsr_nr'] ?: '—' }}</td>
        </tr>
        <tr>
            <td class="lbl">Klient</td>
            <td class="val">
                {{ $rechnung->klient->id }}, {{ $klientName }},
                {{ $rechnung->klient->adresse }}, {{ $rechnung->klient->plz }} {{ $rechnung->klient->ort }}
            </td>
        </tr>
        @if($rechnung->klient->geburtsdatum)
        <tr>
            <td class="lbl">Geburtsdatum</td>
            <td class="val">{{ $rechnung->klient->geburtsdatum->format('d.m.Y') }}</td>
        </tr>
        @endif
        <tr>
            <td class="lbl">Zahlbar bis</td>
            <td class="val"><strong>{{ $zahlbarBis }}</strong></td>
        </tr>
    </table>

    {{-- 6. Positionen ───────────────────────────────────────── --}}
    <div class="abschnitt">

        @if($istPauschale)
        {{-- Pauschale: einfache 4-Spalten-Tabelle ──────────── --}}
        <table class="positionen">
            <thead>
                <tr>
                    <th style="width: 50%">Leistung / Beschreibung</th>
                    <th class="r" style="width: 10%">Tage</th>
                    <th class="r" style="width: 18%">Ansatz CHF</th>
                    <th class="r" style="width: 18%">Total CHF</th>
                </tr>
            </thead>
            <tbody>
                @foreach($positionen as $pos)
                @php
                    $bezeichnung = $pos->einsatz?->leistungsart?->bezeichnung
                                ?? $pos->leistungstyp?->bezeichnung
                                ?? 'Tagespauschale';
                    $betrag = $pos->betrag_patient + $pos->betrag_kk;
                    $ansatz = $pos->menge > 0 ? round($betrag / $pos->menge, 2) : 0;
                @endphp
                <tr>
                    <td>
                        {{ $bezeichnung }}<br>
                        <span style="font-size: 7.5pt; color: #555;">
                            {{ $pos->datum->format('d.m.Y') }}
                            @if($pos->einsatz?->datum_bis) – {{ $pos->einsatz->datum_bis->format('d.m.Y') }}@endif
                        </span>
                    </td>
                    <td class="r">{{ number_format($pos->menge, 0) }}</td>
                    <td class="r">{{ number_format($ansatz, 2, '.', "'") }}</td>
                    <td class="r">{{ number_format($betrag, 2, '.', "'") }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @else
        {{-- Einzelleistungen: Datum + Min + Tarife ─────────── --}}
        <table class="positionen">
            <thead>
                <tr>
                    <th style="width: 11%">Datum</th>
                    <th style="width: {{ $beide ? '28%' : '40%' }}">Leistung</th>
                    <th class="r" style="width: 8%">Min.</th>
                    @if($beide || $nurPatient)
                    <th class="r" style="width: 11%">Tarif Pat.</th>
                    <th class="r" style="width: 12%">Betrag Pat.</th>
                    @endif
                    @if($beide || $nurKK)
                    <th class="r" style="width: 11%">Tarif {{ $kkLabel }}</th>
                    <th class="r" style="width: 12%">Betrag {{ $kkLabel }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($positionen as $pos)
                @php
                    $bezeichnung = $pos->einsatz?->leistungsart?->bezeichnung
                                ?? $pos->leistungstyp?->bezeichnung
                                ?? 'Pflege- und Betreuungsleistung';
                @endphp
                <tr>
                    <td style="font-size: 7.5pt;">{{ $pos->datum->format('d.m.Y') }}</td>
                    <td>{{ $bezeichnung }}</td>
                    <td class="r">{{ $pos->menge }}</td>
                    @if($beide || $nurPatient)
                    <td class="r">{{ number_format($pos->tarif_patient, 2, '.', "'") }}</td>
                    <td class="r">{{ number_format($pos->betrag_patient, 2, '.', "'") }}</td>
                    @endif
                    @if($beide || $nurKK)
                    <td class="r">{{ number_format($pos->tarif_kk, 2, '.', "'") }}</td>
                    <td class="r">{{ number_format($pos->betrag_kk, 2, '.', "'") }}</td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- 8. Totals ───────────────────────────────────────────── --}}
    <div class="totals-block">
        <table class="totals">
            @if($beide || $nurPatient)
            <tr>
                <td>Summe Patientenanteil</td>
                <td class="r">CHF {{ number_format($rechnung->betrag_patient, 2, '.', "'") }}</td>
            </tr>
            @endif
            @if($beide || $nurKK)
            <tr>
                <td>Summe {{ $kkLabel }}</td>
                <td class="r">CHF {{ number_format($rechnung->betrag_kk, 2, '.', "'") }}</td>
            </tr>
            @endif
            <tr class="total-zeile">
                <td>TOTAL</td>
                <td class="r">CHF {{ number_format($rechnung->betrag_total, 2, '.', "'") }}</td>
            </tr>
            <tr class="zahlbar-zeile">
                <td>Unser Guthaben CHF — zahlbar bis {{ $zahlbarBis }}</td>
                <td class="r">{{ number_format($rechnung->betrag_total, 2, '.', "'") }}</td>
            </tr>
        </table>
    </div>

    {{-- 9. Zahlungsinfo ─────────────────────────────────────── --}}
    <div class="zahlung-block">
        @if($qrCodeDataUri)
            Bitte benutzen Sie den beiliegenden Einzahlungsschein.
            Bitte Rechnungsnummer <strong>{{ $rechnung->rechnungsnummer }}</strong> angeben.
        @elseif($regionDaten['iban'] || $regionDaten['postcheckkonto'])
            @if($regionDaten['iban'])IBAN: <strong>{{ $qrIbanFormatiert ?? $regionDaten['iban'] }}</strong>@endif
            @if($regionDaten['bank']) · {{ $regionDaten['bank'] }}@endif
            @if($regionDaten['postcheckkonto']) · Postkonto: {{ $regionDaten['postcheckkonto'] }}@endif
            · Zahlbar netto {{ $zahlbarTage }} Tage
            · Bitte Rechnungsnummer angeben: <strong>{{ $rechnung->rechnungsnummer }}</strong>
        @endif
    </div>

</div>

{{-- Fusszeile ──────────────────────────────────────────────── --}}
<div class="fusszeile">
    {{ $org->name }}
    @if($org->adresse) · {{ $org->adresse }}, {{ $org->plz }} {{ $org->ort }}@endif
    @if($org->telefon) · Tel. {{ $org->telefon }}@endif
    @if($org->email) · {{ $org->email }}@endif
</div>

{{-- Seite 2: QR-Zahlteil ───────────────────────────────────── --}}
@if($qrCodeDataUri)
<div class="qr-seite">
    <div class="qr-trennlinie">Vor der Einzahlung abzutrennen ✂</div>
    <div class="qr-wrap">

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
            <div class="qr-annahme">Annahmestelle</div>
        </div>

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
