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
    @if(session('warnung'))
        <div class="alert alert-warnung" style="margin-bottom: 1rem;">⚠ {{ session('warnung') }}</div>
    @endif
    @if($konflikteIds->isNotEmpty())
        <div class="alert alert-warnung" style="margin-bottom: 1rem;">
            ⚠ <strong>Zeitüberschneidung:</strong> {{ $konflikteIds->count() }} Einsatz/Einsätze haben überlappende geplante Zeiten. Bitte Zeiten prüfen.
        </div>
    @endif

    {{-- Einsätze --}}
    <div class="karte" id="einsatz-karte" style="margin-bottom: 1rem; padding: 0;">

        <div style="padding: 0.875rem 1rem; border-bottom: 1px solid var(--cs-border); display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
            <span class="abschnitt-label" style="margin: 0;">Einsätze ({{ $tour->einsaetze->count() }})</span>
            @php
                $abgeschlossen   = $tour->einsaetze->where('status', 'abgeschlossen')->count();
                $mitKoordinaten  = $tour->einsaetze->filter(fn($e) => $e->klient?->klient_lat)->count();
            @endphp
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                @if($tour->einsaetze->count())
                    <span class="text-hell" style="font-size: 0.8125rem;">{{ $abgeschlossen }}/{{ $tour->einsaetze->count() }} abgeschlossen</span>
                @endif
                @if($mitKoordinaten >= 2 && auth()->user()->rolle === 'admin')
                    <form method="POST" action="{{ route('touren.route.optimieren', $tour) }}" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn btn-sekundaer" style="font-size: 0.75rem; padding: 0.25rem 0.625rem;" onclick="return confirm('Route nach kürzester Strecke optimieren?')">
                            🗺 Route optimieren
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div id="einsatz-liste">
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
        <div
            data-einsatz-id="{{ $e->id }}"
            data-lat="{{ $e->klient?->klient_lat ?? '' }}"
            data-lng="{{ $e->klient?->klient_lng ?? '' }}"
            data-zeit-von="{{ $e->zeit_von ? substr($e->zeit_von, 0, 5) : '' }}"
            data-zeit-bis="{{ $e->zeit_bis ? substr($e->zeit_bis, 0, 5) : '' }}"
            data-name="{{ $e->klient?->vorname }} {{ $e->klient?->nachname }}"
            data-adresse="{{ $e->klient?->adresse }}, {{ $e->klient?->ort }}"
            data-status="{{ $e->status }}"
            @if(auth()->user()->rolle === 'admin') draggable="true" @endif
            style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--cs-border); background: {{ $zeileFarbe }}; {{ auth()->user()->rolle === 'admin' ? 'cursor:grab;' : '' }}">
            <div style="display: flex; align-items: flex-start; gap: 0.75rem;">

                {{-- Drag handle (Admin) + Nummer --}}
                @if(auth()->user()->rolle === 'admin')
                <span title="Ziehen um Reihenfolge zu ändern" style="color: var(--cs-text-hell); padding-top: 0.125rem; font-size: 1rem; line-height: 1; cursor: grab; user-select: none;">⠿</span>
                @endif
                <span class="reihenfolge-nr" style="min-width: 1.5rem; font-size: 0.8rem; color: var(--cs-text-hell); padding-top: 0.125rem;">{{ $e->tour_reihenfolge ?? $idx + 1 }}.</span>

                {{-- Hauptinfo --}}
                <div style="flex: 1; min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="{{ route('einsaetze.vor-ort', $e) }}" style="font-weight: 600; font-size: 0.9375rem; color: var(--cs-text); text-decoration: none;">{{ $e->klient?->vollname() }}</a>
                        <span class="badge {{ $e->statusBadgeKlasse() }}" style="font-size: 0.7rem;">{{ $e->statusLabel() }}</span>
                        @if($konflikteIds->contains($e->id))
                            <span title="Zeitüberschneidung mit einem anderen Einsatz" style="color: var(--cs-warnung); font-size: 0.85rem;">⚠</span>
                        @endif
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
        </div>{{-- #einsatz-liste --}}

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

    {{-- Karte --}}
    @php
        $kartenEinsaetze = $tour->einsaetze
            ->sortBy('tour_reihenfolge')
            ->filter(fn($e) => $e->klient?->klient_lat && $e->klient?->klient_lng)
            ->values();
    @endphp
    @if($kartenEinsaetze->count() >= 1)
    <div class="karte" style="margin-top: 1rem; padding: 0; overflow: hidden;">
        <div style="padding: 0.875rem 1rem; border-bottom: 1px solid var(--cs-border);">
            <span class="abschnitt-label" style="margin: 0;">Route ({{ $kartenEinsaetze->count() }} Stops)</span>
        </div>
        <div id="tourenkarte" style="height: 380px; width: 100%;"></div>
    </div>

    @push('scripts')
    @vite('resources/js/tourenkarte.js')
    @php
        $kartenpunkte = $kartenEinsaetze->map(function($e) {
            return [
                'lat'         => (float) $e->klient->klient_lat,
                'lng'         => (float) $e->klient->klient_lng,
                'klient_name' => $e->klient->vorname . ' ' . $e->klient->nachname,
                'adresse'     => $e->klient->adresse . ', ' . $e->klient->ort,
                'zeit_von'    => $e->zeit_von ? substr($e->zeit_von, 0, 5) : null,
                'zeit_bis'    => $e->zeit_bis ? substr($e->zeit_bis, 0, 5) : null,
                'status'      => $e->status,
            ];
        })->values();
    @endphp
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const punkte = @json($kartenpunkte);
            window.TourenkarteInit(punkte);
        });
    </script>
    @endpush
    @endif

    @if(auth()->user()->rolle === 'admin')
    @push('scripts')
    <script>
    (function() {
        const tourId   = {{ $tour->id }};
        const csrf     = '{{ csrf_token() }}';
        const liste    = document.getElementById('einsatz-liste');
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
            liste.querySelectorAll('[data-einsatz-id]').forEach(r => r.style.outline = '');
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
            speichern();
        });

        function speichern() {
            const reihenfolge = [...liste.querySelectorAll('[data-einsatz-id]')].map(r => r.dataset.einsatzId);

            // Nummern aktualisieren
            liste.querySelectorAll('[data-einsatz-id]').forEach((row, i) => {
                const nr = row.querySelector('.reihenfolge-nr');
                if (nr) nr.textContent = (i + 1) + '.';
            });

            fetch('/touren/' + tourId + '/reihenfolge', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify({ reihenfolge }),
            });

            aktualisiereWarnungen();
        }

        function aktualisiereWarnungen() {
            liste.querySelectorAll('.fahrzeitwarnung').forEach(el => el.remove());

            const rows  = [...liste.querySelectorAll('[data-einsatz-id]')];
            const mapPunkte = rows
                .filter(r => r.dataset.lat && r.dataset.lng)
                .map(r => ({
                    lat:        parseFloat(r.dataset.lat),
                    lng:        parseFloat(r.dataset.lng),
                    klient_name: r.dataset.name  || '',
                    adresse:    r.dataset.adresse || '',
                    zeit_von:   r.dataset.zeitVon || null,
                    zeit_bis:   r.dataset.zeitBis || null,
                    status:     r.dataset.status  || 'geplant',
                }));

            rows.forEach(function(row, i) {
                if (i >= rows.length - 1) return;
                const next = rows[i + 1];
                const pBis = row.dataset.zeitBis;
                const nVon = next.dataset.zeitVon;
                const pLat = row.dataset.lat  ? parseFloat(row.dataset.lat)  : null;
                const pLng = row.dataset.lng  ? parseFloat(row.dataset.lng)  : null;
                const nLat = next.dataset.lat ? parseFloat(next.dataset.lat) : null;
                const nLng = next.dataset.lng ? parseFloat(next.dataset.lng) : null;

                if (!pBis || !nVon || !pLat || !nLat) return;

                const [h1, m1] = pBis.split(':').map(Number);
                const [h2, m2] = nVon.split(':').map(Number);
                const verfuegbar = (h2 * 60 + m2) - (h1 * 60 + m1);
                const distM      = haversineM(pLat, pLng, nLat, nLng);
                const fahrtMin   = Math.ceil(distM / 1000 / 25 * 60) + 2;

                if (verfuegbar < fahrtMin) {
                    const warn = document.createElement('div');
                    warn.className = 'fahrzeitwarnung';
                    warn.style.cssText = 'background:#fef2f2;color:#dc2626;font-size:0.75rem;padding:0.2rem 1rem 0.2rem 3.5rem;border-bottom:1px solid #fca5a5;';
                    const km = (distM / 1000).toFixed(1);
                    warn.textContent = `⚠ Fahrzeit ~${fahrtMin - 2} Min. (${km} km) — nur ${verfuegbar} Min. bis nächsten Einsatz`;
                    row.insertAdjacentElement('afterend', warn);
                }
            });

            if (window.TourenkarteUpdate && window.berechneWarnungen) {
                window.TourenkarteUpdate(mapPunkte, window.berechneWarnungen(mapPunkte));
            }
        }

        function haversineM(lat1, lng1, lat2, lng2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLng = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        }

        // Warnungen beim Laden berechnen (nach Karteninitialisierung)
        setTimeout(aktualisiereWarnungen, 500);
    })();
    </script>
    @endpush
    @endif

</div>
</x-layouts.app>
