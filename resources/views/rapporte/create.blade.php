<x-layouts.app titel="{{ isset($rapport) ? 'Rapport bearbeiten' : 'Neuer Rapport' }}">
<div style="max-width: 680px;">

    <a href="{{ $einsatz ? route('einsaetze.vor-ort', $einsatz) : route('rapporte.index') }}"
        class="link-gedaempt" style="font-size: 0.875rem; display: inline-block; margin-bottom: 1rem;">
        ← {{ $einsatz ? 'Zurück' : 'Rapporte' }}
    </a>

    <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 1.25rem;">{{ isset($rapport) ? 'Rapport bearbeiten' : 'Neuer Rapport' }}</h1>

    <div class="karte">
        <form method="POST" action="{{ isset($rapport) ? route('rapporte.update', $rapport) : route('rapporte.store') }}" id="rapport-form">
            @csrf
            @if(isset($rapport)) @method('PUT') @endif

            @if($einsatz)
                <input type="hidden" name="einsatz_id" value="{{ $einsatz->id }}">
            @endif

            {{-- ── Klient + Typ ── --}}
            <div class="form-grid-2" style="margin-bottom: 0.75rem;">
                <div>
                    <label class="feld-label">Klient *</label>
                    <select name="klient_id" id="klient-select" class="feld" required
                        style="font-size: 1rem; padding: 0.625rem 0.75rem;">
                        <option value="">— wählen —</option>
                        @foreach($klienten as $k)
                            <option value="{{ $k->id }}"
                                data-name="{{ $k->vollname() }}"
                                {{ (old('klient_id', $klient?->id) == $k->id) ? 'selected' : '' }}>
                                {{ $k->vollname() }}
                            </option>
                        @endforeach
                    </select>
                    @error('klient_id')<div class="feld-fehler">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="feld-label">Rapport-Typ *</label>
                    <select name="rapport_typ" id="rapport-typ" class="feld" required
                        style="font-size: 1rem; padding: 0.625rem 0.75rem;">
                        @foreach(\App\Models\Rapport::$typen as $wert => $lbl)
                            <option value="{{ $wert }}"
                                {{ old('rapport_typ', $rapport->rapport_typ ?? 'pflege') === $wert ? 'selected' : '' }}>
                                {{ $lbl }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- ── Datum / Zeit ── --}}
            <div class="form-grid-3" style="margin-bottom: 0.875rem;">
                <div>
                    <label class="feld-label">Datum *</label>
                    <input type="date" name="datum" class="feld" required
                        style="font-size: 1rem; padding: 0.625rem 0.75rem;"
                        value="{{ old('datum', $rapport->datum?->format('Y-m-d') ?? $einsatz?->datum?->format('Y-m-d') ?? date('Y-m-d')) }}">
                </div>
                <div>
                    <label class="feld-label">Zeit von</label>
                    <input type="time" name="zeit_von" class="feld"
                        style="font-size: 1rem; padding: 0.625rem 0.75rem;"
                        value="{{ old('zeit_von', $rapport->zeit_von ?? $einsatz?->checkin_zeit?->format('H:i') ?? '') }}">
                </div>
                <div>
                    <label class="feld-label">Zeit bis</label>
                    <input type="time" name="zeit_bis" class="feld"
                        style="font-size: 1rem; padding: 0.625rem 0.75rem;"
                        value="{{ old('zeit_bis', $rapport->zeit_bis ?? $einsatz?->checkout_zeit?->format('H:i') ?? '') }}">
                </div>
            </div>

            {{-- ══════════════════════════════════════════════
                 KI-ASSISTENT BLOCK
                 ══════════════════════════════════════════════ --}}
            <div class="info-box" style="margin-bottom: 0.875rem;">
                <div class="abschnitt-label" style="color: #1d4ed8; margin-bottom: 0.625rem;">
                    KI-Assistent
                </div>

                {{-- Stichworte-Feld --}}
                <div style="margin-bottom: 0.75rem;">
                    <label style="font-size: 0.8125rem; color: #1d4ed8; margin-bottom: 0.3rem; display: block;">
                        Stichworte / Beobachtungen — tippen oder diktieren, KI schreibt den Bericht
                    </label>
                    <textarea id="stichworte" rows="3"
                        style="width: 100%; border: 1px solid #bfdbfe; border-radius: 0.5rem; padding: 0.625rem 0.75rem; font-size: 0.9375rem; font-family: inherit; resize: vertical; background: #fff; box-sizing: border-box;"
                        placeholder="z.B. unruhig geschlafen, Medikament verweigert, Wunde gereinigt, Blutdruck normal…"></textarea>
                    <div id="diktat-status" style="display: none; font-size: 0.8125rem; color: #dc2626; margin-top: 0.25rem; font-weight: 500;">
                        🔴 Diktat läuft… nochmals klicken zum Stoppen
                    </div>
                </div>

                {{-- Buttons: Mikrofon + KI --}}
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button type="button" id="btn-mikro" onclick="toggleDiktat()"
                        style="flex: 1; min-width: 140px; background: #fff; color: #1d4ed8; border: 2px solid #2563eb; border-radius: 0.5rem; padding: 0.625rem 1rem; font-size: 0.9375rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; transition: all 0.15s;">
                        <span id="mikro-btn-text">🎙 Diktieren</span>
                    </button>
                    <button type="button" id="btn-ki" onclick="kiVorschlag()"
                        style="flex: 1; min-width: 140px; background: #2563eb; color: #fff; border: 2px solid #2563eb; border-radius: 0.5rem; padding: 0.625rem 1rem; font-size: 0.9375rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; transition: background 0.15s;">
                        <span id="ki-btn-text">✨ KI Rapport schreiben</span>
                    </button>
                </div>

                {{-- KI Vorschau --}}
                <div id="ki-vorschau" style="display: none; margin-top: 0.75rem;">
                    <div style="font-size: 0.75rem; color: #1d4ed8; margin-bottom: 0.3rem; display: flex; align-items: center; justify-content: space-between;">
                        <span>KI-Vorschlag (bearbeitbar)</span>
                        <button type="button" onclick="kiVorschlagUebernehmen()"
                            style="background: #2563eb; color: #fff; border: none; border-radius: 0.375rem; padding: 0.2rem 0.625rem; font-size: 0.75rem; cursor: pointer; font-weight: 600;">
                            → In Bericht übernehmen
                        </button>
                    </div>
                    <textarea id="ki-text" rows="5"
                        style="width: 100%; border: 1px solid #bfdbfe; border-radius: 0.5rem; padding: 0.625rem; font-size: 0.875rem; font-family: inherit; resize: vertical; background: #fff; box-sizing: border-box;"></textarea>
                </div>
            </div>

            {{-- ── Bericht-Inhalt (Haupt-Textarea) ── --}}
            <div style="margin-bottom: 0.75rem;">
                <label class="feld-label" style="font-size: 0.9375rem;">
                    Bericht *
                    <span class="text-mini text-hell" style="margin-left: 0.5rem; font-weight: 400;">
                        (direkt tippen, diktieren oder KI-Vorschlag übernehmen)
                    </span>
                </label>
                <textarea name="inhalt" id="inhalt" class="feld" required rows="8"
                    style="font-family: inherit; resize: vertical; font-size: 1rem; padding: 0.75rem; margin-bottom: 0.5rem;"
                    placeholder="Pflegebericht, Beobachtungen, Massnahmen …">{{ old('inhalt', $rapport->inhalt ?? '') }}</textarea>
                {{-- Mikrofon direkt in Bericht --}}
                <button type="button" id="btn-mikro-haupt" onclick="toggleDiktatHaupt()"
                    style="width: 100%; background: #fff; color: #374151; border: 1px solid var(--cs-border); border-radius: 0.5rem; padding: 0.5rem 1rem; font-size: 0.9375rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.4rem; transition: all 0.15s;">
                    <span id="mikro-haupt-btn-text">🎙 Direkt in Bericht diktieren</span>
                </button>
                <div id="diktat-haupt-status" style="display: none; font-size: 0.8125rem; color: #dc2626; margin-top: 0.25rem; font-weight: 500;">
                    🔴 Diktat läuft direkt in Bericht… nochmals klicken zum Stoppen
                </div>
                @error('inhalt')<div class="feld-fehler">{{ $message }}</div>@enderror
            </div>

            {{-- ── Optionen ── --}}
            <div style="display: flex; gap: 1.25rem; flex-wrap: wrap; margin-bottom: 0.875rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                    <input type="hidden" name="vertraulich" value="0">
                    <input type="checkbox" name="vertraulich" value="1" {{ old('vertraulich') ? 'checked' : '' }}
                        style="accent-color: var(--cs-primaer);">
                    Vertraulich (nur Admin)
                </label>
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                    <input type="checkbox" id="cb-notify" onchange="toggleNotify(this.checked)"
                        style="accent-color: var(--cs-primaer);">
                    Mitarbeiter benachrichtigen
                </label>
            </div>

            {{-- Benachrichtigung (optional) --}}
            <div id="notify-block" style="display: none; background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: 0.5rem; padding: 0.75rem; margin-bottom: 0.875rem;">
                <div class="text-klein text-hell" style="margin-bottom: 0.5rem;">Empfänger wählen:</div>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    @foreach($mitarbeiter as $ma)
                    <label style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.8125rem; cursor: pointer;">
                        <input type="checkbox" name="notify_ids[]" value="{{ $ma->id }}"
                            style="accent-color: var(--cs-primaer);">
                        {{ $ma->vorname }} {{ $ma->nachname }}
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Zwischenfall-Hinweis --}}
            <div id="zwischenfall-hinweis" class="warn-box" style="display: none; margin-bottom: 0.875rem; font-size: 0.8125rem; color: #991b1b;">
                ⚠ Zwischenfall: alle Admins werden automatisch benachrichtigt.
            </div>

            <div class="abschnitt-trenn" style="padding-top: 1rem; display: flex; gap: 0.625rem; flex-wrap: wrap;">
                <button type="submit" class="btn btn-primaer" style="font-size: 1rem; padding: 0.625rem 1.5rem;">
                    Rapport speichern
                </button>
                <a href="{{ $einsatz ? route('einsaetze.vor-ort', $einsatz) : route('rapporte.index') }}"
                    class="btn btn-sekundaer" style="font-size: 1rem; padding: 0.625rem 1rem;">
                    Abbrechen
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// ── Zwischenfall-Hinweis ──────────────────────────────────────────
document.getElementById('rapport-typ').addEventListener('change', function() {
    document.getElementById('zwischenfall-hinweis').style.display =
        this.value === 'zwischenfall' ? 'block' : 'none';
});
// Initial prüfen
if (document.getElementById('rapport-typ').value === 'zwischenfall') {
    document.getElementById('zwischenfall-hinweis').style.display = 'block';
}

// ── Benachrichtigung Toggle ───────────────────────────────────────
function toggleNotify(on) {
    document.getElementById('notify-block').style.display = on ? 'block' : 'none';
}

// ── KI-Vorschlag ─────────────────────────────────────────────────
async function kiVorschlag() {
    const stichworte = document.getElementById('stichworte').value.trim();
    if (!stichworte) {
        alert('Bitte erst Stichworte / Beobachtungen eingeben.');
        return;
    }

    const klientSelect = document.getElementById('klient-select');
    const klientName   = klientSelect.options[klientSelect.selectedIndex]?.dataset?.name || '';
    if (!klientName) {
        alert('Bitte erst einen Klienten wählen.');
        return;
    }

    const typ   = document.getElementById('rapport-typ').value;
    const datum = document.querySelector('input[name="datum"]').value;
    const btn   = document.getElementById('btn-ki');
    const btnTxt = document.getElementById('ki-btn-text');

    btn.disabled  = true;
    btnTxt.textContent = '⏳ KI schreibt…';

    try {
        const resp = await fetch('/ki/rapport', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'Accept':       'application/json',
            },
            body: JSON.stringify({
                stichworte:  stichworte,
                klient_name: klientName,
                rapport_typ: typ,
                datum:       datum,
            }),
        });

        const data = await resp.json();

        if (data.success) {
            document.getElementById('ki-text').value = data.text;
            document.getElementById('ki-vorschau').style.display = 'block';
        } else {
            alert('Fehler: ' + (data.error || 'Unbekannter Fehler'));
        }
    } catch (e) {
        alert('KI nicht erreichbar. Bitte manuell schreiben.');
    } finally {
        btn.disabled  = false;
        btnTxt.textContent = '✨ KI Rapport schreiben';
    }
}

function kiVorschlagUebernehmen() {
    const vorschlag = document.getElementById('ki-text').value.trim();
    const inhalt    = document.getElementById('inhalt');
    if (inhalt.value.trim()) {
        if (!confirm('Bestehenden Text ersetzen?')) return;
    }
    inhalt.value = vorschlag;
    inhalt.focus();
    document.getElementById('ki-vorschau').style.display = 'none';
}

// ── Web Speech API — Diktat (Stichworte) ─────────────────────────
let diktatRec   = null;
let diktatAktiv = false;

function toggleDiktat() {
    if (!('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
        alert('Spracherkennung wird von diesem Browser nicht unterstützt.\nBitte Chrome oder Edge verwenden.');
        return;
    }

    if (diktatAktiv) {
        diktatRec?.stop();
        return;
    }

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    diktatRec = new SpeechRecognition();
    diktatRec.lang = 'de-CH';
    diktatRec.continuous = true;
    diktatRec.interimResults = false;

    diktatRec.onstart = () => {
        diktatAktiv = true;
        document.getElementById('btn-mikro').style.background = '#fee2e2';
        document.getElementById('btn-mikro').style.borderColor = '#dc2626';
        document.getElementById('btn-mikro').style.color = '#dc2626';
        document.getElementById('mikro-btn-text').textContent = '🔴 Stoppen';
        document.getElementById('diktat-status').style.display = 'block';
    };

    diktatRec.onresult = (e) => {
        const transkript = Array.from(e.results)
            .map(r => r[0].transcript).join(' ');
        const el = document.getElementById('stichworte');
        el.value = (el.value + ' ' + transkript).trim();
    };

    diktatRec.onend = () => {
        diktatAktiv = false;
        document.getElementById('btn-mikro').style.background = '#fff';
        document.getElementById('btn-mikro').style.borderColor = '#2563eb';
        document.getElementById('btn-mikro').style.color = '#1d4ed8';
        document.getElementById('mikro-btn-text').textContent = '🎙 Diktieren';
        document.getElementById('diktat-status').style.display = 'none';
    };

    diktatRec.start();
}

// ── Web Speech API — Diktat (direkt in Bericht) ───────────────────
let diktatHauptRec   = null;
let diktatHauptAktiv = false;

function toggleDiktatHaupt() {
    if (!('webkitSpeechRecognition' in window || 'SpeechRecognition' in window)) {
        alert('Spracherkennung wird von diesem Browser nicht unterstützt.\nBitte Chrome oder Edge verwenden.');
        return;
    }

    if (diktatHauptAktiv) {
        diktatHauptRec?.stop();
        return;
    }

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    diktatHauptRec = new SpeechRecognition();
    diktatHauptRec.lang = 'de-CH';
    diktatHauptRec.continuous = true;
    diktatHauptRec.interimResults = false;

    diktatHauptRec.onstart = () => {
        diktatHauptAktiv = true;
        document.getElementById('btn-mikro-haupt').style.background = '#fee2e2';
        document.getElementById('btn-mikro-haupt').style.borderColor = '#dc2626';
        document.getElementById('btn-mikro-haupt').style.color = '#dc2626';
        document.getElementById('mikro-haupt-btn-text').textContent = '🔴 Stoppen';
        document.getElementById('diktat-haupt-status').style.display = 'block';
    };

    diktatHauptRec.onresult = (e) => {
        const transkript = Array.from(e.results)
            .map(r => r[0].transcript).join(' ');
        const el = document.getElementById('inhalt');
        el.value = (el.value + ' ' + transkript).trim();
    };

    diktatHauptRec.onend = () => {
        diktatHauptAktiv = false;
        document.getElementById('btn-mikro-haupt').style.background = '#fff';
        document.getElementById('btn-mikro-haupt').style.borderColor = 'var(--cs-border)';
        document.getElementById('btn-mikro-haupt').style.color = '#374151';
        document.getElementById('mikro-haupt-btn-text').textContent = '🎙 Direkt in Bericht diktieren';
        document.getElementById('diktat-haupt-status').style.display = 'none';
    };

    diktatHauptRec.start();
}
</script>
@endpush

</x-layouts.app>
