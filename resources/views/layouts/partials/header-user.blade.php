@auth
<div class="header-user">
    <a href="{{ route('profil.index') }}" class="header-user-link">
        {{ auth()->user()->vorname }} {{ auth()->user()->nachname }}
    </a>
    @if(auth()->user()->rolle === 'admin')
    @php $currentLayout = \App\Models\Organisation::first()?->theme_layout ?? config('theme.layout', 'sidebar'); @endphp
    <form method="POST" action="{{ route('layout.toggle') }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-sekundaer btn-compact" title="Layout wechseln: {{ $currentLayout === 'sidebar' ? 'Zu oben' : 'Zu links' }}">
            {{ $currentLayout === 'sidebar' ? '▤' : '▥' }}
        </button>
    </form>
    @endif
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-sekundaer btn-compact">
            Abmelden
        </button>
    </form>
</div>
@endauth
