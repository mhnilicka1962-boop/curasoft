<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #111; }

.header { border-bottom: 1pt solid #333; padding-bottom: 3mm; margin-bottom: 5mm; }
.header h1 { font-size: 12pt; font-weight: bold; margin-bottom: 1mm; }
.header .meta { font-size: 7.5pt; color: #555; }

table { width: 100%; border-collapse: collapse; }
thead tr { border-bottom: 0.75pt solid #333; background: #f5f5f5; }
thead th { padding: 1.5mm 2mm; font-size: 7pt; font-weight: bold; text-align: left; }
thead th.r { text-align: right; }
tbody tr { border-bottom: 0.3pt solid #e0e0e0; }
tbody tr:nth-child(even) { background: #fafafa; }
tbody td { padding: 1.2mm 2mm; font-size: 7.5pt; vertical-align: middle; }
tbody td.r { text-align: right; font-family: DejaVu Sans Mono, monospace; }
tbody td.hell { color: #666; }
tbody td.datum-td { font-weight: 600; }
tbody td.datum-leer { color: transparent; }

.neue-seite { page-break-before: always; padding-top: 10mm; }
.summary-titel { font-size: 10pt; font-weight: bold; margin-bottom: 4mm; }
tfoot tr { background: #f5f5f5; font-weight: bold; border-top: 0.75pt solid #333; }
tfoot td { padding: 1.5mm 2mm; }
tfoot td.r { text-align: right; }
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
    // Zeilen aufbauen: pro Einsatz × pro Leistungsart → eine Zeile
    $zeilen = [];
    $summary = [];
    foreach ($einsaetze as $e) {
        $von = $e->checkin_zeit?->format('H:i') ?? ($e->zeit_von ? substr($e->zeit_von, 0, 5) : null);
        $bis = $e->checkout_zeit?->format('H:i') ?? ($e->zeit_bis ? substr($e->zeit_bis, 0, 5) : null);
        $zeit = ($von && $bis) ? $von . '–' . $bis : ($von ?? '—');
        $ma   = $e->benutzer ? $e->benutzer->vorname . ' ' . $e->benutzer->nachname : '—';
        $datum = $e->datum->format('d.m.Y');

        $las = $e->einsatzLeistungsarten;
        if ($las->isEmpty()) {
            $zeilen[] = ['datum' => $datum, 'zeit' => $zeit, 'min' => (int)($e->minuten ?? 0), 'la' => '—', 'ma' => $ma];
        } else {
            foreach ($las as $el) {
                $laName = $el->leistungsart?->bezeichnung ?? '—';
                $min    = (int)($el->minuten ?? $e->minuten ?? 0);
                $zeilen[] = ['datum' => $datum, 'zeit' => $zeit, 'min' => $min, 'la' => $laName, 'ma' => $ma];
                if (!isset($summary[$laName])) $summary[$laName] = ['anzahl' => 0, 'min' => 0];
                $summary[$laName]['anzahl']++;
                $summary[$laName]['min'] += $min;
            }
        }
    }
    // Datum nur bei erster Zeile pro Tag anzeigen
    $letztesDatum = null;
    foreach ($zeilen as &$z) {
        if ($z['datum'] === $letztesDatum) {
            $z['datum_zeigen'] = false;
        } else {
            $z['datum_zeigen'] = true;
            $letztesDatum = $z['datum'];
        }
    }
    unset($z);
    $totalMin = array_sum(array_column($summary, 'min'));
    $totalAnzahl = array_sum(array_column($summary, 'anzahl'));
@endphp

<table>
    <thead>
        <tr>
            <th style="width:13%">Datum</th>
            <th style="width:14%">Zeit</th>
            <th class="r" style="width:8%">Min</th>
            <th style="width:33%">Leistungsart</th>
            <th style="width:32%">Mitarbeiter</th>
        </tr>
    </thead>
    <tbody>
    @foreach($zeilen as $z)
    <tr>
        <td class="{{ $z['datum_zeigen'] ? 'datum-td' : 'datum-leer' }}">{{ $z['datum'] }}</td>
        <td class="hell">{{ $z['zeit'] }}</td>
        <td class="r">{{ $z['min'] }}</td>
        <td>{{ $z['la'] }}</td>
        <td class="hell">{{ $z['ma'] }}</td>
    </tr>
    @endforeach
    </tbody>
</table>

{{-- Zusammenfassung auf neuer Seite --}}
<div class="neue-seite">
    <div class="summary-titel">Zusammenfassung</div>
    <table>
        <thead>
            <tr>
                <th style="width:50%">Leistungsart</th>
                <th class="r" style="width:20%">Anzahl</th>
                <th class="r" style="width:15%">Total Min</th>
                <th class="r" style="width:15%">Total Std</th>
            </tr>
        </thead>
        <tbody>
        @foreach($summary as $laName => $s)
        <tr>
            <td>{{ $laName }}</td>
            <td class="r">{{ $s['anzahl'] }}</td>
            <td class="r">{{ $s['min'] }}</td>
            <td class="r">{{ number_format($s['min'] / 60, 2, '.', '') }}</td>
        </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                <td class="r">{{ $totalAnzahl }}</td>
                <td class="r">{{ $totalMin }}</td>
                <td class="r">{{ number_format($totalMin / 60, 2, '.', '') }}</td>
            </tr>
        </tfoot>
    </table>
</div>

</body>
</html>
