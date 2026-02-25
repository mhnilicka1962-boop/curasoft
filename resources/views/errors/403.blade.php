<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kein Zugriff — {{ config('theme.app_name', 'Spitex') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--cs-hintergrund); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;">
<div style="text-align: center; max-width: 420px;">
    <div style="font-size: 3rem; margin-bottom: 1rem;">🔒</div>
    <h1 style="font-size: 1.25rem; font-weight: 700; color: var(--cs-text); margin: 0 0 0.5rem;">Kein Zugriff</h1>
    <p style="color: var(--cs-text-hell); font-size: 0.875rem; margin: 0 0 1.25rem;">
        Sie haben keine Berechtigung für diesen Bereich.
    </p>

    @auth
    <div style="background: #fef9c3; border: 1px solid #fde047; border-radius: 8px; padding: 0.875rem 1rem; margin-bottom: 1.25rem; font-size: 0.8125rem; text-align: left;">
        <div style="font-weight: 600; margin-bottom: 0.25rem;">Eingeloggt als:</div>
        <div>{{ auth()->user()->vorname }} {{ auth()->user()->nachname }}</div>
        <div style="color: #666;">{{ auth()->user()->email }}</div>
        <div style="margin-top: 0.25rem;">
            Rolle: <strong>{{ auth()->user()->rolle }}</strong>
            @if(auth()->user()->rolle === 'pflege')
            — <span style="color: #b45309;">Pflegepersonal hat keinen Zugriff auf diesen Bereich.</span>
            @endif
        </div>
    </div>

    <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
        <a href="{{ route('dashboard') }}" class="btn btn-primaer">Zum Dashboard</a>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-sekundaer">Als anderer Benutzer einloggen</button>
        </form>
    </div>
    @else
    <a href="{{ route('login') }}" class="btn btn-primaer">Zum Login</a>
    @endauth
</div>
</body>
</html>
