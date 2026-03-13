<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 7.5pt;
    color: #1a1a1a;
    line-height: 1.35;
}
.seite { padding: 10mm 14mm 18mm 14mm; }

/* Kopf */
.kopf { display:table; width:100%; margin-bottom:5mm; border-bottom:1pt solid #ccc; padding-bottom:3mm; }
.kopf-links  { display:table-cell; vertical-align:middle; width:55%; }
.kopf-rechts { display:table-cell; vertical-align:middle; width:45%; text-align:right; }
.kopf-logo   { max-height:9mm; max-width:40mm; display:block; }
.dok-titel   { font-size:10pt; font-weight:bold; color:#1a1a1a; }
.org-name    { font-size:7pt; color:#555; margin-top:0.5mm; }

/* MA-Info */
.ma-info { display:table; width:100%; margin-bottom:4mm; }
.ma-links  { display:table-cell; width:60%; }
.ma-rechts { display:table-cell; width:40%; text-align:right; vertical-align:top; }
.info-zeile { font-size:6.5pt; color:#555; margin-bottom:0.5mm; }
.info-zeile span.v { font-weight:bold; color:#1a1a1a; }
.monat-gross { font-size:9pt; font-weight:bold; }

/* Legende */
.legende { display:table; width:100%; margin-bottom:2mm; font-size:6pt; color:#777; }
.leg-item { display:table-cell; padding-right:4mm; }
.farb-box { display:inline-block; width:6pt; height:6pt; margin-right:1.5pt; vertical-align:middle; }

/* Tabelle */
table.zeitraster {
    width: 100%;
    border-collapse: collapse;
    font-size: 7pt;
}
table.zeitraster thead tr {
    background: #2c5f8a;
    color: #fff;
}
table.zeitraster thead th {
    padding: 1.8mm 1.5mm;
    text-align: left;
    font-weight: bold;
    font-size: 6.5pt;
    white-space: nowrap;
}
table.zeitraster thead th.r { text-align: right; }
table.zeitraster thead th.c { text-align: center; }

/* Einsatz-Zeilen */
table.zeitraster tr.einsatz td {
    padding: 1.2mm 1.5mm;
    border-bottom: 0.3pt solid #e8e8e8;
    vertical-align: top;
}
table.zeitraster tr.einsatz-erst td {
    border-top: 0.4pt solid #bbb;
}
table.zeitraster tr.einsatz-letzt td {
    border-bottom: 0.4pt solid #bbb;
}

/* Leerer Tag */
table.zeitraster tr.leer-tag td {
    padding: 0.8mm 1.5mm;
    border-top: 0.4pt solid #ddd;
    border-bottom: 0.4pt solid #ddd;
    color: #aaa;
    font-size: 6.5pt;
}

/* Wochenende */
table.zeitraster tr.wochenende td { background: #f7f7f7; }
table.zeitraster tr.einsatz.wochenende td { background: #f7f7f7; }

/* Tagessumme */
table.zeitraster tr.tagessumme td {
    padding: 0.5mm 1.5mm 1.5mm 1.5mm;
    font-size: 6.5pt;
    color: #555;
    border-bottom: 0.5pt solid #ccc;
    background: #fafafa;
}

/* Wochensumme */
table.zeitraster tr.wochensumme td {
    padding: 1.5mm 1.5mm;
    font-weight: bold;
    font-size: 7pt;
    background: #eef4fa;
    border-top: 0.8pt solid #2c5f8a;
    border-bottom: 0.8pt solid #2c5f8a;
}

/* Monatstotal */
table.zeitraster tr.total td {
    padding: 2mm 1.5mm;
    font-weight: bold;
    font-size: 7.5pt;
    background: #2c5f8a;
    color: #fff;
    border-top: 1pt solid #1a4060;
}

.r { text-align: right; }
.c { text-align: center; }

.tag-nr   { font-size: 8pt; font-weight: bold; display: block; }
.tag-name { font-size: 6pt; color: #777; display: block; }
.tag-nr.we { color: #999; }

.klient-name  { font-weight: bold; }
.leistung-txt { font-size: 6.5pt; color: #666; }

.zeit-block { font-size: 7pt; }
.zeit-hhmm  { font-weight: bold; }
.zeit-von-bis { font-size: 6pt; color: #777; display: block; }

.diff-pos { color: #1a6644; }
.diff-neg { color: #c0392b; }
.diff-null { color: #aaa; }

/* Unterschrift */
.unterschrift {
    margin-top: 8mm;
    display: table;
    width: 100%;
}
.unt-block {
    display: table-cell;
    width: 48%;
    padding: 0 3mm;
}
.unt-linie {
    border-top: 0.7pt solid #333;
    padding-top: 1.5mm;
    font-size: 6.5pt;
    color: #555;
    margin-top: 8mm;
}

/* Fusszeile */
.fusszeile {
    position: fixed;
    bottom: 8mm;
    left: 14mm;
    right: 14mm;
    border-top: 0.4pt solid #ccc;
    padding-top: 1.5mm;
    font-size: 5.5pt;
    color: #aaa;
    display: table;
    width: 100%;
}
.fuss-links  { display: table-cell; }
.fuss-rechts { display: table-cell; text-align: right; }
</style>
</head>
<body>
<div class="seite">

{{-- Kopfzeile --}}
<div class="kopf">
    <div class="kopf-links">
        @if(!empty($logoBase64))
            <img class="kopf-logo" src="data:image/png;base64,{{ $logoBase64 }}">
        @else
            <span style="font-size:8pt; font-weight:bold; color:#2c5f8a;">{{ $org->name }}</span>
        @endif
    </div>
    <div class="kopf-rechts">
        <span class="dok-titel">Arbeitszeit-Nachweis</span>
        <div class="org-name">{{ $org->name }}</div>
    </div>
</div>

{{-- Mitarbeiter-Info --}}
<div class="ma-info">
    <div class="ma-links">
        <div class="info-zeile">Mitarbeiterin / Mitarbeiter: <span class="v">{{ $benutzer->vorname }} {{ $benutzer->nachname }}</span></div>
        <div class="info-zeile">Rolle: <span class="v">{{ ucfirst($benutzer->rolle) }}</span>
            @if($benutzer->anstellungsart && $benutzer->anstellungsart !== 'fachperson')
                &nbsp;· Anstellungsart: <span class="v">{{ ucfirst($benutzer->anstellungsart) }}</span>
            @endif
            @if($benutzer->pensum)
                &nbsp;· Pensum: <span class="v">{{ $benutzer->pensum }}%</span>
            @endif
        </div>
        @if($benutzer->email)
        <div class="info-zeile">E-Mail: <span class="v">{{ $benutzer->email }}</span></div>
        @endif
    </div>
    <div class="ma-rechts">
        <div class="monat-gross">{{ $von->locale('de')->isoFormat('MMMM YYYY') }}</div>
        <div class="info-zeile" style="margin-top:1mm;">{{ $von->format('d.m.Y') }} – {{ $bis->format('d.m.Y') }}</div>
        <div class="info-zeile" style="margin-top:1mm; color:#aaa;">Erstellt: {{ now()->format('d.m.Y H:i') }}</div>
    </div>
</div>

{{-- Zeitraster-Tabelle --}}
<table class="zeitraster">
    <thead>
        <tr>
            <th style="width:11mm;">Tag</th>
            <th>Klient / Leistungsart</th>
            <th class="c" style="width:20mm;">Geplant</th>
            <th class="c" style="width:20mm;">Geleistet</th>
            <th class="r" style="width:14mm;">Plan Min</th>
            <th class="r" style="width:14mm;">Ist Min</th>
            <th class="r" style="width:14mm;">Differenz</th>
        </tr>
    </thead>
    <tbody>
@php
    $kalenderTage = collect();
    $tag = $von->copy();
    while ($tag->lte($bis)) {
        $kalenderTage->push($tag->copy());
        $tag->addDay();
    }

    // Einsätze nach Datum gruppieren
    $einsaetzeNachDatum = $einsaetze->groupBy(fn($e) => $e->datum->format('Y-m-d'));

    $wocheNr        = null;
    $wochePlanMin   = 0;
    $wocheIstMin    = 0;
    $gesamtPlanMin  = 0;
    $gesamtIstMin   = 0;
    $einsaetzeGesamt = 0;

    $tagLabels = ['Mo','Di','Mi','Do','Fr','Sa','So'];

    foreach ($kalenderTage as $tag) {
        $tagKey    = $tag->format('Y-m-d');
        $tagNr     = (int)$tag->format('N'); // 1=Mo, 7=So
        $tagLabel  = $tagLabels[$tagNr - 1];
        $istWE     = $tagNr >= 6;
        $weKlasse  = $istWE ? ' wochenende' : '';

        // Wochenwechsel → Wochensumme ausgeben
        $neueWoche = $tag->copy()->startOfWeek()->format('W');
        if ($wocheNr !== null && $neueWoche !== $wocheNr) {
            $wpDiff = $wocheIstMin - $wochePlanMin;
            $wpSign = $wpDiff >= 0 ? '+' : '−';
            $wpAbs  = abs($wpDiff);
@endphp
        <tr class="wochensumme">
            <td colspan="4" style="font-size:6.5pt;">Woche {{ $wocheNr }} — Zwischentotal</td>
            <td class="r">@php echo ($wochePlanMin > 0 ? intdiv($wochePlanMin,60).':'.str_pad($wochePlanMin%60,2,'0',STR_PAD_LEFT) : '—') @endphp</td>
            <td class="r">@php echo ($wocheIstMin > 0 ? intdiv($wocheIstMin,60).':'.str_pad($wocheIstMin%60,2,'0',STR_PAD_LEFT) : '—') @endphp</td>
            <td class="r">
                @if($wocheIstMin > 0 || $wochePlanMin > 0)
                <span class="@php echo $wpDiff >= 0 ? 'diff-pos' : 'diff-neg' @endphp">
                    {{ $wpSign }}@php echo intdiv($wpAbs,60).':'.str_pad($wpAbs%60,2,'0',STR_PAD_LEFT) @endphp
                </span>
                @else —
                @endif
            </td>
        </tr>
@php
            $wocheIstMin  = 0;
            $wochePlanMin = 0;
        }
        $wocheNr = $neueWoche;

        $tagesEinsaetze = $einsaetzeNachDatum->get($tagKey, collect());
        $tagPlanMin = $tagesEinsaetze->sum('minuten');
        $tagIstMin  = $tagesEinsaetze->whereNotNull('ist_minuten')->sum('ist_minuten');
        $wochePlanMin  += $tagPlanMin;
        $wocheIstMin   += $tagIstMin;
        $gesamtPlanMin += $tagPlanMin;
        $gesamtIstMin  += $tagIstMin;
        $einsaetzeGesamt += $tagesEinsaetze->count();

        if ($tagesEinsaetze->isEmpty()):
@endphp
        <tr class="leer-tag{{ $weKlasse }}">
            <td>
                <span class="tag-nr @php echo $istWE ? 'we' : '' @endphp">{{ $tag->format('d.') }}</span>
                <span class="tag-name">{{ $tagLabel }}</span>
            </td>
            <td colspan="6" style="color:#ccc;">—</td>
        </tr>
@php
        else:
            $anzahl = $tagesEinsaetze->count();
            $idx = 0;
            foreach ($tagesEinsaetze as $e):
                $planMin = (int)($e->minuten ?? 0);
                $istMin  = $e->ist_minuten;
                $diff    = $istMin !== null ? ($istMin - $planMin) : null;
                $dSign   = $diff !== null ? ($diff >= 0 ? '+' : '−') : null;
                $dAbs    = $diff !== null ? abs($diff) : null;
                $dKl     = $diff === null ? 'diff-null' : ($diff > 0 ? 'diff-pos' : ($diff < 0 ? 'diff-neg' : 'diff-null'));
                $erstKl  = $idx === 0 ? ' einsatz-erst' : '';
                $letzKl  = $idx === $anzahl - 1 ? ' einsatz-letzt' : '';
@endphp
        <tr class="einsatz{{ $erstKl }}{{ $letzKl }}{{ $weKlasse }}">
            <td>
                @if($idx === 0)
                <span class="tag-nr @php echo $istWE ? 'we' : '' @endphp">{{ $tag->format('d.') }}</span>
                <span class="tag-name">{{ $tagLabel }}</span>
                @endif
            </td>
            <td>
                <span class="klient-name">{{ $e->klient?->vorname }} {{ $e->klient?->nachname }}</span>
                <span class="leistung-txt"> · {{ $e->leistungsart?->bezeichnung ?? '—' }}</span>
            </td>
            <td class="c">
                @if($e->zeit_von)
                    <span class="zeit-von-bis">{{ $e->zeit_von }}–{{ $e->zeit_bis }}</span>
                @endif
                <span class="zeit-hhmm">@php echo $planMin > 0 ? intdiv($planMin,60).':'.str_pad($planMin%60,2,'0',STR_PAD_LEFT) : '—' @endphp</span>
            </td>
            <td class="c">
                @if($istMin !== null)
                    @if($e->checkin_zeit)
                        <span class="zeit-von-bis">{{ $e->checkin_zeit->format('H:i') }}–{{ $e->checkout_zeit->format('H:i') }}</span>
                    @endif
                    <span class="zeit-hhmm">@php echo intdiv($istMin,60).':'.str_pad($istMin%60,2,'0',STR_PAD_LEFT) @endphp</span>
                @elseif($e->status === 'abgeschlossen')
                    <span style="font-size:6pt; color:#bbb;">kein COut</span>
                @else
                    <span class="diff-null">—</span>
                @endif
            </td>
            <td class="r">{{ $planMin > 0 ? $planMin : '—' }}</td>
            <td class="r">{{ $istMin !== null ? $istMin : '—' }}</td>
            <td class="r">
                @if($diff !== null)
                    <span class="{{ $dKl }}">{{ $dSign }}{{ $dAbs }}</span>
                @else
                    <span class="diff-null">—</span>
                @endif
            </td>
        </tr>
@php
                $idx++;
            endforeach;

            // Tagessumme (nur wenn >1 Einsatz)
            if ($anzahl > 1):
                $tdDiff = $tagIstMin - $tagPlanMin;
                $tdSign = $tdDiff >= 0 ? '+' : '−';
                $tdAbs  = abs($tdDiff);
@endphp
        <tr class="tagessumme{{ $weKlasse }}">
            <td></td>
            <td style="color:#999;">Tagessumme ({{ $anzahl }} Einsätze)</td>
            <td class="c">@php echo intdiv($tagPlanMin,60).':'.str_pad($tagPlanMin%60,2,'0',STR_PAD_LEFT) @endphp</td>
            <td class="c">@php echo $tagIstMin > 0 ? intdiv($tagIstMin,60).':'.str_pad($tagIstMin%60,2,'0',STR_PAD_LEFT) : '—' @endphp</td>
            <td class="r">{{ $tagPlanMin }}</td>
            <td class="r">{{ $tagIstMin > 0 ? $tagIstMin : '—' }}</td>
            <td class="r">
                @if($tagIstMin > 0)
                <span class="@php echo $tdDiff >= 0 ? 'diff-pos' : 'diff-neg' @endphp">{{ $tdSign }}{{ $tdAbs }}</span>
                @else —
                @endif
            </td>
        </tr>
@php
            endif;
        endif; // leerer Tag
    } // foreach tag

    // Letzte Wochensumme ausgeben
    $wpDiff = $wocheIstMin - $wochePlanMin;
    $wpSign = $wpDiff >= 0 ? '+' : '−';
    $wpAbs  = abs($wpDiff);
@endphp
        <tr class="wochensumme">
            <td colspan="4" style="font-size:6.5pt;">Woche {{ $wocheNr }} — Zwischentotal</td>
            <td class="r">@php echo ($wochePlanMin > 0 ? intdiv($wochePlanMin,60).':'.str_pad($wochePlanMin%60,2,'0',STR_PAD_LEFT) : '—') @endphp</td>
            <td class="r">@php echo ($wocheIstMin > 0 ? intdiv($wocheIstMin,60).':'.str_pad($wocheIstMin%60,2,'0',STR_PAD_LEFT) : '—') @endphp</td>
            <td class="r">
                @if($wocheIstMin > 0 || $wochePlanMin > 0)
                <span class="@php echo $wpDiff >= 0 ? 'diff-pos' : 'diff-neg' @endphp">
                    {{ $wpSign }}@php echo intdiv($wpAbs,60).':'.str_pad($wpAbs%60,2,'0',STR_PAD_LEFT) @endphp
                </span>
                @else —
                @endif
            </td>
        </tr>

        {{-- Monatstotal --}}
@php
    $gesDiff = $gesamtIstMin - $gesamtPlanMin;
    $gesSign = $gesDiff >= 0 ? '+' : '−';
    $gesAbs  = abs($gesDiff);
@endphp
        <tr class="total">
            <td colspan="4">MONATSGESAMT — {{ $einsaetzeGesamt }} Einsätze</td>
            <td class="r">@php echo intdiv($gesamtPlanMin,60).':'.str_pad($gesamtPlanMin%60,2,'0',STR_PAD_LEFT) @endphp</td>
            <td class="r">@php echo $gesamtIstMin > 0 ? intdiv($gesamtIstMin,60).':'.str_pad($gesamtIstMin%60,2,'0',STR_PAD_LEFT) : '—' @endphp</td>
            <td class="r">
                @if($gesamtIstMin > 0)
                    {{ $gesSign }}@php echo intdiv($gesAbs,60).':'.str_pad($gesAbs%60,2,'0',STR_PAD_LEFT) @endphp
                @else —
                @endif
            </td>
        </tr>
    </tbody>
</table>

{{-- Legende --}}
<div style="margin-top:2mm; font-size:5.5pt; color:#aaa;">
    Geplant = erfasste Einsatz-Minuten (Soll). Geleistet = Zeit aus Check-in/Check-out (Ist). Differenz = Ist minus Soll. Minuten-Spalten für Lohnabrechnungsprogramm.
</div>

{{-- Unterschriftsblock --}}
<div class="unterschrift">
    <div class="unt-block" style="padding-left:0;">
        <div class="unt-linie">Datum, Unterschrift Mitarbeiter/in</div>
    </div>
    <div class="unt-block" style="padding-right:0;">
        <div class="unt-linie">Datum, Unterschrift Vorgesetzte/r</div>
    </div>
</div>

</div>{{-- .seite --}}

{{-- Fusszeile --}}
<div class="fusszeile">
    <div class="fuss-links">{{ $org->name }} · Arbeitszeit-Nachweis {{ $benutzer->vorname }} {{ $benutzer->nachname }} · {{ $von->locale('de')->isoFormat('MMMM YYYY') }}</div>
    <div class="fuss-rechts">Erstellt {{ now()->format('d.m.Y') }}</div>
</div>

</body>
</html>
