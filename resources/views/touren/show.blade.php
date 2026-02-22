<x-layouts.app :titel="$tour->bezeichnung">
<div style="max-width: 900px;">

    <a href="{{ route('touren.index', ['datum' => $tour->datum->format('Y-m-d')]) }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">← Touren</a>

    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h1 style="font-size: 1.125rem; font-weight: 700; margin: 0;">{{ $tour->bezeichnung }}</h1>
            <div class="text-klein text-hell" style="margin-top: 0.25rem;">
                {{ $tour->datum->format('d.m.Y') }}
                · {{ $tour->benutzer?->vorname }} {{ $tour->benutzer?->nachname }}
                @if($tour->start_zeit) · {{ $tour->start_zeit }} @endif
                @if($tour->end_zeit) – {{ $tour->end_zeit }} @endif
            </div>
        </div>
        <span class="badge {{ $tour->status === 'abgeschlossen' ? 'badge-erfolg' : ($tour->status === 'gestartet' ? 'badge-warnung' : 'badge-grau') }}" style="font-size: 0.875rem;">
            {{ ucfirst($tour->status) }}
        </span>
    </div>

    {{-- Einsätze in Tour --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">
            Einsätze ({{ $tour->einsaetze->count() }})
        </div>

        @if($tour->einsaetze->count())
        <div style="display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem;">
            @foreach($tour->einsaetze as $idx => $e)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.75rem; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); font-size: 0.875rem; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex: 1;">
                    <span class="text-hell" style="min-width: 24px; font-size: 0.8rem;">{{ $idx + 1 }}.</span>
                    <div>
                        <div class="text-fett">{{ $e->klient?->vollname() }}</div>
                        <div class="text-hell" style="font-size: 0.8rem;">
                            {{ $e->leistungsart?->bezeichnung }}
                            @if($e->zeit_von) · {{ $e->zeit_von }}@if($e->zeit_bis) – {{ $e->zeit_bis }}@endif @endif
                            @if($e->klient?->adresse) · {{ $e->klient->adresse }}, {{ $e->klient->ort }} @endif
                        </div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
                    <span class="badge {{ $e->statusBadgeKlasse() }}" style="font-size: 0.75rem;">{{ $e->statusLabel() }}</span>
                    <form method="POST" action="{{ route('touren.einsatz.entfernen', [$tour, $e]) }}" style="margin: 0;" onsubmit="return confirm('Einsatz aus Tour entfernen?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-hell" style="background: none; border: none; cursor: pointer; font-size: 0.875rem; padding: 0;">×</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Einsatz hinzufügen --}}
        @if($offeneEinsaetze->count())
        <details>
            <summary style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); cursor: pointer; padding: 0.375rem 0; list-style: none; display: flex; align-items: center; gap: 0.375rem;">
                + Einsatz zuweisen
            </summary>
            <div style="margin-top: 0.875rem; padding: 1rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); background: var(--cs-hintergrund);">
                <form method="POST" action="{{ route('touren.einsatz.zuweisen', $tour) }}">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 120px; gap: 0.75rem; margin-bottom: 0.75rem;">
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Einsatz *</label>
                            <select name="einsatz_id" class="feld" required style="font-size: 0.875rem;">
                                <option value="">— wählen —</option>
                                @foreach($offeneEinsaetze as $e)
                                    <option value="{{ $e->id }}">{{ $e->klient?->vollname() }} – {{ $e->leistungsart?->bezeichnung }}{{ $e->zeit_von ? ' · ' . $e->zeit_von : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="feld-label" style="font-size: 0.75rem;">Reihenfolge</label>
                            <input type="number" name="tour_reihenfolge" class="feld" min="1" style="font-size: 0.875rem;"
                                value="{{ $tour->einsaetze->count() + 1 }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Zuweisen</button>
                </form>
            </div>
        </details>
        @endif
    </div>

    {{-- Status Update --}}
    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Tour bearbeiten</div>
        <form method="POST" action="{{ route('touren.update', $tour) }}">
            @csrf @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 140px 140px 140px; gap: 0.75rem; margin-bottom: 0.75rem;">
                <div>
                    <label class="feld-label" style="font-size: 0.75rem;">Bezeichnung</label>
                    <input type="text" name="bezeichnung" class="feld" required style="font-size: 0.875rem;" value="{{ $tour->bezeichnung }}">
                </div>
                <div>
                    <label class="feld-label" style="font-size: 0.75rem;">Status</label>
                    <select name="status" class="feld" style="font-size: 0.875rem;">
                        @foreach(['geplant' => 'Geplant', 'gestartet' => 'Gestartet', 'abgeschlossen' => 'Abgeschlossen'] as $wert => $lbl)
                            <option value="{{ $wert }}" {{ $tour->status === $wert ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="feld-label" style="font-size: 0.75rem;">Startzeit</label>
                    <input type="time" name="start_zeit" class="feld" style="font-size: 0.875rem;" value="{{ $tour->start_zeit }}">
                </div>
                <div>
                    <label class="feld-label" style="font-size: 0.75rem;">Endzeit</label>
                    <input type="time" name="end_zeit" class="feld" style="font-size: 0.875rem;" value="{{ $tour->end_zeit }}">
                </div>
            </div>
            <button type="submit" class="btn btn-sekundaer" style="font-size: 0.875rem;">Speichern</button>
        </form>
    </div>

</div>
</x-layouts.app>
