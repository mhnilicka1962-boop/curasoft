<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 8.5pt;
    color: #1a1a1a;
    line-height: 1.4;
}
.seite { padding: 15mm 18mm; }
h1 { font-size: 13pt; margin-bottom: 4mm; }
.intro {
    font-size: 8.5pt; line-height: 1.5; margin-bottom: 5mm;
    background: #f8f9fa; padding: 3mm 4mm; border-radius: 2mm;
}
.intro ul { margin: 1.5mm 0 0 5mm; }
.intro li { margin-bottom: 0.5mm; }
.intro .hinweis { font-size: 7.5pt; color: #555; margin-top: 2mm; font-style: italic; }

table { width: 100%; border-collapse: collapse; margin-bottom: 4mm; font-size: 8pt; }
thead tr { border-bottom: 0.75pt solid #333; }
thead th {
    padding: 1.5mm 2mm; text-align: left;
    font-weight: bold; font-size: 7.5pt; color: #333;
    background: #f8f9fa;
}
thead th.r { text-align: right; }
tbody tr { border-bottom: 0.3pt solid #e5e7eb; }
tbody td { padding: 1.2mm 2mm; vertical-align: top; }
tbody td.r { text-align: right; }
tbody td.hell { color: #777; font-size: 7pt; }
tbody td.fett { font-weight: bold; }
tfoot tr { background: #f8f9fa; font-weight: bold; border-top: 0.75pt solid #333; }
tfoot td { padding: 2mm; }
tfoot td.r { text-align: right; }

.zusatz {
    margin-top: 5mm; padding: 3mm 4mm;
    background: #fef3c7; border-left: 3pt solid #f59e0b;
    font-size: 8pt;
}
.zusatz .label { font-weight: bold; margin-bottom: 1mm; }
.zusatz table { margin: 0; font-size: 7.5pt; }
.zusatz tbody td { padding: 0.8mm 2mm; }

.kopf-info {
    display: table; width: 100%; margin-bottom: 4mm;
    font-size: 7.5pt;
}
.kopf-info td.lbl { color: #555; padding-right: 4mm; vertical-align: top; width: 30mm; }
.kopf-info td.val { padding-bottom: 0.5mm; }
</style>
</head>
<body>
<div class="seite">

    <h1>Berechnung Ihres Anteils — Rechnung {{ $rechnung->rechnungsnummer }}</h1>

    @php
        $patTotal    = (float) $rechnung->betrag_patient;
        $patKvg      = (float) ($rapportblattDaten['summen']['pat'] ?? 0);
        $patNichtKvg = round($patTotal - $patKvg, 2);
        $hatBeides   = $patKvg > 0 && $patNichtKvg > 0.01;
    @endphp
    <table class="kopf-info">
        <tr>
            <td class="lbl">Klient</td>
            <td class="val">{{ $klientName }}</td>
        </tr>
        <tr>
            <td class="lbl">Periode</td>
            <td class="val">{{ $rechnung->periode_von->format('d.m.Y') }} – {{ $rechnung->periode_bis->format('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Ihr Anteil</td>
            <td class="val"><strong>CHF {{ number_format($patTotal, 2, '.', "'") }}</strong>@if($hatBeides) <span style="color:#555;">(KVG {{ number_format($patKvg, 2, '.', "'") }} + Hauswirtschaft {{ number_format($patNichtKvg, 2, '.', "'") }})</span>@endif</td>
        </tr>
    </table>

    <div class="intro">
        Die Patientenbeteiligung an KVG-Pflege wird <strong>pro Tag</strong> berechnet:
        <ul>
            <li>Patient zahlt max. <strong>{{ number_format($rapportblattDaten['beitrag']['limit_prozent'], 0) }}%</strong> des Netto-Anteils (Vollkosten − Krankenkasse)</li>
            <li>Gedeckelt auf <strong>CHF {{ number_format($rapportblattDaten['beitrag']['ansatz_kunde'], 2) }} pro Tag</strong></li>
            <li>Den Rest übernimmt die Gemeinde (Restfinanzierung)</li>
        </ul>
        <div class="hinweis">
            Hinweis: Nicht-KVG-Leistungen (z.B. Hauswirtschaft) werden voll vom Patient getragen und sind separat unten ausgewiesen.
        </div>
    </div>

    @if(!empty($nichtKvgGruppen) && count($nichtKvgGruppen) > 0)
    <div class="zusatz">
        <div class="label">Nicht-KVG-Leistungen (voll vom Patient getragen)</div>
        <table>
            <thead>
                <tr>
                    <th>Leistung</th>
                    <th class="r">Minuten</th>
                    <th class="r">Vollkosten CHF</th>
                </tr>
            </thead>
            <tbody>
                @php $nkvSumme = 0; @endphp
                @foreach($nichtKvgGruppen as $g)
                @php $nkvSumme += $g['betrag']; @endphp
                <tr>
                    <td>{{ $g['bezeichnung'] }}</td>
                    <td class="r">{{ (int)$g['menge'] }}</td>
                    <td class="r">{{ number_format($g['betrag'], 2, '.', "'") }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Nicht-KVG</td>
                    <td></td>
                    <td class="r">{{ number_format($nkvSumme, 2, '.', "'") }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    <div style="page-break-before: always;"></div>
    <table>
        <thead>
            <tr>
                <th>Tag</th>
                <th class="r">Vollkosten</th>
                <th class="r">Krankenkasse</th>
                <th class="r">Netto</th>
                <th class="r">Cap</th>
                <th class="r">Patient</th>
                <th class="r">Gemeinde</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rapportblattDaten['tage'] as $tag)
            @php
                $voll = (float)$tag['taxe_abkl'] + (float)$tag['taxe_unt'] + (float)$tag['taxe_gp'];
                $kk   = (float)$tag['kvg_abkl']  + (float)$tag['kvg_unt']  + (float)$tag['kvg_gp'];
            @endphp
            @if($voll > 0)
            <tr>
                <td>{{ $tag['datum']->format('d.m.Y') }}</td>
                <td class="r">{{ number_format($voll, 2, '.', "'") }}</td>
                <td class="r">{{ number_format($kk, 2, '.', "'") }}</td>
                <td class="r">{{ number_format($tag['netto'], 2, '.', "'") }}</td>
                <td class="r hell">
                    {{ $tag['pat_limit']
                        ? number_format($rapportblattDaten['beitrag']['limit_prozent'], 0).'%'
                        : 'CHF '.number_format($rapportblattDaten['beitrag']['ansatz_kunde'], 2) }}
                </td>
                <td class="r fett">{{ number_format($tag['pat'], 2, '.', "'") }}</td>
                <td class="r">{{ number_format($tag['gemeinde'], 2, '.', "'") }}</td>
            </tr>
            @endif
            @endforeach
        </tbody>
        <tfoot>
            @php
                $sVoll = $rapportblattDaten['summen']['taxe_abkl'] + $rapportblattDaten['summen']['taxe_unt'] + $rapportblattDaten['summen']['taxe_gp'];
                $sKk   = $rapportblattDaten['summen']['kvg_abkl']  + $rapportblattDaten['summen']['kvg_unt']  + $rapportblattDaten['summen']['kvg_gp'];
            @endphp
            <tr>
                <td>Total KVG</td>
                <td class="r">{{ number_format($sVoll, 2, '.', "'") }}</td>
                <td class="r">{{ number_format($sKk, 2, '.', "'") }}</td>
                <td class="r">{{ number_format($rapportblattDaten['summen']['netto'], 2, '.', "'") }}</td>
                <td></td>
                <td class="r">{{ number_format($rapportblattDaten['summen']['pat'], 2, '.', "'") }}</td>
                <td class="r">{{ number_format($rapportblattDaten['summen']['gemeinde'], 2, '.', "'") }}</td>
            </tr>
        </tfoot>
    </table>


</div>
</body>
</html>
