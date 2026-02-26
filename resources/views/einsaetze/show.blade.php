<x-layouts.app :titel="'Einsatz — ' . $einsatz->klient->nachname . ' ' . $einsatz->klient->vorname">
<div style="max-width: 600px;">
    <div class="seiten-kopf" style="margin-bottom: 1.25rem;">
        <a href="{{ route('klienten.show', $einsatz->klient) }}" class="link-gedaempt" style="font-size: 0.875rem;">
            ← {{ $einsatz->klient->nachname }} {{ $einsatz->klient->vorname }}
        </a>
        <div style="display: flex; gap: 0.5rem;">
            @if($einsatz->status === 'geplant' && !$einsatz->tour_id)
            <form method="POST" action="{{ route('einsaetze.destroy', $einsatz) }}"
                onsubmit="return confirm('Einsatz wirklich löschen?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sekundaer" style="font-size: 0.8125rem; color: var(--cs-fehler); border-color: var(--cs-fehler);">
                    Löschen
                </button>
            </form>
            @endif
            @if($einsatz->status !== 'storniert')
            <a href="{{ route('einsaetze.edit', $einsatz) }}" class="btn btn-sekundaer" style="font-size: 0.8125rem;">
                Bearbeiten
            </a>
            @endif
        </div>
    </div>

    {{-- Einsatz-Info --}}
    <div class="karte" style="margin-bottom: 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
            <div>
                <div style="font-size: 1rem; font-weight: 600;">
                    <a href="{{ route('klienten.show', $einsatz->klient) }}"
                        style="color: var(--cs-text); text-decoration: none;">
                        {{ $einsatz->klient->nachname }} {{ $einsatz->klient->vorname }}
                    </a>
                </div>
                <div class="text-klein text-hell" style="margin-top: 0.25rem;">
                    @if($einsatz->datum_bis)
                        {{ $einsatz->datum->format('d.m.Y') }} – {{ $einsatz->datum_bis->format('d.m.Y') }}
                        <span style="color: var(--cs-primaer);">({{ $einsatz->anzahlTage() }} Tage)</span>
                    @else
                        {{ $einsatz->datum->format('d.m.Y') }}
                        @if($einsatz->zeit_von)
                            &nbsp;·&nbsp; {{ substr($einsatz->zeit_von, 0, 5) }}
                            @if($einsatz->zeit_bis) – {{ substr($einsatz->zeit_bis, 0, 5) }} @endif
                        @endif
                    @endif
                </div>
            </div>
            <span class="badge {{ $einsatz->statusBadgeKlasse() }}">{{ $einsatz->statusLabel() }}</span>
        </div>

        <div class="detail-raster">
            <div>
                <div class="detail-label">Leistungsart</div>
                <div class="detail-wert">{{ $einsatz->leistungsart?->bezeichnung ?? '—' }}</div>
            </div>
            <div>
                <div class="detail-label">Kanton / Region</div>
                <div class="detail-wert">
                    @if($einsatz->region)
                        {{ $einsatz->region->kuerzel }} — {{ $einsatz->region->bezeichnung }}
                    @else
                        —
                    @endif
                </div>
            </div>
            <div>
                <div class="detail-label">Mitarbeiter</div>
                <div class="detail-wert">{{ $einsatz->benutzer?->name ?? '—' }}</div>
            </div>
            @if($einsatz->dauerMinuten())
            <div>
                <div class="detail-label">Effektive Dauer</div>
                <div class="detail-wert" style="color: var(--cs-primaer);">{{ $einsatz->dauerMinuten() }} Minuten</div>
            </div>
            @endif
        </div>

        @if($einsatz->bemerkung)
            <div class="abschnitt-trenn text-klein text-hell" style="margin-top: 0.875rem; padding-top: 0.875rem;">
                {{ $einsatz->bemerkung }}
            </div>
        @endif
    </div>

    {{-- Check-in/out Status --}}
    @if($einsatz->status !== 'storniert')
    <div class="karte" style="margin-bottom: 1rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Zeiterfassung</div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
            <div style="padding: 0.875rem; background: {{ $einsatz->isEingecheckt() ? '#dcfce7' : 'var(--cs-hintergrund)' }}; border-radius: var(--cs-radius); border: 1px solid {{ $einsatz->isEingecheckt() ? '#86efac' : 'var(--cs-border)' }};">
                <div class="detail-label" style="margin-bottom: 0.25rem;">Check-in</div>
                @if($einsatz->isEingecheckt())
                    <div style="font-size: 1.125rem; font-weight: 700; color: #166534;">{{ $einsatz->checkin_zeit->format('H:i') }}</div>
                    <div style="font-size: 0.75rem; color: #166534; margin-top: 0.125rem;">
                        {{ ['qr' => 'QR-Code', 'gps' => 'GPS', 'manuell' => 'Manuell'][$einsatz->checkin_methode] ?? '' }}
                        @if($einsatz->checkin_distanz_meter !== null)
                            · {{ $einsatz->checkin_distanz_meter }}m
                        @endif
                    </div>
                @else
                    <div class="text-klein text-hell">—</div>
                @endif
            </div>
            <div style="padding: 0.875rem; background: {{ $einsatz->isAusgecheckt() ? '#dcfce7' : 'var(--cs-hintergrund)' }}; border-radius: var(--cs-radius); border: 1px solid {{ $einsatz->isAusgecheckt() ? '#86efac' : 'var(--cs-border)' }};">
                <div class="detail-label" style="margin-bottom: 0.25rem;">Check-out</div>
                @if($einsatz->isAusgecheckt())
                    <div style="font-size: 1.125rem; font-weight: 700; color: #166534;">{{ $einsatz->checkout_zeit->format('H:i') }}</div>
                    <div style="font-size: 0.75rem; color: #166534; margin-top: 0.125rem;">
                        {{ ['qr' => 'QR-Code', 'gps' => 'GPS', 'manuell' => 'Manuell'][$einsatz->checkout_methode] ?? '' }}
                        @if($einsatz->dauerMinuten()) · {{ $einsatz->dauerMinuten() }} Min. @endif
                    </div>
                @else
                    <div class="text-klein text-hell">—</div>
                @endif
            </div>
        </div>

        {{-- Check-in Aktionen --}}
        @if(!$einsatz->isEingecheckt())
            <button id="btn-gps-checkin" class="btn btn-primaer" style="width: 100%; justify-content: center; margin-bottom: 0.5rem;" onclick="gpsCheckin()">
                GPS Check-in
            </button>
            <form id="form-gps-checkin" method="POST" action="{{ route('checkin.gps', $einsatz) }}" style="display:none;">
                @csrf
                <input type="hidden" name="lat" id="gps-lat">
                <input type="hidden" name="lng" id="gps-lng">
            </form>

            <details style="margin-top: 0.5rem;">
                <summary class="text-klein text-hell" style="cursor: pointer; padding: 0.375rem 0;">Manuell eintragen</summary>
                <form method="POST" action="{{ route('checkin.manuell', $einsatz) }}" style="display: flex; gap: 0.5rem; align-items: flex-end; margin-top: 0.5rem;">
                    @csrf
                    <div style="flex: 1;">
                        <label class="feld-label text-mini">Check-in Zeit</label>
                        <input type="time" name="checkin_zeit" class="feld" value="{{ date('H:i') }}" required>
                    </div>
                    <button type="submit" class="btn btn-sekundaer">Eintragen</button>
                </form>
            </details>

        @elseif(!$einsatz->isAusgecheckt())
            <a href="{{ route('checkin.aktiv', $einsatz) }}" class="btn btn-primaer" style="width: 100%; justify-content: center; margin-bottom: 0.5rem;">
                Einsatz läuft — zum Check-out
            </a>
        @endif
    </div>
    @endif

    {{-- Rapport nach Checkout --}}
    @if($einsatz->isAusgecheckt())
    <div class="info-box" style="margin-top: 0.75rem; text-align: center;">
        <div style="font-size: 0.875rem; color: #1d4ed8; margin-bottom: 0.625rem; font-weight: 600;">
            Einsatz abgeschlossen — Rapport erfassen?
        </div>
        <a href="{{ route('rapporte.create', ['einsatz_id' => $einsatz->id]) }}"
            class="btn btn-primaer" style="font-size: 0.9375rem; padding: 0.625rem 1.25rem;">
            ✏ Rapport jetzt schreiben
        </a>
    </div>
    @endif

    {{-- QR-Code Link --}}
    <div class="text-mitte" style="margin-top: 0.75rem;">
        <a href="{{ route('klienten.qr', $einsatz->klient) }}" class="link-gedaempt" style="font-size: 0.8125rem;">
            QR-Code des Klienten anzeigen
        </a>
    </div>
</div>

@push('scripts')
<script>
function gpsCheckin() {
    const btn = document.getElementById('btn-gps-checkin');
    btn.disabled = true;
    btn.textContent = 'Position wird ermittelt…';

    if (!navigator.geolocation) {
        alert('GPS wird von diesem Browser nicht unterstützt.');
        btn.disabled = false;
        btn.textContent = 'GPS Check-in';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            document.getElementById('gps-lat').value = pos.coords.latitude;
            document.getElementById('gps-lng').value = pos.coords.longitude;
            document.getElementById('form-gps-checkin').submit();
        },
        (err) => {
            alert('GPS nicht verfügbar: ' + err.message + '\nBitte manuell eintragen.');
            btn.disabled = false;
            btn.textContent = 'GPS Check-in';
        },
        { enableHighAccuracy: true, timeout: 15000 }
    );
}
</script>
@endpush
</x-layouts.app>
