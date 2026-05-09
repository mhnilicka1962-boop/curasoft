<x-layouts.app titel="Hilfe Pflege">
<div style="max-width: 860px;">

    <div class="seiten-kopf" style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Hilfe für Pflegepersonen</h1>
        <span class="text-hell text-klein">Stand: {{ date('d.m.Y') }}</span>
    </div>

    {{-- Schnellzugriff --}}
    <div class="karte" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Schnellzugriff</div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="#kap1"  class="badge badge-grau" style="text-decoration: none;">1 — Einloggen</a>
            <a href="#kap2"  class="badge badge-grau" style="text-decoration: none;">2 — Vor-Ort</a>
            <a href="#kap3"  class="badge badge-grau" style="text-decoration: none;">3 — Rapport</a>
            <a href="#kap4"  class="badge badge-grau" style="text-decoration: none;">4 — FAQ</a>
        </div>
    </div>

    {{-- Kapitel 1: Einloggen --}}
    <div class="karte" id="kap1" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">1 — Einloggen</div>
        <div class="tabelle-wrapper" style="margin-bottom: 1.25rem;">
            <table class="tabelle">
                <thead><tr><th>Methode</th><th>Für wen</th><th>Aufwand</th></tr></thead>
                <tbody>
                    <tr><td><strong>Link per E-Mail</strong></td><td>Alle</td><td>Kein Passwort — empfohlen</td></tr>
                    <tr><td><strong>Face ID / Fingerabdruck</strong></td><td>iPhone, Android</td><td>Einmalige Einrichtung</td></tr>
                    <tr><td><strong>Passwort</strong></td><td>Alle</td><td>Klassisch</td></tr>
                </tbody>
            </table>
        </div>

        <div style="font-weight: 600; margin-bottom: 0.5rem;">Link per E-Mail (empfohlen)</div>
        <ol style="margin: 0 0 1rem 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Login-Seite öffnen → Tab <strong>„Link per E-Mail"</strong> ist vorausgewählt</li>
            <li>E-Mail-Adresse eingeben</li>
            <li><strong>„Login-Link senden"</strong> klicken</li>
            <li>E-Mail öffnen → auf den Link klicken → eingeloggt</li>
        </ol>
        <div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.6rem 0.875rem; font-size: 0.875rem; margin-bottom: 1rem;">
            Der Link ist <strong>15 Minuten</strong> gültig.
        </div>

        <div style="font-weight: 600; margin-bottom: 0.5rem;">Face ID einrichten (einmalig)</div>
        <ol style="margin: 0 0 0.75rem 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Normal einloggen (Magic Link oder Passwort)</li>
            <li>Oben rechts → <strong>Profil</strong> öffnen</li>
            <li><strong>„+ Passkey registrieren"</strong> tippen</li>
            <li>Dialog: <strong>„In Passwörter sichern"</strong> wählen <em>(nicht Authenticator!)</em></li>
            <li><strong>„Passkey hinzufügen"</strong> → Face ID bestätigen → fertig</li>
        </ol>
        <p class="text-hell text-klein" style="margin-bottom: 1rem;">Ab sofort: Login-Seite → Tab „Face ID" → Face ID → eingeloggt in 1 Sekunde</p>

        <div style="font-weight: 600; margin-bottom: 0.5rem;">App als Icon auf dem Homescreen</div>
        <p style="font-size: 0.9375rem; margin-bottom: 0.5rem;"><strong>iPhone (Safari):</strong> Teilen-Symbol → „Zum Home-Bildschirm" → Hinzufügen</p>
        <p style="font-size: 0.9375rem; margin-bottom: 1rem;"><strong>Android (Chrome):</strong> Menü → „Zum Startbildschirm hinzufügen"</p>

        <div style="font-weight: 600; margin-bottom: 0.5rem;">Probleme</div>
        <div class="tabelle-wrapper">
            <table class="tabelle">
                <thead><tr><th>Problem</th><th>Lösung</th></tr></thead>
                <tbody>
                    <tr><td>Face ID zeigt „Authenticator"</td><td>iOS Einstellungen → Passwörter → AutoFill → „Passwörter (Passkeys)" aktivieren</td></tr>
                    <tr><td>Magic Link kommt nicht an</td><td>Spam-Ordner prüfen oder Admin fragen</td></tr>
                    <tr><td>„Zu viele Versuche"</td><td>15 Minuten warten</td></tr>
                    <tr><td>Passwort vergessen</td><td>Magic Link verwenden — kein Passwort nötig</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kapitel 2: Vor-Ort --}}
    <div class="karte" id="kap2" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">2 — Vor-Ort-Seite</div>
        <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Dashboard → Klient antippen → Vor-Ort-Seite öffnet sich. Alles läuft von hier aus.</div>

        <div style="font-weight: 600; margin-bottom: 0.5rem;">Was die Seite zeigt:</div>
        <div class="tabelle-wrapper" style="margin-bottom: 1rem;">
            <table class="tabelle">
                <thead><tr><th>Bereich</th><th>Inhalt</th></tr></thead>
                <tbody>
                    <tr><td><strong>Header</strong></td><td>Klientenname, Datum, Leistungsart, geplante Zeit, Alter, Krankenkasse</td></tr>
                    <tr><td><strong>Adresse</strong></td><td>Adresse mit direktem 📍 Maps-Link → Navigation starten</td></tr>
                    <tr><td><strong>Telefon</strong></td><td>Klient-Telefon direkt anrufbar, Notfallnummer rot hervorgehoben 🚨</td></tr>
                    <tr><td><strong>Diagnosen</strong></td><td>ICD-Codes und aktuelle Diagnosen</td></tr>
                    <tr><td><strong>⚠ Warnung</strong></td><td>Ärztliche Verordnung abgelaufen — bitte Admin informieren</td></tr>
                    <tr><td><strong>👤 Helfer</strong></td><td>Name des pflegenden Angehörigen wenn vor Ort anwesend</td></tr>
                </tbody>
            </table>
        </div>

        <div style="font-weight: 600; margin-bottom: 0.5rem;">Ablauf:</div>
        <ol style="margin: 0 0 0 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li><strong>Check-in</strong> antippen — Zeit wird gespeichert, Einsatz gilt als gestartet</li>
            <li><strong>Leistungen</strong> erfassen — Checkboxen anklicken, Minuten bei Bedarf anpassen (+ / − Buttons)</li>
            <li><strong>Rapport schreiben</strong> — Stichworte diktieren → «✨ KI Bericht» → oder direkt eintippen/diktieren</li>
            <li><strong>Check-out</strong> antippen — Einsatz abgeschlossen, Zeit gespeichert</li>
        </ol>
    </div>

    {{-- Kapitel 3: Rapport --}}
    <div class="karte" id="kap3" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">3 — Rapport schreiben</div>
        <ol style="margin: 0 0 1rem 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Rapporte → <strong>„+ Neuer Rapport"</strong> — oder direkt von der Vor-Ort-Seite</li>
            <li>Klient wählen, Typ (Pflege / Verlauf / Zwischenfall / Medikament usw.)</li>
            <li>Bericht schreiben — drei Möglichkeiten:</li>
        </ol>
        <div class="tabelle-wrapper" style="margin-bottom: 1rem;">
            <table class="tabelle">
                <thead><tr><th>Methode</th><th>So geht's</th><th>Geeignet für</th></tr></thead>
                <tbody>
                    <tr><td><strong>Direkt tippen</strong></td><td>Text im Bericht-Feld eingeben</td><td>Kurze Einträge</td></tr>
                    <tr><td><strong>Diktieren in Bericht</strong></td><td>🎙 <strong>„Direkt in Bericht diktieren"</strong> antippen → sprechen → Stop</td><td>Schnelle Bericht-Erfassung</td></tr>
                    <tr><td><strong>KI Bericht schreiben</strong></td><td>Stichworte oben diktieren oder tippen (in beliebiger Sprache) → <strong>„✨ KI Bericht schreiben"</strong> klicken</td><td>Ausformulierter deutscher Bericht — egal in welcher Sprache diktiert wurde</td></tr>
                </tbody>
            </table>
        </div>
        <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: var(--cs-radius); padding: 0.6rem 0.875rem; font-size: 0.875rem; margin-bottom: 1rem;">
            Das Diktat funktioniert nur in <strong>Chrome, Edge oder Safari</strong> — nicht in Firefox.
        </div>

        <div style="font-weight: 600; margin-bottom: 0.5rem;">Rapport-Typen:</div>
        <div class="tabelle-wrapper">
            <table class="tabelle">
                <thead><tr><th>Typ</th><th>Wann verwenden</th></tr></thead>
                <tbody>
                    <tr><td><strong>Pflege</strong></td><td>Allgemeiner Pflegebericht</td></tr>
                    <tr><td><strong>Verlauf</strong></td><td>Regelmässiger Verlaufsbericht nach Einsatz</td></tr>
                    <tr><td><strong>Information</strong></td><td>Allgemeine Information zum Klienten</td></tr>
                    <tr><td><strong>Zwischenfall</strong></td><td>Sturz, Notfall, ungewöhnliche Situation — Admin wird automatisch benachrichtigt</td></tr>
                    <tr><td><strong>Medikament</strong></td><td>Medikamentengabe oder -änderung dokumentieren</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kapitel 4: FAQ --}}
    <div class="karte" id="kap4">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">4 — Häufige Fragen</div>
        <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.9375rem;">
            <div><strong>Ich sehe einen Einsatz nicht?</strong><br><span class="text-hell">Der Admin muss den Einsatz dir zuweisen. Bitte deinen Administrator.</span></div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Vergessener Check-out?</strong><br><span class="text-hell">Einsatz öffnen → Check-out Zeit manuell nachtragen. Falls kein Zugriff: Admin informieren.</span></div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Kann ich in meiner Sprache diktieren?</strong><br><span class="text-hell">Ja — die KI versteht beliebige Sprachen und schreibt den Bericht immer auf Deutsch.</span></div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Face ID zeigt „Authenticator"?</strong><br><span class="text-hell">iOS Einstellungen → Passwörter → AutoFill → „Passwörter (Passkeys)" aktivieren.</span></div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Magic Link kommt nicht an?</strong><br><span class="text-hell">Spam-Ordner prüfen. Falls nichts da: Admin bitten, die Einladung erneut zu senden.</span></div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Was ist Meine Arbeitszeit?</strong><br><span class="text-hell">Zeigt deine eigenen Stunden — geplant vs. geleistet. Rot = deutliche Abweichung → fehlenden Check-out nacherfassen.</span></div>
        </div>
    </div>

</div>

@push('styles')
<style>
.abschnitt-trenn { border-top: 1px solid var(--cs-border); margin: 0; }
</style>
@endpush

</x-layouts.app>
