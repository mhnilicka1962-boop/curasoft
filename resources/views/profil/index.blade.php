<x-layouts.app titel="Mein Profil">
<div style="max-width: 640px;">

    <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 1.5rem;">Mein Profil</h1>

    {{-- Benutzerinfo --}}
    <div class="karte" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Konto</div>
        <div style="font-size: 0.9375rem; font-weight: 600;">{{ auth()->user()->vorname }} {{ auth()->user()->nachname }}</div>
        <div class="text-hell" style="font-size: 0.875rem;">{{ auth()->user()->email }}</div>
        <div class="text-hell" style="font-size: 0.8125rem; margin-top: 0.25rem;">Rolle: {{ ucfirst(auth()->user()->rolle) }}</div>
    </div>

    {{-- Passkeys / Face ID --}}
    <div class="karte">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.875rem;">
            <div>
                <div class="abschnitt-label" style="margin-bottom: 0.125rem;">Passkeys (Face ID / Fingerabdruck)</div>
                <div class="text-hell" style="font-size: 0.8125rem;">Melde dich auf deinem Gerät ohne Passwort an.</div>
            </div>
        </div>

        @if(session('erfolg'))
            <div class="alert alert-erfolg" style="margin-bottom: 1rem;">{{ session('erfolg') }}</div>
        @endif

        {{-- Bestehende Passkeys --}}
        @if($passkeys->count())
        <div style="margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach($passkeys as $pk)
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.875rem; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius);">
                <div>
                    <div style="font-size: 0.875rem; font-weight: 600;">
                        {{ $pk->geraet_name ?: 'Gerät ' . $loop->iteration }}
                    </div>
                    <div class="text-hell" style="font-size: 0.75rem;">
                        Registriert: {{ $pk->created_at->format('d.m.Y H:i') }}
                    </div>
                </div>
                <button onclick="loeschePasskey({{ $pk->id }}, this)"
                    class="btn btn-sekundaer" style="padding: 0.25rem 0.625rem; font-size: 0.75rem; color: var(--cs-fehler);">
                    Entfernen
                </button>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-hell" style="font-size: 0.875rem; margin-bottom: 1.25rem;">
            Noch kein Passkey registriert. Registriere jetzt Face ID oder Fingerabdruck für schnelles Einloggen.
        </p>
        @endif

        {{-- Neuen Passkey registrieren --}}
        <div id="passkey-bereich">
            <div style="display: flex; gap: 0.5rem; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 160px;">
                    <label class="feld-label" style="font-size: 0.8125rem;">Gerätename (optional)</label>
                    <input type="text" id="geraet-name" class="feld" placeholder="z.B. iPhone Sandra"
                        style="font-size: 0.875rem;">
                </div>
                <button id="register-btn" onclick="registrierePasskey()" class="btn btn-primaer">
                    + Passkey registrieren
                </button>
            </div>
            <p id="passkey-status" style="font-size: 0.8125rem; color: var(--cs-text-hell); margin-top: 0.5rem; display: none;"></p>
            <p id="passkey-nicht-unterstuetzt" style="font-size: 0.8125rem; color: var(--cs-warnung); margin-top: 0.5rem; display: none;">
                Dein Browser unterstützt keine Passkeys. Nutze Safari auf iOS 16+ oder Chrome auf Android.
            </p>
        </div>
    </div>

</div>

@push('scripts')
<script>
// Passkey-Unterstützung prüfen
if (!window.PublicKeyCredential) {
    document.getElementById('register-btn').style.display = 'none';
    document.getElementById('passkey-nicht-unterstuetzt').style.display = 'block';
}

async function registrierePasskey() {
    const btn    = document.getElementById('register-btn');
    const status = document.getElementById('passkey-status');
    const name   = document.getElementById('geraet-name').value.trim() || null;

    btn.disabled    = true;
    btn.textContent = 'Bitte warten…';
    status.style.display = 'none';

    try {
        const optRes = await fetch('{{ route("webauthn.register.options") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const opts = await optRes.json();

        opts.challenge = b64uToBuffer(opts.challenge);
        opts.user.id   = b64uToBuffer(opts.user.id);
        if (opts.excludeCredentials) {
            opts.excludeCredentials = opts.excludeCredentials.map(c => ({ ...c, id: b64uToBuffer(c.id) }));
        }

        const credential = await navigator.credentials.create({ publicKey: opts });

        const body = {
            id:         credential.id,
            type:       credential.type,
            geraet_name: name,
            response: {
                clientDataJSON:   bufferToB64u(credential.response.clientDataJSON),
                attestationObject: bufferToB64u(credential.response.attestationObject),
            }
        };

        const res  = await fetch('{{ route("webauthn.register") }}', {
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
            location.reload();
        } else {
            zeigeStatus('Fehler: ' + (data.error || 'Unbekannt'), true);
        }
    } catch (e) {
        if (e.name === 'NotAllowedError') {
            zeigeStatus('Abgebrochen.', false);
        } else {
            zeigeStatus('Fehler: ' + e.message, true);
        }
    }

    btn.disabled    = false;
    btn.textContent = '+ Passkey registrieren';
}

async function loeschePasskey(id, btn) {
    if (!confirm('Passkey wirklich entfernen?')) return;
    btn.disabled = true;
    const res = await fetch('/webauthn/credentials/' + id, {
        method:  'DELETE',
        headers: {
            'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest',
        }
    });
    const data = await res.json();
    if (data.ok) location.reload();
    else { alert('Fehler beim Löschen'); btn.disabled = false; }
}

function zeigeStatus(msg, fehler) {
    const el = document.getElementById('passkey-status');
    el.textContent     = msg;
    el.style.display   = 'block';
    el.style.color     = fehler ? 'var(--cs-fehler)' : 'var(--cs-erfolg)';
}

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
</script>
@endpush
</x-layouts.app>
