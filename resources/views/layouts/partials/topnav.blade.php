<div class="topnav-wrapper">

    {{-- Top-Navigation --}}
    <header class="topnav-header">
        <div class="topnav-logo">
            <x-logo />
        </div>
        <nav class="topnav-nav">
            @include('layouts.partials.nav-horizontal')
        </nav>
        <div class="header-rechts">
            @include('layouts.partials.header-user')
        </div>
        <button class="mobile-menu-btn btn btn-sekundaer" onclick="toggleTopNav()">☰</button>
    </header>

    {{-- Mobile Nav-Dropdown --}}
    <nav class="topnav-mobile" id="topnavMobile">
        @include('layouts.partials.nav')
    </nav>

    {{-- Hauptinhalt --}}
    <main class="topnav-inhalt">
        <div class="seiteninhalt">
            @include('layouts.partials.alerts')
            {{ $slot }}
        </div>
    </main>
</div>
