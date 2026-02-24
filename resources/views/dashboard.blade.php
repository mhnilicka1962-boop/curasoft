<x-layouts.app titel="Dashboard">

<div style="max-width: 1100px;">

    <h1 class="seiten-titel seiten-titel-mb">
        Willkommen, {{ Auth::user()->vorname }}!
    </h1>

    {{-- Stat-Chips --}}
    <div class="stat-chips">

        <a href="{{ route('klienten.index', ['status' => 'aktiv']) }}" class="stat-chip">
            <span class="stat-chip-label">Aktive Klienten</span>
            <span class="stat-chip-zahl primaer">{{ $klientenAktiv }}</span>
        </a>

        <a href="{{ route('einsaetze.index', ['datum_von' => today()->format('Y-m-d'), 'datum_bis' => today()->format('Y-m-d')]) }}" class="stat-chip">
            <span class="stat-chip-label">
                Einsätze heute
                @if($einsaetzeGeplant > 0)
                    <span class="stat-chip-sub">{{ $einsaetzeGeplant }} geplant</span>
                @endif
            </span>
            <span class="stat-chip-zahl">{{ $einsaetzeHeute }}</span>
        </a>

        @if(auth()->user()->rolle !== 'pflege')
        <a href="{{ route('rechnungen.index') }}" class="stat-chip">
            <span class="stat-chip-label">Offene Rechnungen</span>
            <span class="stat-chip-zahl {{ $offeneRechnungen > 0 ? 'warnung' : '' }}">CHF {{ number_format($offeneRechnungen, 0, '.', "'") }}</span>
        </a>
        @endif

        <a href="{{ route('nachrichten.index') }}" class="stat-chip">
            <span class="stat-chip-label">Nachrichten</span>
            <span class="stat-chip-zahl {{ $ungeleseneNachrichten > 0 ? 'primaer' : '' }}">{{ $ungeleseneNachrichten }}</span>
        </a>

    </div>

    {{-- Einsätze heute / nächste --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="karten-kopf">
            <div class="abschnitt-label">{{ $einsaetzeDatumLabel }}</div>
            <a href="{{ route('einsaetze.index', ['datum_von' => today()->format('Y-m-d'), 'datum_bis' => today()->format('Y-m-d')]) }}" class="text-klein link-primaer">Alle →</a>
        </div>

        @forelse($einsaetzeListe as $e)
        <div class="listen-zeile">
            <div class="listen-zeile-inner" style="flex-wrap: wrap; gap: 0.25rem 0.5rem;">
                <div class="flex-1-min" style="min-width: 160px;">
                    <a href="{{ route('klienten.show', $e->klient) }}" class="text-fett link-primaer">{{ $e->klient->vollname() }}</a>
                    <span class="badge badge-klein ml-klein {{ $e->statusBadgeKlasse() }}">{{ $e->statusLabel() }}</span>
                    @if($e->leistungsart)
                        <div class="text-hell listen-meta">{{ $e->leistungsart->bezeichnung }}</div>
                    @endif
                </div>
                <div class="text-mini text-hell text-rechts flex-shrink-0" style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.2rem;">
                    @if($e->zeit_von) <div>{{ substr($e->zeit_von, 0, 5) }}@if($e->zeit_bis) – {{ substr($e->zeit_bis, 0, 5) }}@endif</div> @endif
                    @if(auth()->user()->rolle === 'admin' && $e->benutzer)
                        <div>{{ $e->benutzer->vorname }}</div>
                    @endif
                    <a href="{{ route('einsaetze.vor-ort', $e) }}" class="badge badge-klein badge-grau" style="text-decoration: none;">Vor Ort →</a>
                </div>
            </div>
        </div>
        @empty
        <p class="text-klein text-hell m-05">Keine Einsätze.</p>
        @endforelse
    </div>

    {{-- Letzte Rapporte --}}
    <div class="karte">
            <div class="karten-kopf">
                <div class="abschnitt-label">
                    Letzte Rapporte
                </div>
                <a href="{{ route('rapporte.create') }}" class="text-klein link-primaer">+ Rapport</a>
            </div>

            @forelse($letzteRapporte as $r)
            <div class="listen-zeile">
                <a href="{{ route('rapporte.show', $r) }}" style="text-decoration: none; color: inherit; display: block;">
                    <div class="listen-zeile-inner-start" style="flex-wrap: wrap; gap: 0.25rem 0.5rem;">
                        <div class="flex-1-min" style="min-width: 150px;">
                            <span class="text-fett" style="color: var(--cs-text);">{{ $r->klient->vollname() }}</span>
                            <span class="badge badge-klein ml-klein {{ $r->rapport_typ === 'zwischenfall' ? 'badge-fehler' : 'badge-grau' }}">{{ \App\Models\Rapport::$typen[$r->rapport_typ] ?? $r->rapport_typ }}</span>
                            <div class="text-hell listen-meta">
                                {{ Str::limit($r->inhalt, 60) }}
                            </div>
                        </div>
                        <div class="text-mini text-hell text-rechts flex-shrink-0">
                            {{ $r->datum->format('d.m.') }}
                            @if($r->benutzer) <div>{{ $r->benutzer->vorname }}</div> @endif
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <p class="text-klein text-hell m-05">
                Noch keine Rapporte.
            </p>
            @endforelse
        </div>

</div>

</x-layouts.app>
