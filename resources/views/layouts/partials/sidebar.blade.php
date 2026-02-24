<div class="layout-wrapper">

    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <x-logo />
        </div>
        <nav class="sidebar-nav">
            @include('layouts.partials.nav')
        </nav>
    </aside>

    {{-- Overlay für Mobile --}}
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    {{-- Hauptinhalt --}}
    <div class="hauptinhalt">
        <header class="header">
            <button class="mobile-menu-btn btn btn-sekundaer" onclick="toggleSidebar()">☰</button>
            <div class="header-logo-mobile">
                <x-logo />
            </div>
            <div class="header-rechts">
                @include('layouts.partials.header-user')
            </div>
        </header>

        <main class="seiteninhalt">
            @include('layouts.partials.alerts')
            {{ $slot }}
        </main>
    </div>
</div>
