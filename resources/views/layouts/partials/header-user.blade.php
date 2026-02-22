@auth
<div style="display: flex; align-items: center; gap: 1rem;">
    <a href="{{ route('dashboard') }}" style="font-size: 0.875rem; color: var(--cs-text-hell); text-decoration: none;">
        {{ auth()->user()->vorname }} {{ auth()->user()->nachname }}
    </a>
    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
        @csrf
        <button type="submit" class="btn btn-sekundaer" style="padding: 0.375rem 0.75rem; font-size: 0.8125rem;">
            Abmelden
        </button>
    </form>
</div>
@endauth
