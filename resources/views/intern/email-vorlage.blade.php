<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direktanschreiben — Spitex-Akquise</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: #f9fafb;
            color: #111827;
            line-height: 1.6;
            padding: 2rem 1.5rem;
        }
        .container { max-width: 760px; margin: 0 auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        h1 { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; }
        .subtitle { font-size: 0.9375rem; color: #6b7280; margin-top: 0.25rem; }
        .zaehler {
            background: #2563eb;
            color: #fff;
            padding: 0.5rem 1.25rem;
            border-radius: 0.625rem;
            font-size: 0.875rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .block { margin-bottom: 2rem; }
        .block-label {
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        .text-box {
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.625rem;
            padding: 1.25rem 1.5rem;
            font-size: 0.9375rem;
            color: #1f2937;
            white-space: pre-wrap;
            line-height: 1.75;
            position: relative;
        }
        .copy-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 0.5rem 1.125rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.75rem;
            transition: background 0.15s;
        }
        .copy-btn:hover { background: #1d4ed8; }
        .copy-btn.copied { background: #16a34a; }
        .checkliste {
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.625rem;
            padding: 1.25rem 1.5rem;
        }
        .check-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            user-select: none;
        }
        .check-item:last-child { border-bottom: none; }
        .check-item input[type="checkbox"] {
            width: 1.1rem;
            height: 1.1rem;
            margin-top: 0.15rem;
            cursor: pointer;
            accent-color: #2563eb;
            flex-shrink: 0;
        }
        .check-item label {
            font-size: 0.9375rem;
            color: #374151;
            cursor: pointer;
        }
        .check-item.erledigt label { text-decoration: line-through; color: #9ca3af; }
        a.link-extern {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
        }
        a.link-extern:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div>
            <h1>Direktanschreiben Spitex-Organisationen</h1>
            <p class="subtitle">Kopierfertige Vorlage für die Akquise</p>
        </div>
        <div class="zaehler" id="zaehler-display">Heute gesendet: <span id="zaehler-zahl">0</span></div>
    </div>

    {{-- Block 1: Betreff --}}
    <div class="block">
        <div class="block-label">Betreff</div>
        <div class="text-box" id="betreff-text">Spitex-Software die sich selbst rechnet — 12 Monate kostenlos testen</div>
        <button class="copy-btn" onclick="kopieren('betreff-text', this)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            Kopieren
        </button>
    </div>

    {{-- Block 2: Email-Text --}}
    <div class="block">
        <div class="block-label">Email-Text</div>
        <div class="text-box" id="email-text">Guten Tag [Vorname Nachname]

Ich habe gesehen dass [Organisation] in [Kanton] tätig ist.

Ich bin Mathias Hnilicka und habe CuraSoft entwickelt —
eine Spitex-Software speziell für die Schweiz:

✓ Automatische Kantonstariife (alle 26 Kantone)
✓ XML 450.100 Export auf Knopfdruck
✓ Läuft auf dem Handy, ohne Installation
✓ CHF 150–490 / Monat, monatlich kündbar

Aktuell suche ich Pilotpartner — 12 Monate mit 40%
Rabatt, dafür ca. 1 Stunde Feedback pro Monat.

Darf ich Ihnen in einem 20-Minuten-Gespräch zeigen
was die Software kann?

Freundliche Grüsse
Mathias Hnilicka
mhn@itjob.ch
www.curasoft.ch</div>
        <button class="copy-btn" onclick="kopierenMitZaehler('email-text', this)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
            Kopieren
        </button>
    </div>

    {{-- Block 3: Checkliste --}}
    <div class="block">
        <div class="block-label">Vorgehen</div>
        <div class="checkliste" id="checkliste">
            <div class="check-item" id="check-1">
                <input type="checkbox" id="cb-1" onchange="checkToggle(1)">
                <label for="cb-1">Spitex-Verzeichnis öffnen: <a href="https://www.spitex.ch" target="_blank" class="link-extern">spitex.ch</a></label>
            </div>
            <div class="check-item" id="check-2">
                <input type="checkbox" id="cb-2" onchange="checkToggle(2)">
                <label for="cb-2">Organisation auswählen</label>
            </div>
            <div class="check-item" id="check-3">
                <input type="checkbox" id="cb-3" onchange="checkToggle(3)">
                <label for="cb-3">Name der Leiterin / des Leiters suchen</label>
            </div>
            <div class="check-item" id="check-4">
                <input type="checkbox" id="cb-4" onchange="checkToggle(4)">
                <label for="cb-4">[Vorname Nachname] und [Organisation] und [Kanton] im Text ersetzen</label>
            </div>
            <div class="check-item" id="check-5">
                <input type="checkbox" id="cb-5" onchange="checkToggle(5)">
                <label for="cb-5">Email senden</label>
            </div>
            <div class="check-item" id="check-6">
                <input type="checkbox" id="cb-6" onchange="checkToggle(6)">
                <label for="cb-6">In Excel / Notion als "kontaktiert" markieren</label>
            </div>
        </div>
    </div>

</div>

<script>
    // ── Zähler (LocalStorage, wird täglich zurückgesetzt) ──────────────────
    function getHeute() {
        return new Date().toISOString().slice(0, 10); // YYYY-MM-DD
    }
    function zaehlerLaden() {
        const gespeichert = JSON.parse(localStorage.getItem('akquise_zaehler') || '{}');
        if (gespeichert.datum === getHeute()) {
            return gespeichert.anzahl || 0;
        }
        return 0;
    }
    function zaehlerErhoehen() {
        const n = zaehlerLaden() + 1;
        localStorage.setItem('akquise_zaehler', JSON.stringify({ datum: getHeute(), anzahl: n }));
        document.getElementById('zaehler-zahl').textContent = n;
    }
    document.getElementById('zaehler-zahl').textContent = zaehlerLaden();

    // ── Kopieren ───────────────────────────────────────────────────────────
    function kopieren(id, btn) {
        const text = document.getElementById(id).innerText;
        navigator.clipboard.writeText(text).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = '✓ Kopiert';
            btn.classList.add('copied');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('copied');
            }, 2000);
        });
    }
    function kopierenMitZaehler(id, btn) {
        kopieren(id, btn);
        zaehlerErhoehen();
    }

    // ── Checkliste Toggle ──────────────────────────────────────────────────
    function checkToggle(n) {
        const item = document.getElementById('check-' + n);
        const cb   = document.getElementById('cb-' + n);
        item.classList.toggle('erledigt', cb.checked);
    }
</script>
</body>
</html>
