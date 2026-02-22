<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmelden — {{ config('theme.app_name', 'CuraSoft') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--cs-hintergrund); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;">

<div style="width: 100%; max-width: 400px;">

    {{-- Logo / Titel --}}
    <div style="text-align: center; margin-bottom: 2rem;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; background-color: var(--cs-primaer); border-radius: 0.75rem; margin-bottom: 1rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--cs-text); margin: 0 0 0.25rem;">{{ config('theme.app_name', 'CuraSoft') }}</h1>
        <p style="color: var(--cs-text-hell); font-size: 0.875rem; margin: 0;">Pflegemanagement-Software</p>
    </div>

    {{-- Login-Karte --}}
    <div class="karte" style="padding: 2rem;">
        <h2 style="font-size: 1.125rem; font-weight: 600; color: var(--cs-text); margin: 0 0 1.5rem;">Anmelden</h2>

        {{-- Fehlermeldung --}}
        @if ($errors->any())
            <div class="alert alert-fehler" style="margin-bottom: 1.25rem;">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Session-Nachricht --}}
        @if (session('status'))
            <div class="alert alert-erfolg" style="margin-bottom: 1.25rem;">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- E-Mail --}}
            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="email">E-Mail-Adresse</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="feld"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="name@beispiel.ch"
                    style="{{ $errors->has('email') ? 'border-color: var(--cs-fehler);' : '' }}"
                >
            </div>

            {{-- Passwort --}}
            <div style="margin-bottom: 1.25rem;">
                <label class="feld-label" for="password">Passwort</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="feld"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                >
            </div>

            {{-- Angemeldet bleiben --}}
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--cs-text); cursor: pointer;">
                    <input type="checkbox" name="remember" id="remember" style="width: 1rem; height: 1rem; accent-color: var(--cs-primaer);">
                    Angemeldet bleiben
                </label>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-primaer" style="width: 100%; justify-content: center; padding: 0.625rem 1rem; font-size: 0.9375rem;">
                Anmelden
            </button>
        </form>
    </div>

    <p style="text-align: center; margin-top: 1.5rem; font-size: 0.75rem; color: var(--cs-text-hell);">
        &copy; {{ date('Y') }} {{ config('theme.app_name', 'CuraSoft') }}
    </p>
</div>

</body>
</html>
