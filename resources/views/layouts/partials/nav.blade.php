<div class="nav-abschnitt">Übersicht</div>
<a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'aktiv' : '' }}">
    Dashboard
</a>

<div class="nav-abschnitt">Betrieb</div>
<a href="{{ url('/klienten') }}" class="nav-link {{ request()->is('klienten*') ? 'aktiv' : '' }}">
    Klienten
</a>
<a href="{{ url('/einsaetze') }}" class="nav-link {{ request()->is('einsaetze*') ? 'aktiv' : '' }}">
    Einsätze
</a>

<div class="nav-abschnitt">Abrechnung</div>
<a href="{{ url('/rechnungen') }}" class="nav-link {{ request()->is('rechnungen*') ? 'aktiv' : '' }}">
    Rechnungen
</a>

<div class="nav-abschnitt">Stammdaten</div>
<a href="{{ url('/leistungen') }}" class="nav-link {{ request()->is('leistungen*') ? 'aktiv' : '' }}">
    Leistungen
</a>
<a href="{{ url('/benutzer') }}" class="nav-link {{ request()->is('benutzer*') ? 'aktiv' : '' }}">
    Benutzer
</a>
