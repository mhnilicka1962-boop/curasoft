<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9pt; color: #1a1a1a; line-height: 1.5; }

.fusszeile {
    position: fixed;
    bottom: 5mm;
    left: 12mm;
    right: 12mm;
    border-top: 0.3pt solid #ccc;
    padding-top: 1.5mm;
    font-size: 6pt;
    color: #aaa;
    text-align: center;
}

.seite { padding: 10mm 15mm 20mm 15mm; }

.kopf { display: table; width: 100%; margin-bottom: 5mm; padding-bottom: 3mm; border-bottom: 1pt solid #1a3a5c; }
.kopf-l { display: table-cell; vertical-align: middle; width: 60%; }
.kopf-r { display: table-cell; vertical-align: middle; text-align: right; }

.org-name { font-size: 11pt; font-weight: bold; color: #1a3a5c; }
.org-sub   { font-size: 7pt; color: #777; margin-top: 1mm; }

.typ-badge {
    display: inline-block;
    font-size: 7.5pt;
    font-weight: bold;
    padding: 1mm 3mm;
    border-radius: 2mm;
    color: #fff;
    background: #1a3a5c;
}
.typ-zwischenfall { background: #dc2626; }
.typ-pflege       { background: #1d4ed8; }
.typ-verlauf      { background: #166534; }
.typ-medikament   { background: #7c3aed; }
.typ-information  { background: #6b7280; }

.titel { font-size: 13pt; font-weight: bold; color: #1a1a1a; margin-bottom: 2mm; }

.meta { font-size: 7.5pt; color: #666; margin-bottom: 5mm; }
.meta span { margin-right: 5mm; }

.inhalt {
    font-size: 9.5pt;
    line-height: 1.7;
    white-space: pre-wrap;
    color: #1a1a1a;
    padding: 4mm;
    background: #f9f9f9;
    border: 0.3pt solid #ddd;
    border-radius: 1mm;
}

.vertraulich-banner {
    background: #fef3c7;
    border: 0.5pt solid #f59e0b;
    border-radius: 1mm;
    padding: 2mm 4mm;
    font-size: 8pt;
    font-weight: bold;
    color: #92400e;
    margin-bottom: 4mm;
}
</style>
</head>
<body>

<div class="fusszeile">
    {{ $org->name }} · Erstellt am {{ now()->format('d.m.Y H:i') }} · Vertraulich — nur für interne Zwecke
</div>

<div class="seite">

    {{-- Kopf --}}
    <div class="kopf">
        <div class="kopf-l">
            <div class="org-name">{{ $org->name }}</div>
            @if($org->adresse)
                <div class="org-sub">{{ $org->adresse }}@if($org->plz || $org->ort), {{ $org->plz }} {{ $org->ort }}@endif</div>
            @endif
        </div>
        <div class="kopf-r">
            <span class="typ-badge typ-{{ $rapport->rapport_typ }}">
                {{ \App\Models\Rapport::$typen[$rapport->rapport_typ] ?? $rapport->rapport_typ }}
            </span>
        </div>
    </div>

    {{-- Vertraulich-Hinweis --}}
    @if($rapport->vertraulich)
    <div class="vertraulich-banner">&#9888; Vertraulich — nur für autorisierte Personen</div>
    @endif

    {{-- Titel + Meta --}}
    <div class="titel">{{ $rapport->klient->vollname() }}</div>

    <div class="meta">
        <span>Datum: <strong>{{ $rapport->datum->format('d.m.Y') }}</strong></span>
        @if($rapport->zeit_von)
            <span>Zeit: <strong>{{ $rapport->zeit_von }}@if($rapport->zeit_bis) – {{ $rapport->zeit_bis }}@endif</strong></span>
        @endif
        @if($rapport->benutzer)
            <span>Verfasst von: <strong>{{ $rapport->benutzer->vorname }} {{ $rapport->benutzer->nachname }}</strong></span>
        @endif
    </div>

    {{-- Inhalt --}}
    <div class="inhalt">{{ $rapport->inhalt }}</div>

</div>
</body>
</html>
