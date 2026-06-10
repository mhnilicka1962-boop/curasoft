<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #111; }

.header { border-bottom: 1pt solid #333; padding-bottom: 3mm; margin-bottom: 5mm; }
.header h1 { font-size: 11pt; font-weight: bold; margin-bottom: 1mm; }
.header .meta { font-size: 7.5pt; color: #555; }

table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
thead tr { border-bottom: 0.75pt solid #333; background: #f5f5f5; }
thead th { padding: 1.5mm 2mm; font-size: 7pt; font-weight: bold; text-align: left; }
thead th.r { text-align: right; }
tbody tr { border-bottom: 0.3pt solid #e0e0e0; }
tbody tr:nth-child(even) { background: #fafafa; }
tbody td { padding: 1mm 2mm; font-size: 7.5pt; vertical-align: middle; }
tbody td.r { text-align: right; font-family: DejaVu Sans Mono, monospace; }
tbody td.hell { color: #666; }

.summary { border-top: 0.75pt solid #333; margin-top: 2mm; padding-top: 3mm; }
.summary table { margin-bottom: 0; }
.summary thead tr { background: none; border-bottom: 0.5pt solid #aaa; }
.summary thead th { font-size: 6.5pt; color: #555; }
.summary tbody td { font-size: 7.5pt; padding: 0.8mm 2mm; }
.summary tfoot td { font-weight: bold; border-top: 0.5pt solid #333; padding: 1mm 2mm; font-size: 7.5pt; }
</style>
</head>
<body>

<div class="header">
    <h1>Leistungsnachweis</h1>
    <div class="meta">
        {{ $klientName }} &nbsp;·&nbsp;
        {{ $rechnung->rechnungsnummer }} &nbsp;·&nbsp;
        {{ $rechnung->periode_von->format('d.m.Y') }} – {{ $rechnung->periode_bis->format('d.m.Y') }}
        @if($org->name) &nbsp;·&nbsp; {{ $org->name }} @endif
    </div>
</div>

@php
    $zeilen = $positionen
        ->filter(fn($p) => $p->menge > 0 && !in_array($p->einheit, ['tage', 'pauschal']))
        ->sortBy('datum');

    $summary = [];
    foreach ($zeilen as $p) {
        $la = $p->einsatzLeistungsart?->leistungsart?->bezeichnung ?? $p->beschreibung ?? '—';
        if (!isset($summary[$la])) $summary[$la] = ['einsaetze' => 0, 'min' => 0];
        $summary[$la]['einsaetze']++;
        $summary[$la]['min'] += (int)$p->menge;
    }
    $totalEinsaetze = array_sum(array_column($summary, 'einsaetze'));
    $totalMin       = array_sum(array_column($summary, 'min'));
@endphp

<table>
    <thead>
        <tr>
            <th style="width:12%">Datum</th>
            <th style="width:14%">Zeit</th>
            <th class="r" style="width:9%">Min</th>
            <th style="width:28%">Leistungsart</th>
            <th style="width:37%">Mitarbeiter</th>
        </tr>
    </thead>
    <tbody>
    @foreach($zeilen as $p)
    @php
        $e    = $p->einsatz;
        $von  = $e?->checkin_zeit?->format('H:i') ?? ($e?->zeit_von ? substr($e->zeit_von, 0, 5) : null);
        $bis  = $e?->checkout_zeit?->format('H:i') ?? ($e?->zeit_bis ? substr($e->zeit_bis, 0, 5) : null);
        $zeit = ($von && $bis) ? $von . '–' . $bis : ($von ?? '—');
        $la   = $p->einsatzLeistungsart?->leistungsart?->bezeichnung ?? $p->beschreibung ?? '—';
        $ma   = $e?->benutzer ? $e->benutzer->vorname . ' ' . $e->benutzer->nachname : '—';
    @endphp
    <tr>
        <td>{{ $p->datum->format('d.m.Y') }}</td>
        <td class="hell">{{ $zeit }}</td>
        <td class="r">{{ (int)$p->menge }}</td>
        <td>{{ $la }}</td>
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
        @foreach($summary as $la => $s)
        <tr>
            <td>{{ $la }}</td>
            <td class="r">{{ $s['einsaetze'] }}</td>
            <td class="r">{{ $s['min'] }}</td>
            <td class="r">{{ number_format($s['min'] / 60, 2, '.', '') }}</td>
        </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="r">{{ $totalEinsaetze }}</td>
                <td class="r">{{ $totalMin }}</td>
                <td class="r">{{ number_format($totalMin / 60, 2, '.', '') }}</td>
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>
