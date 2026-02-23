<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('theme.app_name', 'Spitex') }}">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-192.svg">
    <title>{{ $titel ?? 'Dashboard' }} — {{ config('theme.app_name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if(config('theme.farbe_primaer'))
    <style>
        :root {
            --cs-primaer: {{ config('theme.farbe_primaer') }};
            --cs-primaer-dunkel: {{ config('theme.farbe_primaer_dunkel') }};
            --cs-primaer-hell: {{ config('theme.farbe_primaer_hell') }};
        }
    </style>
    @endif

    @stack('styles')
</head>
<body>

@php $layout = config('theme.layout', 'sidebar'); @endphp

@if($layout === 'topnav')
    @include('layouts.partials.topnav')
@else
    @include('layouts.partials.sidebar')
@endif

@stack('scripts')

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('offen');
}

function toggleTopNav() {
    const el = document.getElementById('topnavMobile');
    if (el) el.classList.toggle('offen');
}

// ── Service Worker registrieren ──────────────────────────────────────────────
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js').catch(() => {});

    navigator.serviceWorker.addEventListener('message', event => {
        if (event.data?.type === 'CHECKIN_QUEUED') {
            zeigeToast('📶 Offline — Check-in wird gesendet wenn du wieder online bist.', 'warnung');
        } else if (event.data?.type === 'CHECKIN_SYNCED') {
            zeigeToast('✅ Check-in erfolgreich übermittelt.', 'erfolg');
        }
    });
}

window.addEventListener('online',  () => zeigeToast('✅ Wieder online.', 'erfolg'));
window.addEventListener('offline', () => zeigeToast('📡 Keine Verbindung — gespeicherte Seiten verfügbar.', 'warnung'));

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
        position:     'fixed',
        bottom:       '1.25rem',
        left:         '50%',
        transform:    'translateX(-50%)',
        background:   f.bg,
        color:        f.text,
        border:       `1px solid ${f.border}`,
        borderRadius: '0.5rem',
        padding:      '0.75rem 1.25rem',
        fontSize:     '0.9375rem',
        fontWeight:   '500',
        fontFamily:   'system-ui,sans-serif',
        zIndex:       '9999',
        boxShadow:    '0 4px 16px rgba(0,0,0,0.12)',
        maxWidth:     '90vw',
        textAlign:    'center',
        transition:   'opacity 0.4s',
    });
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 400); }, 4000);
}
</script>

</body>
</html>
