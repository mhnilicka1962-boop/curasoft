<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 8pt;
    color: #1a1a1a;
    line-height: 1.4;
}
.seite { padding: 12mm 20mm 22mm 20mm; }

/* ── Kopfzeile ───────────────────────────────────────────── */
.kopf { display: table; width: 100%; margin-bottom: 5mm; }
.kopf-links { display: table-cell; vertical-align: middle; width: 50%; }
.kopf-rechts {
    display: table-cell; vertical-align: middle; width: 50%;
    text-align: right; font-size: 6.5pt; line-height: 1.4; color: #555;
}
.kopf-logo { max-height: 20mm; max-width: 70mm; display: block; }
.kopf-firma { font-size: 7.5pt; font-weight: bold; color: #1a1a1a; }

/* ── Anschrift ───────────────────────────────────────────── */
.anschrift { font-size: 7.5pt; line-height: 1.4; margin-top: 7mm; margin-bottom: 8mm; min-height: 14mm; }

/* ── Rechnung-Titel ──────────────────────────────────────── */
.rechnung-nr { font-size: 8.5pt; font-weight: bold; margin-bottom: 2mm; }

/* ── Info-Block ──────────────────────────────────────────── */
table.info { font-size: 6.5pt; border-collapse: collapse; margin-bottom: 3mm; line-height: 1.2; }
table.info td.lbl {
    color: #555; padding-right: 5mm; padding-bottom: 0;
    white-space: nowrap; min-width: 24mm; vertical-align: top;
}
table.info td.val { padding-bottom: 0; vertical-align: top; }

/* ── Abschnitt ───────────────────────────────────────────── */
.abschnitt { margin-bottom: 3mm; }
.abschnitt-kopf {
    font-size: 6pt; font-weight: bold; text-transform: uppercase;
    color: #999; letter-spacing: 0.5pt;
    border-top: 0.4pt solid #ccc; padding-top: 1mm; margin-bottom: 1.5mm;
}
table.abschnitt-info { font-size: 7.5pt; border-collapse: collapse; }
table.abschnitt-info td.lbl {
    color: #555; padding-right: 6mm; padding-bottom: 0.5mm;
    min-width: 30mm; vertical-align: top;
}
table.abschnitt-info td.val { padding-bottom: 0.5mm; }

/* ── Positionen ──────────────────────────────────────────── */
table.positionen { width: 100%; border-collapse: collapse; margin-bottom: 3mm; font-size: 7pt; }
table.positionen thead tr { border-bottom: 0.75pt solid #333; }
table.positionen thead th {
    padding: 1.5mm 2mm; text-align: left;
    font-weight: bold; font-size: 6.5pt; color: #333;
}
table.positionen thead th.r { text-align: right; }
table.positionen tbody tr { border-bottom: 0.3pt solid #eee; }
table.positionen tbody tr:nth-child(even) { background: #fafafa; }
table.positionen tbody td { padding: 1mm 2mm; vertical-align: middle; }
table.positionen tbody td.r {
    text-align: right;
    font-family: DejaVu Sans Mono, monospace;
    font-size: 6.5pt;
}

/* ── Totals ──────────────────────────────────────────────── */
.totals-block { margin-left: 55%; margin-bottom: 4mm; }
table.totals { width: 100%; border-collapse: collapse; font-size: 7.5pt; }
table.totals td { padding: 1mm 2mm; }
table.totals td.r { text-align: right; font-family: DejaVu Sans Mono, monospace; }
.total-zeile { font-weight: bold; border-top: 1pt solid #333; }
.total-zeile td { padding-top: 1.5mm; font-size: 9pt; }
.zahlbar-zeile td {
    border-top: 0.5pt solid #bbb; padding-top: 1.5mm;
    font-size: 7.5pt; font-weight: bold;
}

/* ── Zahlungsinfo ────────────────────────────────────────── */
.zahlung-block { margin-top: 2mm; font-size: 7pt; line-height: 1.5; color: #333; }

/* ── Fusszeile ───────────────────────────────────────────── */
.fusszeile {
    position: fixed; bottom: 6mm; left: 20mm; right: 20mm;
    border-top: 0.3pt solid #d1d5db; padding-top: 1.5mm;
    font-size: 6.5pt; color: #9ca3af; text-align: center;
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

    // Anschrift: bei tiers_garant immer Patient, sonst KVG→KK
    $tiersGarant = (\App\Models\Organisation::find($rechnung->organisation_id)?->abrechnungslogik ?? 'tiers_garant') === 'tiers_garant';
    $adrRechnung = $rechnung->klient->adressen->firstWhere('adressart', 'rechnung');
    $adrEinsatz  = $rechnung->klient->adressen->firstWhere('adressart', 'einsatzort');

    if (!$tiersGarant && $nurKK && $rechnung->klient->krankenkassen->isNotEmpty()) {
        $kk = $rechnung->klient->krankenkassen->first();
        $anschriftAnrede   = null;
        $anschriftName     = $kk->krankenkasse->name ?? '';
        $anschriftStr      = $kk->krankenkasse->adresse ?? '';
        $anschriftPostfach = null;
        $anschriftOrt      = trim(($kk->krankenkasse->plz ?? '') . ' ' . ($kk->krankenkasse->ort ?? ''));
    } elseif ($adrRechnung) {
        $anschriftAnrede   = null;
        $anschriftName     = $adrRechnung->nachname ?? $klientName;
        $anschriftStr      = $adrRechnung->strasse ?? '';
        $anschriftPostfach = $adrRechnung->postfach ?? null;
        $anschriftOrt      = trim(($adrRechnung->plz ?? '') . ' ' . ($adrRechnung->ort ?? ''));
    } elseif ($adrEinsatz) {
        $anschriftAnrede   = $rechnung->klient->anrede;
        $anschriftName     = $klientName;
        $anschriftStr      = $adrEinsatz->strasse ?? '';
        $anschriftPostfach = $adrEinsatz->postfach ?? null;
        $anschriftOrt      = trim(($adrEinsatz->plz ?? '') . ' ' . ($adrEinsatz->ort ?? ''));
    } else {
        $anschriftAnrede   = $rechnung->klient->anrede;
        $anschriftName     = $klientName;
        $anschriftStr      = $rechnung->klient->adresse ?? '';
        $anschriftPostfach = null;
        $anschriftOrt      = trim(($rechnung->klient->plz ?? '') . ' ' . ($rechnung->klient->ort ?? ''));
    }

    // Positionen: Pauschale (alle einheit=tage) vs. Einzelleistungen
    $positionen  = $rechnung->positionen->sortBy('datum');
    $istPauschale = $positionen->isNotEmpty() && $positionen->every(fn($p) => in_array($p->einheit, ['tage', 'pauschal']));
@endphp

<div class="seite">

    @if($provisorisch ?? false)
    <div style="background:#dc2626; color:white; text-align:center; font-size:9pt; font-weight:bold; padding:2mm 4mm; margin-bottom:4mm; letter-spacing:1pt;">
        PROVISORISCHE VORSCHAU — KEINE GÜLTIGE RECHNUNG
    </div>
    @endif

    {{-- 1. Kopfzeile — Layout-abhängig ────────────────────── --}}
    @php
        $layout       = $logoAusrichtung ?? 'links_anschrift_rechts';
        $mitFirmadaten = $org->druck_mit_firmendaten ?? true;
        $anschrPos    = $org->rechnungsadresse_position ?? 'links';
    @endphp

    @if($layout === 'mitte_anschrift_fusszeile')
        {{-- Logo zentriert, Firmadaten in Fusszeile --}}
        <div style="text-align:center; margin-bottom:5mm;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" class="kopf-logo" style="display:inline-block;" alt="Logo">
            @else
                <div class="kopf-firma">{{ $org->name }}</div>
            @endif
        </div>
    @elseif($layout === 'rechts_anschrift_links')
        {{-- Logo rechts, Firmadaten links --}}
        <div class="kopf">
            <div class="kopf-links" style="font-size:6.5pt; color:#555; line-height:1.4;">
                @if($mitFirmadaten)
                    <div class="kopf-firma" style="margin-bottom:0.5mm;">{{ $org->name }}</div>
                    @if($org->adresse){{ $org->adresse }}<br>@endif
                    {{ $org->plz }} {{ $org->ort }}
                    @if($org->telefon)<br>Tel. {{ $org->telefon }}@endif
                    @if($org->email)<br>{{ $org->email }}@endif
                @endif
            </div>
            <div class="kopf-rechts">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="kopf-logo" style="display:inline-block;" alt="Logo">
                @else
                    <div class="kopf-firma">{{ $org->name }}</div>
                @endif
            </div>
        </div>
    @else
        {{-- Standard: Logo links, Firmadaten rechts --}}
        <div class="kopf">
            <div class="kopf-links">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="kopf-logo" alt="Logo">
                @else
                    <div class="kopf-firma">{{ $org->name }}</div>
                @endif
            </div>
            <div class="kopf-rechts">
                @if($mitFirmadaten)
                    @if($org->adresse){{ $org->adresse }}<br>@endif
                    @if($org->postfach){{ $org->postfach }}<br>@endif
                    {{ $org->plz }} {{ $org->ort }}
                    @if($org->telefon)<br>Tel. {{ $org->telefon }}@endif
                    @if($org->email) · {{ $org->email }}@endif
                @endif
            </div>
        </div>
    @endif

    {{-- 3. Anschrift ────────────────────────────────────────── --}}
    @if($anschrPos === 'rechts')
    <div style="text-align:right; margin-top:7mm; margin-bottom:8mm; min-height:14mm; font-size:7.5pt; line-height:1.4;">
    @else
    <div class="anschrift">
    @endif
        @if($anschriftAnrede ?? null)<span>{{ $anschriftAnrede }}</span><br>@endif
        <strong>{{ $anschriftName }}</strong><br>
        @if($anschriftStr){{ $anschriftStr }}<br>@endif
        @if($anschriftPostfach ?? null)Postfach {{ $anschriftPostfach }}<br>@endif
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
                    $bezeichnung = $pos->einheit === 'pauschal'
                                ? ($pos->beschreibung ?? 'Einzelleistung')
                                : ($pos->einsatzLeistungsart?->leistungsart?->bezeichnung
                                ?? $pos->beschreibung
                                ?? $pos->leistungstyp?->bezeichnung
                                ?? 'Tagespauschale');
                    $betrag = $pos->betrag_patient + $pos->betrag_kk;
                    $ansatz = $pos->menge > 0 ? round($betrag / $pos->menge, 2) : 0;
                @endphp
                <tr>
                    <td>
                        {{ $bezeichnung }}
                        @if($pos->einheit !== 'pauschal' && $pos->beschreibung !== $bezeichnung)
                        <br><span style="font-size: 7.5pt; color: #555;">
                            {{ $pos->beschreibung ?? $pos->datum->format('d.m.Y') }}
                        </span>
                        @endif
                    </td>
                    <td class="r">{{ number_format($pos->menge, 0) }}</td>
                    <td class="r">{{ number_format($ansatz, 2, '.', "'") }}</td>
                    <td class="r">{{ number_format($betrag, 2, '.', "'") }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @else
        {{-- Einzelleistungen: pro Leistungsart kumuliert ────── --}}
        @php
            // Positionen nach Leistungsart gruppieren + kumulieren
            $gruppen = [];
            foreach ($positionen->filter(fn($p) => $p->menge > 0) as $pos) {
                $la = $pos->einsatzLeistungsart?->leistungsart?->bezeichnung
                   ?? $pos->beschreibung
                   ?? $pos->leistungstyp?->leistungsart?->bezeichnung
                   ?? $pos->leistungstyp?->bezeichnung
                   ?? 'Pflege- und Betreuungsleistung';
                if (!isset($gruppen[$la])) {
                    $gruppen[$la] = [
                        'bezeichnung'   => $la,
                        'menge'         => 0,
                        'betrag_patient'=> 0.0,
                        'betrag_kk'     => 0.0,
                        'tarif_patient' => (float)$pos->tarif_patient,
                        'tarif_kk'      => (float)$pos->tarif_kk,
                        'einheit'       => $pos->einheit ?? 'min',
                    ];
                }
                $gruppen[$la]['menge']          += (float)$pos->menge;
                $gruppen[$la]['betrag_patient'] += (float)$pos->betrag_patient;
                $gruppen[$la]['betrag_kk']      += (float)$pos->betrag_kk;
            }
        @endphp
        <table class="positionen">
            <thead>
                <tr>
                    <th style="width: {{ $beide ? '48%' : '60%' }}">Leistung</th>
                    <th class="r" style="width: 10%">Minuten</th>
                    @if($beide || $nurPatient)
                    <th class="r" style="width: 11%">Tarif Pat.</th>
                    <th class="r" style="width: 13%">Betrag Pat.</th>
                    @endif
                    @if($beide || $nurKK)
                    <th class="r" style="width: 11%">Tarif {{ $kkLabel }}</th>
                    <th class="r" style="width: 13%">Betrag {{ $kkLabel }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($gruppen as $g)
                @php
                    $tarifPatStd = $g['tarif_patient'];
                    $tarifKkStd  = $g['tarif_kk'];
                @endphp
                <tr>
                    <td>{{ $g['bezeichnung'] }}</td>
                    <td class="r">{{ (int)$g['menge'] }}</td>
                    @if($beide || $nurPatient)
                    <td class="r">{{ number_format($tarifPatStd, 2, '.', "'") }}</td>
                    <td class="r">{{ number_format($g['betrag_patient'], 2, '.', "'") }}</td>
                    @endif
                    @if($beide || $nurKK)
                    <td class="r">{{ number_format($tarifKkStd, 2, '.', "'") }}</td>
                    <td class="r">{{ number_format($g['betrag_kk'], 2, '.', "'") }}</td>
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
    @if(($logoAusrichtung ?? '') === 'mitte_anschrift_fusszeile')
        {{ $org->name }}
        @if($org->adresse) · {{ $org->adresse }}, {{ $org->plz }} {{ $org->ort }}@endif
        @if($org->telefon) · Tel. {{ $org->telefon }}@endif
        @if($org->email) · {{ $org->email }}@endif
    @else
        {{ $org->name }}
        @if($org->adresse) · {{ $org->adresse }}, {{ $org->plz }} {{ $org->ort }}@endif
    @endif
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
