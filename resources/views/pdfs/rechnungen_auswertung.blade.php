<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; font-family: DejaVu Sans, Arial, sans-serif; }
body { font-size:8pt; color:#1a1a1a; line-height:1.3; }
.seite { padding:7mm 8mm 7mm 8mm; }

/* Kopf */
.kopf-titel { font-size:9pt; font-weight:bold; margin-bottom:1.5mm; }
table.meta { font-size:7pt; border-collapse:collapse; margin-bottom:3mm; width:100%; }
table.meta td { padding:0.2mm 4mm 0.2mm 0; vertical-align:top; }
table.meta td.lbl { color:#555; white-space:nowrap; min-width:28mm; }
table.meta td.val { font-weight:500; }

/* Tabelle */
table.auswertung { width:100%; border-collapse:collapse; }
table.auswertung thead th {
    background:#1a3a5c;
    color:#fff;
    font-size:7pt;
    font-weight:bold;
    padding:1.5mm 2mm;
    border:0.3pt solid #0f2540;
    text-align:left;
}
table.auswertung thead th.r { text-align:right; }
table.auswertung tbody tr { border-bottom:0.2pt solid #d0d8e4; }
table.auswertung tbody tr:nth-child(even) { background:#f4f7fb; }
table.auswertung tbody td {
    padding:1mm 2mm;
    font-size:7pt;
    vertical-align:middle;
}
table.auswertung tbody td.r { text-align:right; }
table.auswertung tbody td.hell { color:#666; }
table.auswertung tfoot td {
    background:#cdd5e0;
    font-weight:bold;
    font-size:7.5pt;
    padding:1.5mm 2mm;
    border-top:0.5pt solid #999;
}
table.auswertung tfoot td.r { text-align:right; }

.badge-entwurf   { color:#555; }
.badge-gesendet  { color:#1a6cb7; font-weight:bold; }
.badge-bezahlt   { color:#166534; font-weight:bold; }
.badge-storniert { color:#b91c1c; }

.fuss { position:fixed; bottom:5mm; left:8mm; right:8mm; font-size:6pt; color:#aaa; border-top:0.5pt solid #eee; padding-top:1mm; display:table; width:100%; }
.fuss-links  { display:table-cell; }
.fuss-rechts { display:table-cell; text-align:right; }
</style>
</head>
<body>
<div class="seite">

    <div class="kopf-titel">Rechnungen Auswertung</div>

    <table class="meta">
        <tr>
            <td class="lbl">Organisation</td>
            <td class="val">{{ $org->name }}</td>
            <td class="lbl">Erstellt am</td>
            <td class="val">{{ now()->format('d.m.Y H:i') }}</td>
        </tr>
        <tr>
            <td class="lbl">Filter</td>
            <td class="val" colspan="3">
                @if($filterJahr || $filterMonat)
                    Periode: {{ $monatsname }} {{ $filterJahr }}
                @endif
                @if($filterStatus) &nbsp;·&nbsp; Status: {{ ucfirst($filterStatus) }} @endif
                @if($filterSuche) &nbsp;·&nbsp; Suche: «{{ $filterSuche }}» @endif
                @if(!$filterJahr && !$filterMonat && !$filterStatus && !$filterSuche) Alle Rechnungen @endif
            </td>
        </tr>
        <tr>
            <td class="lbl">Anzahl</td>
            <td class="val">{{ $rechnungen->count() }} Rechnungen</td>
            <td class="lbl">Total</td>
            <td class="val">CHF {{ number_format($summeTotal, 2, '.', "'") }}</td>
        </tr>
    </table>

    <table class="auswertung">
        <thead>
            <tr>
                <th>Rechnungsnummer</th>
                <th>Datum</th>
                <th>Klient</th>
                <th>Typ</th>
                <th>Status</th>
                <th class="r">Betrag Pat. CHF</th>
                <th class="r">Betrag KK CHF</th>
                <th class="r">Total CHF</th>
                <th>Zahlungseingang</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rechnungen as $r)
            <tr>
                <td style="font-family:monospace; font-size:6.5pt;">{{ $r->rechnungsnummer }}</td>
                <td class="hell">{{ $r->rechnungsdatum?->format('d.m.Y') }}</td>
                <td>{{ $r->klient?->nachname }} {{ $r->klient?->vorname }}</td>
                <td class="hell">{{ ucfirst($r->rechnungstyp ?? '—') }}</td>
                <td class="badge-{{ $r->status }}">{{ ucfirst($r->status) }}</td>
                <td class="r">{{ number_format((float)$r->betrag_patient, 2, '.', "'") }}</td>
                <td class="r">{{ number_format((float)$r->betrag_kk, 2, '.', "'") }}</td>
                <td class="r" style="font-weight:600;">{{ number_format((float)$r->betrag_total, 2, '.', "'") }}</td>
                <td class="hell">{{ $r->bezahlt_am?->format('d.m.Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">Total ({{ $rechnungen->count() }} Rechnungen)</td>
                <td class="r">CHF {{ number_format($summePatient, 2, '.', "'") }}</td>
                <td class="r">CHF {{ number_format($summeKk, 2, '.', "'") }}</td>
                <td class="r">CHF {{ number_format($summeTotal, 2, '.', "'") }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

</div>

<div class="fuss">
    <div class="fuss-links">{{ $org->name }} · Rechnungen Auswertung · {{ ($filterJahr || $filterMonat) ? trim($monatsname . ' ' . $filterJahr) : 'Alle' }}</div>
    <div class="fuss-rechts">{{ now()->format('d.m.Y') }}</div>
</div>

</body>
</html>
