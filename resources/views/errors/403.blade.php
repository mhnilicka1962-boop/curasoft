<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kein Zugriff — {{ config('theme.app_name', 'Spitex') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="background-color: var(--cs-hintergrund); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem;">
<div style="text-align: center; max-width: 400px;">
    <div style="font-size: 3rem; margin-bottom: 1rem;">🔒</div>
    <h1 style="font-size: 1.25rem; font-weight: 700; color: var(--cs-text); margin: 0 0 0.5rem;">Kein Zugriff</h1>
    <p style="color: var(--cs-text-hell); font-size: 0.875rem; margin: 0 0 1.5rem;">
        Sie haben keine Berechtigung für diesen Bereich.<br>
        Wenden Sie sich an Ihren Administrator.
    </p>
    <a href="{{ route('dashboard') }}" class="btn btn-primaer">Zum Dashboard</a>
</div>
</body>
</html>
