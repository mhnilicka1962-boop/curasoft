<x-layouts.app titel="Tourenplanung">
<div style="max-width: 1000px;">

    <div class="seiten-kopf">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Tourenplanung</h1>
        <a href="{{ route('touren.create') }}" class="btn btn-primaer">+ Neue Tour</a>
    </div>

    {{-- Tages-Navigation --}}
    <form method="GET" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; margin-bottom: 1.25rem;">
        <a href="{{ route('touren.index', array_merge(request()->query(), ['datum' => $datum->copy()->subDay()->format('Y-m-d')])) }}"
            class="btn btn-sekundaer" style="padding: 0.375rem 0.625rem;">‹</a>

        <input type="date" name="datum" class="feld" style="width: 160px;" value="{{ $datum->format('Y-m-d') }}"
            onchange="this.form.submit()">

        <span style="font-size: 1rem; font-weight: 600;">{{ $datum->isoFormat('dddd, D. MMMM YYYY') }}</span>

        <a href="{{ route('touren.index', array_merge(request()->query(), ['datum' => $datum->copy()->addDay()->format('Y-m-d')])) }}"
            class="btn btn-sekundaer" style="padding: 0.375rem 0.625rem;">›</a>

        @if(auth()->user()->rolle === 'admin')
        <select name="benutzer_id" class="feld" style="width: 180px;" onchange="this.form.submit()">
            <option value="">Alle Mitarbeiter</option>
            @foreach($mitarbeiter as $m)
                <option value="{{ $m->id }}" {{ request('benutzer_id') == $m->id ? 'selected' : '' }}>{{ $m->vorname }} {{ $m->nachname }}</option>
            @endforeach
        </select>
        @endif

        @if($datum->isToday())
            <span class="badge badge-erfolg">Heute</span>
        @endif
    </form>

    @forelse($touren as $tour)
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="seiten-kopf" style="margin-bottom: 0.875rem; gap: 0.5rem;">
            <div>
                <span style="font-weight: 700; font-size: 1rem;">{{ $tour->bezeichnung }}</span>
                <span class="text-klein text-hell" style="margin-left: 0.75rem;">{{ $tour->benutzer?->vorname }} {{ $tour->benutzer?->nachname }}</span>
                @if($tour->start_zeit)
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">{{ $tour->start_zeit }}</span>
                @endif
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <span class="badge {{ $tour->status === 'abgeschlossen' ? 'badge-erfolg' : ($tour->status === 'gestartet' ? 'badge-warnung' : 'badge-grau') }}" style="font-size: 0.75rem;">
                    {{ ucfirst($tour->status) }}
                </span>
                <a href="{{ route('touren.show', $tour) }}" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.25rem 0.625rem;">Detail</a>
            </div>
        </div>

        @if($tour->einsaetze->count())
        <div style="display: flex; flex-direction: column; gap: 0.375rem;">
            @foreach($tour->einsaetze as $idx => $e)
            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.375rem 0.625rem; background: var(--cs-hintergrund); border-radius: var(--cs-radius); font-size: 0.875rem;">
                <span class="text-hell" style="font-size: 0.8rem; min-width: 20px;">{{ $idx + 1 }}.</span>
                <span class="text-fett">{{ $e->klient?->vollname() }}</span>
                <span class="text-hell">{{ $e->leistungsart?->bezeichnung }}</span>
                @if($e->zeit_von)
                    <span class="text-hell" style="margin-left: auto; font-size: 0.8rem;">{{ $e->zeit_von }}</span>
                @endif
                <span class="badge {{ $e->statusBadgeKlasse() }}" style="font-size: 0.7rem;">{{ $e->statusLabel() }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-klein text-hell" style="margin: 0;">Noch keine Einsätze zugewiesen.</p>
        @endif
    </div>
    @empty
    <div class="karte" style="text-align: center; padding: 2rem; color: var(--cs-text-hell);">
        Keine Touren für diesen Tag.
    </div>
    @endforelse

    {{-- Nicht eingeplante Einsätze (Lücken) --}}
    @if($ohneTouren->isNotEmpty())
    <div class="karte" style="border-left: 3px solid var(--cs-warnung); margin-top: 1rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.875rem; flex-wrap: wrap; gap: 0.5rem;">
            <div>
                <span class="abschnitt-label" style="margin: 0; color: var(--cs-warnung);">
                    ⚠ Nicht eingeplante Einsätze
                </span>
                <span class="text-hell" style="font-size: 0.8125rem; margin-left: 0.5rem;">
                    ({{ $ohneTouren->flatten()->count() }} Einsatz/Einsätze ohne Tour)
                </span>
            </div>
        </div>

        @foreach($ohneTouren as $benutzerId => $einsaetze)
        @php $ma = $einsaetze->first()->benutzer; @endphp
        <div style="margin-bottom: 0.875rem; padding: 0.75rem; background: var(--cs-hintergrund); border-radius: var(--cs-radius);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                <span style="font-weight: 600; font-size: 0.9375rem;">
                    {{ $ma?->vorname }} {{ $ma?->nachname }}
                    <span class="text-hell" style="font-weight: 400; font-size: 0.8125rem; margin-left: 0.25rem;">{{ $einsaetze->count() }} Einsatz{{ $einsaetze->count() !== 1 ? 'ätze' : '' }}</span>
                </span>
                @if(auth()->user()->rolle === 'admin')
                <a href="{{ route('touren.create', ['benutzer_id' => $benutzerId, 'datum' => $datum->format('Y-m-d')]) }}"
                   class="btn btn-primaer" style="font-size: 0.8125rem; padding: 0.25rem 0.625rem;">
                    + Tour erstellen
                </a>
                @endif
            </div>
            <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                @foreach($einsaetze as $e)
                <div style="display: flex; align-items: center; gap: 0.75rem; font-size: 0.8125rem; padding: 0.25rem 0; border-top: 1px solid var(--cs-border);">
                    @if($e->zeit_von)
                        <span class="text-hell" style="min-width: 45px;">{{ substr($e->zeit_von,0,5) }}</span>
                    @else
                        <span class="text-hell" style="min-width: 45px;">—</span>
                    @endif
                    <span style="font-weight: 500;">{{ $e->klient?->vollname() }}</span>
                    <span class="text-hell">{{ $e->leistungsart?->bezeichnung }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
</x-layouts.app>
