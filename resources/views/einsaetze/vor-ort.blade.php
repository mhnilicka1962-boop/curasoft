<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $einsatz->klient->vollname() }} — Vor Ort</title>
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; }
        body { background: var(--cs-hintergrund); margin: 0; padding: 0; font-family: system-ui, sans-serif; }
        .vo-header { background: var(--cs-primaer); color: #fff; padding: 1rem; }
        .vo-header a { color: rgba(255,255,255,0.8); font-size: 0.8125rem; text-decoration: none; }
        .vo-name { font-size: 1.375rem; font-weight: 700; margin: 0.375rem 0 0.125rem; }
        .vo-meta { font-size: 0.8125rem; opacity: 0.85; }
        .vo-sektion { margin: 0.75rem 0.75rem 0; }
        .vo-karte { background: #fff; border-radius: 10px; padding: 0.875rem 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.07); margin-bottom: 0.625rem; }
        .vo-karte-titel { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cs-text-hell); margin-bottom: 0.625rem; }
        .vo-zeile { display: flex; justify-content: space-between; align-items: baseline; padding: 0.2rem 0; font-size: 0.9rem; }
        .vo-label { color: var(--cs-text-hell); font-size: 0.8125rem; min-width: 90px; }
        .vo-wert { font-weight: 500; text-align: right; flex: 1; }
        .vo-adresse { font-size: 0.9375rem; font-weight: 500; line-height: 1.5; }
        .vo-tel { display: block; font-size: 1.0625rem; font-weight: 600; color: var(--cs-primaer); text-decoration: none; padding: 0.375rem 0; }
        .vo-tel-label { font-size: 0.75rem; color: var(--cs-text-hell); display: block; }
        .vo-notfall { background: #fff5f5; border: 1px solid #fca5a5; border-radius: 10px; padding: 0.875rem 1rem; margin-bottom: 0.625rem; }
        .vo-notfall .vo-karte-titel { color: #dc2626; }
        .vo-notfall a { color: #dc2626; }
        .vo-hinweis { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 0.875rem 1rem; margin-bottom: 0.625rem; }
        .vo-hinweis .vo-karte-titel { color: #d97706; }
        .vo-checkin-btn { display: block; width: calc(100% - 1.5rem); margin: 0 0.75rem 0.625rem; padding: 0.875rem; border-radius: 10px; border: none; font-size: 1rem; font-weight: 700; cursor: pointer; text-align: center; }
        .vo-checkin-btn.ein { background: var(--cs-primaer); color: #fff; }
        .vo-checkin-btn.aus { background: #16a34a; color: #fff; }
        .vo-checkin-btn.done { background: var(--cs-hintergrund); color: var(--cs-text-hell); border: 1px solid var(--cs-border); cursor: default; }
        .vo-badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 999px; font-size: 0.75rem; font-weight: 600; }
        .vo-badge-ok { background: #dcfce7; color: #15803d; }
        .vo-badge-warn { background: #fef9c3; color: #a16207; }
        .vo-badge-grau { background: #f3f4f6; color: #6b7280; }
        .vo-nav { display: flex; gap: 0.5rem; padding: 0.75rem; }
        .vo-nav a { flex: 1; text-align: center; padding: 0.5rem; background: #fff; border-radius: 8px; font-size: 0.8125rem; color: var(--cs-primaer); text-decoration: none; border: 1px solid var(--cs-border); font-weight: 500; }
    </style>
</head>
<body>

{{-- Header --}}
<div class="vo-header">
    <a href="{{ route('einsaetze.show', $einsatz) }}">← Einsatz</a>
    <div class="vo-name">{{ $einsatz->klient->vollname() }}</div>
    <div class="vo-meta">
        {{ $einsatz->datum->format('d.m.Y') }}
        · {{ $einsatz->leistungsart?->bezeichnung }}
        @if($einsatz->zeit_von) · {{ \Carbon\Carbon::parse($einsatz->zeit_von)->format('H:i') }}@if($einsatz->zeit_bis)–{{ \Carbon\Carbon::parse($einsatz->zeit_bis)->format('H:i') }}@endif @endif
    </div>
</div>

{{-- Check-in / Check-out --}}
<div style="margin-top: 0.75rem;">
@if(!$einsatz->checkin_zeit)
    <form method="POST" action="{{ route('checkin.in', $einsatz) }}">
        @csrf
        <button type="submit" class="vo-checkin-btn ein">▶ Check-in jetzt</button>
    </form>
@elseif(!$einsatz->checkout_zeit)
    <div style="padding: 0.5rem 0.75rem 0; font-size: 0.8125rem; color: #16a34a; font-weight: 500; text-align: center;">
        ✓ Eingecheckt {{ $einsatz->checkin_zeit->format('H:i') }} Uhr
    </div>
    <form method="POST" action="{{ route('checkin.out', $einsatz) }}">
        @csrf
        <button type="submit" class="vo-checkin-btn aus" style="margin-top: 0.5rem;">■ Check-out</button>
    </form>
@else
    <button class="vo-checkin-btn done">
        ✓ Abgeschlossen {{ $einsatz->checkin_zeit->format('H:i') }}–{{ $einsatz->checkout_zeit->format('H:i') }} Uhr
    </button>
@endif
</div>

<div class="vo-sektion">

    {{-- Adresse --}}
    @php
        $einsatzort = $einsatz->klient->adressen->firstWhere('typ', 'einsatzort');
        $adresse    = $einsatzort?->strasse ?? $einsatz->klient->adresse;
        $plz        = $einsatzort?->plz ?? $einsatz->klient->plz;
        $ort        = $einsatzort?->ort ?? $einsatz->klient->ort;
    @endphp
    @if($adresse || $plz)
    <div class="vo-karte">
        <div class="vo-karte-titel">Adresse</div>
        <div class="vo-adresse">{{ $adresse }}</div>
        <div class="vo-adresse">{{ $plz }} {{ $ort }}</div>
        @if($adresse)
        <a href="https://maps.google.com/?q={{ urlencode($adresse . ', ' . $plz . ' ' . $ort) }}"
           target="_blank" style="display: inline-block; margin-top: 0.5rem; font-size: 0.8125rem; color: var(--cs-primaer);">
            📍 In Maps öffnen
        </a>
        @endif
    </div>
    @endif

    {{-- Telefon --}}
    @if($einsatz->klient->telefon)
    <div class="vo-karte">
        <div class="vo-karte-titel">Telefon</div>
        <span class="vo-tel-label">Klient</span>
        <a href="tel:{{ preg_replace('/\s+/', '', $einsatz->klient->telefon) }}" class="vo-tel">
            {{ $einsatz->klient->telefon }}
        </a>
    </div>
    @endif

    {{-- Notfallkontakte --}}
    @php
        $notfall = $einsatz->klient->notfallnummer;
        $notfallkontakte = $einsatz->klient->kontakte->filter(fn($k) => $k->notfallkontakt || $k->bevollmaechtigt)->take(2);
    @endphp
    @if($notfall || $notfallkontakte->isNotEmpty())
    <div class="vo-notfall">
        <div class="vo-karte-titel">🚨 Notfall</div>
        @if($notfall)
        <span class="vo-tel-label">Notfallnummer</span>
        <a href="tel:{{ preg_replace('/\s+/', '', $notfall) }}" class="vo-tel">{{ $notfall }}</a>
        @endif
        @foreach($notfallkontakte as $nk)
        <span class="vo-tel-label">{{ $nk->vorname }} {{ $nk->nachname }} ({{ $nk->rolle }})</span>
        @if($nk->telefon)
        <a href="tel:{{ preg_replace('/\s+/', '', $nk->telefon) }}" class="vo-tel">{{ $nk->telefon }}</a>
        @endif
        @endforeach
    </div>
    @endif

    {{-- Pflegehinweis (Bemerkung auf Einsatz) --}}
    @if($einsatz->bemerkung)
    <div class="vo-hinweis">
        <div class="vo-karte-titel">⚠ Hinweis</div>
        <div style="font-size: 0.9375rem; line-height: 1.5;">{{ $einsatz->bemerkung }}</div>
    </div>
    @endif

    {{-- Klient Basisdaten --}}
    <div class="vo-karte">
        <div class="vo-karte-titel">Patient</div>
        @if($einsatz->klient->geburtsdatum)
        <div class="vo-zeile">
            <span class="vo-label">Geburtsdatum</span>
            <span class="vo-wert">{{ $einsatz->klient->geburtsdatum->format('d.m.Y') }} ({{ $einsatz->klient->geburtsdatum->age }} J.)</span>
        </div>
        @endif
        @if($einsatz->klient->geschlecht)
        <div class="vo-zeile">
            <span class="vo-label">Geschlecht</span>
            <span class="vo-wert">{{ match($einsatz->klient->geschlecht) { 'm' => 'Männlich', 'w' => 'Weiblich', default => 'Divers' } }}</span>
        </div>
        @endif
        @if($einsatz->klient->ahv_nr)
        <div class="vo-zeile">
            <span class="vo-label">AHV-Nr.</span>
            <span class="vo-wert" style="font-family: monospace; font-size: 0.875rem;">{{ $einsatz->klient->ahv_nr }}</span>
        </div>
        @endif
        @php $kk = $einsatz->klient->krankenkassen->first(); @endphp
        @if($kk)
        <div class="vo-zeile">
            <span class="vo-label">Krankenkasse</span>
            <span class="vo-wert">{{ $kk->krankenkasse?->name ?? $einsatz->klient->krankenkasse_name }}</span>
        </div>
        @endif
    </div>

    {{-- Diagnosen --}}
    @if($einsatz->klient->diagnosen->isNotEmpty())
    <div class="vo-karte">
        <div class="vo-karte-titel">Diagnosen</div>
        @foreach($einsatz->klient->diagnosen->take(5) as $d)
        <div class="vo-zeile">
            <span class="vo-label" style="font-family: monospace; font-size: 0.8125rem;">{{ $d->icd_code }}</span>
            <span class="vo-wert" style="font-size: 0.875rem; font-weight: 400;">{{ $d->bezeichnung }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Verordnung --}}
    @if($einsatz->verordnung)
    <div class="vo-karte">
        <div class="vo-karte-titel">Ärztliche Verordnung</div>
        <div class="vo-zeile">
            <span class="vo-label">Leistung</span>
            <span class="vo-wert">{{ $einsatz->verordnung->leistungsart?->bezeichnung ?? 'Alle Leistungen' }}</span>
        </div>
        @if($einsatz->verordnung->gueltig_ab)
        <div class="vo-zeile">
            <span class="vo-label">Gültig ab</span>
            <span class="vo-wert">{{ $einsatz->verordnung->gueltig_ab->format('d.m.Y') }}</span>
        </div>
        @endif
        @if($einsatz->verordnung->gueltig_bis)
        <div class="vo-zeile">
            <span class="vo-label">Gültig bis</span>
            <span class="vo-wert" style="color: {{ $einsatz->verordnung->gueltig_bis->isPast() ? 'var(--cs-fehler)' : 'inherit' }};">
                {{ $einsatz->verordnung->gueltig_bis->format('d.m.Y') }}
                @if($einsatz->verordnung->gueltig_bis->isPast()) ⚠ abgelaufen @endif
            </span>
        </div>
        @endif
    </div>
    @endif

</div>

{{-- Navigation unten --}}
<div class="vo-nav">
    <a href="{{ route('rapporte.create', ['klient_id' => $einsatz->klient_id, 'einsatz_id' => $einsatz->id]) }}">+ Rapport</a>
    <a href="{{ route('klienten.show', $einsatz->klient) }}">Klient-Detail</a>
    <a href="{{ route('einsaetze.show', $einsatz) }}">Einsatz</a>
</div>

<div style="height: 1.5rem;"></div>

</body>
</html>
