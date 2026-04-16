<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1a1a1a; }
    .seite { padding: 20mm 20mm 15mm 25mm; }

    /* Absender klein oben */
    .absender-klein { font-size: 7pt; color: #555; border-bottom: 1px solid #999; padding-bottom: 1mm; margin-bottom: 3mm; }

    /* Empfänger-Block */
    .empfaenger { margin-bottom: 12mm; min-height: 35mm; }
    .empfaenger div { font-size: 10pt; line-height: 1.5; }

    /* Rechnungskopf rechts */
    .kopf-rechts { text-align: right; margin-bottom: 8mm; }
    .kopf-rechts .titel { font-size: 14pt; font-weight: bold; margin-bottom: 2mm; }
    .kopf-rechts .meta { font-size: 8.5pt; color: #555; }

    /* Tabellen */
    table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
    th { background: #f0f0f0; padding: 2mm 3mm; font-size: 8.5pt; text-align: left; border-bottom: 1px solid #ccc; }
    td { padding: 2mm 3mm; font-size: 9pt; border-bottom: 1px solid #e8e8e8; }
    .text-rechts { text-align: right; }

    /* Totale */
    .totale-box { margin-left: auto; width: 70mm; border: 1px solid #ccc; border-radius: 2mm; padding: 3mm 4mm; margin-top: 3mm; }
    .totale-zeile { display: flex; justify-content: space-between; padding: 1mm 0; font-size: 9pt; }
    .totale-zeile.haupt { font-weight: bold; font-size: 10pt; border-top: 1px solid #ccc; padding-top: 2mm; margin-top: 1mm; }

    /* Zahlungsteil */
    .zahlungs-box { margin-top: 6mm; background: #f8f8f8; border: 1px solid #ccc; border-radius: 2mm; padding: 4mm 5mm; font-size: 9pt; }
    .zahlungs-box .titel { font-weight: bold; margin-bottom: 2mm; font-size: 9.5pt; }
    .zahlungs-feld { margin-bottom: 1.5mm; }
    .zahlungs-feld span:first-child { color: #666; display: inline-block; width: 28mm; font-size: 8.5pt; }

    /* Fusszeile */
    .fusszeile { margin-top: 10mm; font-size: 7.5pt; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 2mm; }
</style>
</head>
<body>
<div class="seite">

    {{-- Absender klein --}}
    <div class="absender-klein">
        {{ $org->name }} · {{ $org->adresse }}, {{ $org->plz }} {{ $org->ort }} · {{ $org->email }}
    </div>

    {{-- Empfänger --}}
    <div class="empfaenger">
        <div>{{ $rechnung->klient->gemeinde_name ?? '—' }}</div>
        @if($rechnung->klient->gemeinde_adresse)
        <div>{{ $rechnung->klient->gemeinde_adresse }}</div>
        @endif
        <div>{{ $rechnung->klient->gemeinde_plz }} {{ $rechnung->klient->gemeinde_ort }}</div>
    </div>

    {{-- Kopf rechts --}}
    <div class="kopf-rechts">
        <div class="titel">Restfinanzierungsrechnung</div>
        <div class="meta">
            Datum: {{ now()->format('d.m.Y') }}<br>
            Rechnungs-Nr.: GDE-{{ $rechnung->rechnungsnummer }}<br>
            Periode: {{ $rechnung->periode_von->format('d.m.Y') }} – {{ $rechnung->periode_bis->format('d.m.Y') }}
        </div>
    </div>

    {{-- Klient-Info --}}
    <table>
        <tr>
            <th>Klient / Patientin</th>
            <th>Geburtsdatum</th>
            <th>AHV-Nummer</th>
            <th>Gemeinde</th>
        </tr>
        <tr>
            <td>{{ $rechnung->klient->nachname }} {{ $rechnung->klient->vorname }}</td>
            <td>{{ $rechnung->klient->geburtsdatum ? $rechnung->klient->geburtsdatum->format('d.m.Y') : '—' }}</td>
            <td>{{ $rechnung->klient->ahv_nr ?? '—' }}</td>
            <td>{{ $rechnung->klient->gemeinde_ort ?? '—' }}</td>
        </tr>
    </table>

    {{-- Positionen --}}
    <table>
        <thead>
            <tr>
                <th>Datum</th>
                <th>Leistung</th>
                <th class="text-rechts">Vollkosten CHF</th>
                <th class="text-rechts">KK-Anteil CHF</th>
                <th class="text-rechts">Patient CHF</th>
                <th class="text-rechts">Gemeinde CHF</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tage as $tag)
                @if($tag['taxe_abkl'] + $tag['taxe_unt'] + $tag['taxe_gp'] > 0)
                <tr>
                    <td>{{ $tag['datum']->format('d.m.') }}</td>
                    <td style="font-size:8pt; color:#555;">
                        @if($tag['abkl_min'] > 0) Abklärung {{ $tag['abkl_min'] }} Min. @endif
                        @if($tag['unt_min'] > 0) Untersuchung/Behandlung {{ $tag['unt_min'] }} Min. @endif
                        @if($tag['gp_min'] > 0) Grundpflege {{ $tag['gp_min'] }} Min. @endif
                    </td>
                    <td class="text-rechts">{{ number_format($tag['taxe_abkl'] + $tag['taxe_unt'] + $tag['taxe_gp'], 2, '.', "'") }}</td>
                    <td class="text-rechts">{{ number_format($tag['kvg_abkl'] + $tag['kvg_unt'] + $tag['kvg_gp'], 2, '.', "'") }}</td>
                    <td class="text-rechts">{{ number_format($tag['pat'], 2, '.', "'") }}</td>
                    <td class="text-rechts">{{ number_format($tag['gemeinde'], 2, '.', "'") }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    {{-- Totale --}}
    <div style="overflow: hidden;">
        <div class="totale-box">
            <div class="totale-zeile">
                <span>Vollkosten</span>
                <span>CHF {{ number_format($summen['vollkosten'], 2, '.', "'") }}</span>
            </div>
            <div class="totale-zeile">
                <span>KK-Beitrag</span>
                <span>CHF {{ number_format($summen['kk'], 2, '.', "'") }}</span>
            </div>
            <div class="totale-zeile">
                <span>Patient/in</span>
                <span>CHF {{ number_format($summen['pat'], 2, '.', "'") }}</span>
            </div>
            <div class="totale-zeile haupt">
                <span>Restfinanzierung Gemeinde</span>
                <span>CHF {{ number_format($summen['gemeinde'], 2, '.', "'") }}</span>
            </div>
        </div>
    </div>

    {{-- Zahlungsteil --}}
    @if($org->iban)
    <div class="zahlungs-box">
        <div class="titel">Zahlungsangaben</div>
        <div class="zahlungs-feld"><span>Betrag:</span> <strong>CHF {{ number_format($summen['gemeinde'], 2, '.', "'") }}</strong></div>
        <div class="zahlungs-feld"><span>IBAN:</span> {{ $org->iban }}</div>
        <div class="zahlungs-feld"><span>Zugunsten:</span> {{ $org->name }}</div>
        <div class="zahlungs-feld"><span>Vermerk:</span> GDE-{{ $rechnung->rechnungsnummer }} / {{ $rechnung->klient->nachname }} {{ $rechnung->klient->vorname }}</div>
        @if($org->zahlbar_tage ?? false)
        <div class="zahlungs-feld"><span>Zahlbar bis:</span> {{ now()->addDays(30)->format('d.m.Y') }}</div>
        @else
        <div class="zahlungs-feld"><span>Zahlbar bis:</span> {{ now()->addDays(30)->format('d.m.Y') }}</div>
        @endif
    </div>
    @endif

    <div class="fusszeile">
        {{ $org->name }} · {{ $org->adresse }}, {{ $org->plz }} {{ $org->ort }}
        @if($org->telefon) · Tel. {{ $org->telefon }} @endif
        @if($org->email) · {{ $org->email }} @endif
    </div>

</div>
</body>
</html>
