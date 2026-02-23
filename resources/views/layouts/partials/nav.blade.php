<div class="nav-abschnitt">Übersicht</div>
<a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'aktiv' : '' }}">
    Dashboard
</a>

<div class="nav-abschnitt">Kommunikation</div>
<a href="{{ route('nachrichten.index') }}" class="nav-link nav-link-mit-badge {{ request()->routeIs('nachrichten.*') ? 'aktiv' : '' }}">
    <span>Nachrichten</span>
    @if(!empty($navNachrichtenUngelesen) && $navNachrichtenUngelesen > 0)
        <span class="nav-badge">{{ $navNachrichtenUngelesen }}</span>
    @endif
</a>

<div class="nav-abschnitt">Betrieb</div>
<a href="{{ route('klienten.index') }}" class="nav-link {{ request()->routeIs('klienten.*') ? 'aktiv' : '' }}">
    Klienten
</a>
<a href="{{ route('einsaetze.index') }}" class="nav-link {{ request()->routeIs('einsaetze.*') || request()->routeIs('checkin.*') || request()->routeIs('checkout.*') ? 'aktiv' : '' }}">
    Einsätze
</a>
<a href="{{ route('touren.index') }}" class="nav-link {{ request()->routeIs('touren.*') ? 'aktiv' : '' }}">
    Tourenplanung
</a>
<a href="{{ route('rapporte.index') }}" class="nav-link {{ request()->routeIs('rapporte.*') ? 'aktiv' : '' }}">
    Rapporte
</a>

<div class="nav-abschnitt">Abrechnung</div>
<a href="{{ route('rechnungen.index') }}" class="nav-link {{ request()->routeIs('rechnungen.*') ? 'aktiv' : '' }}">
    Rechnungen
</a>

@if(auth()->user()?->rolle === 'admin')
<div class="nav-abschnitt">Stammdaten</div>
<a href="{{ route('firma.index') }}" class="nav-link {{ request()->routeIs('firma.*') ? 'aktiv' : '' }}">
    Firma
</a>
<a href="{{ route('leistungsarten.index') }}" class="nav-link {{ request()->routeIs('leistungsarten.*') ? 'aktiv' : '' }}">
    Leistungsarten
</a>
<a href="{{ route('einsatzarten.index') }}" class="nav-link {{ request()->routeIs('einsatzarten.*') ? 'aktiv' : '' }}">
    Einsatzarten
</a>
<a href="{{ route('regionen.index') }}" class="nav-link {{ request()->routeIs('regionen.*') ? 'aktiv' : '' }}">
    Regionen
</a>
<a href="{{ route('aerzte.index') }}" class="nav-link {{ request()->routeIs('aerzte.*') ? 'aktiv' : '' }}">
    Ärzte
</a>
<a href="{{ route('krankenkassen.index') }}" class="nav-link {{ request()->routeIs('krankenkassen.*') ? 'aktiv' : '' }}">
    Krankenkassen
</a>

<a href="{{ route('mitarbeiter.index') }}" class="nav-link {{ request()->routeIs('mitarbeiter.*') ? 'aktiv' : '' }}">
    Mitarbeitende
</a>

<div class="nav-abschnitt">System</div>
<a href="{{ route('audit.index') }}" class="nav-link {{ request()->is('audit-log*') ? 'aktiv' : '' }}">
    Audit-Log
</a>
@endif
