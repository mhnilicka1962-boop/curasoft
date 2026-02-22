<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort festlegen — {{ config('theme.app_name', 'CuraSoft') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--cs-hintergrund); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;">

<div style="width: 100%; max-width: 420px;">

    <div style="text-align: center; margin-bottom: 2rem;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 3rem; height: 3rem; background-color: var(--cs-primaer); border-radius: 0.75rem; margin-bottom: 1rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--cs-text); margin: 0 0 0.25rem;">{{ config('theme.app_name', 'CuraSoft') }}</h1>
        <p style="color: var(--cs-text-hell); font-size: 0.875rem; margin: 0;">Willkommen, {{ $benutzer->vorname }}!</p>
    </div>

    <div class="karte" style="padding: 2rem;">
        <h2 style="font-size: 1.125rem; font-weight: 600; margin: 0 0 0.375rem;">Passwort festlegen</h2>
        <p class="text-hell text-klein" style="margin: 0 0 1.5rem;">
            Wähle ein sicheres Passwort für deinen Zugang.
        </p>

        @if($errors->any())
            <div class="alert alert-fehler" style="margin-bottom: 1.25rem;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('einladung.store', $token) }}">
            @csrf

            <div style="margin-bottom: 1rem;">
                <label class="feld-label" for="password">Passwort</label>
                <input type="password" id="password" name="password" class="feld"
                    required autocomplete="new-password" placeholder="Mindestens 8 Zeichen">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label class="feld-label" for="password_confirmation">Passwort wiederholen</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="feld" required autocomplete="new-password" placeholder="Passwort bestätigen">
            </div>

            <button type="submit" class="btn btn-primaer" style="width: 100%; justify-content: center; padding: 0.625rem 1rem; font-size: 0.9375rem;">
                Passwort speichern & anmelden
            </button>
        </form>
    </div>

    <p style="text-align: center; margin-top: 1.5rem; font-size: 0.75rem; color: var(--cs-text-hell);">
        &copy; {{ date('Y') }} {{ config('theme.app_name', 'CuraSoft') }}
    </p>
</div>

</body>
</html>
