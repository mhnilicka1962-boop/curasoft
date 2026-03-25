<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; color: #333; font-size: 14px; line-height: 1.6;">

<p>Guten Tag {{ $benutzer->vorname }} {{ $benutzer->nachname }}</p>

<p>Im Anhang finden Sie Ihren Zeitnachweis für den abgelaufenen Monat.</p>

<p>Bei Fragen wenden Sie sich bitte direkt an die Administration.</p>

<p style="margin-top: 2rem;">
    Freundliche Grüsse<br>
    {{ $org->name }}
</p>

</body>
</html>
