@auth
<div class="header-user">
    <a href="{{ route('profil.index') }}" class="header-user-link">
        {{ auth()->user()->vorname }} {{ auth()->user()->nachname }}
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-sekundaer btn-compact">
            Abmelden
        </button>
    </form>
</div>
@endauth
