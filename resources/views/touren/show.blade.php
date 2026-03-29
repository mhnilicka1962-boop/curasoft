<x-layouts.app :titel="$tour->bezeichnung">
<div style="max-width: 960px;">

    <a href="{{ route('touren.index', ['datum' => $tour->datum->format('Y-m-d')]) }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">← Touren</a>

    {{-- Kachel: Tourdaten --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <form method="POST" action="{{ route('touren.update', $tour) }}">
            @csrf @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 1fr 120px 120px 140px; gap: 0.75rem; margin-bottom: 0.875rem; align-items: end;">
                <div>
                    <label class="feld-label" style="font-size: 0.75rem;">Bezeichnung</label>
                    <input type="text" name="bezeichnung" class="feld" required style="font-size: 0.875rem;" value="{{ $tour->bezeichnung }}">
                </div>
                <div>
                    <label class="feld-label" style="font-size: 0.75rem;">Mitarbeiter/in</label>
                    <div class="feld" style="font-size: 0.875rem; background: var(--cs-hintergrund); color: var(--cs-text-hell);">{{ $tour->benutzer?->vorname }} {{ $tour->benutzer?->nachname }}</div>
                </div>
                <div>
                    <label class="feld-label" style="font-size: 0.75rem;">Startzeit <span style="color:var(--cs-fehler);">*</span></label>
                    <input type="time" name="start_zeit" class="feld" style="font-size: 0.875rem;" value="{{ $tour->start_zeit ? substr($tour->start_zeit, 0, 5) : '' }}" required>
                </div>
                <div>
                    <label class="feld-label" style="font-size: 0.75rem;">Endzeit</label>
                    <input type="time" name="end_zeit" class="feld" style="font-size: 0.875rem;" value="{{ $tour->end_zeit ? substr($tour->end_zeit, 0, 5) : '' }}">
                </div>
                <div>
                    <label class="feld-label" style="font-size: 0.75rem;">Status</label>
                    <select name="status" class="feld" style="font-size: 0.875rem;">
                        @foreach(['geplant' => 'Geplant', 'gestartet' => 'Gestartet', 'abgeschlossen' => 'Abgeschlossen'] as $wert => $lbl)
                            <option value="{{ $wert }}" {{ $tour->status === $wert ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Speichern</button>
                <span class="text-hell" style="font-size: 0.8125rem;">{{ $tour->datum->format('d.m.Y') }}</span>
                <span style="flex: 1;"></span>
                <button type="submit" form="tour-loeschen-form" class="btn btn-gefahr" style="font-size: 0.8125rem;">Tour löschen</button>
            </div>
        </form>
        <form method="POST" action="{{ route('touren.destroy', $tour) }}" id="tour-loeschen-form"
              onsubmit="return confirm('Tour wirklich löschen?')">
            @csrf @method('DELETE')
        </form>
    </div>

    @if($konflikteIds->isNotEmpty())
        <div class="alert alert-warnung" style="margin-bottom: 1rem;">
            ⚠ <strong>Zeitüberschneidung:</strong> {{ $konflikteIds->count() }} Einsatz/Einsätze haben überlappende geplante Zeiten.
        </div>
    @endif

    {{-- Einsätze --}}
    <div class="karte" style="margin-bottom: 1rem; padding: 0;">

        <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--cs-border); display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
            <span style="font-weight: 600; font-size: 0.9375rem;">Einsätze</span>
            <span class="text-hell" style="font-size: 0.8125rem;">{{ $tour->einsaetze->where('status','abgeschlossen')->count() }}/{{ $tour->einsaetze->count() }} abgeschlossen</span>
            <span style="flex: 1;"></span>
            @if($tour->einsaetze->filter(fn($e) => $e->klient?->klient_lat)->count() >= 2 && auth()->user()->rolle === 'admin')
                <form method="POST" action="{{ route('touren.route.optimieren', $tour) }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-sekundaer" style="font-size: 0.75rem; padding: 0.25rem 0.625rem;" onclick="return confirm('Route optimieren?')">🗺 Route optimieren</button>
                </form>
            @endif
            @php
                $letzteZeitBis = $tour->einsaetze->sortBy('tour_reihenfolge')->last()?->zeit_bis;
                $naechsteZeit  = $letzteZeitBis
                    ? substr($letzteZeitBis, 0, 5)
                    : ($tour->start_zeit ? substr($tour->start_zeit, 0, 5) : null);
            @endphp
            <a href="{{ route('einsaetze.create', array_filter(['datum' => $tour->datum->format('Y-m-d'), 'benutzer_id' => $tour->benutzer_id, '_tour_redirect' => $tour->id, 'zeit_von' => $naechsteZeit])) }}"
               class="btn btn-primaer" style="font-size: 0.8125rem; padding: 0.3rem 0.75rem;">+ Einsatz anlegen</a>
        </div>

        <div id="einsatz-liste">
        @forelse($tour->einsaetze->sortBy('tour_reihenfolge') as $idx => $e)
        @php
            $zeileFarbe = match(true) {
                $e->status === 'abgeschlossen' => 'var(--cs-erfolg-hell, #f0fdf4)',
                $e->status === 'aktiv'         => 'var(--cs-warnung-hell, #fffbeb)',
                default => 'transparent',
            };
        @endphp
        <div data-einsatz-id="{{ $e->id }}"
             data-lat="{{ $e->klient?->klient_lat ?? '' }}"
             data-lng="{{ $e->klient?->klient_lng ?? '' }}"
             data-zeit-von="{{ $e->zeit_von ? substr($e->zeit_von, 0, 5) : '' }}"
             data-zeit-bis="{{ $e->zeit_bis ? substr($e->zeit_bis, 0, 5) : '' }}"
             data-name="{{ $e->klient?->vorname }} {{ $e->klient?->nachname }}"
             data-adresse="{{ $e->klient?->adresse }}, {{ $e->klient?->ort }}"
             data-status="{{ $e->status }}"
             @if(auth()->user()->rolle === 'admin') draggable="true" @endif
             style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-bottom: 1px solid var(--cs-border); background: {{ $zeileFarbe }}; {{ auth()->user()->rolle === 'admin' ? 'cursor:grab;' : '' }}">

            @if(auth()->user()->rolle === 'admin')
            <span style="color: var(--cs-text-hell); font-size: 1rem; cursor: grab; user-select: none; flex-shrink: 0;">⠿</span>
            @endif

            <span class="reihenfolge-nr text-hell" style="font-size: 0.8rem; min-width: 18px; flex-shrink: 0;">{{ $e->tour_reihenfolge ?? $idx + 1 }}.</span>

            <a href="{{ route('einsaetze.vor-ort', $e) }}" style="font-weight: 600; font-size: 0.9rem; color: var(--cs-text); text-decoration: none; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ $e->klient?->vollname() }}
            </a>

            <span class="text-hell" style="font-size: 0.8125rem; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                {{ $e->einsatzLeistungsarten->map(fn($el) => $el->leistungsart?->bezeichnung)->filter()->implode(', ') }}
            </span>

            @if($e->zeit_von)
            <span class="text-hell" style="font-size: 0.8rem; flex-shrink: 0; white-space: nowrap;">
                {{ substr($e->zeit_von, 0, 5) }}@if($e->zeit_bis)–{{ substr($e->zeit_bis, 0, 5) }}@endif
            </span>
            @endif

            <span class="badge {{ $e->statusBadgeKlasse() }}" style="font-size: 0.7rem; flex-shrink: 0;">{{ $e->statusLabel() }}</span>

            @if($konflikteIds->contains($e->id))
                <span title="Zeitüberschneidung" style="color: var(--cs-warnung); font-size: 0.85rem; flex-shrink: 0;">⚠</span>
            @endif

            <a href="{{ route('einsaetze.edit', $e) }}" class="btn btn-sekundaer" style="font-size: 0.8125rem; padding: 0.25rem 0.625rem; flex-shrink: 0;">Bearbeiten</a>
        </div>
        @empty
        <div class="text-hell" style="padding: 2rem; text-align: center; font-size: 0.875rem;">Noch keine Einsätze zugewiesen.</div>
        @endforelse
        </div>

        {{-- Offene Einsätze hinzufügen --}}
        @if($offeneEinsaetze->count())
        <div style="padding: 0.875rem 1rem; border-top: 1px solid var(--cs-border);">
            <form method="POST" action="{{ route('touren.einsatz.zuweisen', $tour) }}">
                @csrf
                <div style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); margin-bottom: 0.625rem;">
                    + Bestehende Einsätze zuweisen ({{ $offeneEinsaetze->count() }} verfügbar)
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.25rem; margin-bottom: 0.75rem;">
                    @foreach($offeneEinsaetze as $e)
                    <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.25rem 0;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer; flex: 1; min-width: 0;">
                            <input type="checkbox" name="einsatz_ids[]" value="{{ $e->id }}" checked>
                            <span style="font-weight: 500;">{{ $e->klient?->vollname() }}</span>
                            <span class="text-hell">{{ $e->einsatzLeistungsarten->map(fn($el) => $el->leistungsart?->bezeichnung)->filter()->implode(', ') }}</span>
                            @if($e->zeit_von)
                                <span class="text-hell" style="margin-left: auto; font-size: 0.8rem;">{{ substr($e->zeit_von, 0, 5) }}</span>
                            @endif
                        </label>
                        <a href="{{ route('einsaetze.edit', $e) }}" class="btn btn-sekundaer" style="font-size: 0.75rem; padding: 0.2rem 0.5rem; flex-shrink: 0;">Detail</a>
                    </div>
                    @endforeach
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Alle zuweisen</button>
                    <span class="text-hell" style="font-size: 0.8rem;">alle angehakten Einsätze werden zugewiesen</span>
                </div>
            </form>
        </div>
        @endif

    </div>

    {{-- Karte --}}
    @if($kartenEinsaetze->count() >= 1)
    <div class="karte" style="padding: 0; overflow: hidden;">
        <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--cs-border);">
            <span style="font-weight: 600;">Route ({{ $kartenEinsaetze->count() }} Stops)</span>
        </div>
        <div id="tourenkarte" style="height: 380px; width: 100%;"></div>
    </div>

    @push('scripts')
    @vite('resources/js/tourenkarte.js')
    @php
        $kartenpunkte = $kartenEinsaetze->map(fn($e) => [
            'lat'         => (float) $e->klient->klient_lat,
            'lng'         => (float) $e->klient->klient_lng,
            'klient_name' => $e->klient->vorname . ' ' . $e->klient->nachname,
            'adresse'     => $e->klient->adresse . ', ' . $e->klient->ort,
            'zeit_von'    => $e->zeit_von ? substr($e->zeit_von, 0, 5) : null,
            'zeit_bis'    => $e->zeit_bis ? substr($e->zeit_bis, 0, 5) : null,
            'status'      => $e->status,
        ])->values();
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.TourenkarteInit(@json($kartenpunkte));
        });
    </script>
    @endpush
    @endif

    @if(auth()->user()->rolle === 'admin')
    @push('scripts')
    <script>
    (function() {
        const tourId = {{ $tour->id }};
        const csrf   = '{{ csrf_token() }}';
        const liste  = document.getElementById('einsatz-liste');
        if (!liste) return;

        let dragSrc = null;

        liste.addEventListener('dragstart', function(e) {
            const row = e.target.closest('[data-einsatz-id]');
            if (!row) return;
            dragSrc = row;
            setTimeout(() => row.style.opacity = '0.45', 0);
            e.dataTransfer.effectAllowed = 'move';
        });

        liste.addEventListener('dragend', function(e) {
            const row = e.target.closest('[data-einsatz-id]');
            if (row) row.style.opacity = '';
            dragSrc = null;
        });

        liste.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (!dragSrc) return;
            const row = e.target.closest('[data-einsatz-id]');
            if (!row || row === dragSrc) return;
            const mid = row.getBoundingClientRect().top + row.getBoundingClientRect().height / 2;
            if (e.clientY < mid) {
                if (row.previousSibling !== dragSrc) liste.insertBefore(dragSrc, row);
            } else {
                if (row.nextSibling !== dragSrc) liste.insertBefore(dragSrc, row.nextSibling);
            }
        });

        liste.addEventListener('drop', function(e) {
            e.preventDefault();
            const reihenfolge = [...liste.querySelectorAll('[data-einsatz-id]')].map(r => r.dataset.einsatzId);
            liste.querySelectorAll('[data-einsatz-id]').forEach((row, i) => {
                const nr = row.querySelector('.reihenfolge-nr');
                if (nr) nr.textContent = (i + 1) + '.';
            });
            fetch('/touren/' + tourId + '/reihenfolge', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ reihenfolge }),
            });
        });
    })();
    </script>
    @endpush
    @endif

</div>
</x-layouts.app>
