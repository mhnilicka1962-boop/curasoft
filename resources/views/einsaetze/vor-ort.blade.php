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
        .vo-kat-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cs-text-hell); margin: 0.75rem 0 0.25rem; }
        .vo-kat-label:first-child { margin-top: 0; }
        .vo-akt-zeile { display: flex; justify-content: space-between; align-items: center; padding: 0.35rem 0.5rem; border-radius: 7px; margin-bottom: 0.2rem; background: var(--cs-hintergrund); transition: background 0.15s; }
        .vo-akt-zeile.vo-akt-aktiv { background: #dcfce7; }
        .vo-akt-check { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; cursor: pointer; flex: 1; min-width: 0; }
        .vo-akt-check input[type=checkbox] { width: 1.1rem; height: 1.1rem; flex-shrink: 0; accent-color: var(--cs-primaer); }
        .vo-akt-min { display: flex; align-items: center; gap: 0.25rem; flex-shrink: 0; }
        .vo-min-btn { width: 1.75rem; height: 1.75rem; border: 1px solid var(--cs-border); background: #fff; border-radius: 5px; font-size: 1rem; line-height: 1; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .vo-min-input { width: 3rem; text-align: center; border: 1px solid var(--cs-border); border-radius: 5px; padding: 0.2rem 0.25rem; font-size: 0.875rem; background: #fff; }
        .vo-min-label { font-size: 0.75rem; color: var(--cs-text-hell); }
        .vo-akt-gesamt { text-align: right; font-size: 0.875rem; color: var(--cs-text-hell); padding: 0.5rem 0.25rem 0; }
    </style>
</head>
<body>

{{-- Header --}}
<div class="vo-header">
    <a href="{{ auth()->user()->rolle === 'admin' ? route('einsaetze.show', $einsatz) : route('dashboard') }}">← Zurück</a>
    <div class="vo-name">{{ $einsatz->klient->vollname() }}</div>
    <div class="vo-meta">
        {{ $einsatz->datum->format('d.m.Y') }}
        · {{ $einsatz->leistungsart?->bezeichnung }}
        @if($einsatz->zeit_von) · {{ \Carbon\Carbon::parse($einsatz->zeit_von)->format('H:i') }}@if($einsatz->zeit_bis)–{{ \Carbon\Carbon::parse($einsatz->zeit_bis)->format('H:i') }}@endif @endif
    </div>
</div>

{{-- Rapport-Button oben --}}
<div style="padding: 0.75rem 0.75rem 0;">
    <a href="{{ route('rapporte.create', ['klient_id' => $einsatz->klient_id, 'einsatz_id' => $einsatz->id]) }}"
       class="vo-checkin-btn ein" style="display: block; text-decoration: none; text-align: center; padding: 0.75rem; background: var(--cs-primaer);">
        + Rapport schreiben
    </a>
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


    {{-- Leistungen erfassen --}}
    <div class="vo-karte">
        <div class="vo-karte-titel">Leistungen erfassen</div>
        <form method="POST" action="{{ route('einsaetze.aktivitaeten.speichern', $einsatz) }}" id="form-leistungen">
            @csrf
            @foreach(\App\Models\EinsatzAktivitaet::$aktivitaeten as $kategorie => $items)
            <div class="vo-kat-label">{{ $kategorie }}</div>
            @foreach($items as $item)
            @php $key = $kategorie . '|' . $item; $saved = $gespeicherteAktivitaeten[$key] ?? null; @endphp
            <div class="vo-akt-zeile" id="zeile-{{ md5($key) }}">
                <label class="vo-akt-check">
                    <input type="checkbox" name="akt[]" value="{{ $key }}"
                        {{ $saved ? 'checked' : '' }}
                        onchange="toggleZeile(this)">
                    <span>{{ $item }}</span>
                </label>
                <div class="vo-akt-min">
                    <button type="button" class="vo-min-btn" onclick="adjustMin(this, -5)">−</button>
                    <input type="number" name="min[{{ $key }}]"
                        value="{{ $saved?->minuten ?? 5 }}" min="5" step="5"
                        class="vo-min-input">
                    <button type="button" class="vo-min-btn" onclick="adjustMin(this, 5)">+</button>
                    <span class="vo-min-label">min</span>
                </div>
            </div>
            @endforeach
            @endforeach

            @if($gespeicherteAktivitaeten->isNotEmpty())
            <div class="vo-akt-gesamt">
                Gesamt: <strong>{{ $einsatz->aktivitaeten->sum('minuten') }} min</strong>
            </div>
            @endif

            <button type="submit" class="vo-checkin-btn ein" style="margin-top: 0.75rem;">
                ✓ Leistungen speichern
            </button>
        </form>
    </div>

</div>

{{-- Rapporte zu diesem Einsatz --}}
@if($einsatz->rapporte->isNotEmpty())
<div class="vo-karte" style="margin: 0 0 0.75rem;">
    <div class="vo-abschnitt-titel">Rapporte</div>
    @foreach($einsatz->rapporte as $r)
    <a href="{{ route('rapporte.show', $r) }}" style="display: block; text-decoration: none; padding: 0.625rem 0; border-bottom: 1px solid var(--vo-border, #e5e7eb);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
            <div style="font-size: 0.875rem; color: var(--cs-text);">{{ Str::limit($r->inhalt, 80) }}</div>
            <div style="font-size: 0.75rem; color: var(--cs-text-hell); white-space: nowrap;">{{ $r->datum->format('d.m.') }}</div>
        </div>
    </a>
    @endforeach
</div>
@endif

{{-- Navigation unten --}}
<div class="vo-nav">
    <a href="{{ route('rapporte.create', ['klient_id' => $einsatz->klient_id, 'einsatz_id' => $einsatz->id]) }}" style="background: var(--cs-primaer); color: #fff; border-color: var(--cs-primaer); font-size: 0.9375rem;">+ Rapport schreiben</a>
</div>

<div style="height: 1.5rem;"></div>

<script>
function toggleZeile(cb) {
    cb.closest('.vo-akt-zeile').classList.toggle('vo-akt-aktiv', cb.checked);
}
function adjustMin(btn, delta) {
    const input = btn.closest('.vo-akt-min').querySelector('.vo-min-input');
    input.value = Math.max(5, (parseInt(input.value) || 5) + delta);
}
// Initialer Zustand
document.querySelectorAll('.vo-akt-zeile input[type=checkbox]:checked').forEach(cb => {
    cb.closest('.vo-akt-zeile').classList.add('vo-akt-aktiv');
});
</script>
</body>
</html>
