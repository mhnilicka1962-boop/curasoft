<x-layouts.app titel="Schulung Pflege">

<div style="max-width: 860px;">

<div class="seiten-kopf">
    <h1>Schulung für Pflegepersonen</h1>
</div>

<div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-left: 4px solid #16a34a; border-radius: var(--cs-radius); padding: 0.875rem 1.25rem; margin-bottom: 1.75rem; font-size: 0.875rem;">
    Pflegende brauchen nur 3 Dinge: <strong>Einloggen → Vor-Ort → Rapport</strong>. Kein Admin-Zugang nötig. Alles läuft über das Handy.
</div>

{{-- 1 — Einloggen --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">1 — Einloggen</div>
    <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
        <table class="tabelle">
            <thead><tr><th>Methode</th><th>So geht's</th><th>Hinweis</th></tr></thead>
            <tbody>
                <tr>
                    <td><strong>Link per E-Mail</strong><br><span class="text-hell" style="font-size:0.8rem;">Empfohlen</span></td>
                    <td>Login-Seite öffnen → E-Mail eingeben → «Login-Link senden» → E-Mail öffnen → Link antippen → eingeloggt</td>
                    <td>Link ist 15 Minuten gültig. Kein Passwort nötig.</td>
                </tr>
                <tr>
                    <td><strong>Face ID / Fingerabdruck</strong><br><span class="text-hell" style="font-size:0.8rem;">Schnellste Methode</span></td>
                    <td>Einmalig einrichten: Normal einloggen → oben rechts Profil → «+ Passkey registrieren» → «In Passwörter sichern» wählen → Face ID bestätigen</td>
                    <td>Ab sofort: Login → Tab «Face ID» → fertig in 1 Sekunde</td>
                </tr>
                <tr>
                    <td><strong>App als Icon</strong></td>
                    <td><strong>iPhone (Safari):</strong> Teilen-Symbol → «Zum Home-Bildschirm» → Hinzufügen<br><strong>Android (Chrome):</strong> Menü → «Zum Startbildschirm hinzufügen»</td>
                    <td>Öffnet wie eine native App — kein Browser-UI</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: var(--cs-radius); padding: 0.5rem 0.75rem; font-size: 0.8125rem;">
        <strong>Problem Face ID zeigt «Authenticator»?</strong> → iOS Einstellungen → Passwörter → AutoFill → «Passwörter (Passkeys)» aktivieren
    </div>
</div>

{{-- 2 — Dashboard --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">2 — Dashboard & Einsätze heute</div>
    <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Nach dem Login erscheint das Dashboard mit allen heutigen Einsätzen der eingeloggten Pflegeperson.</div>
    <ul style="margin: 0 0 0.75rem 1.25rem; font-size: 0.875rem; line-height: 1.9;">
        <li>Dashboard zeigt alle Einsätze heute — Klient antippen → Vor-Ort-Seite öffnet sich</li>
        <li>Oder: Einsätze → Liste aller anstehenden Einsätze (nur eigene sichtbar)</li>
        <li>Tour-Ansicht: Touren → eigene Tour des Tages mit optimierter Reihenfolge</li>
    </ul>
    <a href="{{ route('dashboard') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Dashboard →</a>
</div>

{{-- 3 — Vor-Ort --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">3 — Vor-Ort-Seite (Herzstück für Pflege)</div>
    <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Optimiert für Mobile. Zeigt alle wichtigen Infos auf einen Blick und führt durch den Einsatz.</div>

    <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem;">Was die Seite zeigt:</div>
    <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
        <table class="tabelle">
            <thead><tr><th>Bereich</th><th>Inhalt</th></tr></thead>
            <tbody>
                <tr><td><strong>Header</strong></td><td>Klientenname, Datum, Leistungsart, geplante Zeit, Alter, Krankenkasse</td></tr>
                <tr><td><strong>Adresse</strong></td><td>Adresse mit direktem 📍 Maps-Link → Navigation starten</td></tr>
                <tr><td><strong>Telefon</strong></td><td>Klient-Telefon direkt anrufbar, Notfallnummer rot hervorgehoben 🚨</td></tr>
                <tr><td><strong>Diagnosen</strong></td><td>ICD-Codes und Bezeichnungen der aktuellen Diagnosen</td></tr>
                <tr><td><strong>⚠ Warnung</strong></td><td>Erscheint wenn ärztliche Verordnung abgelaufen ist — Admin informieren</td></tr>
                <tr><td><strong>👤 Helfer</strong></td><td>Name des pflegenden Angehörigen wenn vor Ort anwesend</td></tr>
            </tbody>
        </table>
    </div>

    <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem;">Ablauf Schritt für Schritt:</div>
    <ol style="margin: 0 0 0.75rem 1.25rem; font-size: 0.875rem; line-height: 1.9;">
        <li><strong>Check-in</strong> antippen — Zeit wird gespeichert, Einsatz gilt als gestartet</li>
        <li><strong>Leistungen</strong> erfassen — Checkboxen anklicken, Minuten bei Bedarf anpassen (+ / − Buttons)</li>
        <li><strong>Rapport schreiben</strong> — Stichworte diktieren → «✨ KI Bericht» → oder direkt eintippen/diktieren</li>
        <li><strong>Check-out</strong> antippen — Einsatz abgeschlossen, Zeit gespeichert</li>
    </ol>

    <div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.6rem 0.875rem; font-size: 0.875rem; margin-bottom: 0.75rem;">
        <strong>Tipp:</strong> KI versteht jede Sprache — einfach in der Muttersprache diktieren, der Bericht wird automatisch auf Deutsch geschrieben.
    </div>
    <a href="{{ route('einsaetze.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Einsätze →</a>
</div>

{{-- 4 — Meine Arbeitszeit --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">4 — Meine Arbeitszeit</div>
    <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Jede Pflegeperson sieht ihre eigenen Stunden — geplant vs. geleistet, pro Tag und Monat.</div>
    <ul style="margin: 0 0 0.75rem 1.25rem; font-size: 0.875rem; line-height: 1.9;">
        <li>Monat wählen → Übersicht aller Einsätze mit geplanter und geleisteter Zeit</li>
        <li>Rot = deutliche Abweichung → fehlenden Check-out im Einsatz nacherfassen</li>
        <li>PDF Zeitnachweis: Admin erstellt diesen unter Personalabrechnung → unterschreiben</li>
    </ul>
    <a href="{{ route('personalabrechnung.show', [auth()->id(), 'monat' => now()->format('Y-m')]) }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Meine Arbeitszeit →</a>
</div>

{{-- Tagesablauf --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Tagesablauf Pflege</div>
    <ol style="margin: 0 0 0 1.1rem; padding: 0; line-height: 2.2; font-size: 0.875rem;">
        <li>Dashboard → Einsätze heute</li>
        <li>Vor Ort → Check-in beim Klienten</li>
        <li>Leistungen erfassen (Checkliste)</li>
        <li>Rapport schreiben (KI hilft)</li>
        <li>Check-out → nächster Einsatz</li>
    </ol>
</div>

{{-- FAQ Pflege --}}
<div class="karte">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Häufige Fragen</div>
    @php
    $faq = [
        ['Wie logge ich mich das erste Mal ein?', 'Du erhältst automatisch einen Einladungslink per E-Mail — einfach anklicken, Passwort setzen, fertig.'],
        ['Kann ich das Handy verwenden?', 'Ja — die Vor-Ort-Ansicht ist für Mobile optimiert. Check-in, Rapport, Leistungen — alles geht am Telefon.'],
        ['Kann ich in meiner Sprache diktieren?', 'Ja — die KI versteht beliebige Sprachen (Serbisch, Albanisch, Portugiesisch usw.) und schreibt den Bericht immer auf Deutsch.'],
        ['Ich sehe einen Einsatz nicht — was tun?', 'Der Admin muss den Einsatz dir zuweisen. Bitte deinen Administrator, den Einsatz zu prüfen.'],
        ['Vergessener Check-out — was tun?', 'Einsatz öffnen → Check-out Zeit manuell nachtragen. Falls du keinen Zugriff hast, Admin informieren.'],
        ['Face ID zeigt «Authenticator» statt Passkey?', 'iOS Einstellungen → Passwörter → AutoFill → «Passwörter (Passkeys)» aktivieren.'],
    ];
    @endphp
    @foreach($faq as $i => $f)
    <div style="{{ $i < count($faq) - 1 ? 'border-bottom: 1px solid var(--cs-border);' : '' }} padding: 0.7rem 0;">
        <div class="text-fett text-klein">{{ $f[0] }}</div>
        <div class="text-klein text-hell" style="margin-top: 0.15rem; line-height: 1.5;">{{ $f[1] }}</div>
    </div>
    @endforeach
</div>

</div>

</x-layouts.app>
