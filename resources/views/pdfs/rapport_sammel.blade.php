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

/* Kopfzeile — nur erste Seite */
.kopf { display: table; width: 100%; margin-bottom: 5mm; padding-bottom: 3mm; border-bottom: 1pt solid #1a3a5c; }
.kopf-l { display: table-cell; vertical-align: middle; width: 60%; }
.kopf-r { display: table-cell; vertical-align: middle; text-align: right; }
.org-name { font-size: 11pt; font-weight: bold; color: #1a3a5c; }
.org-sub   { font-size: 7pt; color: #777; margin-top: 1mm; }

/* Deckblatt-Titel */
.deckblatt-titel { font-size: 14pt; font-weight: bold; color: #1a3a5c; margin: 6mm 0 1mm; }
.deckblatt-sub   { font-size: 8pt; color: #666; margin-bottom: 2mm; }
.filter-box {
    background: #f1f5f9;
    border: 0.3pt solid #cbd5e1;
    border-radius: 1mm;
    padding: 3mm 4mm;
    font-size: 8pt;
    color: #475569;
    margin-bottom: 6mm;
}
.filter-box strong { color: #1a1a1a; }

/* Trennlinie zwischen Rapporten */
.rapport-block { margin-bottom: 6mm; padding-bottom: 6mm; border-bottom: 0.5pt solid #e2e8f0; }
.rapport-block:last-child { border-bottom: none; }

/* Rapport-Kopf */
.rapport-kopf { display: table; width: 100%; margin-bottom: 2mm; }
.rapport-kopf-l { display: table-cell; vertical-align: middle; width: 70%; }
.rapport-kopf-r { display: table-cell; vertical-align: middle; text-align: right; }

.klient-name { font-size: 10.5pt; font-weight: bold; color: #1a1a1a; }
.rapport-meta { font-size: 7.5pt; color: #666; margin-top: 0.5mm; }
.rapport-meta span { margin-right: 4mm; }

.typ-badge {
    display: inline-block;
    font-size: 7pt;
    font-weight: bold;
    padding: 0.8mm 2.5mm;
    border-radius: 2mm;
    color: #fff;
    background: #1a3a5c;
}
.typ-zwischenfall { background: #dc2626; }
.typ-pflege       { background: #1d4ed8; }
.typ-verlauf      { background: #166534; }
.typ-medikament   { background: #7c3aed; }
.typ-information  { background: #6b7280; }

.vertraulich-hinweis {
    display: inline-block;
    font-size: 6.5pt;
    font-weight: bold;
    color: #92400e;
    background: #fef3c7;
    border: 0.3pt solid #f59e0b;
    border-radius: 1mm;
    padding: 0.5mm 2mm;
    margin-left: 2mm;
}

.inhalt {
    font-size: 9pt;
    line-height: 1.7;
    white-space: pre-wrap;
    color: #1a1a1a;
    padding: 3mm 4mm;
    background: #f9fafb;
    border: 0.3pt solid #e5e7eb;
    border-radius: 1mm;
}

.seitenumbruch { page-break-after: always; }
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
            <div class="org-sub" style="text-align:right;">Erstellt: {{ now()->format('d.m.Y') }}</div>
        </div>
    </div>

    {{-- Deckblatt-Titel --}}
    <div class="deckblatt-titel">Pflegedokumentation — Rapporte</div>
    <div class="deckblatt-sub">{{ $rapporte->count() }} {{ $rapporte->count() === 1 ? 'Eintrag' : 'Einträge' }}</div>

    {{-- Filterangaben --}}
    <div class="filter-box">
        @if($filter['klient_id'])
            <strong>Klient:</strong> {{ $rapporte->first()?->klient?->vollname() }}&nbsp;&nbsp;
        @else
            <strong>Klient:</strong> Alle&nbsp;&nbsp;
        @endif
        @if($filter['typ'])
            <strong>Typ:</strong> {{ \App\Models\Rapport::$typen[$filter['typ']] ?? $filter['typ'] }}&nbsp;&nbsp;
        @else
            <strong>Typ:</strong> Alle&nbsp;&nbsp;
        @endif
        @if($filter['datum_von'] || $filter['datum_bis'])
            <strong>Zeitraum:</strong>
            {{ $filter['datum_von'] ? \Carbon\Carbon::parse($filter['datum_von'])->format('d.m.Y') : '–' }}
            bis
            {{ $filter['datum_bis'] ? \Carbon\Carbon::parse($filter['datum_bis'])->format('d.m.Y') : '–' }}
        @endif
    </div>

    {{-- Rapporte --}}
    @foreach($rapporte as $rapport)
    <div class="rapport-block">
        <div class="rapport-kopf">
            <div class="rapport-kopf-l">
                <span class="klient-name">{{ $rapport->klient->vollname() }}</span>
                @if($rapport->vertraulich)
                    <span class="vertraulich-hinweis">&#9888; Vertraulich</span>
                @endif
                <div class="rapport-meta">
                    <span>{{ $rapport->datum->format('d.m.Y') }}{{ $rapport->zeit_von ? ' · ' . $rapport->zeit_von . ($rapport->zeit_bis ? '–' . $rapport->zeit_bis : '') : '' }}</span>
                    @if($rapport->benutzer)
                        <span>{{ $rapport->benutzer->vorname }} {{ $rapport->benutzer->nachname }}</span>
                    @endif
                </div>
            </div>
            <div class="rapport-kopf-r">
                <span class="typ-badge typ-{{ $rapport->rapport_typ }}">
                    {{ \App\Models\Rapport::$typen[$rapport->rapport_typ] ?? $rapport->rapport_typ }}
                </span>
            </div>
        </div>

        <div class="inhalt">{{ $rapport->inhalt }}</div>
    </div>
    @endforeach

</div>
</body>
</html>
