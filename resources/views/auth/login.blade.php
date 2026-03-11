<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ config('theme.app_name', 'Spitex') }}">
    <meta name="theme-color" content="#2563eb">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-192.svg">
    <title>Anmelden — {{ config('theme.app_name', 'Spitex') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--cs-hintergrund); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;">

{{-- PWA Install-Banner (nur Android/Chrome) --}}
<div id="install-banner" style="display: none; position: fixed; top: 0; left: 0; right: 0; background: var(--cs-primaer); color: #fff; padding: 0.75rem 1rem; display: none; align-items: center; justify-content: space-between; gap: 1rem; z-index: 100; font-size: 0.875rem;">
    <span>📱 Spitex als App installieren</span>
    <div style="display: flex; gap: 0.5rem;">
        <button id="install-btn" style="background: #fff; color: var(--cs-primaer); border: none; border-radius: 6px; padding: 0.25rem 0.75rem; font-size: 0.8125rem; font-weight: 600; cursor: pointer;">Installieren</button>
        <button onclick="document.getElementById('install-banner').style.display='none'" style="background: none; border: none; color: #fff; font-size: 1.25rem; cursor: pointer; padding: 0 0.25rem;">×</button>
    </div>
</div>

<div style="width: 100%; max-width: 400px;">

    {{-- Logo / Titel --}}
    <div style="text-align: center; margin-bottom: 2rem;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; background-color: var(--cs-primaer); border-radius: 0.75rem; margin-bottom: 1rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--cs-text); margin: 0 0 0.25rem;">{{ config('theme.app_name', 'Spitex') }}</h1>
        <p style="color: var(--cs-text-hell); font-size: 0.875rem; margin: 0;">Pflegemanagement-Software</p>
    </div>

    {{-- Login-Karte --}}
    <div class="karte" style="padding: 2rem;">
        <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--cs-text); margin: 0 0 1.5rem;">Anmelden</h2>

        @if ($errors->any())
            <div class="alert alert-fehler" style="margin-bottom: 1.25rem;">{{ $errors->first() }}</div>
        @endif

        @if (session('status'))
            <div class="alert alert-erfolg" style="margin-bottom: 1.25rem;">{{ session('status') }}</div>
        @endif

        {{-- Tabs --}}
        <div style="display: flex; gap: 0; margin-bottom: 1.5rem; border-bottom: 2px solid var(--cs-border);">
            <button type="button" id="tab-magic" onclick="zeigeTab('magic')"
                style="padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; color: var(--cs-text-hell);">
                Link per E-Mail
            </button>
            <button type="button" id="tab-faceid" onclick="zeigeTab('faceid')"
                style="padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; color: var(--cs-text-hell); display: none;">
                Face ID
            </button>
            <button type="button" id="tab-passwort" onclick="zeigeTab('passwort')"
                style="padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; color: var(--cs-text-hell);">
                Passwort
            </button>
        </div>

        {{-- Passwort-Form --}}
        <form method="POST" action="{{ route('login') }}" id="form-passwort" style="display: none;">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="email">E-Mail-Adresse</label>
                <input type="email" id="email" name="email" class="feld"
                    value="{{ old('email') }}" required autofocus autocomplete="email"
                    placeholder="name@beispiel.ch">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label class="feld-label" for="password">Passwort</label>
                <input type="password" id="password" name="password" class="feld"
                    required autocomplete="current-password" placeholder="••••••••">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                    <input type="checkbox" name="remember" style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                    Angemeldet bleiben
                </label>
            </div>
            <button type="submit" class="btn btn-primaer" style="width: 100%; justify-content: center; padding: 0.625rem 1rem; font-size: 0.9375rem;">
                Anmelden
            </button>
        </form>

        {{-- Magic Link --}}
        <form method="POST" action="{{ route('login.magic') }}" id="form-magic" style="display: none;">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="email-magic">E-Mail-Adresse</label>
                <input type="email" id="email-magic" name="email" class="feld" required
                    placeholder="name@beispiel.ch" autocomplete="email">
            </div>
            <button type="submit" class="btn btn-primaer" style="width: 100%; justify-content: center; padding: 0.625rem 1rem; font-size: 0.9375rem;">
                Login-Link senden
            </button>
            <p style="margin-top: 1rem; font-size: 0.8125rem; color: var(--cs-text-hell);">
                Du erhältst einen Link per E-Mail. Klicke darauf — fertig. Kein Passwort nötig.
            </p>
        </form>

        {{-- Face ID / Passkey --}}
        <div id="form-faceid" style="display: none; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🔐</div>
            <p style="font-size: 0.9375rem; color: var(--cs-text); margin-bottom: 1.5rem;">
                Mit Face ID oder Fingerabdruck anmelden
            </p>
            <button id="passkey-btn" onclick="startPasskeyLogin()"
                class="btn btn-primaer" style="width: 100%; justify-content: center; padding: 0.75rem 1rem; font-size: 1rem;">
                Face ID / Fingerabdruck
            </button>
            <p id="passkey-fehler" style="margin-top: 1rem; font-size: 0.8125rem; color: var(--cs-fehler); display: none;"></p>
            <p style="margin-top: 1.25rem; font-size: 0.8125rem; color: var(--cs-text-hell);">
                Noch kein Passkey? <a href="#" onclick="zeigeTab('passwort'); return false;" class="link-primaer">Passwort verwenden</a>
                und danach unter <strong>Profil</strong> registrieren.
            </p>
        </div>
    </div>

    <p style="text-align: center; margin-top: 1.5rem; font-size: 0.75rem; color: var(--cs-text-hell);">
        &copy; {{ date('Y') }} {{ config('theme.app_name', 'Spitex') }}
    </p>
</div>

<script>
// ── Tab-Logik ──────────────────────────────────────────────────────
function zeigeTab(tab) {
    ['passwort', 'magic', 'faceid'].forEach(t => {
        const form = document.getElementById('form-' + t);
        const btn  = document.getElementById('tab-' + t);
        if (form) form.style.display = 'none';
        if (btn)  { btn.style.borderBottomColor = 'transparent'; btn.style.color = 'var(--cs-text-hell)'; }
    });
    const aktiv = document.getElementById('form-' + tab);
    const tabEl = document.getElementById('tab-' + tab);
    if (aktiv) aktiv.style.display = 'block';
    if (tabEl) { tabEl.style.borderBottomColor = 'var(--cs-primaer)'; tabEl.style.color = 'var(--cs-primaer)'; }
}

// ── Face ID verfügbar? ─────────────────────────────────────────────
const passkeyVerfuegbar = !!window.PublicKeyCredential;
if (passkeyVerfuegbar) {
    document.getElementById('tab-faceid').style.display = 'inline-block';
}

// Magic Link als Standard; auf Mobil mit Passkey → Face ID
const istMobil = window.innerWidth < 768 && passkeyVerfuegbar;
zeigeTab(istMobil ? 'faceid' : 'magic');

// ── WebAuthn Login ─────────────────────────────────────────────────
async function startPasskeyLogin() {
    const btn    = document.getElementById('passkey-btn');
    const fehler = document.getElementById('passkey-fehler');
    btn.disabled    = true;
    btn.textContent = 'Bitte warten…';
    fehler.style.display = 'none';

    try {
        const optRes = await fetch('{{ route("webauthn.authenticate.options") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const opts = await optRes.json();

        opts.challenge = b64uToBuffer(opts.challenge);
        if (opts.allowCredentials?.length) {
            opts.allowCredentials = opts.allowCredentials.map(c => ({ ...c, id: b64uToBuffer(c.id) }));
        } else {
            delete opts.allowCredentials;
        }

        const credential = await navigator.credentials.get({ publicKey: opts });

        const body = {
            id:   credential.id,
            type: credential.type,
            response: {
                clientDataJSON:    bufferToB64u(credential.response.clientDataJSON),
                authenticatorData: bufferToB64u(credential.response.authenticatorData),
                signature:         bufferToB64u(credential.response.signature),
            }
        };

        const res  = await fetch('{{ route("webauthn.authenticate") }}', {
            method:  'POST',
            headers: {
                'Content-Type':     'application/json',
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(body)
        });
        const data = await res.json();

        if (data.ok) {
            window.location.href = data.redirect;
        } else {
            zeigeFehler(data.error || 'Anmeldung fehlgeschlagen.');
        }
    } catch (e) {
        if (e.name !== 'NotAllowedError') zeigeFehler(e.message);
    }

    btn.disabled    = false;
    btn.textContent = 'Face ID / Fingerabdruck';
}

function zeigeFehler(msg) {
    const el = document.getElementById('passkey-fehler');
    el.textContent   = msg;
    el.style.display = 'block';
}

// ── Hilfsfunktionen ───────────────────────────────────────────────
function b64uToBuffer(b64u) {
    const pad = b64u.length % 4;
    const str = (pad ? b64u + '===='.slice(pad) : b64u).replace(/-/g, '+').replace(/_/g, '/');
    const bin = atob(str);
    return new Uint8Array([...bin].map(c => c.charCodeAt(0))).buffer;
}

function bufferToB64u(buf) {
    const bytes = new Uint8Array(buf instanceof ArrayBuffer ? buf : buf.buffer ?? buf);
    let str = '';
    bytes.forEach(b => str += String.fromCharCode(b));
    return btoa(str).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, '');
}

// ── PWA Install-Prompt (Android/Chrome) ──────────────────────────
let deferredInstallPrompt = null;
window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredInstallPrompt = e;
    document.getElementById('install-banner').style.display = 'flex';
});
document.getElementById('install-btn')?.addEventListener('click', async () => {
    if (!deferredInstallPrompt) return;
    deferredInstallPrompt.prompt();
    await deferredInstallPrompt.userChoice;
    deferredInstallPrompt = null;
    document.getElementById('install-banner').style.display = 'none';
});
</script>
</body>
</html>
