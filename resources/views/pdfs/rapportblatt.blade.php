<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; font-family: DejaVu Sans, Arial, sans-serif; }
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 8pt;
    color: #1a1a1a;
    line-height: 1.3;
}
.seite { padding: 7mm 8mm 7mm 8mm; }

/* ── Kopf ─────────────────────────────────────────────────── */
.kopf-titel { font-size: 8.5pt; font-weight: bold; margin-bottom: 1.5mm; }
table.meta { font-size: 7pt; border-collapse: collapse; margin-bottom: 3mm; width: 100%; }
table.meta td { font-family: DejaVu Sans, Arial, sans-serif; padding: 0.2mm 4mm 0.2mm 0; vertical-align: top; }
table.meta td.lbl { font-family: DejaVu Sans, Arial, sans-serif; color: #555; white-space: nowrap; min-width: 30mm; }
table.meta td.val { font-family: DejaVu Sans, Arial, sans-serif; font-weight: 500; }

/* ── Haupttabelle ─────────────────────────────────────────── */
table.rapport {
    width: 100%;
    border-collapse: collapse;
}

/* Kopfzeilen */
table.rapport thead tr.kopf1 th {
    font-family: DejaVu Sans, Arial, sans-serif;
    background: #1a3a5c;
    color: #fff;
    font-size: 7pt;
    font-weight: bold;
    text-align: center;
    padding: 1mm 1mm;
    border: 0.3pt solid #0f2540;
    line-height: 1.4;
}
table.rapport thead tr.kopf2 th {
    font-family: DejaVu Sans, Arial, sans-serif;
    background: #cdd5e0;
    color: #111;
    font-size: 6.5pt;
    font-weight: bold;
    text-align: center;
    padding: 0.5mm 1mm;
    border: 0.3pt solid #a0aabb;
    font-style: italic;
}

/* Datenzeilen */
table.rapport tbody tr { border-bottom: 0.2pt solid #d0d8e4; }
table.rapport tbody tr:nth-child(even) { background: #f4f7fb; }
table.rapport tbody td {
    font-family: DejaVu Sans, Arial, sans-serif;
    padding: 0.2mm 0.8mm;
    text-align: right;
    vertical-align: middle;
    font-size: 6.5pt;
    border-right: 0.2pt solid #dde3ea;
    line-height: 1.2;
}

/* Tag-Spalte */
td.col-tag, th.col-tag {
    font-family: DejaVu Sans, Arial, sans-serif;
    text-align: center !important;
    font-weight: bold;
    border-right: 1.5pt solid #5a7a9a !important;
    width: 4%;
}
td.col-tag {
    background: #e2e9f2 !important;
    color: #1a1a1a !important;
}

/* Trennlinie nach Min-Gruppe */
td.col-sep, th.col-sep {
    border-right: 2pt solid #1a3a5c !important;
}

/* Restbetrag / VP / Total */
td.col-rest { background: #eef4ee; font-weight: bold; border-left: 1pt solid #88aa88; }
td.col-vp   { background: #fdf6ee; }
td.col-tot  { background: #eef4ee; font-weight: bold; }

/* Leere Zelle */
td.leer { color: #ccc; }

/* Total-Zeile */
table.rapport tfoot tr {
    border-top: 1.5pt solid #1a3a5c;
    background: #cdd5e0;
}
table.rapport tfoot td {
    font-family: DejaVu Sans, Arial, sans-serif;
    padding: 0.4mm 0.8mm;
    font-weight: bold;
    text-align: right;
    font-size: 6.5pt;
    border-right: 0.2pt solid #a0aabb;
    line-height: 1.2;
}
table.rapport tfoot td.col-tag {
    font-family: DejaVu Sans, Arial, sans-serif;
    text-align: left !important;
    background: #b8c4d4 !important;
    border-right: 1.5pt solid #5a7a9a !important;
    font-size: 6pt;
}
table.rapport tfoot td.col-sep { border-right: 2pt solid #1a3a5c !important; }
table.rapport tfoot td.col-rest { background: #c4d8c4; }
table.rapport tfoot td.col-vp   { background: #f0e4cc; }
table.rapport tfoot td.col-tot  { background: #c4d8c4; }

/* ── Footer ───────────────────────────────────────────────── */
.footer {
    margin-top: 2mm;
    border-top: 0.5pt solid #aaa;
    padding-top: 1.5mm;
    font-size: 6pt;
    display: table;
    width: 100%;
    line-height: 1.6;
}
.footer-links  { display: table-cell; width: 40%; vertical-align: top; }
.footer-mitte  { display: table-cell; width: 25%; text-align: center; vertical-align: top; }
.footer-rechts { display: table-cell; width: 35%; text-align: right; vertical-align: top; }
</style>
</head>
<body>
<div class="seite">

@php
    $rb   = $rapportblattDaten;
    $s    = $rb['summen'];
    $tar  = $rb['tarife'];
    $bei  = $rb['beitrag'];

    // Welche Leistungsarten sind vorhanden?
    $hatAbkl = $s['abkl_min'] > 0 || $tar['abkl']['ansatz'] > 0;
    $hatUnt  = $s['unt_min']  > 0 || $tar['unt']['ansatz']  > 0;
    $hatGp   = $s['gp_min']   > 0 || $tar['gp']['ansatz']   > 0;
    // Immer alle 3 zeigen (wie Vorgabe "titel immer")
    $hatAbkl = true; $hatUnt = true; $hatGp = true;

    $fmt  = fn($n) => $n != 0 ? number_format((float)$n, 2, '.', "'") : '';
    $fmtZ = fn($n) => $n > 0  ? (string)(int)$n : '';
    $fmtF = fn($n) => number_format((float)$n, 2, '.', "'");

    $vpLabel = 'max. CHF ' . number_format($bei['ansatz_kunde'], 2)
             . ' / ' . number_format($bei['limit_prozent'], 2) . '%';

    $klientName2 = $rechnung->klient->vollname();
@endphp

{{-- Kopf ─────────────────────────────────────────────────── --}}
<div class="kopf-titel" style="font-family: DejaVu Sans, Arial, sans-serif;">Pflegeleistungen — Leistungsdetail (Rapportblatt)</div>

<table class="meta">
    <tr>
        <td class="lbl">Leistungserbringer</td>
        <td class="val">{{ $org->name }}{{ $org->adresse ? ', ' . $org->adresse : '' }}{{ $org->postfach ? ', ' . $org->postfach : '' }}, {{ $org->plz }} {{ $org->ort }}</td>
        <td class="lbl">Zeitraum</td>
        <td class="val">{{ $rechnung->periode_von->format('01.m.Y') }} – {{ $rechnung->periode_bis->format('d.m.Y') }}</td>
    </tr>
    <tr>
        <td class="lbl">Versicherte Person</td>
        <td class="val">{{ $klientName }}</td>
        <td class="lbl">Geburtsdatum</td>
        <td class="val">{{ $rechnung->klient->geburtsdatum?->format('d.m.Y') ?? '—' }}</td>
    </tr>
</table>

{{-- Haupttabelle ─────────────────────────────────────────── --}}
<table class="rapport">
    <thead>
        {{-- Zeile 1: Gruppen --}}
        <tr class="kopf1">
            <td class="col-tag" rowspan="2" style="font-family: DejaVu Sans, Arial, sans-serif; font-weight: bold;">Tag</td>
            <td colspan="3" style="font-family: DejaVu Sans, Arial, sans-serif; font-weight: bold; background:#1a3a5c; color:#fff; font-size:7pt; text-align:center; padding:1mm; border:0.3pt solid #0f2540;">
                Minuten
            </td>
            <td colspan="3" style="font-family: DejaVu Sans, Arial, sans-serif; font-weight: bold; background:#1a3a5c; color:#fff; font-size:7pt; text-align:center; padding:1mm; border:0.3pt solid #0f2540; line-height:1.4;">
                Taxe CHF/h<br><span style="font-size:5pt; font-weight:normal; font-family: DejaVu Sans, Arial, sans-serif;">{{ number_format($tar['abkl']['ansatz'],2) }} / {{ number_format($tar['unt']['ansatz'],2) }} / {{ number_format($tar['gp']['ansatz'],2) }}</span>
            </td>
            <td class="col-sep" colspan="3" style="font-family: DejaVu Sans, Arial, sans-serif; font-weight: bold; background:#1a3a5c; color:#fff; font-size:7pt; text-align:center; padding:1mm; border:0.3pt solid #0f2540; line-height:1.4;">
                Krankenkasse CHF/h<br><span style="font-size:5pt; font-weight:normal; font-family: DejaVu Sans, Arial, sans-serif;">{{ number_format($tar['abkl']['kkasse'],2) }} / {{ number_format($tar['unt']['kkasse'],2) }} / {{ number_format($tar['gp']['kkasse'],2) }}</span>
            </td>
            <td rowspan="2" class="col-rest" style="width:7%; font-family: DejaVu Sans, Arial, sans-serif; font-weight: bold; background:#1a3a5c; color:#fff; font-size:7pt; text-align:center; padding:1mm; border:0.3pt solid #0f2540;">Restbetrag<br><span style="font-size:5pt; font-weight:normal; font-family: DejaVu Sans, Arial, sans-serif;">(Taxe-KK)</span></td>
            <td rowspan="2" class="col-vp" style="width:9%; font-family: DejaVu Sans, Arial, sans-serif; font-weight: bold; background:#1a3a5c; color:#fff; font-size:7pt; text-align:center; padding:1mm; border:0.3pt solid #0f2540;">Beitrag VP<br><span style="font-size:5pt; font-weight:normal; font-family: DejaVu Sans, Arial, sans-serif;">{{ $vpLabel }}</span></td>
            <td rowspan="2" class="col-tot" style="width:7%; font-family: DejaVu Sans, Arial, sans-serif; font-weight: bold; background:#1a3a5c; color:#fff; font-size:7pt; text-align:center; padding:1mm; border:0.3pt solid #0f2540;">Beitrag<br>Total</td>
        </tr>
        {{-- Zeile 2: Unter-Header --}}
        <tr class="kopf2">
            <td style="width:5%; font-family: DejaVu Sans, Arial, sans-serif; font-weight:bold; font-style:italic; background:#cdd5e0; color:#111; font-size:6.5pt; text-align:center; padding:0.5mm 1mm; border:0.3pt solid #a0aabb;">Abkl.<br>Beratung</td>
            <td style="width:5%; font-family: DejaVu Sans, Arial, sans-serif; font-weight:bold; font-style:italic; background:#cdd5e0; color:#111; font-size:6.5pt; text-align:center; padding:0.5mm 1mm; border:0.3pt solid #a0aabb;">Unt.<br>Behand.</td>
            <td style="width:5%; font-family: DejaVu Sans, Arial, sans-serif; font-weight:bold; font-style:italic; background:#cdd5e0; color:#111; font-size:6.5pt; text-align:center; padding:0.5mm 1mm; border:0.3pt solid #a0aabb;">Grund-<br>pflege</td>
            <td style="width:7%; font-family: DejaVu Sans, Arial, sans-serif; font-weight:bold; font-style:italic; background:#cdd5e0; color:#111; font-size:6.5pt; text-align:center; padding:0.5mm 1mm; border:0.3pt solid #a0aabb;">Abkl.<br>Beratung</td>
            <td style="width:7%; font-family: DejaVu Sans, Arial, sans-serif; font-weight:bold; font-style:italic; background:#cdd5e0; color:#111; font-size:6.5pt; text-align:center; padding:0.5mm 1mm; border:0.3pt solid #a0aabb;">Unt.<br>Behand.</td>
            <td style="width:7%; font-family: DejaVu Sans, Arial, sans-serif; font-weight:bold; font-style:italic; background:#cdd5e0; color:#111; font-size:6.5pt; text-align:center; padding:0.5mm 1mm; border:0.3pt solid #a0aabb;">Grund-<br>pflege</td>
            <td style="width:7%; font-family: DejaVu Sans, Arial, sans-serif; font-weight:bold; font-style:italic; background:#cdd5e0; color:#111; font-size:6.5pt; text-align:center; padding:0.5mm 1mm; border:0.3pt solid #a0aabb;">Abkl.<br>Beratung</td>
            <td style="width:7%; font-family: DejaVu Sans, Arial, sans-serif; font-weight:bold; font-style:italic; background:#cdd5e0; color:#111; font-size:6.5pt; text-align:center; padding:0.5mm 1mm; border:0.3pt solid #a0aabb;">Unt.<br>Behand.</td>
            <td class="col-sep" style="width:7%; font-family: DejaVu Sans, Arial, sans-serif; font-weight:bold; font-style:italic; background:#cdd5e0; color:#111; font-size:6.5pt; text-align:center; padding:0.5mm 1mm; border:0.3pt solid #a0aabb;">Grund-<br>pflege</td>
        </tr>
    </thead>
    <tbody>
        @foreach($rb['tage'] as $tag)
        @php $leer = $tag['abkl_min'] + $tag['unt_min'] + $tag['gp_min'] === 0; @endphp
        <tr>
            <td class="col-tag" style="font-family: DejaVu Sans, Arial, sans-serif;">{{ $tag['datum']->day }}</td>
            <td>{{ $fmtZ($tag['abkl_min']) }}</td>
            <td>{{ $fmtZ($tag['unt_min']) }}</td>
            <td>{{ $fmtZ($tag['gp_min']) }}</td>
            <td>{{ $fmt($tag['taxe_abkl']) }}</td>
            <td>{{ $fmt($tag['taxe_unt']) }}</td>
            <td>{{ $fmt($tag['taxe_gp']) }}</td>
            <td>{{ $fmt($tag['kvg_abkl']) }}</td>
            <td>{{ $fmt($tag['kvg_unt']) }}</td>
            <td class="col-sep">{{ $fmt($tag['kvg_gp']) }}</td>
            @if($leer)
            <td class="col-rest leer">—</td>
            <td class="col-vp"></td>
            <td class="col-tot"></td>
            @else
            <td class="col-rest">{{ $fmtF($tag['netto']) }}</td>
            <td class="col-vp">{{ $fmtF($tag['pat']) }}{{ $tag['pat_limit'] ? '*' : '' }}</td>
            <td class="col-tot">{{ $fmtF($tag['gemeinde']) }}</td>
            @endif
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td class="col-tag" style="font-family: DejaVu Sans, Arial, sans-serif;">Total</td>
            <td>{{ $s['abkl_min'] ?: '' }}</td>
            <td>{{ $s['unt_min']  ?: '' }}</td>
            <td>{{ $s['gp_min']   ?: '' }}</td>
            <td>{{ $fmt($s['taxe_abkl']) }}</td>
            <td>{{ $fmt($s['taxe_unt']) }}</td>
            <td>{{ $fmt($s['taxe_gp']) }}</td>
            <td>{{ $fmt($s['kvg_abkl']) }}</td>
            <td>{{ $fmt($s['kvg_unt']) }}</td>
            <td class="col-sep">{{ $fmt($s['kvg_gp']) }}</td>
            <td class="col-rest">{{ $fmtF($s['netto']) }}</td>
            <td class="col-vp">{{ $fmtF($s['pat']) }}</td>
            <td class="col-tot">{{ $fmtF($s['gemeinde']) }}</td>
        </tr>
    </tfoot>
</table>

{{-- Footer ───────────────────────────────────────────────── --}}
<div class="footer">
    <div class="footer-links">
        Beilage: Antrag und Kopie der ärztlichen Anordnung (bei erster Abrechnung)<br>
        <span style="color:#777; font-size:5pt;">* Limit% angewendet &nbsp;·&nbsp; Restbetrag = ΣTaxe − ΣKK &nbsp;·&nbsp; VP = min(CHF, Limit%×Restbetrag)</span>
    </div>
    <div class="footer-mitte">
        Datum: {{ now()->format('d.m.Y') }}
    </div>
    <div class="footer-rechts">
        Stempel und Unterschrift<br>
        {{ $org->name }}<br>
        {!! $org->postfach ? $org->postfach . '<br>' : '' !!}{{ $org->plz }} {{ $org->ort }}
    </div>
</div>

</div>
</body>
</html>
