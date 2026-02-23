<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <title>Einsatz läuft — {{ config('theme.app_name', 'Spitex') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--cs-hintergrund); min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem;">

<div style="width: 100%; max-width: 400px; text-align: center;">

    {{-- Laufende Uhr --}}
    <div style="background: var(--cs-primaer); color: #fff; border-radius: 1rem; padding: 2rem; margin-bottom: 1.25rem;">
        <div style="font-size: 0.875rem; opacity: 0.8; margin-bottom: 0.5rem;">Einsatz läuft seit</div>
        <div id="laufzeit" style="font-size: 3rem; font-weight: 700; letter-spacing: 0.05em; font-variant-numeric: tabular-nums;">00:00</div>
        <div style="font-size: 0.875rem; opacity: 0.8; margin-top: 0.5rem;">
            Check-in: {{ $einsatz->checkin_zeit->format('H:i') }} Uhr
        </div>
    </div>

    {{-- Klient-Info --}}
    <div class="karte" style="margin-bottom: 1.25rem; padding: 1rem;">
        <div class="text-fett">{{ $einsatz->klient->vorname }} {{ $einsatz->klient->nachname }}</div>
        <div class="text-klein text-hell">{{ $einsatz->klient->adresse }} · {{ $einsatz->klient->ort }}</div>
    </div>

    {{-- GPS Check-out --}}
    <button id="btn-checkout" class="btn btn-primaer"
        style="width: 100%; justify-content: center; padding: 1rem; font-size: 1rem; margin-bottom: 0.75rem; background-color: var(--cs-erfolg);"
        onclick="gpsCheckout()">
        ✓ GPS Check-out — Einsatz beenden
    </button>
    <form id="form-checkout" method="POST" action="{{ route('checkout.gps', $einsatz) }}" style="display:none;">
        @csrf
        <input type="hidden" name="lat" id="co-lat">
        <input type="hidden" name="lng" id="co-lng">
    </form>

    {{-- Manueller Check-out --}}
    <details style="text-align: left;">
        <summary class="text-klein text-hell" style="cursor: pointer; padding: 0.5rem 0; text-align: center;">✏️ Manuell eintragen</summary>
        <form method="POST" action="{{ route('checkout.manuell', $einsatz) }}" style="display: flex; gap: 0.5rem; align-items: flex-end; margin-top: 0.5rem;">
            @csrf
            <div style="flex: 1;">
                <label class="feld-label" style="font-size: 0.75rem;">Check-out Zeit</label>
                <input type="time" name="checkout_zeit" class="feld" value="{{ date('H:i') }}" required>
            </div>
            <button type="submit" class="btn btn-sekundaer">Eintragen</button>
        </form>
    </details>

</div>

<script>
const checkinZeit = new Date('{{ $einsatz->checkin_zeit->toIso8601String() }}');

function aktualisiereUhr() {
    const jetzt    = new Date();
    const diffSek  = Math.floor((jetzt - checkinZeit) / 1000);
    const std      = Math.floor(diffSek / 3600);
    const min      = Math.floor((diffSek % 3600) / 60);
    const sek      = diffSek % 60;
    document.getElementById('laufzeit').textContent =
        (std > 0 ? String(std).padStart(2,'0') + ':' : '') +
        String(min).padStart(2,'0') + ':' +
        String(sek).padStart(2,'0');
}
aktualisiereUhr();
setInterval(aktualisiereUhr, 1000);

function gpsCheckout() {
    const btn = document.getElementById('btn-checkout');
    btn.disabled = true;
    btn.textContent = '⏳ Position wird ermittelt…';

    if (!navigator.geolocation) {
        zeigeToast('GPS nicht verfügbar — bitte manuell eintragen.', 'warnung');
        btn.disabled = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (pos) => {
            btn.textContent = '⏳ Wird übermittelt…';
            const form   = document.getElementById('form-checkout');
            const body   = new URLSearchParams({
                _token: document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                lat:    pos.coords.latitude,
                lng:    pos.coords.longitude,
            });

            try {
                const resp = await fetch(form.action, {
                    method:      'POST',
                    headers:     { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body:        body.toString(),
                    credentials: 'include',
                });
                if (resp.redirected) {
                    window.location.href = resp.url;
                } else if (resp.ok) {
                    window.location.reload();
                } else {
                    throw new Error('Serverfehler');
                }
            } catch (err) {
                // SW übernimmt Offline-Queue — Toast kommt vom SW via postMessage
                if (!navigator.onLine) {
                    zeigeToast('📶 Offline — Check-out wird gesendet wenn du wieder online bist.', 'warnung');
                } else {
                    zeigeToast('Fehler beim Übermitteln. Bitte manuell eintragen.', 'fehler');
                }
                btn.disabled = false;
                btn.textContent = '✓ GPS Check-out — Einsatz beenden';
            }
        },
        (err) => {
            zeigeToast('GPS nicht verfügbar — bitte manuell eintragen.', 'warnung');
            btn.disabled = false;
            btn.textContent = '✓ GPS Check-out — Einsatz beenden';
        },
        { enableHighAccuracy: true, timeout: 15000 }
    );
}

// SW registrieren + Toast-Funktion (diese Seite hat kein App-Layout)
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(() => {});
    navigator.serviceWorker.addEventListener('message', event => {
        if (event.data?.type === 'CHECKIN_SYNCED') zeigeToast('✅ Check-out erfolgreich übermittelt.', 'erfolg');
    });
}
window.addEventListener('online',  () => zeigeToast('✅ Wieder online.', 'erfolg'));
window.addEventListener('offline', () => zeigeToast('📡 Keine Verbindung.', 'warnung'));

function zeigeToast(text, typ) {
    const farben = {
        erfolg:  { bg: '#dcfce7', text: '#166534', border: '#86efac' },
        warnung: { bg: '#fef9c3', text: '#854d0e', border: '#fde047' },
        fehler:  { bg: '#fee2e2', text: '#991b1b', border: '#fca5a5' },
    };
    const f = farben[typ] || farben.warnung;
    const el = document.createElement('div');
    el.textContent = text;
    Object.assign(el.style, {
        position: 'fixed', bottom: '1.25rem', left: '50%', transform: 'translateX(-50%)',
        background: f.bg, color: f.text, border: `1px solid ${f.border}`,
        borderRadius: '0.5rem', padding: '0.75rem 1.25rem', fontSize: '0.9375rem',
        fontWeight: '500', fontFamily: 'system-ui,sans-serif', zIndex: '9999',
        boxShadow: '0 4px 16px rgba(0,0,0,.12)', maxWidth: '90vw', textAlign: 'center',
        transition: 'opacity 0.4s',
    });
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 4500);
}
</script>
</body>
</html>
