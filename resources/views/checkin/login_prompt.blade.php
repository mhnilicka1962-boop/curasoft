<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Check-in — {{ $klient->vorname }} {{ $klient->nachname }}</title>
    @vite(['resources/css/app.css'])
    <style>
        body {
            background: #f3f4f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            font-family: var(--cs-schrift, sans-serif);
            margin: 0;
        }
        .checkin-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem 1.5rem;
            max-width: 360px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }
        .avatar {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            background: var(--cs-primaer-hell, #dbeafe);
            color: var(--cs-primaer, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0 auto 1rem;
        }
        .klient-name { font-size: 1.25rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem; }
        .klient-adresse { font-size: 0.875rem; color: #6b7280; margin-bottom: 1.5rem; }
        .checkin-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
        .hinweis { font-size: 0.9375rem; color: #374151; margin-bottom: 1.5rem; line-height: 1.5; }
    </style>
</head>
<body>
<div class="checkin-card">
    <div class="avatar">
        {{ strtoupper(substr($klient->vorname, 0, 1)) }}{{ strtoupper(substr($klient->nachname, 0, 1)) }}
    </div>
    <div class="klient-name">{{ $klient->vorname }} {{ $klient->nachname }}</div>
    <div class="klient-adresse">{{ $klient->adresse }}, {{ $klient->plz }} {{ $klient->ort }}</div>

    <div class="checkin-icon">🔐</div>
    <div class="hinweis">Bitte einloggen um den Check-in abzuschliessen.</div>

    <a href="{{ route('login') }}?redirect={{ urlencode(route('checkin.scan', $token)) }}"
       class="btn btn-primaer" style="width: 100%; justify-content: center; font-size: 1rem; padding: 0.875rem; display: flex;">
        Zum Login
    </a>
</div>
</body>
</html>
