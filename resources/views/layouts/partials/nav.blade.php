<div class="nav-abschnitt">Übersicht</div>
<a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'aktiv' : '' }}">
    Dashboard
</a>
<a href="{{ route('hilfe') }}" class="nav-link {{ request()->routeIs('hilfe') ? 'aktiv' : '' }}">
    Hilfe
</a>
<a href="{{ route('schulung') }}" class="nav-link {{ request()->routeIs('schulung') ? 'aktiv' : '' }}">
    Schulung
</a>

<div class="nav-abschnitt">Kommunikation</div>
<a href="{{ route('chat.index') }}" class="nav-link nav-link-mit-badge {{ request()->routeIs('chat.*') ? 'aktiv' : '' }}">
    <span>💬 Chat</span>
    @if(!empty($navChatUngelesen) && $navChatUngelesen > 0)
        <span class="nav-badge">{{ $navChatUngelesen }}</span>
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
@if(auth()->user()?->rolle === 'admin')
<a href="{{ route('angehoerigenpflege.index') }}" class="nav-link {{ request()->routeIs('angehoerigenpflege.*') ? 'aktiv' : '' }}">
    Angehörigenpflege
</a>
<a href="{{ route('kalender.index') }}" class="nav-link {{ request()->routeIs('kalender.*') ? 'aktiv' : '' }}">
    Einsatzplanung 📅
</a>
<a href="{{ route('vertretung.index') }}" class="nav-link {{ request()->routeIs('vertretung.*') ? 'aktiv' : '' }}">
    Ferienvertretung
</a>
@endif
<a href="{{ route('rapporte.index') }}" class="nav-link {{ request()->routeIs('rapporte.*') ? 'aktiv' : '' }}">
    Rapporte
</a>
@if(auth()->user()?->rolle === 'pflege')
<a href="{{ route('personalabrechnung.show', [auth()->id(), 'monat' => now()->format('Y-m')]) }}"
   class="nav-link {{ request()->routeIs('personalabrechnung.*') ? 'aktiv' : '' }}">
    Meine Arbeitszeit
</a>
@endif

@if(auth()->user()?->rolle !== 'pflege')
<div class="nav-abschnitt">Abrechnung</div>
<a href="{{ route('rechnungen.index') }}" class="nav-link {{ request()->routeIs('rechnungen.*') ? 'aktiv' : '' }}">
    Rechnungen
</a>
<a href="{{ route('rechnungslauf.index') }}" class="nav-link {{ request()->routeIs('rechnungslauf.*') ? 'aktiv' : '' }}">
    Rechnungsläufe
</a>
<a href="{{ route('personalabrechnung.index') }}" class="nav-link {{ request()->routeIs('personalabrechnung.*') ? 'aktiv' : '' }}">
    Personalabrechnung
</a>
@endif

@if(auth()->user()?->rolle === 'admin')
<a href="{{ route('mitarbeiter.index') }}" class="nav-link {{ request()->routeIs('mitarbeiter.*') ? 'aktiv' : '' }}">
    Mitarbeitende
</a>

@php
$einstellungenAktiv = request()->routeIs('firma.*') || request()->routeIs('leistungsarten.*')
    || request()->routeIs('einsatzarten.*') || request()->routeIs('regionen.*')
    || request()->routeIs('aerzte.*') || request()->routeIs('krankenkassen.*')
    || request()->is('audit-log*');
@endphp
<details {{ $einstellungenAktiv ? 'open' : '' }} style="margin-top: 0.25rem;">
    <summary class="nav-abschnitt" style="cursor: pointer; user-select: none; list-style: none; display: flex; justify-content: space-between; align-items: center;">
        <span>Einstellungen</span>
        <span style="font-size: 1rem; opacity: 0.75;">▾</span>
    </summary>
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
    <a href="{{ route('audit.index') }}" class="nav-link {{ request()->is('audit-log*') ? 'aktiv' : '' }}">
        Audit-Log
    </a>
</details>
@endif

<div class="nav-abschnitt">Konto</div>
<a href="{{ route('profil.index') }}" class="nav-link {{ request()->routeIs('profil.*') ? 'aktiv' : '' }}">
    Mein Profil &amp; Passkey
</a>
