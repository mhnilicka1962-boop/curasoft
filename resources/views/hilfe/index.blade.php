<x-layouts.app titel="Hilfe & Betriebsanweisung">
<div style="max-width: 800px;">

    <div class="seiten-kopf">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Hilfe & Betriebsanweisung</h1>
        <span class="text-hell text-klein">Stand: 24.02.2026</span>
    </div>

    {{-- Navigation --}}
    <div class="karte" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Kapitel</div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="#kap1" class="badge badge-primaer" style="text-decoration: none;">1 — Einloggen</a>
            <a href="#kap2" class="badge badge-grau" style="text-decoration: none;">2 — Tagesablauf Admin</a>
            <a href="#kap3" class="badge badge-grau" style="text-decoration: none;">3 — Neuer Patient</a>
            <a href="#kap4" class="badge badge-grau" style="text-decoration: none;">4 — Neuer Mitarbeiter</a>
            <a href="#kap5" class="badge badge-grau" style="text-decoration: none;">5 — Rapport</a>
            <a href="#kap6" class="badge badge-grau" style="text-decoration: none;">6 — Rechnung</a>
            <a href="#kap7" class="badge badge-grau" style="text-decoration: none;">7 — FAQ</a>
        </div>
    </div>

    {{-- Kapitel 1: Einloggen --}}
    <div class="karte" id="kap1" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 1 — Einloggen</div>

        <table class="tabelle" style="margin-bottom: 1.25rem;">
            <thead><tr><th>Methode</th><th>Für wen</th><th>Aufwand</th></tr></thead>
            <tbody>
                <tr><td><strong>Link per E-Mail</strong></td><td>Alle</td><td>Kein Passwort — empfohlen</td></tr>
                <tr><td><strong>Face ID / Fingerabdruck</strong></td><td>iPhone, Android</td><td>Einmalige Einrichtung</td></tr>
                <tr><td><strong>Passwort</strong></td><td>Alle</td><td>Klassisch</td></tr>
            </tbody>
        </table>

        <div class="abschnitt-trenn"></div>
        <div style="font-weight: 600; margin: 1rem 0 0.5rem;">1.1 Link per E-Mail (empfohlen)</div>
        <ol style="margin: 0 0 1rem 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Login-Seite öffnen → Tab <strong>„Link per E-Mail"</strong> ist vorausgewählt</li>
            <li>E-Mail-Adresse eingeben</li>
            <li><strong>„Login-Link senden"</strong> klicken</li>
            <li>E-Mail öffnen → auf den Link klicken → eingeloggt</li>
        </ol>
        <div class="info-box" style="margin-bottom: 1rem;">Der Link ist <strong>15 Minuten</strong> gültig.</div>

        <div class="abschnitt-trenn"></div>
        <div style="font-weight: 600; margin: 1rem 0 0.5rem;">1.2 Face ID einrichten (einmalig)</div>
        <ol style="margin: 0 0 0.75rem 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Normal einloggen (Magic Link oder Passwort)</li>
            <li>Oben rechts → <strong>Profil</strong> öffnen</li>
            <li><strong>„+ Passkey registrieren"</strong> tippen</li>
            <li>Dialog: <strong>„In Passwörter sichern"</strong> wählen <em>(nicht Authenticator!)</em></li>
            <li><strong>„Passkey hinzufügen"</strong> → Face ID bestätigen → fertig</li>
        </ol>
        <p class="text-hell text-klein" style="margin-bottom: 1rem;">Ab sofort: Login-Seite → Tab „Face ID" → Face ID → eingeloggt</p>

        <div class="abschnitt-trenn"></div>
        <div style="font-weight: 600; margin: 1rem 0 0.5rem;">1.3 App als Icon auf dem Homescreen</div>
        <p style="font-size: 0.9375rem; margin-bottom: 0.5rem;"><strong>iPhone (Safari):</strong> Teilen-Symbol → „Zum Home-Bildschirm" → Hinzufügen</p>
        <p style="font-size: 0.9375rem; margin-bottom: 1rem;"><strong>Android (Chrome):</strong> Menü → „Zum Startbildschirm hinzufügen"</p>

        <div class="abschnitt-trenn"></div>
        <div style="font-weight: 600; margin: 1rem 0 0.5rem;">1.4 Probleme</div>
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

    {{-- Kapitel 2: Tagesablauf --}}
    <div class="karte" id="kap2" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 2 — Tagesablauf Admin</div>

        <div style="font-weight: 600; margin-bottom: 0.5rem;">Morgens: Planen</div>
        <ol style="margin: 0 0 1rem 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Klienten → Patient → <strong>„+ Einsatz planen"</strong> klicken</li>
            <li>Mitarbeiter, Datum, Zeit, Leistungsart wählen → speichern</li>
            <li>Touren → <strong>„+ Tour erstellen"</strong> → Einsätze zuweisen</li>
        </ol>

        <div class="abschnitt-trenn"></div>
        <div style="font-weight: 600; margin: 1rem 0 0.5rem;">Abends: Nachkontrolle</div>
        <ul style="margin: 0 0 0 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Rapporte → Zwischenfälle (rotes Badge)</li>
            <li>Touren → Orange = zu spät; kein Check-in = nachfragen</li>
        </ul>
    </div>

    {{-- Kapitel 3: Neuer Patient --}}
    <div class="karte" id="kap3" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 3 — Neuer Patient</div>
        <ol style="margin: 0 0 0 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Klienten → <strong>„+ Neuer Klient"</strong></li>
            <li>Pflichtfelder: Vorname, Nachname, Region (Kanton)</li>
            <li>Danach ergänzen: Adresse, Krankenkasse, Pflegestufe, Arzt, Angehörige</li>
            <li>Ersten Einsatz planen (Pflegeplan → „+ Einsatz planen")</li>
        </ol>
    </div>

    {{-- Kapitel 4: Neuer Mitarbeiter --}}
    <div class="karte" id="kap4" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 4 — Neuer Mitarbeiter</div>
        <ol style="margin: 0 0 0 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Mitarbeiter → <strong>„+ Neuer Mitarbeiter"</strong></li>
            <li>E-Mail, Rolle (Pflege / Buchhaltung / Admin) eingeben</li>
            <li>Einladungs-Mail wird automatisch verschickt (48h gültig)</li>
            <li>Mitarbeiter setzt Passwort über Link in der Mail</li>
            <li>Im Mitarbeiter-Detail: Qualifikationen + Klienten-Zuweisung ergänzen</li>
        </ol>
    </div>

    {{-- Kapitel 5: Rapport --}}
    <div class="karte" id="kap5" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 5 — Rapport schreiben (Pflege)</div>
        <ol style="margin: 0 0 0 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Rapporte → <strong>„+ Neuer Rapport"</strong></li>
            <li>Klient wählen, Typ (Verlaufsbericht / Zwischenfall / Übergabe)</li>
            <li>Text eingeben → speichern</li>
        </ol>
    </div>

    {{-- Kapitel 6: Rechnung --}}
    <div class="karte" id="kap6" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 6 — Rechnung erstellen</div>
        <ol style="margin: 0 0 0 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Rechnungen → <strong>„+ Neue Rechnung"</strong></li>
            <li>Klient wählen → Leistungsperiode (Von–Bis)</li>
            <li>Einsätze werden automatisch einbezogen</li>
            <li>XML-Export: Rechnung öffnen → <strong>„XML exportieren"</strong></li>
        </ol>
    </div>

    {{-- Kapitel 7: FAQ --}}
    <div class="karte" id="kap7" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 7 — Häufige Fragen</div>
        <div style="display: flex; flex-direction: column; gap: 0.75rem; font-size: 0.9375rem;">
            <div><strong>Einsatz falsch zugewiesen?</strong><br>Einsätze → Einsatz öffnen → Bearbeiten → Mitarbeiter ändern</div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Tour-Einsatz entfernen?</strong><br>Touren → Tour-Detail → × beim Einsatz klicken</div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Klient abwesend (Spital)?</strong><br>Einsätze für diesen Zeitraum stornieren oder nicht anlegen</div>
        </div>
    </div>

</div>
</x-layouts.app>
