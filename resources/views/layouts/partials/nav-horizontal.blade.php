<a href="{{ url('/') }}" class="topnav-link {{ request()->is('/') ? 'aktiv' : '' }}">Dashboard</a>
<a href="{{ url('/klienten') }}" class="topnav-link {{ request()->is('klienten*') ? 'aktiv' : '' }}">Klienten</a>
<a href="{{ url('/einsaetze') }}" class="topnav-link {{ request()->is('einsaetze*') ? 'aktiv' : '' }}">Einsätze</a>
<a href="{{ url('/rechnungen') }}" class="topnav-link {{ request()->is('rechnungen*') ? 'aktiv' : '' }}">Rechnungen</a>
<a href="{{ url('/leistungen') }}" class="topnav-link {{ request()->is('leistungen*') ? 'aktiv' : '' }}">Leistungen</a>
<a href="{{ url('/benutzer') }}" class="topnav-link {{ request()->is('benutzer*') ? 'aktiv' : '' }}">Benutzer</a>
