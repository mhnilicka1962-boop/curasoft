<x-layouts.app :titel="$tour->bezeichnung">
<div style="max-width: 960px;">

    <a href="{{ route('touren.index', ['datum' => $tour->datum->format('Y-m-d')]) }}" class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">← Touren</a>

    {{-- Kopf --}}
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <div>
            <h1 style="font-size: 1.125rem; font-weight: 700; margin: 0;">{{ $tour->bezeichnung }}</h1>
            <div class="text-klein text-hell" style="margin-top: 0.25rem;">
                {{ $tour->datum->format('d.m.Y') }}
                · {{ $tour->benutzer?->vorname }} {{ $tour->benutzer?->nachname }}
                @if($tour->start_zeit) · {{ \Carbon\Carbon::parse($tour->start_zeit)->format('H:i') }} @endif
                @if($tour->end_zeit) – {{ \Carbon\Carbon::parse($tour->end_zeit)->format('H:i') }} @endif
            </div>
        </div>
        <span class="badge {{ $tour->status === 'abgeschlossen' ? 'badge-erfolg' : ($tour->status === 'gestartet' ? 'badge-warnung' : 'badge-grau') }}" style="font-size: 0.875rem;">
            {{ ucfirst($tour->status) }}
        </span>
    </div>

    @if(session('erfolg'))
        <div class="alert alert-erfolg" style="margin-bottom: 1rem;">{{ session('erfolg') }}</div>
    @endif

    {{-- Einsätze --}}
    <div class="karte" style="margin-bottom: 1rem; padding: 0;">

        <div style="padding: 0.875rem 1rem; border-bottom: 1px solid var(--cs-border); display: flex; align-items: center; justify-content: space-between;">
            <span class="abschnitt-label" style="margin: 0;">Einsätze ({{ $tour->einsaetze->count() }})</span>
            @php
                $abgeschlossen = $tour->einsaetze->where('status', 'abgeschlossen')->count();
            @endphp
            @if($tour->einsaetze->count())
                <span class="text-hell" style="font-size: 0.8125rem;">{{ $abgeschlossen }}/{{ $tour->einsaetze->count() }} abgeschlossen</span>
            @endif
        </div>

        @forelse($tour->einsaetze->sortBy('tour_reihenfolge') as $idx => $e)
        @php
            $rapporte    = $rapportZahlen[$e->id] ?? 0;
            $abweichung  = null;
            $istSpaet    = false;
            if ($e->checkin_zeit && $e->zeit_von) {
                $geplant    = \Carbon\Carbon::parse($e->datum->format('Y-m-d') . ' ' . $e->zeit_von);
                $abweichung = (int) $e->checkin_zeit->diffInMinutes($geplant, false);
                $istSpaet   = $abweichung > 5;
            }
            $zeileFarbe = match(true) {
                $e->status === 'abgeschlossen' && !$istSpaet => 'var(--cs-erfolg-hell, #f0fdf4)',
                $e->status === 'abgeschlossen' && $istSpaet  => 'var(--cs-warnung-hell, #fffbeb)',
                default => 'transparent',
            };
        @endphp
        <div style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--cs-border); background: {{ $zeileFarbe }};">
            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">

                {{-- Nummer --}}
                <span style="min-width: 1.5rem; font-size: 0.8rem; color: var(--cs-text-hell); padding-top: 0.125rem;">{{ $e->tour_reihenfolge ?? $idx + 1 }}.</span>

                {{-- Hauptinfo --}}
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="{{ route('einsaetze.vor-ort', $e) }}" style="font-weight: 600; font-size: 0.9375rem; color: var(--cs-text); text-decoration: none;">{{ $e->klient?->vollname() }}</a>
                        <span class="badge {{ $e->statusBadgeKlasse() }}" style="font-size: 0.7rem;">{{ $e->statusLabel() }}</span>
                        @if($rapporte > 0)
                            <a href="{{ route('rapporte.index', ['klient_id' => $e->klient_id]) }}" class="badge badge-info" style="font-size: 0.7rem; text-decoration: none;">
                                {{ $rapporte }} Rapport{{ $rapporte > 1 ? 'e' : '' }}
                            </a>
                        @endif
                    </div>

                    <div style="font-size: 0.8125rem; color: var(--cs-text-hell); margin-top: 0.2rem;">
                        {{ $e->leistungsart?->bezeichnung }}
                        @if($e->klient?->adresse)
                            · {{ $e->klient->adresse }}, {{ $e->klient->plz }} {{ $e->klient->ort }}
                        @endif
                    </div>

                    {{-- Zeiten --}}
                    <div style="display: flex; gap: 1.25rem; flex-wrap: wrap; margin-top: 0.375rem; font-size: 0.8125rem;">

                        {{-- Geplant --}}
                        @if($e->zeit_von)
                        <div>
                            <span class="text-hell">Geplant:</span>
                            <span>{{ \Carbon\Carbon::parse($e->zeit_von)->format('H:i') }}
                                @if($e->zeit_bis)–{{ \Carbon\Carbon::parse($e->zeit_bis)->format('H:i') }}@endif
                            </span>
                        </div>
                        @endif

                        {{-- Check-in --}}
                        @if($e->checkin_zeit)
                        <div>
                            <span class="text-hell">Check-in:</span>
                            <span style="color: {{ $istSpaet ? 'var(--cs-warnung)' : 'var(--cs-erfolg)' }}; font-weight: 500;">
                                {{ $e->checkin_zeit->format('H:i') }}
                            </span>
                            @if($abweichung !== null && abs($abweichung) > 1)
                                <span style="font-size: 0.75rem; color: {{ $istSpaet ? 'var(--cs-warnung)' : 'var(--cs-erfolg)' }};">
                                    ({{ $abweichung > 0 ? '+' : '' }}{{ $abweichung }} Min.)
                                </span>
                            @endif
                            <span class="text-hell" style="font-size: 0.75rem;">{{ $e->checkin_methode }}</span>
                        </div>
                        @else
                            @if($e->status !== 'abgeschlossen')
                            <div class="text-hell">Noch nicht eingecheckt</div>
                            @endif
                        @endif

                        {{-- Check-out + Dauer --}}
                        @if($e->checkout_zeit)
                        <div>
                            <span class="text-hell">Check-out:</span>
                            <span style="font-weight: 500;">{{ $e->checkout_zeit->format('H:i') }}</span>
                            <span class="text-hell" style="font-size: 0.75rem;">{{ $e->checkout_methode }}</span>
                        </div>
                        @if($e->dauerMinuten())
                        <div>
                            <span class="text-hell">Dauer:</span>
                            <span style="font-weight: 500;">{{ $e->dauerMinuten() }} Min.</span>
                        </div>
                        @endif
                        @endif

                    </div>
                </div>

                {{-- Entfernen --}}
                <form method="POST" action="{{ route('touren.einsatz.entfernen', [$tour, $e]) }}" style="flex-shrink: 0;" onsubmit="return confirm('Einsatz aus Tour entfernen?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-hell" style="background: none; border: none; cursor: pointer; font-size: 1rem; padding: 0.125rem 0.25rem;" title="Aus Tour entfernen">×</button>
                </form>

            </div>
        </div>
        @empty
        <div class="text-hell" style="padding: 2rem; text-align: center; font-size: 0.875rem;">Noch keine Einsätze zugewiesen.</div>
        @endforelse

        {{-- Offene Einsätze hinzufügen --}}
        @if($offeneEinsaetze->count())
        <div style="padding: 0.875rem 1rem;">
            <form method="POST" action="{{ route('touren.einsatz.zuweisen', $tour) }}">
                @csrf
                <div style="font-size: 0.8125rem; font-weight: 600; color: var(--cs-primaer); margin-bottom: 0.625rem;">
                    + Einsätze hinzufügen ({{ $offeneEinsaetze->count() }} verfügbar)
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.25rem; margin-bottom: 0.75rem;">
                    @foreach($offeneEinsaetze as $e)
                    <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer; padding: 0.25rem 0;">
                        <input type="checkbox" name="einsatz_ids[]" value="{{ $e->id }}" checked>
                        <span style="font-weight: 500;">{{ $e->klient?->vollname() }}</span>
                        <span class="text-hell">{{ $e->leistungsart?->bezeichnung }}</span>
                        @if($e->zeit_von)
                            <span class="text-hell" style="margin-left: auto; font-size: 0.8rem;">{{ \Carbon\Carbon::parse($e->zeit_von)->format('H:i') }}</span>
                        @endif
                    </label>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primaer" style="font-size: 0.875rem;">Zuweisen</button>
            </form>
        </div>
        @endif

    </div>

    {{-- Tour bearbeiten --}}
    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 0.875rem;">Tour bearbeiten</div>
        <form method="POST" action="{{ route('touren.update', $tour) }}">
            @csrf @method('PUT')
            <div style="display: grid; grid-template-columns: 1fr 140px 120px 120px; gap: 0.75rem; margin-bottom: 0.75rem;">
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
