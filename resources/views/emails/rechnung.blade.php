<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #222; line-height: 1.6; }
        .kopf { margin-bottom: 24px; border-bottom: 2px solid #1a56db; padding-bottom: 12px; }
        .org-name { font-size: 18px; font-weight: bold; color: #1a56db; }
        .betrag { font-size: 18px; font-weight: bold; margin: 16px 0; }
        .info-tabelle { border-collapse: collapse; margin: 16px 0; }
        .info-tabelle td { padding: 4px 12px 4px 0; }
        .lbl { color: #555; }
        .fuss { margin-top: 32px; padding-top: 16px; border-top: 1px solid #ddd; color: #555; font-size: 12px; }
    </style>
</head>
<body>

<div class="kopf">
    <div class="org-name">{{ $org->name }}</div>
</div>

<p>
    @if($klient->anrede) {{ $klient->anrede }} @endif{{ $klient->vorname }} {{ $klient->nachname }},
</p>

<p>anbei erhalten Sie Ihre Rechnung für die geleisteten Pflegedienste.</p>

<div class="betrag">
    CHF {{ number_format($rechnung->betrag_total, 2, '.', "'") }}
</div>

<table class="info-tabelle">
    <tr>
        <td class="lbl">Rechnungsnummer</td>
        <td>{{ $rechnung->rechnungsnummer }}</td>
    </tr>
    <tr>
        <td class="lbl">Leistungsperiode</td>
        <td>{{ $rechnung->periode_von->format('d.m.Y') }} – {{ $rechnung->periode_bis->format('d.m.Y') }}</td>
    </tr>
    <tr>
        <td class="lbl">Rechnungsdatum</td>
        <td>{{ $rechnung->rechnungsdatum->format('d.m.Y') }}</td>
    </tr>
    @php $zahlbarTage = $rechnung->klient->zahlbar_tage ?? 30; @endphp
    <tr>
        <td class="lbl">Zahlbar bis</td>
        <td>{{ $rechnung->rechnungsdatum->addDays($zahlbarTage)->format('d.m.Y') }}</td>
    </tr>
</table>

<p>Die Rechnung liegt dieser E-Mail als PDF-Anhang bei.</p>

@if($org->telefon || $org->email)
<p>Bei Fragen stehen wir Ihnen gerne zur Verfügung:
    @if($org->telefon) {{ $org->telefon }}@endif
    @if($org->telefon && $org->email) / @endif
    @if($org->email) {{ $org->email }}@endif
</p>
@endif

<p>Freundliche Grüsse<br>
{{ $org->name }}</p>

<div class="fuss">
    {{ $org->name }}
    @if($org->adresse) · {{ $org->adresse }}@endif
    @if($org->plz || $org->ort) · {{ $org->plz }} {{ $org->ort }}@endif
</div>

</body>
</html>
