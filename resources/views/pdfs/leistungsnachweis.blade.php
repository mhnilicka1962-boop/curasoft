<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #111; }

.header { border-bottom: 1pt solid #333; padding-bottom: 3mm; margin-bottom: 5mm; }
.header h1 { font-size: 12pt; font-weight: bold; margin-bottom: 1mm; letter-spacing: 0.02em; }
.header .meta { font-size: 7.5pt; color: #555; }

table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
thead tr { border-bottom: 0.75pt solid #333; background: #f5f5f5; }
thead th { padding: 1.5mm 2mm; font-size: 7pt; font-weight: bold; text-align: left; }
thead th.r { text-align: right; }
tbody tr { border-bottom: 0.3pt solid #e0e0e0; }
tbody tr:nth-child(even) { background: #fafafa; }
tbody td { padding: 1.2mm 2mm; font-size: 7.5pt; vertical-align: middle; }
tbody td.r { text-align: right; font-family: DejaVu Sans Mono, monospace; }
tbody td.hell { color: #666; }

.summary { border-top: 0.75pt solid #333; margin-top: 3mm; padding-top: 4mm; }
.summary table { margin-bottom: 0; }
.summary thead th { font-size: 6.5pt; color: #555; background: none; }
.summary tfoot td { font-weight: bold; border-top: 0.5pt solid #333; padding: 1.2mm 2mm; }
</style>
</head>
<body>

<div class="header">
    <h1>Tagesrapport</h1>
    <div class="meta">
        {{ $klientName }} &nbsp;·&nbsp;
        {{ $rechnung->periode_von->format('d.m.Y') }} – {{ $rechnung->periode_bis->format('d.m.Y') }}
        &nbsp;·&nbsp; {{ $rechnung->rechnungsnummer }}
        @if($org->name) &nbsp;·&nbsp; {{ $org->name }} @endif
    </div>
</div>

@php
    $summary = [];
    $totalMin = 0;
@endphp

<table>
    <thead>
        <tr>
            <th style="width:13%">Datum</th>
            <th style="width:14%">Zeit</th>
            <th class="r" style="width:8%">Min</th>
            <th style="width:30%">Leistungsart</th>
            <th style="width:35%">Mitarbeiter</th>
        </tr>
    </thead>
    <tbody>
    @foreach($einsaetze as $e)
    @php
        $von  = $e->checkin_zeit?->format('H:i') ?? ($e->zeit_von ? substr($e->zeit_von, 0, 5) : null);
        $bis  = $e->checkout_zeit?->format('H:i') ?? ($e->zeit_bis ? substr($e->zeit_bis, 0, 5) : null);
        $zeit = ($von && $bis) ? $von . '–' . $bis : ($von ?? '—');
        $ma   = $e->benutzer ? $e->benutzer->vorname . ' ' . $e->benutzer->nachname : '—';
        $min  = (int) ($e->minuten ?? 0);
        $las  = $e->einsatzLeistungsarten
                  ->map(fn($el) => $el->leistungsart?->bezeichnung)
                  ->filter()->implode(', ');
        if (!$las) $las = '—';

        // Summary
        foreach ($e->einsatzLeistungsarten as $el) {
            $laName = $el->leistungsart?->bezeichnung ?? '—';
            if (!isset($summary[$laName])) $summary[$laName] = ['einsaetze' => 0, 'min' => 0];
            $summary[$laName]['einsaetze']++;
            $summary[$laName]['min'] += (int)($el->minuten ?? $min);
        }
        $totalMin += $min;
    @endphp
    <tr>
        <td>{{ $e->datum->format('d.m.Y') }}</td>
        <td class="hell">{{ $zeit }}</td>
        <td class="r">{{ $min }}</td>
        <td>{{ $las }}</td>
        <td class="hell">{{ $ma }}</td>
    </tr>
    @endforeach
    </tbody>
</table>

<div class="summary">
    <table>
        <thead>
            <tr>
                <th style="width:40%">Leistungsart</th>
                <th class="r" style="width:20%">Einsätze</th>
                <th class="r" style="width:20%">Total Min</th>
                <th class="r" style="width:20%">Total Std</th>
            </tr>
        </thead>
        <tbody>
        @foreach($summary as $laName => $s)
        <tr>
            <td>{{ $laName }}</td>
            <td class="r">{{ $s['einsaetze'] }}</td>
            <td class="r">{{ $s['min'] }}</td>
            <td class="r">{{ number_format($s['min'] / 60, 2, '.', '') }}</td>
        </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="r">{{ count($einsaetze) }}</td>
                <td class="r">{{ $totalMin }}</td>
                <td class="r">{{ number_format($totalMin / 60, 2, '.', '') }}</td>
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>
