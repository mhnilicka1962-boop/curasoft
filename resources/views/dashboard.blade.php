<x-layouts.app titel="Dashboard">

<div style="max-width: 1100px;">

    <h1 class="seiten-titel seiten-titel-mb">
        Willkommen, {{ Auth::user()->vorname }}!
    </h1>

    {{-- ===== ONBOARDING (nur Admin, solange Setup nicht fertig) ===== --}}
    @if(auth()->user()->rolle === 'admin' && !$setupFertig)
    <div class="karte" style="margin-bottom: 1.5rem; border-left: 4px solid var(--cs-primaer);">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
            <div>
                <div style="font-size: 1.0625rem; font-weight: 700;">Ersteinrichtung — In 5 Schritten startklar</div>
                <div class="text-klein text-hell" style="margin-top: 0.2rem;">Folgen Sie den Schritten — danach verschwindet diese Anleitung automatisch.</div>
            </div>
            @php $done = collect($setup)->filter()->count(); $total = count($setup); @endphp
            <div style="text-align: right;">
                <span style="font-size: 1.5rem; font-weight: 700; color: var(--cs-primaer);">{{ $done }}/{{ $total }}</span>
                <div class="text-mini text-hell">abgeschlossen</div>
            </div>
        </div>

        {{-- Fortschrittsbalken --}}
        <div style="background: var(--cs-border); border-radius: 4px; height: 6px; margin-bottom: 1.25rem;">
            <div style="background: var(--cs-primaer); height: 6px; border-radius: 4px; width: {{ round($done / $total * 100) }}%; transition: width .4s;"></div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.6rem;">

            {{-- Schritt 1: Firma --}}
            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem; border-radius: var(--cs-radius); background: {{ $setup['firma'] ? 'var(--cs-hintergrund)' : 'var(--cs-hintergrund)' }};">
                <div style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                    background: {{ $setup['firma'] ? 'var(--cs-erfolg)' : 'var(--cs-border)' }};
                    color: {{ $setup['firma'] ? '#fff' : 'var(--cs-text-hell)' }}; font-weight: 700; font-size: 0.8rem;">
                    {{ $setup['firma'] ? '✓' : '1' }}
                </div>
                <div style="flex: 1;">
                    <div class="text-fett" style="font-size: 0.9rem; {{ $setup['firma'] ? 'text-decoration: line-through; color: var(--cs-text-hell);' : '' }}">Firmen-Daten erfassen</div>
                    <div class="text-mini text-hell">Name, Adresse, IBAN — für Rechnungen und PDF</div>
                </div>
                @if(!$setup['firma'])
                <a href="{{ route('firma.index') }}" class="btn btn-primaer" style="font-size: 0.8rem; padding: 0.25rem 0.75rem; white-space: nowrap;">Jetzt →</a>
                @endif
            </div>

            {{-- Schritt 2: Region --}}
            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem; border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                <div style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                    background: {{ $setup['region'] ? 'var(--cs-erfolg)' : ($setup['firma'] ? 'var(--cs-primaer)' : 'var(--cs-border)') }};
                    color: {{ $setup['region'] || $setup['firma'] ? '#fff' : 'var(--cs-text-hell)' }}; font-weight: 700; font-size: 0.8rem;">
                    {{ $setup['region'] ? '✓' : '2' }}
                </div>
                <div style="flex: 1;">
                    <div class="text-fett" style="font-size: 0.9rem; {{ $setup['region'] ? 'text-decoration: line-through; color: var(--cs-text-hell);' : '' }}">Kanton / Region anlegen</div>
                    <div class="text-mini text-hell">z.B. Kanton Aargau — bestimmt die Tarife (KVG-Ansätze)</div>
                </div>
                @if(!$setup['region'])
                <a href="{{ route('regionen.index') }}" class="btn {{ $setup['firma'] ? 'btn-primaer' : 'btn-sekundaer' }}" style="font-size: 0.8rem; padding: 0.25rem 0.75rem; white-space: nowrap;">Jetzt →</a>
                @endif
            </div>

            {{-- Schritt 3: Klient --}}
            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem; border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                <div style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                    background: {{ $setup['klient'] ? 'var(--cs-erfolg)' : ($setup['region'] ? 'var(--cs-primaer)' : 'var(--cs-border)') }};
                    color: {{ $setup['klient'] || $setup['region'] ? '#fff' : 'var(--cs-text-hell)' }}; font-weight: 700; font-size: 0.8rem;">
                    {{ $setup['klient'] ? '✓' : '3' }}
                </div>
                <div style="flex: 1;">
                    <div class="text-fett" style="font-size: 0.9rem; {{ $setup['klient'] ? 'text-decoration: line-through; color: var(--cs-text-hell);' : '' }}">Ersten Klienten anlegen</div>
                    <div class="text-mini text-hell">Name, Adresse, Krankenkasse — Pflichtfelder reichen für den Start</div>
                </div>
                @if(!$setup['klient'])
                <a href="{{ route('klienten.index') }}#neu" class="btn {{ $setup['region'] ? 'btn-primaer' : 'btn-sekundaer' }}" style="font-size: 0.8rem; padding: 0.25rem 0.75rem; white-space: nowrap;">Jetzt →</a>
                @endif
            </div>

            {{-- Schritt 4: Mitarbeiter --}}
            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem; border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                <div style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                    background: {{ $setup['mitarbeiter'] ? 'var(--cs-erfolg)' : ($setup['klient'] ? 'var(--cs-primaer)' : 'var(--cs-border)') }};
                    color: {{ $setup['mitarbeiter'] || $setup['klient'] ? '#fff' : 'var(--cs-text-hell)' }}; font-weight: 700; font-size: 0.8rem;">
                    {{ $setup['mitarbeiter'] ? '✓' : '4' }}
                </div>
                <div style="flex: 1;">
                    <div class="text-fett" style="font-size: 0.9rem; {{ $setup['mitarbeiter'] ? 'text-decoration: line-through; color: var(--cs-text-hell);' : '' }}">Erste Mitarbeiterin einladen</div>
                    <div class="text-mini text-hell">Pflegeperson mit E-Mail — erhält automatisch Einladungslink</div>
                </div>
                @if(!$setup['mitarbeiter'])
                <a href="{{ route('mitarbeiter.index') }}#neu" class="btn {{ $setup['klient'] ? 'btn-primaer' : 'btn-sekundaer' }}" style="font-size: 0.8rem; padding: 0.25rem 0.75rem; white-space: nowrap;">Jetzt →</a>
                @endif
            </div>

            {{-- Schritt 5: Einsatz --}}
            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.6rem 0.75rem; border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                <div style="width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                    background: {{ $setup['einsatz'] ? 'var(--cs-erfolg)' : ($setup['mitarbeiter'] ? 'var(--cs-primaer)' : 'var(--cs-border)') }};
                    color: {{ $setup['einsatz'] || $setup['mitarbeiter'] ? '#fff' : 'var(--cs-text-hell)' }}; font-weight: 700; font-size: 0.8rem;">
                    {{ $setup['einsatz'] ? '✓' : '5' }}
                </div>
                <div style="flex: 1;">
                    <div class="text-fett" style="font-size: 0.9rem; {{ $setup['einsatz'] ? 'text-decoration: line-through; color: var(--cs-text-hell);' : '' }}">Ersten Einsatz planen</div>
                    <div class="text-mini text-hell">Klient + Mitarbeiterin + Datum + Zeit — fertig</div>
                </div>
                @if(!$setup['einsatz'])
                <a href="{{ route('einsaetze.create') }}" class="btn {{ $setup['mitarbeiter'] ? 'btn-primaer' : 'btn-sekundaer' }}" style="font-size: 0.8rem; padding: 0.25rem 0.75rem; white-space: nowrap;">Jetzt →</a>
                @endif
            </div>

        </div>

        <div class="text-mini text-hell" style="margin-top: 1rem; text-align: center;">
            Fragen? <a href="{{ route('schulung') }}" class="link-primaer">Schulungsunterlagen</a> ·
            <a href="{{ route('hilfe') }}" class="link-primaer">Hilfe & Scripts</a>
        </div>
    </div>
    @endif

    {{-- ===== NORMALE DASHBOARD-ANSICHT ===== --}}
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
