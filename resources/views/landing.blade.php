<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Spitex-Software für kleine, private Organisationen in der Schweiz. Einsatzplanung, Abrechnung und Klientenverwaltung — einfach und bezahlbar.">
    <title>Spitex — Pflegemanagement-Software für kleine Organisationen</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blau:        #2563eb;
            --blau-hell:   #eff6ff;
            --blau-dunkel: #1d4ed8;
            --text:        #111827;
            --text-hell:   #6b7280;
            --border:      #e5e7eb;
            --hintergrund: #f9fafb;
            --weiss:       #ffffff;
            --gruen:       #16a34a;
            --gruen-hell:  #f0fdf4;
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            color: var(--text);
            background: var(--weiss);
            line-height: 1.6;
        }

        /* ── Topbar ─────────────────────────────────────────────────── */
        .topbar {
            position: fixed; top: 0; left: 0; right: 0;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--border);
            z-index: 100;
            padding: 0 1.5rem;
            height: 3.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .topbar-logo {
            display: flex; align-items: center; gap: 0.625rem;
            text-decoration: none; color: var(--text);
        }
        .topbar-logo-icon {
            width: 2rem; height: 2rem;
            background: var(--blau);
            border-radius: 0.5rem;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .topbar-logo-name {
            font-weight: 700; font-size: 1rem; letter-spacing: -0.01em;
        }
        .topbar-nav {
            display: flex; align-items: center; gap: 1rem;
        }
        .btn-login {
            display: inline-flex; align-items: center; gap: 0.375rem;
            background: var(--blau); color: #fff;
            padding: 0.4rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem; font-weight: 600;
            text-decoration: none;
            transition: background 0.15s;
        }
        .btn-login:hover { background: var(--blau-dunkel); }

        /* ── Sections ───────────────────────────────────────────────── */
        section { padding: 5rem 1.5rem; }
        .container { max-width: 1100px; margin: 0 auto; }
        .container-sm { max-width: 720px; margin: 0 auto; }

        /* ── Hero ───────────────────────────────────────────────────── */
        .hero {
            padding-top: 8rem;
            padding-bottom: 5rem;
            background: linear-gradient(160deg, var(--blau-hell) 0%, var(--weiss) 60%);
            text-align: center;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 0.375rem;
            background: var(--blau-hell); color: var(--blau-dunkel);
            border: 1px solid #bfdbfe;
            padding: 0.3rem 0.875rem;
            border-radius: 999px;
            font-size: 0.8125rem; font-weight: 600;
            margin-bottom: 1.5rem;
        }
        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.25rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.15;
            color: var(--text);
            margin-bottom: 1.25rem;
        }
        .hero h1 span { color: var(--blau); }
        .hero-lead {
            font-size: clamp(1rem, 2vw, 1.2rem);
            color: var(--text-hell);
            max-width: 600px;
            margin: 0 auto 2.5rem;
        }
        .hero-cta {
            display: flex; flex-wrap: wrap;
            gap: 0.875rem;
            justify-content: center;
            margin-bottom: 3rem;
        }
        .btn-primaer {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: var(--blau); color: #fff;
            padding: 0.75rem 1.75rem;
            border-radius: 0.625rem;
            font-size: 1rem; font-weight: 600;
            text-decoration: none;
            transition: background 0.15s, transform 0.1s;
        }
        .btn-primaer:hover { background: var(--blau-dunkel); transform: translateY(-1px); }
        .btn-sekundaer {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: var(--weiss); color: var(--text);
            padding: 0.75rem 1.75rem;
            border-radius: 0.625rem;
            border: 1.5px solid var(--border);
            font-size: 1rem; font-weight: 600;
            text-decoration: none;
            transition: border-color 0.15s, transform 0.1s;
        }
        .btn-sekundaer:hover { border-color: var(--blau); color: var(--blau); transform: translateY(-1px); }
        .hero-trust {
            display: flex; flex-wrap: wrap;
            gap: 1.25rem 2rem;
            justify-content: center;
            font-size: 0.8125rem; color: var(--text-hell);
        }
        .hero-trust-item { display: flex; align-items: center; gap: 0.375rem; }

        /* ── Problem ────────────────────────────────────────────────── */
        .problem { background: var(--hintergrund); }
        .section-label {
            font-size: 0.8125rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: var(--blau); margin-bottom: 0.75rem;
        }
        .section-title {
            font-size: clamp(1.5rem, 3vw, 2.25rem);
            font-weight: 800; letter-spacing: -0.02em;
            margin-bottom: 1rem;
        }
        .section-lead {
            font-size: 1.0625rem; color: var(--text-hell);
            margin-bottom: 3rem;
        }
        .problem-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.25rem;
        }
        .problem-card {
            background: var(--weiss);
            border: 1px solid var(--border);
            border-radius: 0.875rem;
            padding: 1.5rem;
        }
        .problem-icon {
            font-size: 1.75rem; margin-bottom: 0.75rem;
        }
        .problem-card h3 {
            font-size: 1rem; font-weight: 700;
            margin-bottom: 0.375rem;
        }
        .problem-card p { font-size: 0.9rem; color: var(--text-hell); }

        /* ── Lösung / Features ──────────────────────────────────────── */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .feature-card {
            border: 1px solid var(--border);
            border-radius: 0.875rem;
            padding: 1.75rem;
            transition: box-shadow 0.2s;
        }
        .feature-card:hover { box-shadow: 0 8px 24px rgba(37,99,235,0.1); border-color: #bfdbfe; }
        .feature-icon {
            width: 2.75rem; height: 2.75rem;
            background: var(--blau-hell);
            border-radius: 0.625rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        .feature-card h3 {
            font-size: 1.0625rem; font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .feature-card p { font-size: 0.9rem; color: var(--text-hell); }

        /* ── Für wen ────────────────────────────────────────────────── */
        .fuer-wen { background: var(--hintergrund); }
        .fuer-wen-inner {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        @media (max-width: 720px) { .fuer-wen-inner { grid-template-columns: 1fr; } }
        .check-list { list-style: none; display: flex; flex-direction: column; gap: 0.875rem; }
        .check-list li {
            display: flex; align-items: flex-start; gap: 0.75rem;
            font-size: 0.9375rem;
        }
        .check-list .check {
            width: 1.25rem; height: 1.25rem;
            background: var(--gruen-hell); color: var(--gruen);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.6875rem; font-weight: 700;
            flex-shrink: 0; margin-top: 0.1rem;
        }
        .nicht-list li .check { background: #fef2f2; color: #dc2626; }
        .fuer-wen-zahlen {
            display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
        }
        .zahl-card {
            background: var(--weiss);
            border: 1px solid var(--border);
            border-radius: 0.875rem;
            padding: 1.25rem;
            text-align: center;
        }
        .zahl-card .zahl {
            font-size: 2rem; font-weight: 800; color: var(--blau);
            letter-spacing: -0.03em; line-height: 1;
            margin-bottom: 0.25rem;
        }
        .zahl-card .zahl-text { font-size: 0.8125rem; color: var(--text-hell); }

        /* ── Pilot-Angebot ──────────────────────────────────────────── */
        .pilot {
            background: var(--blau);
            color: var(--weiss);
        }
        .pilot .section-label { color: #bfdbfe; }
        .pilot .section-title { color: var(--weiss); }
        .pilot .section-lead { color: #bfdbfe; }
        .pilot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        .pilot-card {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 0.875rem;
            padding: 1.5rem;
            text-align: center;
        }
        .pilot-card .pilot-icon { font-size: 2rem; margin-bottom: 0.75rem; }
        .pilot-card h3 { font-size: 0.9375rem; font-weight: 700; margin-bottom: 0.375rem; }
        .pilot-card p { font-size: 0.8375rem; color: #bfdbfe; }
        .pilot-cta { text-align: center; }
        .btn-weiss {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: var(--weiss); color: var(--blau);
            padding: 0.875rem 2rem;
            border-radius: 0.625rem;
            font-size: 1rem; font-weight: 700;
            text-decoration: none;
            transition: transform 0.1s;
        }
        .btn-weiss:hover { transform: translateY(-2px); }

        /* ── Preis ──────────────────────────────────────────────────── */
        .preis-box {
            max-width: 680px; margin: 0 auto;
            background: var(--weiss);
            border: 2px solid var(--blau);
            border-radius: 1.25rem;
            padding: 2.5rem;
            text-align: center;
        }
        .preis-badge {
            display: inline-block;
            background: var(--blau); color: #fff;
            padding: 0.25rem 0.875rem;
            border-radius: 999px;
            font-size: 0.8125rem; font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .preis-zahl {
            font-size: 3.5rem; font-weight: 800;
            color: var(--blau); letter-spacing: -0.04em;
            line-height: 1;
        }
        .preis-einheit {
            font-size: 1rem; color: var(--text-hell);
            margin-bottom: 1.75rem;
        }
        .preis-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.625rem;
            text-align: left;
            margin-bottom: 2rem;
        }
        @media (max-width: 500px) { .preis-features { grid-template-columns: 1fr; } }
        .preis-feature {
            display: flex; align-items: center; gap: 0.5rem;
            font-size: 0.9rem;
        }
        .preis-feature .check { color: var(--gruen); font-size: 0.9rem; }
        .preis-hinweis { font-size: 0.8125rem; color: var(--text-hell); margin-top: 1.5rem; }

        /* ── Kontakt ────────────────────────────────────────────────── */
        .kontakt { background: var(--hintergrund); }
        .kontakt-box {
            max-width: 600px; margin: 0 auto;
            background: var(--weiss);
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            padding: 2.5rem;
        }
        .kontakt-box h3 {
            font-size: 1.25rem; font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .kontakt-box p { font-size: 0.9375rem; color: var(--text-hell); margin-bottom: 1.75rem; }
        .kontakt-form { display: flex; flex-direction: column; gap: 1rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 500px) { .form-row { grid-template-columns: 1fr; } }
        .form-field { display: flex; flex-direction: column; gap: 0.375rem; }
        .form-field label { font-size: 0.875rem; font-weight: 600; color: var(--text); }
        .form-field input,
        .form-field textarea,
        .form-field select {
            padding: 0.625rem 0.875rem;
            border: 1.5px solid var(--border);
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            font-family: inherit;
            color: var(--text);
            background: var(--weiss);
            outline: none;
            transition: border-color 0.15s;
            width: 100%;
        }
        .form-field input:focus,
        .form-field textarea:focus,
        .form-field select:focus { border-color: var(--blau); }
        .form-field textarea { resize: vertical; min-height: 100px; }
        .btn-form {
            background: var(--blau); color: #fff;
            padding: 0.75rem 1.5rem;
            border: none; border-radius: 0.625rem;
            font-size: 1rem; font-weight: 600;
            cursor: pointer; width: 100%;
            transition: background 0.15s;
        }
        .btn-form:hover { background: var(--blau-dunkel); }
        .form-hinweis { font-size: 0.8125rem; color: var(--text-hell); text-align: center; }
        .kontakt-alternativ {
            margin-top: 1.5rem; padding-top: 1.5rem;
            border-top: 1px solid var(--border);
            text-align: center; font-size: 0.875rem; color: var(--text-hell);
        }
        .kontakt-alternativ a { color: var(--blau); text-decoration: none; font-weight: 600; }

        /* ── Footer ─────────────────────────────────────────────────── */
        footer {
            background: var(--text);
            color: #9ca3af;
            padding: 2rem 1.5rem;
            text-align: center;
            font-size: 0.875rem;
        }
        footer a { color: #d1d5db; text-decoration: none; }
        footer a:hover { color: #fff; }
        .footer-inner {
            max-width: 1100px; margin: 0 auto;
            display: flex; flex-wrap: wrap;
            gap: 1rem;
            align-items: center;
            justify-content: space-between;
        }

        /* ── Formular-Erfolg ─────────────────────────────────────────── */
        .alert-erfolg {
            background: var(--gruen-hell);
            border: 1px solid #bbf7d0;
            color: #15803d;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            display: none;
        }

        @media (max-width: 640px) {
            section { padding: 3.5rem 1.25rem; }
            .hero { padding-top: 6rem; }
        }
    </style>
</head>
<body>

{{-- ── Topbar ──────────────────────────────────────────────────────────── --}}
<header class="topbar">
    <a href="#" class="topbar-logo">
        <div class="topbar-logo-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <span class="topbar-logo-name">Spitex</span>
    </a>
    <nav class="topbar-nav">
        <a href="#kontakt" class="btn-sekundaer" style="padding: 0.375rem 0.875rem; font-size: 0.875rem;">Pilot anfragen</a>
        <a href="{{ route('login') }}" class="btn-login">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            Login
        </a>
    </nav>
</header>

{{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
<section class="hero">
    <div class="container">
        <div class="hero-badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Jetzt Pilotpartner werden — erstes Jahr kostenlos
        </div>
        <h1>Die Spitex-Software für<br><span>kleine, private Organisationen</span></h1>
        <p class="hero-lead">
            Einsatzplanung, Klientenverwaltung und Abrechnung — alles in einer einfachen Software,
            die für Betriebe mit 2 bis 10 Mitarbeitenden gemacht ist.
        </p>
        <div class="hero-cta">
            <a href="#kontakt" class="btn-primaer">
                Pilot anfragen
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
            <a href="#loesungen" class="btn-sekundaer">
                Funktionen entdecken
            </a>
        </div>
        <div class="hero-trust">
            <span class="hero-trust-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Entwickelt in der Schweiz
            </span>
            <span class="hero-trust-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Daten bleiben bei Ihnen
            </span>
            <span class="hero-trust-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Kein IT-Wissen nötig
            </span>
            <span class="hero-trust-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Direkte Ansprechperson
            </span>
        </div>
    </div>
</section>

{{-- ── Problem ──────────────────────────────────────────────────────────── --}}
<section class="problem">
    <div class="container">
        <div class="container-sm">
            <p class="section-label">Das Problem</p>
            <h2 class="section-title">Bestehende Lösungen passen nicht</h2>
            <p class="section-lead">
                Die meisten Spitex-Softwares sind für grosse Organisationen gebaut — komplex, teuer und
                überladen. Kleine private Betriebe arbeiten deshalb mit Excel, Papier oder gar nichts.
            </p>
        </div>
        <div class="problem-grid">
            <div class="problem-card">
                <div class="problem-icon">📋</div>
                <h3>Einsatzplanung per Excel</h3>
                <p>Manuell gepflegte Tabellen, keine Übersicht über Doppelbelegungen, Änderungen per WhatsApp.</p>
            </div>
            <div class="problem-card">
                <div class="problem-icon">🗂️</div>
                <h3>Patientenakten auf Papier</h3>
                <p>Ordner im Büro, unleserliche Handnotizen, kein Zugriff von unterwegs.</p>
            </div>
            <div class="problem-card">
                <div class="problem-icon">🧾</div>
                <h3>Abrechnung als Zeitfresser</h3>
                <p>Stunden für manuelle Rechnungsstellung an Krankenkassen — jede Woche aufs Neue.</p>
            </div>
            <div class="problem-card">
                <div class="problem-icon">💸</div>
                <h3>Grosse Software, grosse Kosten</h3>
                <p>Marktführer kosten CHF 200–500 pro Monat — zu viel für einen 3-Personen-Betrieb.</p>
            </div>
        </div>
    </div>
</section>

{{-- ── Lösung ───────────────────────────────────────────────────────────── --}}
<section id="loesungen">
    <div class="container">
        <div class="container-sm">
            <p class="section-label">Die Lösung</p>
            <h2 class="section-title">Alles was Sie brauchen — nichts was Sie nicht brauchen</h2>
            <p class="section-lead">
                Spitex ist eine moderne, browserbasierte Software — kein App-Store, keine Installation,
                läuft auf dem Handy wie auf dem PC.
            </p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📅</div>
                <h3>Einsatzplanung</h3>
                <p>Klare Wochenübersicht, Touren planen, Doppelbelegungen sofort erkennen. Änderungen in Sekunden.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👤</div>
                <h3>Klientenverwaltung</h3>
                <p>Alle wichtigen Daten auf einen Blick: Diagnosen, Kontakte, Krankenkasse, Pflegestufe.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✅</div>
                <h3>Check-in / Check-out</h3>
                <p>Mitarbeitende scannen beim Klienten einen QR-Code — Zeit wird automatisch erfasst, kein Zettel nötig.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3>Rapporte & Dokumentation</h3>
                <p>Kurzer Einsatzbericht direkt nach dem Besuch erfassen — auch offline, synchronisiert sich automatisch.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🧾</div>
                <h3>Abrechnung</h3>
                <p>Leistungen werden automatisch erfasst, Rechnungen an Krankenkassen generiert. Später: MediData-Export.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Funktioniert auf dem Handy</h3>
                <p>Installierbar wie eine App, funktioniert auch ohne Internetverbindung. Keine App-Store-Kosten.</p>
            </div>
        </div>
    </div>
</section>

{{-- ── Für wen ──────────────────────────────────────────────────────────── --}}
<section class="fuer-wen">
    <div class="container">
        <div class="fuer-wen-inner">
            <div>
                <p class="section-label">Für wen</p>
                <h2 class="section-title">Gemacht für kleine, private Spitex-Betriebe</h2>
                <p style="font-size: 0.9375rem; color: var(--text-hell); margin-bottom: 1.75rem;">
                    Sie sind kein Grossbetrieb mit IT-Abteilung.
                    Sie brauchen eine Lösung, die am Tag 1 funktioniert —
                    ohne Schulung, ohne Berater, ohne Vertrag über 5 Jahre.
                </p>
                <ul class="check-list">
                    <li>
                        <span class="check">✓</span>
                        <span>Private Spitex ohne Leistungsvereinbarung mit der Gemeinde</span>
                    </li>
                    <li>
                        <span class="check">✓</span>
                        <span>2 bis 10 Mitarbeitende — Inhaber direkt im Betrieb</span>
                    </li>
                    <li>
                        <span class="check">✓</span>
                        <span>Region Zürich, Zug, Aargau</span>
                    </li>
                    <li>
                        <span class="check">✓</span>
                        <span>Heute mit Excel/Papier oder gar keiner Software</span>
                    </li>
                    <li>
                        <span class="check">✓</span>
                        <span>Wunsch nach mehr Überblick ohne Bürokratie</span>
                    </li>
                </ul>
            </div>
            <div class="fuer-wen-zahlen">
                <div class="zahl-card">
                    <div class="zahl">150+</div>
                    <div class="zahl-text">private Spitex-Orgs im Kanton Zürich</div>
                </div>
                <div class="zahl-card">
                    <div class="zahl">2–10</div>
                    <div class="zahl-text">Mitarbeitende — Ihr Betrieb, Ihre Regeln</div>
                </div>
                <div class="zahl-card">
                    <div class="zahl">−70%</div>
                    <div class="zahl-text">weniger Administrationszeit, geschätzt</div>
                </div>
                <div class="zahl-card">
                    <div class="zahl">1. Jahr</div>
                    <div class="zahl-text">für Pilotpartner vollständig kostenlos</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Pilot-Angebot ────────────────────────────────────────────────────── --}}
<section class="pilot" id="pilot">
    <div class="container">
        <div class="container-sm" style="text-align: center;">
            <p class="section-label">Pilotprogramm</p>
            <h2 class="section-title">Gemeinsam entwickeln — kostenlos im ersten Jahr</h2>
            <p class="section-lead" style="margin-bottom: 3rem;">
                Ich suche 1–2 Organisationen, die das Produkt mit mir zusammen aufbauen.
                Ihr Feedback prägt direkt, was als nächstes gebaut wird.
            </p>
        </div>
        <div class="pilot-grid">
            <div class="pilot-card">
                <div class="pilot-icon">🆓</div>
                <h3>Kostenlose Nutzung</h3>
                <p>12 Monate vollständig gratis. Kein Vertrag, keine Kreditkarte.</p>
            </div>
            <div class="pilot-card">
                <div class="pilot-icon">🎯</div>
                <h3>Direkter Einfluss</h3>
                <p>Sie entscheiden mit, was gebaut wird. ~1 Stunde Feedback pro Monat.</p>
            </div>
            <div class="pilot-card">
                <div class="pilot-icon">🤝</div>
                <h3>Persönliche Betreuung</h3>
                <p>Direkte Erreichbarkeit. Kein Ticketsystem, kein Callcenter.</p>
            </div>
            <div class="pilot-card">
                <div class="pilot-icon">🔒</div>
                <h3>Keine Verpflichtung</h3>
                <p>Nach dem Pilotjahr entscheiden Sie frei, ob Sie weitermachen.</p>
            </div>
        </div>
        <div class="pilot-cta">
            <a href="#kontakt" class="btn-weiss">
                Jetzt als Pilotpartner bewerben
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ── Preis ────────────────────────────────────────────────────────────── --}}
<section id="preis">
    <div class="container">
        <div class="container-sm" style="text-align: center; margin-bottom: 3rem;">
            <p class="section-label">Preis</p>
            <h2 class="section-title">Transparent und fair</h2>
        </div>
        <div class="preis-box">
            <span class="preis-badge">Nach dem Pilotjahr</span>
            <div class="preis-zahl">CHF 30–50</div>
            <div class="preis-einheit">pro Mitarbeitenden / Monat</div>
            <div class="preis-features">
                <div class="preis-feature"><span class="check">✓</span> Einsatzplanung</div>
                <div class="preis-feature"><span class="check">✓</span> Klientenverwaltung</div>
                <div class="preis-feature"><span class="check">✓</span> Check-in / Zeiterfassung</div>
                <div class="preis-feature"><span class="check">✓</span> Rapporte & Dokumentation</div>
                <div class="preis-feature"><span class="check">✓</span> Rechnungsstellung</div>
                <div class="preis-feature"><span class="check">✓</span> Mobile App (PWA)</div>
                <div class="preis-feature"><span class="check">✓</span> Datensicherung täglich</div>
                <div class="preis-feature"><span class="check">✓</span> Support direkt erreichbar</div>
            </div>
            <a href="#kontakt" class="btn-primaer" style="justify-content: center;">
                Kostenlos starten als Pilotpartner
            </a>
            <p class="preis-hinweis">
                Kein Setup-Fee. Keine Mindestlaufzeit. Preis wird gemeinsam mit Pilotpartnern festgelegt.
            </p>
        </div>
    </div>
</section>

{{-- ── Kontakt ──────────────────────────────────────────────────────────── --}}
<section class="kontakt" id="kontakt">
    <div class="container">
        <div class="container-sm" style="text-align: center; margin-bottom: 2.5rem;">
            <p class="section-label">Kontakt</p>
            <h2 class="section-title">Interesse? Schreiben Sie mir.</h2>
        </div>
        <div class="kontakt-box">
            <h3>Pilotpartner anfragen</h3>
            <p>Kurze Nachricht genügt — ich melde mich innerhalb von 24 Stunden.</p>

            <div id="form-erfolg" class="alert-erfolg">
                Vielen Dank! Ich melde mich innerhalb von 24 Stunden bei Ihnen.
            </div>

            <form class="kontakt-form" id="kontakt-form" method="POST" action="{{ route('kontakt.senden') }}">
                @csrf
                <div class="form-row">
                    <div class="form-field">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" placeholder="Maria Muster" required>
                    </div>
                    <div class="form-field">
                        <label for="organisation">Organisation</label>
                        <input type="text" id="organisation" name="organisation" placeholder="Spitex Adliswil GmbH">
                    </div>
                </div>
                <div class="form-field">
                    <label for="email">E-Mail-Adresse</label>
                    <input type="email" id="email" name="email" placeholder="name@beispiel.ch" required>
                </div>
                <div class="form-field">
                    <label for="mitarbeitende">Anzahl Mitarbeitende</label>
                    <select id="mitarbeitende" name="mitarbeitende">
                        <option value="">Bitte wählen</option>
                        <option>1–3</option>
                        <option>4–6</option>
                        <option>7–10</option>
                        <option>Mehr als 10</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="nachricht">Nachricht (optional)</label>
                    <textarea id="nachricht" name="nachricht" placeholder="Wie verwalten Sie heute Ihre Einsätze? Was ist Ihr grösstes Problem?"></textarea>
                </div>
                <button type="submit" class="btn-form">Nachricht senden</button>
                <p class="form-hinweis">Ihre Daten werden vertraulich behandelt und nicht weitergegeben.</p>
            </form>

            <div class="kontakt-alternativ">
                Lieber direkt? <a href="mailto:mhn@itjob.ch">mhn@itjob.ch</a>
                &nbsp;·&nbsp;
                Mathias Riedel, Adliswil
            </div>
        </div>
    </div>
</section>

{{-- ── Footer ───────────────────────────────────────────────────────────── --}}
<footer>
    <div class="footer-inner">
        <span>© {{ date('Y') }} Spitex — entwickelt von Mathias Riedel, Adliswil</span>
        <span>
            <a href="{{ route('login') }}">Login</a>
            &nbsp;·&nbsp;
            <a href="mailto:mhn@itjob.ch">Kontakt</a>
        </span>
    </div>
</footer>

<script>
document.getElementById('kontakt-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const btn  = form.querySelector('button[type="submit"]');
    btn.disabled    = true;
    btn.textContent = 'Wird gesendet…';

    try {
        const res = await fetch(form.action, {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type':     'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(new FormData(form)),
        });
        if (res.ok) {
            form.style.display = 'none';
            document.getElementById('form-erfolg').style.display = 'block';
        } else {
            btn.disabled    = false;
            btn.textContent = 'Nachricht senden';
            alert('Beim Senden ist ein Fehler aufgetreten. Bitte schreiben Sie direkt an mhn@itjob.ch');
        }
    } catch {
        btn.disabled    = false;
        btn.textContent = 'Nachricht senden';
        alert('Beim Senden ist ein Fehler aufgetreten. Bitte schreiben Sie direkt an mhn@itjob.ch');
    }
});
</script>

</body>
</html>
