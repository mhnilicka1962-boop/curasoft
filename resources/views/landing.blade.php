<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Spitex-Software für die ganze Schweiz. Kantonsübergreifende Einsatzplanung, automatische KK-Abrechnung nach XML 450.100, Bexio-Integration — für alle Spitex-Dienste.">
    <title>Spitex — Die Software für alle Spitex-Dienste in der Schweiz</title>
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
            --rot:         #dc2626;
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
        .topbar-logo-name { font-weight: 700; font-size: 1rem; letter-spacing: -0.01em; }
        .topbar-nav { display: flex; align-items: center; gap: 1rem; }
        .topbar-link {
            font-size: 0.875rem; color: var(--text-hell);
            text-decoration: none; font-weight: 500;
        }
        .topbar-link:hover { color: var(--blau); }
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
            max-width: 640px;
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

        /* ── Kantone Banner ─────────────────────────────────────────── */
        .kantone-banner {
            background: var(--hintergrund);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 1.25rem 1.5rem;
            text-align: center;
        }
        .kantone-label {
            font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: var(--text-hell); margin-bottom: 0.875rem;
        }
        .kantone-liste {
            display: flex; flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
        }
        .kanton-pill {
            background: var(--weiss);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 0.2rem 0.625rem;
            font-size: 0.8rem; font-weight: 600;
            color: var(--text);
        }
        .kanton-pill.aktiv {
            background: var(--blau-hell);
            border-color: #bfdbfe;
            color: var(--blau-dunkel);
        }

        /* ── Problem ────────────────────────────────────────────────── */
        .problem { background: var(--weiss); }
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
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
        }
        .problem-card {
            background: var(--hintergrund);
            border: 1px solid var(--border);
            border-radius: 0.875rem;
            padding: 1.5rem;
        }
        .problem-icon { font-size: 1.75rem; margin-bottom: 0.75rem; }
        .problem-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: 0.375rem; }
        .problem-card p { font-size: 0.9rem; color: var(--text-hell); }

        /* ── Features ───────────────────────────────────────────────── */
        .features { background: var(--hintergrund); }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .feature-card {
            background: var(--weiss);
            border: 1px solid var(--border);
            border-radius: 0.875rem;
            padding: 1.75rem;
            transition: box-shadow 0.2s;
        }
        .feature-card:hover { box-shadow: 0 8px 24px rgba(37,99,235,0.1); border-color: #bfdbfe; }
        .feature-card.highlight {
            border-color: var(--blau);
            background: var(--blau-hell);
        }
        .feature-icon {
            width: 2.75rem; height: 2.75rem;
            background: var(--blau-hell);
            border-radius: 0.625rem;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        .feature-card.highlight .feature-icon { background: #dbeafe; }
        .feature-card h3 { font-size: 1.0625rem; font-weight: 700; margin-bottom: 0.5rem; }
        .feature-card p { font-size: 0.9rem; color: var(--text-hell); }
        .feature-tag {
            display: inline-block;
            background: var(--blau); color: #fff;
            font-size: 0.7rem; font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            margin-bottom: 0.625rem;
            text-transform: uppercase; letter-spacing: 0.05em;
        }

        /* ── Kantonsübergreifend Sektion ─────────────────────────────── */
        .kantons-sektion { background: var(--weiss); }
        .kantons-inner {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }
        @media (max-width: 768px) { .kantons-inner { grid-template-columns: 1fr; gap: 2rem; } }
        .tarif-beispiel {
            background: var(--hintergrund);
            border: 1px solid var(--border);
            border-radius: 1rem;
            overflow: hidden;
        }
        .tarif-kopf {
            background: var(--blau);
            color: #fff;
            padding: 0.875rem 1.25rem;
            font-size: 0.875rem; font-weight: 700;
        }
        .tarif-zeile {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid var(--border);
            font-size: 0.875rem;
        }
        .tarif-zeile:last-child { border-bottom: none; }
        .tarif-kanton { font-weight: 600; }
        .tarif-wert { color: var(--blau); font-weight: 700; }
        .tarif-einheit { color: var(--text-hell); font-size: 0.8rem; }

        /* ── Schnittstellen ─────────────────────────────────────────── */
        .schnittstellen { background: var(--hintergrund); }
        .schnitt-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.25rem;
        }
        .schnitt-card {
            background: var(--weiss);
            border: 1px solid var(--border);
            border-radius: 0.875rem;
            padding: 1.5rem;
        }
        .schnitt-logo {
            height: 2rem;
            display: flex; align-items: center;
            margin-bottom: 1rem;
        }
        .schnitt-logo-text {
            font-size: 1.125rem; font-weight: 800;
            letter-spacing: -0.02em;
        }
        .schnitt-card h3 { font-size: 1rem; font-weight: 700; margin-bottom: 0.375rem; }
        .schnitt-card p { font-size: 0.875rem; color: var(--text-hell); }
        .schnitt-badge {
            display: inline-block;
            background: var(--gruen-hell); color: var(--gruen);
            border: 1px solid #bbf7d0;
            font-size: 0.75rem; font-weight: 700;
            padding: 0.15rem 0.5rem;
            border-radius: 999px;
            margin-top: 0.75rem;
        }

        /* ── Für wen ────────────────────────────────────────────────── */
        .fuer-wen { background: var(--weiss); }
        .fuer-wen-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        .fuer-wen-card {
            border: 1px solid var(--border);
            border-radius: 0.875rem;
            padding: 1.75rem;
        }
        .fuer-wen-card h3 { font-size: 1.0625rem; font-weight: 700; margin-bottom: 1rem; }
        .check-list { list-style: none; display: flex; flex-direction: column; gap: 0.625rem; }
        .check-list li { display: flex; align-items: flex-start; gap: 0.625rem; font-size: 0.9rem; }
        .check {
            width: 1.125rem; height: 1.125rem;
            background: var(--gruen-hell); color: var(--gruen);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.6rem; font-weight: 700;
            flex-shrink: 0; margin-top: 0.15rem;
        }

        /* ── Zahlen ─────────────────────────────────────────────────── */
        .zahlen { background: var(--blau); color: #fff; }
        .zahlen-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 2rem;
            text-align: center;
        }
        .zahl-gross {
            font-size: 3rem; font-weight: 800;
            letter-spacing: -0.04em; line-height: 1;
            color: #fff; margin-bottom: 0.375rem;
        }
        .zahl-text { font-size: 0.875rem; color: #bfdbfe; }

        /* ── Pilot ──────────────────────────────────────────────────── */
        .pilot { background: var(--hintergrund); }
        .pilot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.25rem;
            margin-bottom: 3rem;
        }
        .pilot-card {
            background: var(--weiss);
            border: 1px solid var(--border);
            border-radius: 0.875rem;
            padding: 1.5rem;
            text-align: center;
        }
        .pilot-card .pilot-icon { font-size: 2rem; margin-bottom: 0.75rem; }
        .pilot-card h3 { font-size: 0.9375rem; font-weight: 700; margin-bottom: 0.375rem; }
        .pilot-card p { font-size: 0.85rem; color: var(--text-hell); }
        .pilot-cta { text-align: center; }

        /* ── Preis ──────────────────────────────────────────────────── */
        .preis { background: var(--weiss); }
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
        .preis-einheit { font-size: 1rem; color: var(--text-hell); margin-bottom: 1.75rem; }
        .preis-features {
            display: grid; grid-template-columns: 1fr 1fr;
            gap: 0.625rem; text-align: left; margin-bottom: 2rem;
        }
        @media (max-width: 500px) { .preis-features { grid-template-columns: 1fr; } }
        .preis-feature { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; }
        .preis-feature .check { color: var(--gruen); font-size: 0.9rem; background: none; width: auto; height: auto; }
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
        .kontakt-box h3 { font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; }
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
        .alert-erfolg {
            background: var(--gruen-hell);
            border: 1px solid #bbf7d0;
            color: #15803d;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.9375rem;
            display: none;
        }

        /* ── Footer ─────────────────────────────────────────────────── */
        footer {
            background: var(--text); color: #9ca3af;
            padding: 2rem 1.5rem;
            text-align: center; font-size: 0.875rem;
        }
        footer a { color: #d1d5db; text-decoration: none; }
        footer a:hover { color: #fff; }
        .footer-inner {
            max-width: 1100px; margin: 0 auto;
            display: flex; flex-wrap: wrap;
            gap: 1rem; align-items: center; justify-content: space-between;
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
        <a href="#loesungen" class="topbar-link" style="display:none;">Funktionen</a>
        <a href="#schnittstellen" class="topbar-link" style="display:none;">Schnittstellen</a>
        <a href="#kontakt" class="btn-sekundaer" style="padding: 0.375rem 0.875rem; font-size: 0.875rem;">Demo anfragen</a>
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
            Für alle Spitex-Dienste — kantonal und kantonsübergreifend
        </div>
        <h1>Die Spitex-Software<br><span>für die ganze Schweiz</span></h1>
        <p class="hero-lead">
            Einsatzplanung, Klientenverwaltung und KK-Abrechnung — mit automatischen Kantonstarifen,
            XML 450.100-Export und Bexio-Integration. Einfach. Sicher. Schweizweit.
        </p>
        <div class="hero-cta">
            <a href="#kontakt" class="btn-primaer">
                Demo anfragen
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
                Alle 26 Kantone unterstützt
            </span>
            <span class="hero-trust-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                KVG + VVG Abrechnung
            </span>
            <span class="hero-trust-item">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Daten bleiben in der Schweiz
            </span>
        </div>
    </div>
</section>

{{-- ── Kantone Banner ───────────────────────────────────────────────────── --}}
<div class="kantone-banner">
    <p class="kantone-label">Unterstützte Kantone — alle 26</p>
    <div class="kantone-liste">
        @foreach(['AG','AI','AR','BE','BL','BS','FR','GE','GL','GR','JU','LU','NE','NW','OW','SG','SH','SO','SZ','TG','TI','UR','VD','VS','ZG','ZH'] as $kanton)
            <span class="kanton-pill">{{ $kanton }}</span>
        @endforeach
    </div>
</div>

{{-- ── Problem ──────────────────────────────────────────────────────────── --}}
<section class="problem">
    <div class="container">
        <div class="container-sm">
            <p class="section-label">Das Problem</p>
            <h2 class="section-title">Kantonsübergreifend arbeiten — und trotzdem korrekt abrechnen</h2>
            <p class="section-lead">
                Spitex-Dienste, die in mehreren Kantonen tätig sind, stehen vor einem echten Problem:
                Jeder Kanton hat eigene Tarife, eigene Regeln — und bestehende Software macht das zur Qual.
            </p>
        </div>
        <div class="problem-grid">
            <div class="problem-card">
                <div class="problem-icon">🗺️</div>
                <h3>Verschiedene Kantonstariife</h3>
                <p>AG, ZH, BE, ZG — jeder Kanton hat eigene Ansätze für KVG und VVG. Manuelle Pflege ist fehleranfällig.</p>
            </div>
            <div class="problem-card">
                <div class="problem-icon">🧾</div>
                <h3>KK-Abrechnung als Zeitfresser</h3>
                <p>XML 450.100 manuell erstellen, Übermittlung per Post oder Portal — Stunden pro Woche für Verwaltung.</p>
            </div>
            <div class="problem-card">
                <div class="problem-icon">📋</div>
                <h3>Einsatzplanung ohne Übersicht</h3>
                <p>Wer ist wann wo? Touren über Kantonsgrenzen hinweg lassen sich in Excel kaum planen.</p>
            </div>
            <div class="problem-card">
                <div class="problem-icon">💸</div>
                <h3>Teure Branchenlösungen</h3>
                <p>Die grossen Anbieter kosten CHF 300–800/Monat, sind überladen und brauchen Wochen Einführung.</p>
            </div>
        </div>
    </div>
</section>

{{-- ── Lösung / Features ─────────────────────────────────────────────────── --}}
<section id="loesungen" class="features">
    <div class="container">
        <div class="container-sm">
            <p class="section-label">Die Lösung</p>
            <h2 class="section-title">Alles was Sie brauchen — für jeden Kanton korrekt</h2>
            <p class="section-lead">
                Eine moderne, browserbasierte Software — läuft auf dem Handy wie auf dem PC,
                ohne Installation, ohne IT-Abteilung.
            </p>
        </div>
        <div class="features-grid">
            <div class="feature-card highlight">
                <span class="feature-tag">Kernfunktion</span>
                <div class="feature-icon">🗺️</div>
                <h3>Kantonsübergreifende Abrechnung</h3>
                <p>Automatische Tarife pro Kanton — AG, ZH, BE und alle weiteren. Pro Leistungsart, historisiert. Immer der korrekte Ansatz zur richtigen Zeit.</p>
            </div>
            <div class="feature-card highlight">
                <span class="feature-tag">Kernfunktion</span>
                <div class="feature-icon">📄</div>
                <h3>XML 450.100 Export</h3>
                <p>Rechnungen an Krankenkassen auf Knopfdruck als XML 450.100 exportieren — der Schweizer Standard für KVG-Abrechnungen.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📅</div>
                <h3>Einsatzplanung & Touren</h3>
                <p>Wochenübersicht, Tourenplanung, Doppelbelegungen sofort erkennen. Wiederkehrende Einsätze mit einem Klick erstellen.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👤</div>
                <h3>Klientenverwaltung</h3>
                <p>Diagnosen (ICD-10), Krankenkasse, Pflegestufe (BESA/RAI-HC), Ärzte, Angehörige — alles auf einen Blick.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">✅</div>
                <h3>Check-in / Check-out</h3>
                <p>Mitarbeitende checken per QR-Code beim Klienten ein — Zeit wird automatisch erfasst, GPS-Standort optional.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Mobile App (PWA)</h3>
                <p>Installierbar wie eine App, funktioniert auch offline. Kein App-Store, keine Zusatzkosten.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📝</div>
                <h3>Rapporte & Dokumentation</h3>
                <p>Pflegebericht direkt nach dem Einsatz erfassen — auch unterwegs, synchronisiert sich automatisch.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3>Sicherer Login</h3>
                <p>Face ID, Windows Hello oder Magic Link — kein Passwort nötig. Passwort-Variante ebenfalls verfügbar.</p>
            </div>
        </div>
    </div>
</section>

{{-- ── Kantonsübergreifend ───────────────────────────────────────────────── --}}
<section class="kantons-sektion">
    <div class="container">
        <div class="kantons-inner">
            <div>
                <p class="section-label">Kantonstariife</p>
                <h2 class="section-title">Der richtige Tarif — automatisch, pro Kanton</h2>
                <p style="font-size: 0.9375rem; color: var(--text-hell); margin-bottom: 1.5rem;">
                    Jeder Kanton hat eigene Ansätze für Grundpflege, Haushaltshilfe und weitere Leistungen.
                    Das System kennt alle Tarife und wendet automatisch den richtigen an — abhängig davon,
                    wo der Einsatz stattfindet.
                </p>
                <ul class="check-list">
                    <li>
                        <span class="check">✓</span>
                        <span>Tarife pro Kanton und Leistungsart hinterlegt</span>
                    </li>
                    <li>
                        <span class="check">✓</span>
                        <span>Historisiert — Tarifänderungen werden lückenlos dokumentiert</span>
                    </li>
                    <li>
                        <span class="check">✓</span>
                        <span>KVG und VVG getrennt verwaltet</span>
                    </li>
                    <li>
                        <span class="check">✓</span>
                        <span>Neuer Kanton in Minuten einrichten — Tarife werden automatisch vorausgefüllt</span>
                    </li>
                    <li>
                        <span class="check">✓</span>
                        <span>Beitrag des Klienten separat hinterlegt und historisiert</span>
                    </li>
                </ul>
            </div>
            <div class="tarif-beispiel">
                <div class="tarif-kopf">Grundpflege — Ansatz KVG (Beispielwerte)</div>
                <div class="tarif-zeile">
                    <span class="tarif-kanton">🏔 Kanton AG</span>
                    <span>
                        <span class="tarif-wert">CHF 54.60</span>
                        <span class="tarif-einheit"> / h</span>
                    </span>
                </div>
                <div class="tarif-zeile">
                    <span class="tarif-kanton">🏙 Kanton ZH</span>
                    <span>
                        <span class="tarif-wert">CHF 58.30</span>
                        <span class="tarif-einheit"> / h</span>
                    </span>
                </div>
                <div class="tarif-zeile">
                    <span class="tarif-kanton">🏔 Kanton BE</span>
                    <span>
                        <span class="tarif-wert">CHF 52.80</span>
                        <span class="tarif-einheit"> / h</span>
                    </span>
                </div>
                <div class="tarif-zeile">
                    <span class="tarif-kanton">🏙 Kanton ZG</span>
                    <span>
                        <span class="tarif-wert">CHF 56.00</span>
                        <span class="tarif-einheit"> / h</span>
                    </span>
                </div>
                <div class="tarif-zeile" style="background: var(--blau-hell);">
                    <span style="font-size: 0.8rem; color: var(--text-hell);">+ alle weiteren 22 Kantone</span>
                    <span style="font-size: 0.8rem; color: var(--blau); font-weight: 600;">→ frei konfigurierbar</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Schnittstellen ────────────────────────────────────────────────────── --}}
<section id="schnittstellen" class="schnittstellen">
    <div class="container">
        <div class="container-sm" style="margin-bottom: 3rem;">
            <p class="section-label">Schnittstellen</p>
            <h2 class="section-title">Integriert in Ihre bestehende Infrastruktur</h2>
            <p class="section-lead">
                Keine Insellösung — Spitex verbindet sich mit den Systemen, die Sie bereits nutzen.
            </p>
        </div>
        <div class="schnitt-grid">
            <div class="schnitt-card">
                <div class="schnitt-logo">
                    <span class="schnitt-logo-text" style="color: var(--blau);">XML 450.100</span>
                </div>
                <h3>Krankenkassen-Abrechnung</h3>
                <p>Export im Schweizer Standard XML 450.100 für KVG-Leistungen — kompatibel mit allen Schweizer Krankenkassen.</p>
                <span class="schnitt-badge">✓ Verfügbar</span>
            </div>
            <div class="schnitt-card">
                <div class="schnitt-logo">
                    <span class="schnitt-logo-text" style="color: #6366f1;">MediData</span>
                </div>
                <h3>Elektronische Übermittlung</h3>
                <p>Direkte Übermittlung an Krankenkassen via MediData — dem Schweizer Standard-Übermittlungskanal.</p>
                <span class="schnitt-badge" style="background: #fefce8; color: #ca8a04; border-color: #fef08a;">→ In Entwicklung</span>
            </div>
            <div class="schnitt-card">
                <div class="schnitt-logo">
                    <span class="schnitt-logo-text" style="color: #7c3aed;">bexio</span>
                </div>
                <h3>Bexio Buchhaltung</h3>
                <p>Kontakte und Rechnungen werden automatisch mit Bexio synchronisiert — keine doppelte Datenpflege.</p>
                <span class="schnitt-badge">✓ Verfügbar</span>
            </div>
            <div class="schnitt-card">
                <div class="schnitt-logo">
                    <span class="schnitt-logo-text" style="color: #059669;">QR / GPS</span>
                </div>
                <h3>Check-in System</h3>
                <p>Mitarbeitende checken per QR-Code oder GPS beim Klienten ein — vollständig mobil, auch offline.</p>
                <span class="schnitt-badge">✓ Verfügbar</span>
            </div>
        </div>
    </div>
</section>

{{-- ── Für wen ──────────────────────────────────────────────────────────── --}}
<section class="fuer-wen">
    <div class="container">
        <div class="container-sm" style="margin-bottom: 3rem;">
            <p class="section-label">Für wen</p>
            <h2 class="section-title">Für jeden Spitex-Dienst in der Schweiz</h2>
            <p class="section-lead">Egal ob kleiner Privatbetrieb oder kantonsübergreifende Organisation — die Software passt sich an.</p>
        </div>
        <div class="fuer-wen-grid">
            <div class="fuer-wen-card">
                <h3>🏠 Kleine private Spitex</h3>
                <ul class="check-list">
                    <li><span class="check">✓</span><span>2 bis 10 Mitarbeitende</span></li>
                    <li><span class="check">✓</span><span>Inhaber direkt im Betrieb</span></li>
                    <li><span class="check">✓</span><span>Heute mit Excel oder Papier</span></li>
                    <li><span class="check">✓</span><span>Einfacher Einstieg, sofort nutzbar</span></li>
                </ul>
            </div>
            <div class="fuer-wen-card">
                <h3>🏢 Mittlere Spitex-Organisationen</h3>
                <ul class="check-list">
                    <li><span class="check">✓</span><span>10 bis 50 Mitarbeitende</span></li>
                    <li><span class="check">✓</span><span>Mehrere Einsatzgebiete</span></li>
                    <li><span class="check">✓</span><span>Strukturierte Tourenplanung</span></li>
                    <li><span class="check">✓</span><span>Rollen für Pflege & Buchhaltung</span></li>
                </ul>
            </div>
            <div class="fuer-wen-card">
                <h3>🗺️ Kantonsübergreifende Dienste</h3>
                <ul class="check-list">
                    <li><span class="check">✓</span><span>Tätig in mehreren Kantonen</span></li>
                    <li><span class="check">✓</span><span>Automatische Kantonstariife</span></li>
                    <li><span class="check">✓</span><span>Korrekte Abrechnung je Kanton</span></li>
                    <li><span class="check">✓</span><span>Alle 26 Kantone unterstützt</span></li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- ── Zahlen ────────────────────────────────────────────────────────────── --}}
<section class="zahlen">
    <div class="container">
        <div class="zahlen-grid">
            <div>
                <div class="zahl-gross">700+</div>
                <div class="zahl-text">Spitex-Organisationen in der Schweiz</div>
            </div>
            <div>
                <div class="zahl-gross">26</div>
                <div class="zahl-text">Kantone — alle unterstützt</div>
            </div>
            <div>
                <div class="zahl-gross">−70%</div>
                <div class="zahl-text">weniger Administrationsaufwand</div>
            </div>
            <div>
                <div class="zahl-gross">1. Jahr</div>
                <div class="zahl-text">für Pilotpartner kostenlos</div>
            </div>
        </div>
    </div>
</section>

{{-- ── Pilot ────────────────────────────────────────────────────────────── --}}
<section class="pilot">
    <div class="container">
        <div class="container-sm" style="text-align: center; margin-bottom: 3rem;">
            <p class="section-label">Pilotprogramm</p>
            <h2 class="section-title">Gemeinsam entwickeln — kostenlos im ersten Jahr</h2>
            <p class="section-lead">
                Wir suchen Spitex-Dienste, die das Produkt mit uns zusammen aufbauen.
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
                <p>Sie entscheiden mit, was gebaut wird. Ca. 1 Stunde Feedback pro Monat.</p>
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
            <a href="#kontakt" class="btn-primaer">
                Jetzt als Pilotpartner bewerben
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ── Preis ────────────────────────────────────────────────────────────── --}}
<section class="preis" id="preis">
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
                <div class="preis-feature"><span class="check">✓</span> Einsatzplanung & Touren</div>
                <div class="preis-feature"><span class="check">✓</span> Klientenverwaltung</div>
                <div class="preis-feature"><span class="check">✓</span> Alle 26 Kantone</div>
                <div class="preis-feature"><span class="check">✓</span> XML 450.100 Export</div>
                <div class="preis-feature"><span class="check">✓</span> Bexio-Integration</div>
                <div class="preis-feature"><span class="check">✓</span> Mobile App (PWA)</div>
                <div class="preis-feature"><span class="check">✓</span> Rapporte & Dokumentation</div>
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
            <h2 class="section-title">Interesse? Schreiben Sie uns.</h2>
        </div>
        <div class="kontakt-box">
            <h3>Demo oder Pilotpartner anfragen</h3>
            <p>Kurze Nachricht genügt — wir melden uns innerhalb von 24 Stunden.</p>

            <div id="form-erfolg" class="alert-erfolg">
                Vielen Dank! Wir melden uns innerhalb von 24 Stunden bei Ihnen.
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
                        <input type="text" id="organisation" name="organisation" placeholder="Spitex Bern AG">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-field">
                        <label for="email">E-Mail</label>
                        <input type="email" id="email" name="email" placeholder="name@beispiel.ch" required>
                    </div>
                    <div class="form-field">
                        <label for="kanton">Kanton(e)</label>
                        <input type="text" id="kanton" name="kanton" placeholder="z.B. AG, ZH, BE">
                    </div>
                </div>
                <div class="form-field">
                    <label for="mitarbeitende">Anzahl Mitarbeitende</label>
                    <select id="mitarbeitende" name="mitarbeitende">
                        <option value="">Bitte wählen</option>
                        <option>1–5</option>
                        <option>6–15</option>
                        <option>16–30</option>
                        <option>Mehr als 30</option>
                    </select>
                </div>
                <div class="form-field">
                    <label for="nachricht">Nachricht (optional)</label>
                    <textarea id="nachricht" name="nachricht" placeholder="In welchen Kantonen sind Sie tätig? Was ist Ihr grösstes Problem heute?"></textarea>
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
