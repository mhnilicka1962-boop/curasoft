<x-layouts.app titel="Dashboard">

<div style="max-width: 1100px;">

    <h1 class="seiten-titel seiten-titel-mb">
        Willkommen, {{ Auth::user()->vorname }}!
    </h1>

    {{-- Kennzahlen-Karten --}}
    <div class="form-grid form-grid-mb" style="align-items: stretch;">

        <div class="karte karte-statisch">
            <div class="abschnitt-label abschnitt-label-kompakt">Aktive Klienten</div>
            <div class="kennzahl-zahl">{{ $klientenAktiv }}</div>
            <a href="{{ route('klienten.index', ['status' => 'aktiv']) }}" class="text-klein text-hell link-block-mt">Alle anzeigen →</a>
        </div>

        <div class="karte karte-statisch">
            <div class="abschnitt-label abschnitt-label-kompakt">Einsätze heute</div>
            <div class="kennzahl-zahl-neutral">{{ $einsaetzeHeute }}</div>
            @if($einsaetzeGeplant > 0)
                <div class="kennzahl-hinweis">{{ $einsaetzeGeplant }} noch geplant</div>
            @endif
            <a href="{{ route('einsaetze.index', ['datum_von' => today()->format('Y-m-d'), 'datum_bis' => today()->format('Y-m-d')]) }}" class="text-klein text-hell link-block-mt">Alle anzeigen →</a>
        </div>

        @if(auth()->user()->rolle !== 'pflege')
        <div class="karte karte-statisch">
            <div class="abschnitt-label abschnitt-label-kompakt">Offene Rechnungen</div>
            <div class="kennzahl-zahl-neutral" style="color: {{ $offeneRechnungen > 0 ? 'var(--cs-warnung)' : 'var(--cs-text)' }};">
                CHF {{ number_format($offeneRechnungen, 0, '.', "'") }}
            </div>
            <a href="{{ route('rechnungen.index') }}" class="text-klein text-hell link-block-mt">Rechnungen →</a>
        </div>
        @endif

        <div class="karte karte-statisch">
            <div class="abschnitt-label abschnitt-label-kompakt">Nachrichten</div>
            <div class="kennzahl-zahl-neutral" style="color: {{ $ungeleseneNachrichten > 0 ? 'var(--cs-primaer)' : 'var(--cs-text)' }};">{{ $ungeleseneNachrichten }}</div>
            <a href="{{ route('nachrichten.index') }}" class="text-klein text-hell link-block-mt">Posteingang →</a>
        </div>

    </div>

    <div class="form-grid-2 form-grid-2-gap">

        {{-- Heutige Touren --}}
        <div class="karte">
            <div class="karten-kopf">
                <div class="abschnitt-label">
                    Touren heute
                </div>
                <a href="{{ route('touren.index', ['datum' => today()->format('Y-m-d')]) }}" class="text-klein link-primaer">Tourenplan →</a>
            </div>

            @forelse($meineTourenHeute as $tour)
            <div class="listen-zeile">
                <div class="listen-zeile-inner">
                    <div>
                        <a href="{{ route('touren.show', $tour) }}" class="text-fett link-primaer">{{ $tour->bezeichnung }}</a>
                        @if(auth()->user()->rolle === 'admin')
                            <span class="text-hell text-8 ml-klein">{{ $tour->benutzer?->vorname }}</span>
                        @endif
                    </div>
                    <div class="flex-gap-klein">
                        <span class="text-hell text-8">{{ $tour->einsaetze->count() }} Einsätze</span>
                        <span class="badge badge-klein {{ $tour->status === 'abgeschlossen' ? 'badge-erfolg' : ($tour->status === 'gestartet' ? 'badge-warnung' : 'badge-grau') }}">{{ ucfirst($tour->status) }}</span>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-klein text-hell m-05">
                Keine Touren für heute.
            </p>
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
                <div class="listen-zeile-inner-start">
                    <div class="flex-1-min">
                        <a href="{{ route('klienten.show', $r->klient) }}" class="text-fett link-primaer">{{ $r->klient->vollname() }}</a>
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
            </div>
            @empty
            <p class="text-klein text-hell m-05">
                Noch keine Rapporte.
            </p>
            @endforelse
        </div>

    </div>

</div>

</x-layouts.app>
