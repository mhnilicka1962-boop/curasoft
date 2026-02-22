<x-layouts.app :titel="'Einsätze'">

{{-- Kopfzeile --}}
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; gap: 1rem; flex-wrap: wrap;">
    <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Einsätze</h1>
    <a href="{{ route('einsaetze.create') }}" class="btn btn-primaer">+ Neuer Einsatz</a>
</div>

{{-- Meine Woche (nur Pflege) --}}
@if($meineWoche !== null)
<div class="karte" style="margin-bottom: 1.25rem; padding: 1rem;">
    <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cs-text-hell); margin-bottom: 0.875rem;">Meine nächsten 14 Tage</div>
    @if($meineWoche->isEmpty())
        <p style="color: var(--cs-text-hell); font-size: 0.875rem; margin: 0;">Keine anstehenden Einsätze.</p>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach($meineWoche as $datumKey => $tagesEinsaetze)
            @php
                $datum = \Carbon\Carbon::parse($datumKey);
                $istHeute = $datum->isToday();
                $istMorgen = $datum->isTomorrow();
                $tagLabel = $istHeute ? 'Heute' : ($istMorgen ? 'Morgen' : $datum->isoFormat('ddd, D. MMM'));
            @endphp
            <div>
                <div style="font-size: 0.75rem; font-weight: 700; color: {{ $istHeute ? 'var(--cs-primaer)' : 'var(--cs-text)' }}; margin-bottom: 0.3rem; text-transform: uppercase; letter-spacing: 0.04em;">
                    {{ $tagLabel }}
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    @foreach($tagesEinsaetze as $e)
                    <div style="display: flex; align-items: center; gap: 0.75rem; background: var(--cs-hintergrund); border-radius: 6px; padding: 0.4rem 0.75rem; font-size: 0.875rem;">
                        <span style="white-space: nowrap; color: var(--cs-text-hell); min-width: 90px;">
                            @if($e->zeit_von){{ substr($e->zeit_von, 0, 5) }}{{ $e->zeit_bis ? '–'.substr($e->zeit_bis, 0, 5) : '' }}@else—@endif
                        </span>
                        <span style="font-weight: 600; flex: 1;">
                            <a href="{{ route('einsaetze.show', $e) }}" style="text-decoration: none; color: var(--cs-text);">
                                {{ $e->klient->nachname }} {{ $e->klient->vorname }}
                            </a>
                        </span>
                        <span style="color: var(--cs-text-hell); font-size: 0.8125rem;">{{ $e->leistungsart?->bezeichnung ?? '' }}</span>
                        <span class="badge {{ $e->statusBadgeKlasse() }}">{{ $e->statusLabel() }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endif

{{-- Tab-Switcher --}}
<div style="display: flex; gap: 0; margin-bottom: 1.25rem; border-bottom: 2px solid var(--cs-border);">
    <a href="{{ route('einsaetze.index', array_merge(request()->except(['ansicht','page']), ['ansicht' => 'anstehend'])) }}"
        style="padding: 0.5rem 1.25rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $ansicht === 'anstehend' ? 'var(--cs-primaer)' : 'transparent' }}; margin-bottom: -2px; color: {{ $ansicht === 'anstehend' ? 'var(--cs-primaer)' : 'var(--cs-text-hell)' }};">
        Anstehend
    </a>
    <a href="{{ route('einsaetze.index', array_merge(request()->except(['ansicht','page']), ['ansicht' => 'vergangen'])) }}"
        style="padding: 0.5rem 1.25rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; border-bottom: 2px solid {{ $ansicht === 'vergangen' ? 'var(--cs-primaer)' : 'transparent' }}; margin-bottom: -2px; color: {{ $ansicht === 'vergangen' ? 'var(--cs-primaer)' : 'var(--cs-text-hell)' }};">
        Vergangen
    </a>
</div>

{{-- Filter --}}
<div class="karte" style="margin-bottom: 1.25rem; padding: 1rem;">
    <form method="GET" action="{{ route('einsaetze.index') }}">
        <input type="hidden" name="ansicht" value="{{ $ansicht }}">
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.75rem; align-items: end;">

            <div>
                <label class="feld-label" style="font-size: 0.75rem;">Klient</label>
                <input type="text" name="suche" class="feld" style="font-size: 0.875rem;"
                    placeholder="Name suchen…" value="{{ request('suche') }}">
            </div>

            <div>
                <label class="feld-label" style="font-size: 0.75rem;">Datum von</label>
                <input type="date" name="datum_von" class="feld" style="font-size: 0.875rem;"
                    value="{{ request('datum_von') }}">
            </div>

            <div>
                <label class="feld-label" style="font-size: 0.75rem;">Datum bis</label>
                <input type="date" name="datum_bis" class="feld" style="font-size: 0.875rem;"
                    value="{{ request('datum_bis') }}">
            </div>

            <div>
                <label class="feld-label" style="font-size: 0.75rem;">Status</label>
                <select name="status" class="feld" style="font-size: 0.875rem;">
                    <option value="">Alle</option>
                    <option value="geplant"        {{ request('status') === 'geplant'        ? 'selected' : '' }}>Geplant</option>
                    <option value="aktiv"          {{ request('status') === 'aktiv'          ? 'selected' : '' }}>Läuft</option>
                    <option value="abgeschlossen"  {{ request('status') === 'abgeschlossen'  ? 'selected' : '' }}>Abgeschlossen</option>
                    <option value="storniert"      {{ request('status') === 'storniert'      ? 'selected' : '' }}>Storniert</option>
                </select>
            </div>

            <div>
                <label class="feld-label" style="font-size: 0.75rem;">Leistungsart</label>
                <select name="leistungsart_id" class="feld" style="font-size: 0.875rem;">
                    <option value="">Alle</option>
                    @foreach($leistungsarten as $la)
                        <option value="{{ $la->id }}" {{ request('leistungsart_id') == $la->id ? 'selected' : '' }}>
                            {{ $la->bezeichnung }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(auth()->user()->rolle === 'admin' && $mitarbeiter->count())
            <div>
                <label class="feld-label" style="font-size: 0.75rem;">Mitarbeiter</label>
                <select name="benutzer_id" class="feld" style="font-size: 0.875rem;">
                    <option value="">Alle</option>
                    @foreach($mitarbeiter as $m)
                        <option value="{{ $m->id }}" {{ request('benutzer_id') == $m->id ? 'selected' : '' }}>
                            {{ $m->nachname }} {{ $m->vorname }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
                <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem; flex: 1;">Filtern</button>
                @if(request()->anyFilled(['suche','datum_von','datum_bis','status','leistungsart_id','benutzer_id']))
                    <a href="{{ route('einsaetze.index', ['ansicht' => $ansicht]) }}" class="btn btn-sekundaer" style="font-size: 0.875rem;">×</a>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- Tabelle --}}
<div class="karte" style="padding: 0; overflow: hidden;">
    @if($einsaetze->count())
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
            <thead>
                <tr style="border-bottom: 2px solid var(--cs-border); background: var(--cs-hintergrund);">
                    <th style="padding: 0.625rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cs-text-hell); white-space: nowrap;">Datum</th>
                    <th style="padding: 0.625rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cs-text-hell);">Klient</th>
                    <th style="padding: 0.625rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cs-text-hell);">Leistungsart</th>
                    <th style="padding: 0.625rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cs-text-hell); white-space: nowrap;">Zeit</th>
                    <th style="padding: 0.625rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cs-text-hell);">Mitarbeiter</th>
                    <th style="padding: 0.625rem 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: var(--cs-text-hell);">Status</th>
                    <th style="padding: 0.625rem 1rem;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($einsaetze as $einsatz)
                <tr style="border-bottom: 1px solid var(--cs-border);">
                    <td style="padding: 0.625rem 1rem; white-space: nowrap; color: var(--cs-text-hell);">
                        {{ $einsatz->datum->format('d.m.Y') }}
                        @if($einsatz->datum_bis)
                            <br><span style="font-size: 0.75rem;">– {{ $einsatz->datum_bis->format('d.m.Y') }}</span>
                        @endif
                    </td>
                    <td style="padding: 0.625rem 1rem;">
                        <a href="{{ route('klienten.show', $einsatz->klient) }}"
                            style="font-weight: 600; text-decoration: none; color: var(--cs-text);">
                            {{ $einsatz->klient->nachname }} {{ $einsatz->klient->vorname }}
                        </a>
                    </td>
                    <td style="padding: 0.625rem 1rem; color: var(--cs-text-hell);">
                        {{ $einsatz->leistungsart?->bezeichnung ?? '—' }}
                    </td>
                    <td style="padding: 0.625rem 1rem; white-space: nowrap; color: var(--cs-text-hell);">
                        @if($einsatz->checkin_zeit && $einsatz->checkout_zeit)
                            {{ $einsatz->checkin_zeit->format('H:i') }}–{{ $einsatz->checkout_zeit->format('H:i') }}
                            <span style="color: var(--cs-primaer); font-size: 0.75rem;">{{ $einsatz->dauerMinuten() }}'</span>
                        @elseif($einsatz->zeit_von)
                            {{ substr($einsatz->zeit_von, 0, 5) }}{{ $einsatz->zeit_bis ? '–' . substr($einsatz->zeit_bis, 0, 5) : '' }}
                        @else
                            —
                        @endif
                    </td>
                    <td style="padding: 0.625rem 1rem; color: var(--cs-text-hell);">
                        {{ $einsatz->benutzer?->vorname ?? '—' }} {{ $einsatz->benutzer?->nachname ?? '' }}
                    </td>
                    <td style="padding: 0.625rem 1rem;">
                        <span class="badge {{ $einsatz->statusBadgeKlasse() }}">{{ $einsatz->statusLabel() }}</span>
                    </td>
                    <td style="padding: 0.625rem 1rem; text-align: right; white-space: nowrap;">
                        <a href="{{ route('einsaetze.show', $einsatz) }}"
                            class="btn btn-sekundaer" style="font-size: 0.75rem; padding: 0.2rem 0.625rem;">
                            Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($einsaetze->hasPages())
    <div style="padding: 0.875rem 1rem; border-top: 1px solid var(--cs-border); display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
        <div style="font-size: 0.8125rem; color: var(--cs-text-hell);">
            {{ $einsaetze->firstItem() }}–{{ $einsaetze->lastItem() }} von {{ $einsaetze->total() }} Einsätzen
        </div>
        <div style="display: flex; gap: 0.375rem;">
            @if($einsaetze->onFirstPage())
                <span class="btn btn-sekundaer" style="opacity: 0.4; pointer-events: none; padding: 0.3rem 0.75rem; font-size: 0.8125rem;">←</span>
            @else
                <a href="{{ $einsaetze->previousPageUrl() }}" class="btn btn-sekundaer" style="padding: 0.3rem 0.75rem; font-size: 0.8125rem;">←</a>
            @endif

            <span style="padding: 0.3rem 0.75rem; font-size: 0.8125rem; color: var(--cs-text-hell);">
                Seite {{ $einsaetze->currentPage() }} / {{ $einsaetze->lastPage() }}
            </span>

            @if($einsaetze->hasMorePages())
                <a href="{{ $einsaetze->nextPageUrl() }}" class="btn btn-sekundaer" style="padding: 0.3rem 0.75rem; font-size: 0.8125rem;">→</a>
            @else
                <span class="btn btn-sekundaer" style="opacity: 0.4; pointer-events: none; padding: 0.3rem 0.75rem; font-size: 0.8125rem;">→</span>
            @endif
        </div>
    </div>
    @endif

    @else
    <div style="text-align: center; padding: 3rem; color: var(--cs-text-hell);">
        @if(request()->anyFilled(['suche','datum_von','datum_bis','status','leistungsart_id','benutzer_id']))
            Keine Einsätze für die gewählten Filter.
        @else
            {{ $ansicht === 'vergangen' ? 'Keine vergangenen Einsätze vorhanden.' : 'Keine anstehenden Einsätze.' }}
        @endif
    </div>
    @endif
</div>

</x-layouts.app>
