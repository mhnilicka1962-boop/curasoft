<x-layouts.app titel="Dashboard">

<div style="max-width: 1100px;">

    <h1 class="seiten-titel" style="margin: 0 0 1.5rem;">
        Willkommen, {{ Auth::user()->vorname }}!
    </h1>

    {{-- Kennzahlen-Karten --}}
    <div class="form-grid" style="margin-bottom: 1.5rem;">

        <div class="karte" style="cursor: default;">
            <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Aktive Klienten</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--cs-primaer);">{{ $klientenAktiv }}</div>
            <a href="{{ route('klienten.index', ['status' => 'aktiv']) }}" class="text-klein text-hell" style="text-decoration: none; display: inline-block; margin-top: 0.375rem;">Alle anzeigen →</a>
        </div>

        <div class="karte" style="cursor: default;">
            <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Einsätze heute</div>
            <div style="font-size: 2rem; font-weight: 700; color: var(--cs-text);">{{ $einsaetzeHeute }}</div>
            @if($einsaetzeGeplant > 0)
                <div style="font-size: 0.8125rem; color: var(--cs-warnung);">{{ $einsaetzeGeplant }} noch geplant</div>
            @endif
            <a href="{{ route('einsaetze.index', ['datum_von' => today()->format('Y-m-d'), 'datum_bis' => today()->format('Y-m-d')]) }}" class="text-klein text-hell" style="text-decoration: none; display: inline-block; margin-top: 0.375rem;">Alle anzeigen →</a>
        </div>

        @if(auth()->user()->rolle !== 'pflege')
        <div class="karte" style="cursor: default;">
            <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Offene Rechnungen</div>
            <div style="font-size: 2rem; font-weight: 700; color: {{ $offeneRechnungen > 0 ? 'var(--cs-warnung)' : 'var(--cs-text)' }};">
                CHF {{ number_format($offeneRechnungen, 0, '.', "'") }}
            </div>
            <a href="{{ route('rechnungen.index') }}" class="text-klein text-hell" style="text-decoration: none; display: inline-block; margin-top: 0.375rem;">Rechnungen →</a>
        </div>
        @endif

        <div class="karte" style="cursor: default;">
            <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Nachrichten</div>
            <div style="font-size: 2rem; font-weight: 700; color: {{ $ungeleseneNachrichten > 0 ? 'var(--cs-primaer)' : 'var(--cs-text)' }};">{{ $ungeleseneNachrichten }}</div>
            <a href="{{ route('nachrichten.index') }}" class="text-klein text-hell" style="text-decoration: none; display: inline-block; margin-top: 0.375rem;">Posteingang →</a>
        </div>

    </div>

    <div class="form-grid-2" style="gap: 1rem;">

        {{-- Heutige Touren --}}
        <div class="karte">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.875rem;">
                <div class="abschnitt-label">
                    Touren heute
                </div>
                <a href="{{ route('touren.index', ['datum' => today()->format('Y-m-d')]) }}" class="text-klein link-primaer">Tourenplan →</a>
            </div>

            @forelse($meineTourenHeute as $tour)
            <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                    <div>
                        <a href="{{ route('touren.show', $tour) }}" class="text-fett link-primaer">{{ $tour->bezeichnung }}</a>
                        @if(auth()->user()->rolle === 'admin')
                            <span class="text-hell" style="font-size: 0.8rem; margin-left: 0.375rem;">{{ $tour->benutzer?->vorname }}</span>
                        @endif
                    </div>
                    <div style="display: flex; gap: 0.375rem; align-items: center;">
                        <span class="text-hell" style="font-size: 0.8rem;">{{ $tour->einsaetze->count() }} Einsätze</span>
                        <span class="badge {{ $tour->status === 'abgeschlossen' ? 'badge-erfolg' : ($tour->status === 'gestartet' ? 'badge-warnung' : 'badge-grau') }}" style="font-size: 0.7rem;">{{ ucfirst($tour->status) }}</span>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-klein text-hell" style="margin: 0.5rem 0;">
                Keine Touren für heute.
            </p>
            @endforelse
        </div>

        {{-- Letzte Rapporte --}}
        <div class="karte">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.875rem;">
                <div class="abschnitt-label">
                    Letzte Rapporte
                </div>
                <a href="{{ route('rapporte.create') }}" class="text-klein link-primaer">+ Rapport</a>
            </div>

            @forelse($letzteRapporte as $r)
            <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--cs-border); font-size: 0.875rem;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem;">
                    <div style="flex: 1; min-width: 0;">
                        <a href="{{ route('klienten.show', $r->klient) }}" class="text-fett link-primaer">{{ $r->klient->vollname() }}</a>
                        <span class="badge {{ $r->rapport_typ === 'zwischenfall' ? 'badge-fehler' : 'badge-grau' }}" style="font-size: 0.7rem; margin-left: 0.375rem;">{{ \App\Models\Rapport::$typen[$r->rapport_typ] ?? $r->rapport_typ }}</span>
                        <div class="text-hell" style="font-size: 0.8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 0.125rem;">
                            {{ Str::limit($r->inhalt, 60) }}
                        </div>
                    </div>
                    <div class="text-mini text-hell text-rechts" style="flex-shrink: 0;">
                        {{ $r->datum->format('d.m.') }}
                        @if($r->benutzer) <div>{{ $r->benutzer->vorname }}</div> @endif
                    </div>
                </div>
            </div>
            @empty
            <p class="text-klein text-hell" style="margin: 0.5rem 0;">
                Noch keine Rapporte.
            </p>
            @endforelse
        </div>

    </div>

</div>

</x-layouts.app>
