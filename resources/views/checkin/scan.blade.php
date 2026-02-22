<x-layouts.app :titel="'Check-in — ' . $klient->vorname . ' ' . $klient->nachname">
<div style="max-width: 480px; margin: 0 auto;">

    <div class="karte" style="text-align: center; padding: 1.5rem; margin-bottom: 1rem;">
        <div style="width: 3.5rem; height: 3.5rem; border-radius: 50%; background-color: var(--cs-primaer-hell); color: var(--cs-primaer); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; margin: 0 auto 0.75rem;">
            {{ strtoupper(substr($klient->vorname, 0, 1)) }}{{ strtoupper(substr($klient->nachname, 0, 1)) }}
        </div>
        <div style="font-size: 1.125rem; font-weight: 700;">{{ $klient->vorname }} {{ $klient->nachname }}</div>
        <div class="text-klein text-hell">{{ $klient->adresse }} · {{ $klient->ort }}</div>
        <div style="margin-top: 0.5rem;">
            <span class="badge badge-info">📲 QR-Code gescannt</span>
        </div>
    </div>

    @if($einsaetze->isEmpty())
        <div class="karte text-mitte text-hell" style="padding: 2rem;">
            Keine Einsätze für heute geplant.<br>
            <a href="{{ route('einsaetze.create') }}" class="btn btn-primaer" style="margin-top: 1rem;">Einsatz anlegen</a>
        </div>
    @else
        <div class="abschnitt-label" style="margin-bottom: 0.5rem; padding: 0 0.25rem;">
            Einsätze heute — {{ now()->format('d.m.Y') }}
        </div>
        @foreach($einsaetze as $einsatz)
        <div class="karte" style="margin-bottom: 0.75rem; padding: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <div>
                    <div class="text-mittel" style="font-size: 0.9375rem;">{{ $einsatz->benutzer->vorname ?? '—' }} {{ $einsatz->benutzer->nachname ?? '' }}</div>
                    <div class="text-klein text-hell">
                        {{ $einsatz->zeit_von ? substr($einsatz->zeit_von, 0, 5) : 'Keine Zeit' }}
                        @if($einsatz->zeit_bis) – {{ substr($einsatz->zeit_bis, 0, 5) }} @endif
                    </div>
                </div>
                @if($einsatz->isAusgecheckt())
                    <span class="badge badge-erfolg">✓ Fertig</span>
                @elseif($einsatz->isEingecheckt())
                    <span class="badge badge-warnung">Läuft</span>
                @endif
            </div>

            @if(!$einsatz->isEingecheckt())
                <form method="POST" action="{{ route('checkin.qr', $token) }}">
                    @csrf
                    <input type="hidden" name="einsatz_id" value="{{ $einsatz->id }}">
                    <button type="submit" class="btn btn-primaer" style="width: 100%; justify-content: center; padding: 0.75rem;">
                        ✓ Jetzt einchecken
                    </button>
                </form>
            @elseif(!$einsatz->isAusgecheckt())
                <a href="{{ route('checkin.aktiv', $einsatz) }}" class="btn btn-primaer" style="display: flex; justify-content: center; padding: 0.75rem; text-decoration: none;">
                    ● Zum Check-out
                </a>
            @else
                <div class="text-mitte text-klein text-hell" style="padding: 0.5rem;">
                    Einsatz abgeschlossen um {{ $einsatz->checkout_zeit->format('H:i') }}
                </div>
            @endif
        </div>
        @endforeach
    @endif
</div>
</x-layouts.app>
