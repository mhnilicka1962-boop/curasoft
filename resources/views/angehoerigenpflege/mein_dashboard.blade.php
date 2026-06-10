<x-layouts.app titel="Meine Übersicht">
@php
    function angMinH(int $min): string {
        if ($min <= 0) return '—';
        return intdiv($min, 60) . ':' . str_pad($min % 60, 2, '0', STR_PAD_LEFT) . ' h';
    }
    $monat = now()->locale('de')->isoFormat('MMMM YYYY');
@endphp

<div style="max-width: 720px;">

    <div class="seiten-kopf" style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Meine Übersicht</h1>
    </div>

    {{-- Kacheln --}}
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
        <div class="karte" style="flex: 1; min-width: 130px; text-align: center;">
            <div class="text-mini text-hell" style="text-transform: uppercase; letter-spacing: .05em;">Meine Klienten</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--cs-primaer);">{{ $meineKlienten->count() }}</div>
        </div>
        <div class="karte" style="flex: 1; min-width: 130px; text-align: center;">
            <div class="text-mini text-hell" style="text-transform: uppercase; letter-spacing: .05em;">Einsätze heute</div>
            <div style="font-size: 1.75rem; font-weight: 700;">{{ $einsaetzeListe->count() }}</div>
        </div>
        <div class="karte" style="flex: 1; min-width: 130px; text-align: center;">
            <div class="text-mini text-hell" style="text-transform: uppercase; letter-spacing: .05em;">Stunden {{ now()->format('M') }}</div>
            <div style="font-size: 1.75rem; font-weight: 700; color: var(--cs-primaer);">{{ angMinH((int)$stundenMonat) }}</div>
        </div>
    </div>

    {{-- Meine Klienten --}}
    @if($meineKlienten->isNotEmpty())
    <div class="karte" style="margin-bottom: 1.5rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Meine betreuten Klienten</div>
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach($meineKlienten as $zuw)
            <a href="{{ route('klienten.show', $zuw->klient_id) }}"
               style="display: flex; align-items: center; gap: 0.75rem; padding: 0.5rem 0.75rem; background: var(--cs-hintergrund); border-radius: var(--cs-radius); text-decoration: none; color: inherit;">
                <span style="font-weight: 600;">{{ $zuw->klient->vorname }} {{ $zuw->klient->nachname }}</span>
                @if($zuw->klient->geburtsdatum)
                    <span class="text-hell text-klein">* {{ $zuw->klient->geburtsdatum->format('d.m.Y') }}</span>
                @endif
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Nächste Einsätze --}}
    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 0.75rem;">
            Einsätze {{ $einsaetzeListe->first()?->datum->isSameDay(today()) ? 'heute' : ($einsaetzeListe->first()?->datum->format('d.m.Y') ?? 'demnächst') }}
        </div>

        @if($einsaetzeListe->isEmpty())
        <div style="text-align: center; padding: 1.5rem; color: var(--cs-text-hell);">
            Keine offenen Einsätze geplant.
        </div>
        @else
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach($einsaetzeListe as $e)
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.625rem 0.75rem; background: var(--cs-hintergrund); border-radius: var(--cs-radius); flex-wrap: wrap;">
                <div style="flex: 1; min-width: 160px;">
                    <div style="font-weight: 600;">{{ $e->klient?->vorname }} {{ $e->klient?->nachname }}</div>
                    <div class="text-klein text-hell">
                        @if($e->zeit_von)
                            {{ substr($e->zeit_von, 0, 5) }}
                            @if($e->zeit_bis) – {{ substr($e->zeit_bis, 0, 5) }} @endif
                            ·
                        @endif
                        {{ $e->einsatzLeistungsarten->map(fn($el) => $el->leistungsart?->bezeichnung)->filter()->implode(', ') ?: '—' }}
                    </div>
                </div>
                <a href="{{ route('einsaetze.vor-ort', $e) }}" class="btn btn-primaer" style="font-size: 0.8125rem; padding: 0.25rem 0.75rem;">
                    Vor-Ort →
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
</x-layouts.app>
