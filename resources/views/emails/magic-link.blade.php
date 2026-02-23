<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login-Link</title>
</head>
<body style="background:#f9fafb; font-family: system-ui, sans-serif; margin:0; padding: 2rem 1rem;">

<div style="max-width: 520px; margin: 0 auto; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb; overflow: hidden;">

    <div style="background: #2563eb; padding: 1.5rem 2rem;">
        <div style="font-size: 1.25rem; font-weight: 700; color: #ffffff;">{{ config('theme.app_name', 'Spitex') }}</div>
    </div>

    <div style="padding: 2rem;">
        <p style="margin: 0 0 1rem; font-size: 1rem; color: #1f2937;">
            Hallo,
        </p>
        <p style="margin: 0 0 1.5rem; font-size: 0.9375rem; color: #374151; line-height: 1.6;">
            Du hast einen Login-Link angefordert. Klicke auf den Button, um dich anzumelden.
        </p>

        <div style="text-align: center; margin: 2rem 0;">
            <a href="{{ $link }}"
               style="display: inline-block; background: #2563eb; color: #ffffff; text-decoration: none; padding: 0.75rem 2rem; border-radius: 6px; font-size: 1rem; font-weight: 600;">
                Jetzt einloggen
            </a>
        </div>

        <p style="margin: 1.5rem 0 0; font-size: 0.8125rem; color: #6b7280; line-height: 1.5;">
            Der Link ist {{ $gueltigMinuten }} Minuten gültig.<br>
            Falls du diesen Link nicht angefordert hast, ignorier diese E-Mail.
        </p>

        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; font-size: 0.75rem; color: #9ca3af;">
            <a href="{{ $link }}" style="color: #6b7280;">{{ $link }}</a>
        </div>
    </div>

</div>

</body>
</html>
