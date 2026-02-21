<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titel ?? 'CuraSoft' }} — CuraSoft</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Kunden-Theme: überschreibt nur CSS-Variablen --}}
    @stack('theme')
</head>
<body>

<div class="layout-wrapper">

    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            CuraSoft
        </div>
        <nav class="sidebar-nav">
            <div class="nav-abschnitt">Übersicht</div>
            <a href="#" class="nav-link {{ request()->is('/') ? 'aktiv' : '' }}">
                Dashboard
            </a>

            <div class="nav-abschnitt">Betrieb</div>
            <a href="#" class="nav-link {{ request()->is('klienten*') ? 'aktiv' : '' }}">
                Klienten
            </a>
            <a href="#" class="nav-link {{ request()->is('einsaetze*') ? 'aktiv' : '' }}">
                Einsätze
            </a>

            <div class="nav-abschnitt">Abrechnung</div>
            <a href="#" class="nav-link {{ request()->is('rechnungen*') ? 'aktiv' : '' }}">
                Rechnungen
            </a>

            <div class="nav-abschnitt">Stammdaten</div>
            <a href="#" class="nav-link {{ request()->is('leistungen*') ? 'aktiv' : '' }}">
                Leistungen
            </a>
            <a href="#" class="nav-link {{ request()->is('benutzer*') ? 'aktiv' : '' }}">
                Benutzer
            </a>
        </nav>
    </aside>

    {{-- Hauptinhalt --}}
    <div class="hauptinhalt">

        {{-- Header --}}
        <header class="header">
            <button class="mobile-menu-btn btn btn-sekundaer" onclick="toggleSidebar()">
                ☰
            </button>
            <div style="font-size: 0.875rem; color: var(--cs-text-hell);">
                {{-- Hier später: Benutzer-Info, Organisation --}}
            </div>
        </header>

        {{-- Seiteninhalt --}}
        <main class="seiteninhalt">
            @if(isset($titel))
                <h1 class="seiten-titel">{{ $titel }}</h1>
            @endif

            @if(session('erfolg'))
                <div class="alert alert-erfolg">{{ session('erfolg') }}</div>
            @endif
            @if(session('fehler'))
                <div class="alert alert-fehler">{{ session('fehler') }}</div>
            @endif

            {{ $slot }}
        </main>

    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('offen');
}
</script>

</body>
</html>
