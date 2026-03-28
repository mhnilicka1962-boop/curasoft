<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 7pt; color: #1a1a1a; line-height: 1.3; }

/* Feste Fusszeile */
.fusszeile {
    position: fixed;
    bottom: 5mm;
    left: 12mm;
    right: 12mm;
    border-top: 0.3pt solid #ccc;
    padding-top: 1.5mm;
    font-size: 5.5pt;
    color: #aaa;
    text-align: center;
}

/* Seiteninhalt */
.seite { padding: 8mm 12mm 16mm 12mm; }

/* Erster-Seite-Kopf (gross, nur Seite 1) */
.kopf-seite1 { display:table; width:100%; margin-bottom:3mm; padding-bottom:2mm; border-bottom:0.5pt solid #1a3a5c; }
.kopf1-l { display:table-cell; width:40%; vertical-align:middle; }
.kopf1-m { display:table-cell; width:30%; vertical-align:middle; text-align:center; }
.kopf1-r { display:table-cell; width:30%; vertical-align:middle; text-align:right; }
.kopf1-logo  { max-height:10mm; max-width:35mm; display:block; }
.kopf1-titel { font-size:9pt; font-weight:bold; color:#1a3a5c; }
.kopf1-sub   { font-size:6pt; color:#777; }
.ma-info { font-size:6.5pt; color:#666; margin-bottom:3mm; }

/* Tabelle */
table.zt { width:100%; border-collapse:collapse; font-size:6.5pt; }
table.zt thead tr { background:#1a3a5c; color:#fff; }
table.zt thead th { padding:1.5mm 1.5mm; text-align:left; font-size:6pt; font-weight:bold; white-space:nowrap; }
table.zt thead th.r { text-align:right; }
table.zt thead th.c { text-align:center; }

tr.einsatz td { padding:0.8mm 1.5mm; border-bottom:0.2pt solid #eee; vertical-align:middle; }
tr.einsatz.we td { background:#f7f7f7; }
tr.leer td  { padding:0.5mm 1.5mm; color:#ccc; border-bottom:0.2pt solid #f0f0f0; }
tr.leer.we td { background:#f9f9f9; }
tr.tsum td  { padding:0.5mm 1.5mm; font-size:6pt; color:#888; background:#f5f5f5; border-bottom:0.3pt solid #ddd; }
tr.wsum td  { padding:1mm 1.5mm; font-weight:bold; font-size:6.5pt; background:#dce7f3; border-top:0.5pt solid #1a3a5c; border-bottom:0.5pt solid #1a3a5c; }
tr.total td { padding:1.5mm 1.5mm; font-weight:bold; font-size:7.5pt; background:#1a3a5c; color:#fff; }

.r { text-align:right; }
.c { text-align:center; }
.tag-nr  { font-size:7.5pt; font-weight:bold; }
.tag-we  { color:#aaa; }
.tag-day { font-size:5.5pt; color:#888; display:block; }
.la   { color:#666; }
.zeit { font-size:6pt; color:#888; }
.dp { color:#1a6644; }
.dn { color:#c0392b; }
.d0 { color:#bbb; }

/* Unterschrift */
.sign { display:table; width:100%; margin-top:8mm; }
.sign-l { display:table-cell; width:48%; padding-right:4mm; }
.sign-r { display:table-cell; width:48%; padding-left:4mm; }
.sign-linie { border-top:0.7pt solid #333; padding-top:1.5mm; font-size:6.5pt; color:#555; margin-top:8mm; }
</style>
</head>
<body>

{{-- Feste Fusszeile (jede Seite) --}}
<div class="fusszeile">
    {{ $org->name }} · Arbeitszeit-Nachweis {{ $benutzer->vorname }} {{ $benutzer->nachname }} · {{ $von->locale('de')->isoFormat('MMMM YYYY') }}
</div>

<div class="seite">

{{-- Grosser Kopf nur Seite 1 --}}
<div class="kopf-seite1">
    <div class="kopf1-l">
        @if(!empty($logoBase64))
            <img class="kopf1-logo" src="data:image/png;base64,{{ $logoBase64 }}">
        @else
            <span style="font-weight:bold;color:#1a3a5c;font-size:9pt;">{{ $org->name }}</span>
        @endif
    </div>
    <div class="kopf1-m">
        <span class="kopf1-titel">Arbeitszeit-Nachweis</span><br>
        <span class="kopf1-sub">{{ $von->locale('de')->isoFormat('MMMM YYYY') }}</span>
    </div>
    <div class="kopf1-r">
        <span style="font-weight:bold;">{{ $benutzer->vorname }} {{ $benutzer->nachname }}</span><br>
        <span class="kopf1-sub">{{ ucfirst($benutzer->rolle) }}@if($benutzer->pensum) · {{ $benutzer->pensum }}%@endif</span>
    </div>
</div>
<div class="ma-info">
    {{ $von->format('d.m.Y') }} – {{ $bis->format('d.m.Y') }} &nbsp;·&nbsp; Erstellt: {{ now()->format('d.m.Y H:i') }}
</div>

{{-- Tabelle --}}
@php
    $tagLabels = ['Mo','Di','Mi','Do','Fr','Sa','So'];
    $hm = fn(int $min): string => intdiv($min,60).':'.str_pad($min%60,2,'0',STR_PAD_LEFT);
    $zt = fn(?string $t): string => $t ? substr($t,0,5) : '';

    $kalenderTage = collect();
    $t = $von->copy();
    while ($t->lte($bis)) { $kalenderTage->push($t->copy()); $t->addDay(); }
    $einsaetzeNachDatum = $einsaetze->groupBy(fn($e) => $e->datum->format('Y-m-d'));

    $wocheNr = null; $wochePlan = 0; $wocheIst = 0;
    $gesamtPlan = 0; $gesamtIst = 0; $totalAnz = 0;
@endphp

<table class="zt">
    <thead>
        <tr>
            <th style="width:9mm;">Tag</th>
            <th>Klient · Leistungsart</th>
            <th class="c" style="width:24mm;">Geplant</th>
            <th class="c" style="width:24mm;">Geleistet</th>
            <th class="r" style="width:14mm;">Differenz</th>
        </tr>
    </thead>
    <tbody>
    @foreach ($kalenderTage as $tag)
    @php
        $tagKey = $tag->format('Y-m-d');
        $tagNr  = (int)$tag->format('N');
        $tagLbl = $tagLabels[$tagNr - 1];
        $istWE  = $tagNr >= 6;
        $weKl   = $istWE ? ' we' : '';
        $neueKW = $tag->copy()->startOfWeek()->format('W');

        if ($wocheNr !== null && $neueKW !== $wocheNr) {
            $wd = $wocheIst - $wochePlan;
    @endphp
        <tr class="wsum">
            <td colspan="2">KW {{ $wocheNr }}</td>
            <td class="c">{{ $wochePlan > 0 ? $hm($wochePlan) : '—' }}</td>
            <td class="c">{{ $wocheIst > 0 ? $hm($wocheIst) : '—' }}</td>
            <td class="r">
                @if($wochePlan > 0 || $wocheIst > 0)
                    <span class="{{ $wd>=0?'dp':'dn' }}">{{ $wd>=0?'+':'−' }}{{ $hm(abs($wd)) }}</span>
                @else <span class="d0">—</span>
                @endif
            </td>
        </tr>
    @php
            $wochePlan = 0; $wocheIst = 0;
        }
        $wocheNr = $neueKW;

        $te = $einsaetzeNachDatum->get($tagKey, collect());
        $tagPlan = $te->sum('minuten');
        $tagIst  = $te->whereNotNull('ist_minuten')->sum('ist_minuten');
        $wochePlan  += $tagPlan; $wocheIst  += $tagIst;
        $gesamtPlan += $tagPlan; $gesamtIst += $tagIst;
        $totalAnz   += $te->count();
    @endphp

    @if($te->isEmpty())
        <tr class="leer{{ $weKl }}">
            <td><span class="tag-nr {{ $istWE?'tag-we':'' }}">{{ $tag->format('d.') }}</span><span class="tag-day">{{ $tagLbl }}</span></td>
            <td colspan="4" style="color:#ddd;">—</td>
        </tr>
    @else
        @php $anzahl = $te->count(); @endphp
        @foreach ($te as $idx => $e)
        @php
            $planMin = (int)($e->minuten ?? 0);
            $istMin  = $e->ist_minuten;
            $diff    = $istMin !== null ? ($istMin - $planMin) : null;
            $dSign   = $diff !== null ? ($diff >= 0 ? '+' : '−') : null;
            $dKl     = $diff === null ? 'd0' : ($diff > 0 ? 'dp' : ($diff < 0 ? 'dn' : 'd0'));
        @endphp
        <tr class="einsatz{{ $weKl }}">
            <td>
                @if($idx === 0)
                    <span class="tag-nr {{ $istWE?'tag-we':'' }}">{{ $tag->format('d.') }}</span>
                    <span class="tag-day">{{ $tagLbl }}</span>
                @endif
            </td>
            <td>{{ $e->klient?->vorname }} {{ $e->klient?->nachname }} <span class="la">· {{ $e->einsatzLeistungsarten->map(fn($el) => $el->leistungsart?->bezeichnung)->filter()->implode(', ') ?: '—' }}</span></td>
            <td class="c">
                @if($e->zeit_von)<span class="zeit">{{ $zt($e->zeit_von) }}–{{ $zt($e->zeit_bis) }} </span>@endif
                <strong>{{ $planMin > 0 ? $hm($planMin) : '—' }}</strong>
            </td>
            <td class="c">
                @if($istMin !== null)
                    @if($e->checkin_zeit)<span class="zeit">{{ $e->checkin_zeit->format('H:i') }}–{{ $e->checkout_zeit->format('H:i') }} </span>@endif
                    <strong>{{ $hm($istMin) }}</strong>
                @elseif($e->status === 'abgeschlossen')
                    <span style="color:#ccc;font-size:5.5pt;">kein Out</span>
                @else
                    <span class="d0">—</span>
                @endif
            </td>
            <td class="r">
                @if($diff !== null)
                    <span class="{{ $dKl }}">{{ $dSign }}{{ $hm(abs($diff)) }}</span>
                @else
                    <span class="d0">—</span>
                @endif
            </td>
        </tr>
        @endforeach
        @if($anzahl > 1)
        @php $td = $tagIst - $tagPlan; @endphp
        <tr class="tsum">
            <td></td>
            <td style="color:#999;">Tagessumme ({{ $anzahl }} Einsätze)</td>
            <td class="c"><strong>{{ $hm($tagPlan) }}</strong></td>
            <td class="c"><strong>{{ $tagIst > 0 ? $hm($tagIst) : '—' }}</strong></td>
            <td class="r"><span class="{{ $td>=0?'dp':'dn' }}">{{ $td>=0?'+':'−' }}{{ $hm(abs($td)) }}</span></td>
        </tr>
        @endif
    @endif
    @endforeach

    {{-- Letzte Wochensumme --}}
    @php $wd = $wocheIst - $wochePlan; @endphp
    <tr class="wsum">
        <td colspan="2">KW {{ $wocheNr }}</td>
        <td class="c">{{ $wochePlan > 0 ? $hm($wochePlan) : '—' }}</td>
        <td class="c">{{ $wocheIst > 0 ? $hm($wocheIst) : '—' }}</td>
        <td class="r">
            @if($wochePlan > 0 || $wocheIst > 0)
                <span class="{{ $wd>=0?'dp':'dn' }}">{{ $wd>=0?'+':'−' }}{{ $hm(abs($wd)) }}</span>
            @else <span class="d0">—</span>
            @endif
        </td>
    </tr>

    {{-- Monatstotal --}}
    @php $gd = $gesamtIst - $gesamtPlan; @endphp
    <tr class="total">
        <td colspan="2">MONATSGESAMT — {{ $totalAnz }} Einsätze</td>
        <td class="c">{{ $hm($gesamtPlan) }}</td>
        <td class="c">{{ $gesamtIst > 0 ? $hm($gesamtIst) : '—' }}</td>
        <td class="r">@if($gesamtIst > 0){{ $gd>=0?'+':'−' }}{{ $hm(abs($gd)) }}@else —@endif</td>
    </tr>
    </tbody>
</table>

{{-- Unterschrift --}}
<div class="sign">
    <div class="sign-l"><div class="sign-linie">Datum, Unterschrift Mitarbeiter/in</div></div>
    <div class="sign-r"><div class="sign-linie">Datum, Unterschrift Vorgesetzte/r</div></div>
</div>

</div>
</body>
</html>
