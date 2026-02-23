<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spitex einrichten</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .schritt { display: none; }
        .schritt.aktiv { display: block; }

        .schritt-indikator {
            display: flex;
            align-items: center;
            gap: 0;
            margin-bottom: 2rem;
        }
        .schritt-punkt {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            font-size: 0.8125rem;
            font-weight: 600;
            background-color: var(--cs-border);
            color: var(--cs-text-hell);
            flex-shrink: 0;
            transition: background-color 0.2s, color 0.2s;
        }
        .schritt-punkt.aktiv   { background-color: var(--cs-primaer); color: #fff; }
        .schritt-punkt.fertig  { background-color: var(--cs-erfolg);  color: #fff; }
        .schritt-linie {
            flex: 1;
            height: 2px;
            background-color: var(--cs-border);
            transition: background-color 0.2s;
        }
        .schritt-linie.fertig { background-color: var(--cs-erfolg); }

        .logo-vorschau {
            width: 120px;
            height: 60px;
            object-fit: contain;
            border: 1px solid var(--cs-border);
            border-radius: var(--cs-radius);
            padding: 0.5rem;
            background: #fff;
        }
        .farb-vorschau {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--cs-radius);
            border: 1px solid var(--cs-border);
            flex-shrink: 0;
        }
    </style>
</head>
<body style="background-color: var(--cs-hintergrund); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;">

<div style="width: 100%; max-width: 520px;">

    {{-- Header --}}
    <div style="text-align: center; margin-bottom: 2rem;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; background-color: var(--cs-primaer); border-radius: 0.75rem; margin-bottom: 1rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--cs-text); margin: 0 0 0.25rem;">Spitex einrichten</h1>
        <p style="color: var(--cs-text-hell); font-size: 0.875rem; margin: 0;">Erstmalige Konfiguration — dauert nur 2 Minuten</p>
    </div>

    {{-- Fehler --}}
    @if ($errors->any())
        <div class="alert alert-fehler" style="margin-bottom: 1.25rem;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="karte" style="padding: 2rem;">

        {{-- Schritt-Indikator --}}
        <div class="schritt-indikator">
            <div class="schritt-punkt aktiv" id="punkt-1">1</div>
            <div class="schritt-linie" id="linie-1"></div>
            <div class="schritt-punkt" id="punkt-2">2</div>
            <div class="schritt-linie" id="linie-2"></div>
            <div class="schritt-punkt" id="punkt-3">3</div>
        </div>

        <form method="POST" action="{{ route('setup.store') }}" enctype="multipart/form-data" id="setup-form">
            @csrf

            {{-- ===================== SCHRITT 1: Organisation ===================== --}}
            <div class="schritt aktiv" id="schritt-1">
                <h2 style="font-size: 1rem; font-weight: 600; color: var(--cs-text); margin: 0 0 1.25rem;">Organisation einrichten</h2>

                {{-- Name --}}
                <div style="margin-bottom: 1rem;">
                    <label class="feld-label" for="org_name">Name der Organisation</label>
                    <input type="text" id="org_name" name="org_name" class="feld"
                        value="{{ old('org_name') }}"
                        placeholder="z.B. Spitex Zürich Nord"
                        required>
                </div>

                {{-- Layout --}}
                <div style="margin-bottom: 1rem;">
                    <label class="feld-label">Navigation</label>
                    <div style="display: flex; gap: 0.75rem; margin-top: 0.25rem;">
                        <label style="flex: 1; cursor: pointer;">
                            <input type="radio" name="layout" value="sidebar"
                                {{ old('layout', 'sidebar') === 'sidebar' ? 'checked' : '' }}
                                style="display: none;" class="layout-radio">
                            <div class="layout-option" data-val="sidebar" style="border: 2px solid var(--cs-primaer); border-radius: var(--cs-radius); padding: 0.75rem; text-align: center; font-size: 0.8125rem;">
                                <div style="font-size: 1.25rem; margin-bottom: 0.25rem;">◫</div>
                                <div style="font-weight: 500;">Sidebar</div>
                                <div style="color: var(--cs-text-hell); font-size: 0.75rem;">Navigation links</div>
                            </div>
                        </label>
                        <label style="flex: 1; cursor: pointer;">
                            <input type="radio" name="layout" value="topnav"
                                {{ old('layout') === 'topnav' ? 'checked' : '' }}
                                style="display: none;" class="layout-radio">
                            <div class="layout-option" data-val="topnav" style="border: 2px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.75rem; text-align: center; font-size: 0.8125rem;">
                                <div style="font-size: 1.25rem; margin-bottom: 0.25rem;">⬒</div>
                                <div style="font-weight: 500;">Top-Navigation</div>
                                <div style="color: var(--cs-text-hell); font-size: 0.75rem;">Navigation oben</div>
                            </div>
                        </label>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-primaer" onclick="naechsterSchritt(1)">
                        Weiter →
                    </button>
                </div>
            </div>

            {{-- ===================== SCHRITT 2: Design ===================== --}}
            <div class="schritt" id="schritt-2">
                <h2 style="font-size: 1rem; font-weight: 600; color: var(--cs-text); margin: 0 0 1.25rem;">Design & Logo</h2>

                {{-- Primärfarbe --}}
                <div style="margin-bottom: 1.25rem;">
                    <label class="feld-label" for="farbe_primaer">Primärfarbe</label>
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.25rem;">
                        <input type="color" id="farbe_picker" value="{{ old('farbe_primaer', '#2563eb') }}"
                            style="width: 2.5rem; height: 2.5rem; border: 1px solid var(--cs-border); border-radius: var(--cs-radius); cursor: pointer; padding: 2px;"
                            oninput="document.getElementById('farbe_primaer').value = this.value; document.getElementById('farbe_hex').value = this.value; document.getElementById('farb-demo').style.backgroundColor = this.value;">
                        <input type="text" id="farbe_hex" value="{{ old('farbe_primaer', '#2563eb') }}"
                            class="feld" style="max-width: 120px; font-family: monospace;"
                            placeholder="#2563eb"
                            oninput="if(/^#[0-9a-fA-F]{6}$/.test(this.value)) { document.getElementById('farbe_picker').value = this.value; document.getElementById('farbe_primaer').value = this.value; document.getElementById('farb-demo').style.backgroundColor = this.value; }">
                        <input type="hidden" name="farbe_primaer" id="farbe_primaer" value="{{ old('farbe_primaer', '#2563eb') }}">
                        <div id="farb-demo" style="width: 2.5rem; height: 2.5rem; border-radius: var(--cs-radius); background-color: {{ old('farbe_primaer', '#2563eb') }};"></div>
                    </div>
                    <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem; flex-wrap: wrap;">
                        @foreach(['#2563eb','#16a34a','#9333ea','#dc2626','#d97706','#0891b2','#1f2937'] as $farbe)
                            <button type="button"
                                style="width: 1.5rem; height: 1.5rem; border-radius: 50%; background-color: {{ $farbe }}; border: 2px solid transparent; cursor: pointer;"
                                onclick="setFarbe('{{ $farbe }}')"></button>
                        @endforeach
                    </div>
                </div>

                {{-- Logo --}}
                <div style="margin-bottom: 1rem;">
                    <label class="feld-label" for="logo">Logo (optional)</label>
                    <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/svg+xml,image/gif"
                        class="feld" style="padding: 0.375rem;"
                        onchange="logoVorschau(this)">
                    <div style="font-size: 0.75rem; color: var(--cs-text-hell); margin-top: 0.25rem;">PNG, SVG oder JPG — max. 2 MB. Empfohlen: transparenter Hintergrund, ca. 200×50px.</div>
                    <div id="logo-vorschau-wrapper" style="display: none; margin-top: 0.75rem;">
                        <img id="logo-vorschau" class="logo-vorschau" src="" alt="Vorschau">
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-sekundaer" onclick="vorherigenSchritt(2)">← Zurück</button>
                    <button type="button" class="btn btn-primaer" onclick="naechsterSchritt(2)">Weiter →</button>
                </div>
            </div>

            {{-- ===================== SCHRITT 3: Admin-Konto ===================== --}}
            <div class="schritt" id="schritt-3">
                <h2 style="font-size: 1rem; font-weight: 600; color: var(--cs-text); margin: 0 0 1.25rem;">Administrator-Konto</h2>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1rem;">
                    <div>
                        <label class="feld-label" for="vorname">Vorname</label>
                        <input type="text" id="vorname" name="vorname" class="feld"
                            value="{{ old('vorname') }}" required placeholder="Max">
                    </div>
                    <div>
                        <label class="feld-label" for="nachname">Nachname</label>
                        <input type="text" id="nachname" name="nachname" class="feld"
                            value="{{ old('nachname') }}" required placeholder="Muster">
                    </div>
                </div>

                <div style="margin-bottom: 1rem;">
                    <label class="feld-label" for="email">E-Mail-Adresse</label>
                    <input type="email" id="email" name="email" class="feld"
                        value="{{ old('email') }}" required placeholder="admin@beispiel.ch">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label class="feld-label" for="password">Passwort</label>
                    <input type="password" id="password" name="password" class="feld"
                        required placeholder="Mindestens 8 Zeichen" autocomplete="new-password">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label class="feld-label" for="password_confirmation">Passwort bestätigen</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="feld"
                        required placeholder="Passwort wiederholen" autocomplete="new-password">
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-sekundaer" onclick="vorherigenSchritt(3)">← Zurück</button>
                    <button type="submit" class="btn btn-primaer" style="padding: 0.625rem 1.5rem;">
                        Einrichten & starten
                    </button>
                </div>
            </div>

        </form>
    </div>

    <p style="text-align: center; margin-top: 1.5rem; font-size: 0.75rem; color: var(--cs-text-hell);">
        Spitex &copy; {{ date('Y') }}
    </p>
</div>

<script>
let aktuellerSchritt = 1;

// Bei Validierungsfehlern direkt zu Schritt 3 springen
@if($errors->any())
    aktuellerSchritt = 3;
    document.addEventListener('DOMContentLoaded', () => zeigeSchritt(3));
@endif

function zeigeSchritt(nr) {
    document.querySelectorAll('.schritt').forEach(el => el.classList.remove('aktiv'));
    document.getElementById('schritt-' + nr).classList.add('aktiv');

    for (let i = 1; i <= 3; i++) {
        const punkt = document.getElementById('punkt-' + i);
        punkt.classList.remove('aktiv', 'fertig');
        if (i < nr)       punkt.classList.add('fertig');
        else if (i === nr) punkt.classList.add('aktiv');
    }
    for (let i = 1; i <= 2; i++) {
        const linie = document.getElementById('linie-' + i);
        linie.classList.toggle('fertig', i < nr);
    }

    aktuellerSchritt = nr;
}

function naechsterSchritt(von) {
    if (von === 1) {
        const name = document.getElementById('org_name').value.trim();
        if (!name) { document.getElementById('org_name').focus(); return; }
    }
    zeigeSchritt(von + 1);
}

function vorherigenSchritt(von) {
    zeigeSchritt(von - 1);
}

function setFarbe(hex) {
    document.getElementById('farbe_picker').value = hex;
    document.getElementById('farbe_hex').value    = hex;
    document.getElementById('farbe_primaer').value = hex;
    document.getElementById('farb-demo').style.backgroundColor = hex;
}

function logoVorschau(input) {
    const wrapper = document.getElementById('logo-vorschau-wrapper');
    const img     = document.getElementById('logo-vorschau');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; wrapper.style.display = 'block'; };
        reader.readAsDataURL(input.files[0]);
    } else {
        wrapper.style.display = 'none';
    }
}

// Layout-Auswahl visuell hervorheben
document.querySelectorAll('.layout-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.layout-option').forEach(opt => {
            opt.style.borderColor = 'var(--cs-border)';
        });
        radio.closest('label').querySelector('.layout-option').style.borderColor = 'var(--cs-primaer)';
    });
});
</script>

</body>
</html>
