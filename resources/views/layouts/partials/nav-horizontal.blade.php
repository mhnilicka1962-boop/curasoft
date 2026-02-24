<a href="{{ route('dashboard') }}" class="topnav-link {{ request()->routeIs('dashboard') ? 'aktiv' : '' }}">Dashboard</a>
<a href="{{ route('nachrichten.index') }}" class="topnav-link {{ request()->routeIs('nachrichten.*') ? 'aktiv' : '' }}">Nachrichten</a>
<a href="{{ route('klienten.index') }}" class="topnav-link {{ request()->routeIs('klienten.*') ? 'aktiv' : '' }}">Klienten</a>
<a href="{{ route('einsaetze.index') }}" class="topnav-link {{ request()->routeIs('einsaetze.*') || request()->routeIs('checkin.*') || request()->routeIs('checkout.*') ? 'aktiv' : '' }}">Einsätze</a>
<a href="{{ route('touren.index') }}" class="topnav-link {{ request()->routeIs('touren.*') ? 'aktiv' : '' }}">Touren</a>
<a href="{{ route('rapporte.index') }}" class="topnav-link {{ request()->routeIs('rapporte.*') ? 'aktiv' : '' }}">Rapporte</a>
@if(in_array(auth()->user()?->rolle, ['admin', 'buchhaltung']))
<a href="{{ route('rechnungen.index') }}" class="topnav-link {{ request()->routeIs('rechnungen.*') ? 'aktiv' : '' }}">Rechnungen</a>
@endif
@if(auth()->user()?->rolle === 'admin')
<div class="topnav-dropdown">
    <a href="{{ route('firma.index') }}" class="topnav-link {{ request()->routeIs('firma.*') || request()->routeIs('leistungsarten.*') || request()->routeIs('einsatzarten.*') || request()->routeIs('regionen.*') || request()->routeIs('aerzte.*') || request()->routeIs('krankenkassen.*') || request()->routeIs('mitarbeiter.*') || request()->routeIs('audit.*') ? 'aktiv' : '' }}">Verwaltung ▾</a>
    <div class="topnav-dropdown-menu">
        <a href="{{ route('mitarbeiter.index') }}">Mitarbeitende</a>
        <div class="topnav-dropdown-trenn"></div>
        <a href="{{ route('firma.index') }}">Firma</a>
        <a href="{{ route('leistungsarten.index') }}">Leistungsarten</a>
        <a href="{{ route('einsatzarten.index') }}">Einsatzarten</a>
        <a href="{{ route('regionen.index') }}">Regionen</a>
        <a href="{{ route('aerzte.index') }}">Ärzte</a>
        <a href="{{ route('krankenkassen.index') }}">Krankenkassen</a>
        <div class="topnav-dropdown-trenn"></div>
        <a href="{{ route('audit.index') }}">Audit-Log</a>
    </div>
</div>
@endif
