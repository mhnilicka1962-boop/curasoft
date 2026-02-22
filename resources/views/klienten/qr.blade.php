<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR-Code — {{ $klient->vorname }} {{ $klient->nachname }}</title>
    @vite(['resources/css/app.css'])
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body style="background: white; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; padding: 2rem; font-family: var(--cs-schrift);">

<div style="text-align: center; max-width: 320px;">

    <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: #6b7280; margin-bottom: 0.5rem;">
        {{ config('theme.app_name', 'CuraSoft') }} · Check-in
    </div>

    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem;">
        {{ $klient->vorname }} {{ $klient->nachname }}
    </div>
    <div style="font-size: 0.9375rem; color: #6b7280; margin-bottom: 1.5rem;">
        {{ $klient->adresse }}<br>{{ $klient->plz }} {{ $klient->ort }}
    </div>

    {{-- QR-Code --}}
    <div id="qrcode" style="display: inline-block; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 0.75rem; margin-bottom: 1.5rem;"></div>

    <div style="font-size: 0.8125rem; color: #6b7280; margin-bottom: 2rem;">
        Diesen Code beim Klienten anbringen.<br>
        Pflegende scannen ihn beim Eintreffen.
    </div>

    <div class="no-print" style="display: flex; gap: 0.75rem; justify-content: center;">
        <button onclick="window.print()" class="btn btn-primaer">🖨️ Drucken</button>
        <a href="{{ route('klienten.show', $klient) }}" class="btn btn-sekundaer">← Zurück</a>
    </div>
</div>

<script>
new QRCode(document.getElementById('qrcode'), {
    text: '{{ route('checkin.scan', $klient->qr_token) }}',
    width: 200,
    height: 200,
    colorDark: '#1f2937',
    colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M
});
</script>
</body>
</html>
