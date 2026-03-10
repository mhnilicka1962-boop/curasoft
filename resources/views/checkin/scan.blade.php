<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Check-in — {{ $klient->vorname }} {{ $klient->nachname }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            background: #f3f4f6;
            min-height: 100vh;
            padding: 1rem;
            font-family: var(--cs-schrift, sans-serif);
            margin: 0;
        }
        .scan-wrap { max-width: 480px; margin: 0 auto; }
        .header-karte {
            background: var(--cs-primaer, #2563eb);
            color: white;
            border-radius: 1rem;
            padding: 1.25rem 1.25rem 1rem;
            margin-bottom: 1rem;
        }
        .avatar {
            width: 3rem; height: 3rem;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.125rem; font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .header-name { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.125rem; }
        .header-sub  { font-size: 0.875rem; opacity: 0.8; }
        .checkin-datum { font-size: 0.8125rem; opacity: 0.7; margin-top: 0.5rem; }
        .einsatz-karte {
            background: white;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .einsatz-kopf {
            display: flex; justify-content: space-between; align-items: flex-start;
            margin-bottom: 0.875rem;
        }
        .einsatz-name { font-weight: 600; font-size: 0.9375rem; color: #1f2937; }
        .einsatz-zeit { font-size: 0.8125rem; color: #6b7280; margin-top: 0.125rem; }
        .btn-checkin {
            width: 100%; display: flex; justify-content: center; align-items: center;
            padding: 0.875rem; font-size: 1rem; font-weight: 600;
            background: var(--cs-primaer, #2563eb); color: white;
            border: none; border-radius: 0.625rem; cursor: pointer;
            text-decoration: none;
        }
        .btn-checkin:hover { opacity: 0.9; }
        .btn-checkout {
            width: 100%; display: flex; justify-content: center; align-items: center;
            padding: 0.875rem; font-size: 1rem; font-weight: 600;
            background: #d97706; color: white;
            border-radius: 0.625rem; text-decoration: none;
        }
        .abgemeldet-text { text-align: center; font-size: 0.875rem; color: #6b7280; padding: 0.5rem 0; }
        .keine-einsaetze {
            background: white; border-radius: 0.75rem; padding: 2rem 1.5rem;
            text-align: center; color: #6b7280;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .erfolg-banner {
            background: #d1fae5; color: #065f46; border-radius: 0.625rem;
            padding: 0.75rem 1rem; margin-bottom: 1rem; font-size: 0.9375rem;
        }
    </style>
</head>
<body>
<div class="scan-wrap">

    @if(session('erfolg'))
    <div class="erfolg-banner">✓ {{ session('erfolg') }}</div>
    @endif

    {{-- Header --}}
    <div class="header-karte">
        <div class="avatar">
            {{ strtoupper(substr($klient->vorname, 0, 1)) }}{{ strtoupper(substr($klient->nachname, 0, 1)) }}
        </div>
        <div class="header-name">{{ $klient->vorname }} {{ $klient->nachname }}</div>
        <div class="header-sub">{{ $klient->adresse }}, {{ $klient->plz }} {{ $klient->ort }}</div>
        <div class="checkin-datum">📲 Check-in · {{ now()->format('d.m.Y') }}</div>
    </div>

    {{-- Einsätze heute --}}
    @if($einsaetze->isEmpty())
        <div class="keine-einsaetze">
            <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">📋</div>
            <div style="font-weight: 600; margin-bottom: 0.25rem;">Keine Einsätze heute</div>
            <div style="font-size: 0.875rem;">Für {{ $klient->vorname }} {{ $klient->nachname }} sind heute keine Einsätze geplant.</div>
        </div>
    @else
        @foreach($einsaetze as $einsatz)
        <div class="einsatz-karte">
            <div class="einsatz-kopf">
                <div>
                    <div class="einsatz-name">{{ $einsatz->benutzer->vorname ?? '—' }} {{ $einsatz->benutzer->nachname ?? '' }}</div>
                    <div class="einsatz-zeit">
                        {{ $einsatz->zeit_von ? substr($einsatz->zeit_von, 0, 5) : 'Keine Zeit' }}
                        @if($einsatz->zeit_bis) – {{ substr($einsatz->zeit_bis, 0, 5) }} @endif
                    </div>
                </div>
                @if($einsatz->isAusgecheckt())
                    <span class="badge badge-erfolg">✓ Fertig</span>
                @elseif($einsatz->isEingecheckt())
                    <span class="badge badge-warnung">● Läuft</span>
                @else
                    <span class="badge badge-grau">Offen</span>
                @endif
            </div>

            @if(!$einsatz->isEingecheckt())
                <form method="POST" action="{{ route('checkin.qr', $token) }}">
                    @csrf
                    <input type="hidden" name="einsatz_id" value="{{ $einsatz->id }}">
                    <button type="submit" class="btn-checkin">
                        ✓ Jetzt einchecken
                    </button>
                </form>
            @elseif(!$einsatz->isAusgecheckt())
                <a href="{{ route('checkin.aktiv', $einsatz) }}" class="btn-checkout">
                    ● Zum Check-out
                </a>
            @else
                <div class="abgemeldet-text">
                    ✓ Abgeschlossen um {{ $einsatz->checkout_zeit?->format('H:i') ?? '—' }}
                </div>
            @endif
        </div>
        @endforeach
    @endif

</div>
</body>
</html>
