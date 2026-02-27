<x-layouts.app titel="Hilfe & Betriebsanweisung">
<div style="max-width: 800px;">

    <div class="seiten-kopf">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Hilfe & Betriebsanweisung</h1>
        <span class="text-hell text-klein">Stand: 27.02.2026</span>
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
            <a href="#kap6b" class="badge badge-grau" style="text-decoration: none;">6b — Rechnungslauf</a>
            <a href="#kap7" class="badge badge-grau" style="text-decoration: none;">7 — FAQ</a>
        </div>
    </div>

    {{-- Kapitel 1: Einloggen --}}
    <div class="karte" id="kap1" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 1 — Einloggen</div>

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
        <ol style="margin: 0 0 1rem 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Rapporte → <strong>„+ Neuer Rapport"</strong></li>
            <li>Klient wählen, Typ (Verlaufsbericht / Zwischenfall / Medikament usw.)</li>
            <li>Bericht schreiben — drei Möglichkeiten:</li>
        </ol>

        <div class="tabelle-wrapper" style="margin-bottom: 1rem;">
        <table class="tabelle">
            <thead><tr><th>Methode</th><th>So geht's</th><th>Geeignet für</th></tr></thead>
            <tbody>
                <tr>
                    <td><strong>Direkt tippen</strong></td>
                    <td>Text im Bericht-Feld eingeben</td>
                    <td>Kurze Einträge</td>
                </tr>
                <tr>
                    <td><strong>Diktieren in Bericht</strong></td>
                    <td>🎙 <strong>„Direkt in Bericht diktieren"</strong> antippen → sprechen → Stop</td>
                    <td>Schnelle Bericht-Erfassung</td>
                </tr>
                <tr>
                    <td><strong>KI Bericht schreiben</strong></td>
                    <td>Stichworte oben diktieren oder tippen → <strong>„✨ KI Bericht schreiben"</strong> klicken → KI formuliert den Bericht</td>
                    <td>Ausformulierter Bericht aus Stichworten</td>
                </tr>
            </tbody>
        </table>
        </div>

        <div class="info-box" style="margin-bottom: 1rem;">
            Das Diktat funktioniert nur in <strong>Chrome, Edge oder Safari</strong> — nicht in Firefox.
        </div>

        <ol start="4" style="margin: 0 0 0 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Vertraulich-Häkchen setzen wenn nötig → <strong>Speichern</strong></li>
        </ol>
    </div>

    {{-- Kapitel 6: Rechnung --}}
    <div class="karte" id="kap6" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 6 — Rechnung erstellen</div>
        <ol style="margin: 0 0 1rem 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Rechnungen → <strong>„+ Neue Rechnung"</strong></li>
            <li>Klient wählen → Leistungsperiode (Von–Bis)</li>
            <li>Einsätze werden automatisch einbezogen</li>
            <li>Tarife bei Bedarf anpassen (nur im Status „Entwurf")</li>
            <li>Rechnung versenden → Status auf <strong>„Gesendet"</strong> setzen</li>
        </ol>

        <div class="abschnitt-trenn"></div>
        <div style="font-weight: 600; margin: 1rem 0 0.5rem;">Export-Optionen</div>
        <div class="tabelle-wrapper">
        <table class="tabelle">
            <thead><tr><th>Schaltfläche</th><th>Zweck</th><th>Format</th></tr></thead>
            <tbody>
                <tr>
                    <td><strong>📋 XML</strong></td>
                    <td>Elektronische Abrechnung an Krankenkasse (MediData)</td>
                    <td>XML 450.100 (Schweizer Standard)</td>
                </tr>
                <tr>
                    <td><strong>📄 PDF</strong></td>
                    <td>Druckbare Rechnung für Klient oder Ablage</td>
                    <td>PDF, A4</td>
                </tr>
                <tr>
                    <td><strong>→ Bexio</strong></td>
                    <td>Rechnung in Bexio-Buchhaltung übertragen (Erstsync)</td>
                    <td>Nur wenn Bexio konfiguriert</td>
                </tr>
                <tr>
                    <td><strong>✓ Bexio bezahlt?</strong></td>
                    <td>Zahlungsstatus von Bexio abrufen — setzt Status automatisch auf «Bezahlt»</td>
                    <td>Erscheint nach Bexio-Sync</td>
                </tr>
            </tbody>
        </table>
        </div>

        <div class="info-box" style="margin-top: 1rem;">
            Die Tarife in der Rechnung sind <strong>eingefroren</strong> — Tarifänderungen betreffen nur neue Rechnungen. Das PDF kann jederzeit erneut heruntergeladen werden.
        </div>
    </div>

    {{-- Kapitel 6b: Rechnungslauf --}}
    <div class="karte" id="kap6b" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 6b — Rechnungslauf (Monatliche Sammelabrechnung)</div>
        <p style="font-size: 0.9375rem; margin-bottom: 1rem;">Mit dem Rechnungslauf werden alle Klienten einer Periode auf einmal abgerechnet — statt einzeln.</p>

        <ol style="margin: 0 0 1rem 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Rechnungsläufe → <strong>„+ Neuer Lauf"</strong></li>
            <li>Periode wählen (Von–Bis) → Vorschau zeigt alle Klienten mit Einsätzen</li>
            <li>Klienten abwählen die nicht abgerechnet werden sollen → <strong>„Lauf erstellen"</strong></li>
            <li>Im Lauf-Detail: Rechnungen versenden (Email / Post / XML an KK)</li>
        </ol>

        <div class="abschnitt-trenn"></div>
        <div style="font-weight: 600; margin: 1rem 0 0.5rem;">Versandwege</div>
        <div class="tabelle-wrapper" style="margin-bottom: 1rem;">
        <table class="tabelle">
            <thead><tr><th>Schaltfläche</th><th>Was passiert</th></tr></thead>
            <tbody>
                <tr><td><strong>Email versenden</strong></td><td>PDF-Rechnung per Mail an Klient (wenn E-Mail hinterlegt)</td></tr>
                <tr><td><strong>Sammel-PDF drucken</strong></td><td>Alle Post-Rechnungen zusammen → Druckdialog öffnet sich</td></tr>
                <tr><td><strong>XML-ZIP KVG</strong></td><td>Alle XML 450.100-Dateien als ZIP für MediData-Upload</td></tr>
                <tr><td><strong>✓ Bexio Zahlungsabgleich</strong></td><td>Prüft alle Rechnungen des Laufs in Bexio auf Zahlungseingang</td></tr>
            </tbody>
        </table>
        </div>

        <div class="info-box">
            Solange keine Rechnung im Status «Gesendet» oder «Bezahlt» ist, kann der ganze Lauf storniert werden — alle Einsätze werden dabei zurückgesetzt.
        </div>
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
            <div class="abschnitt-trenn"></div>
            <div><strong>Rechnung ist in Bexio bezahlt, aber in Spitex noch «Gesendet»?</strong><br>Rechnung öffnen → <strong>„✓ Bexio bezahlt?"</strong> klicken — oder im Rechnungslauf auf <strong>„✓ Bexio Zahlungsabgleich"</strong></div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Rechnung wurde doppelt erstellt?</strong><br>Den neueren Eintrag öffnen → «Stornieren» — die Einsätze werden dabei nicht zurückgesetzt (nur beim Lauf-Storno)</div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Rechnungslauf zeigt Klient nicht in der Vorschau?</strong><br>Mögliche Gründe: keine abgeschlossenen Einsätze (kein Check-out), alle Einsätze bereits verrechnet, oder der Klient ist inaktiv</div>
        </div>
    </div>

</div>
</x-layouts.app>
