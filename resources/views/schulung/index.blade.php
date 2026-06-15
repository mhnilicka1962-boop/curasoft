<x-layouts.app titel="Schulung">

<div style="max-width: 860px;">

<div class="seiten-kopf">
    <h1>Schulung & Einführung</h1>
    <a href="{{ route('hilfe') }}" class="btn btn-sekundaer">Hilfe & Scripts →</a>
</div>

{{-- Intro --}}
<div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-left: 4px solid var(--cs-primaer); border-radius: var(--cs-radius); padding: 1rem 1.25rem; margin-bottom: 1.75rem;">
    <div class="text-fett" style="margin-bottom: 0.3rem;">Wie funktioniert die Einführung?</div>
    <div class="text-klein" style="color: var(--cs-text-hell); line-height: 1.6;">
        Ein neuer Kunde ist in <strong>ca. 1 Stunde</strong> produktiv — diesem Lernpfad folgen, die Schritte in der Reihenfolge abarbeiten.
        Die Schritt-für-Schritt-Scripts in der <a href="{{ route('hilfe') }}" class="link-primaer">Hilfe-Seite</a> decken alle Alltagsprozesse ab.
    </div>
</div>

{{-- ═══ LERNPFAD ═══ --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 1rem;">Empfohlener Lernpfad — Neue Spitex</div>
    <div style="display: flex; flex-direction: column; gap: 0;">

        {{-- ── MODUL 1: Firma ── --}}
        <div style="display: flex; gap: 0; border-bottom: 1px solid var(--cs-border); padding: 0.875rem 0;">
            <div style="display: flex; flex-direction: column; align-items: center; margin-right: 1rem; flex-shrink: 0;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem;">1</div>
                <div style="width: 2px; background: var(--cs-border); flex: 1; margin-top: 4px;"></div>
            </div>
            <div style="flex: 1; padding-top: 0.3rem;">
                <div style="display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.4rem;">
                    <span class="text-fett">Firma einrichten</span>
                    <span class="badge badge-grau" style="font-size: 0.7rem;">15 min</span>
                </div>
                <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Stammdaten, Abrechnungsmodell und Regionen — einmalig, dann nie wieder.</div>

                <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
                    <table class="tabelle">
                        <thead><tr><th>Feld</th><th>Was eingeben</th><th>Hinweis</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Firmenname</strong></td><td>Offizieller Name der Spitex-Organisation</td><td>Pflichtfeld — erscheint auf Rechnungen</td></tr>
                            <tr><td><strong>Adresse / Telefon / E-Mail</strong></td><td>Kontaktdaten der Organisation</td><td>Erscheint auf Rechnungen und PDFs</td></tr>
                            <tr><td><strong>Logo</strong></td><td>PNG oder JPG hochladen</td><td>Erscheint auf Rechnungen und Rapportblatt</td></tr>
                            <tr><td><strong>ZSR-Nummer</strong></td><td>Zahlstellenregister-Nummer der Spitex</td><td>Pflicht für XML-Abrechnung mit Krankenkassen</td></tr>
                            <tr><td><strong>MwSt-Nummer</strong></td><td>UID-Nummer (falls MwSt-pflichtig)</td><td>Optional — nur wenn MwSt verrechnet wird</td></tr>
                        </tbody>
                    </table>
                </div>

                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: var(--cs-radius); padding: 0.75rem 1rem; margin-bottom: 0.75rem; font-size: 0.875rem;">
                    <div class="text-fett" style="margin-bottom: 0.4rem;">Abrechnungsmodell wählen — einmalig für die ganze Organisation</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 0.4rem;">
                        <div style="background: #fff; border: 1px solid #bfdbfe; border-radius: var(--cs-radius); padding: 0.5rem 0.75rem;">
                            <div class="text-fett" style="font-size: 0.8125rem;">Tiers garant</div>
                            <div class="text-hell" style="font-size: 0.8125rem; margin-top: 0.2rem;">Klient zahlt die gesamte Spitex-Rechnung. Er reicht das <strong>Rapportblatt</strong> (PDF aus CuraSoft) bei Krankenkasse und Gemeinde ein und erhält seinen Anteil zurückerstattet. Das Rapportblatt enthält alle nötigen Angaben — der Klient muss nichts selbst zusammenstellen.</div>
                        </div>
                        <div style="background: #fff; border: 1px solid #bfdbfe; border-radius: var(--cs-radius); padding: 0.5rem 0.75rem;">
                            <div class="text-fett" style="font-size: 0.8125rem;">Tiers payant</div>
                            <div class="text-hell" style="font-size: 0.8125rem; margin-top: 0.2rem;">Spitex rechnet direkt mit Krankenkasse und Gemeinde ab via XML 450.100 / MediData. Klient zahlt nur seinen Eigenanteil. Mehr Aufwand für die Spitex, aber einfacher für den Klienten. <strong>Auch hier ist das Rapportblatt wertvoll</strong> — als interne Dokumentation, als Beilage zur Patient-Rechnung und bei Rückfragen von Krankenkassen oder Gemeinden.</div>
                        </div>
                    </div>
                    <div class="text-hell" style="font-size: 0.8125rem;">Nur eine Methode pro Organisation möglich. Kann später geändert werden — bestehende Rechnungen bleiben unverändert.</div>
                </div>

                <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: var(--cs-radius); padding: 0.75rem 1rem; margin-bottom: 0.75rem; font-size: 0.875rem;">
                    <div class="text-fett" style="margin-bottom: 0.35rem;">⚠ Regionen / Kantone — Voraussetzung für alles</div>
                    <div class="text-hell" style="line-height: 1.6;">
                        Nur aufgeschaltete Regionen können bei Klienten verwendet werden. Ohne konfigurierte Region kann kein Klient erfasst werden — und ohne Klient keine Einsätze und keine Abrechnung.
                        <br><strong>Vorgehen:</strong> Stammdaten → Regionen → Kanton anlegen → Button <em>«Standard-Tarife anlegen»</em> klicken → fertig.
                    </div>
                    <div class="tabelle-wrapper" style="margin-top: 0.5rem;">
                        <table class="tabelle">
                            <thead><tr><th>Feld im Tarif</th><th>Bedeutung</th></tr></thead>
                            <tbody>
                                <tr><td><strong>Ansatz (CHF/h)</strong></td><td>Voller Stundentarif — was der Klient zahlt (inkl. KK-Anteil)</td></tr>
                                <tr><td><strong>KVG (CHF/h)</strong></td><td>Davon der KK-Anteil</td></tr>
                                <tr><td><strong>KVG Angehöriger (CHF/Tag)</strong></td><td>KK-Tagespauschale für pflegende Angehörige — wird bei Angehörigen-Einsätzen statt Stundentarif verwendet. Wird automatisch aus der Leistungsart übernommen wenn nicht manuell gesetzt.</td></tr>
                                <tr><td><strong>Ansatz akut</strong></td><td>Erhöhter Tarif bei akuter Pflege</td></tr>
                                <tr><td><strong>Verrechnung aktiv</strong></td><td>✓ = Einsätze dieser Leistungsart fliessen in die Rechnung ein. Ohne Häkchen → nicht verrechnet.</td></tr>
                                <tr><td><strong>Ansatz Minuten / Stunden / Tage</strong></td><td>Abrechnungseinheit</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <a href="{{ route('firma.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Firma einrichten →</a>
                    <a href="{{ route('regionen.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Regionen / Tarife →</a>
                    <a href="{{ route('leistungsarten.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Leistungsarten →</a>
                </div>
            </div>
        </div>

        {{-- ── MODUL 2: Mitarbeiter ── --}}
        <div style="display: flex; gap: 0; border-bottom: 1px solid var(--cs-border); padding: 0.875rem 0;">
            <div style="display: flex; flex-direction: column; align-items: center; margin-right: 1rem; flex-shrink: 0;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem;">2</div>
                <div style="width: 2px; background: var(--cs-border); flex: 1; margin-top: 4px;"></div>
            </div>
            <div style="flex: 1; padding-top: 0.3rem;">
                <div style="display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.4rem;">
                    <span class="text-fett">Mitarbeiter erfassen & einladen</span>
                    <span class="badge badge-grau" style="font-size: 0.7rem;">10 min</span>
                </div>
                <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Mitarbeitende müssen vor der Serieerstellung vorhanden sein — Pflege braucht eine zugewiesene Person.</div>

                <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
                    <table class="tabelle">
                        <thead><tr><th>Feld</th><th>Was eingeben</th><th>Hinweis</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Vorname / Name *</strong></td><td>Vollständiger Name</td><td>Pflichtfeld</td></tr>
                            <tr><td><strong>E-Mail (Login) *</strong></td><td>Geschäftliche E-Mail-Adresse</td><td>Pflichtfeld — wird für Login und Einladungsmail verwendet</td></tr>
                            <tr><td><strong>Rolle *</strong></td><td>Pflege / Buchhaltung / Admin</td><td>Bestimmt die Zugriffsrechte (siehe Rollenübersicht unten)</td></tr>
                            <tr><td><strong>Anstellungsart</strong></td><td>Fachperson / Pflegender Angehöriger / Freiwillig / Praktikum</td><td>Pflegender Angehöriger: spezielle Behandlung in Einsätzen</td></tr>
                            <tr><td><strong>Pensum %</strong></td><td>z.B. 80 für 80%</td><td>Für Zeitnachweis und Personalabrechnung</td></tr>
                            <tr><td><strong>GLN (13-stellig)</strong></td><td>Aus NAREG-Register</td><td>Pflicht für XML-Abrechnung mit KK</td></tr>
                            <tr><td><strong>AHV-Nr.</strong></td><td>756.XXXX.XXXX.XX</td><td>Für Personalabrechnung</td></tr>
                            <tr><td><strong>IBAN</strong></td><td>Bankverbindung</td><td>Für Lohnüberweisung</td></tr>
                        </tbody>
                    </table>
                </div>

                <div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.6rem 0.875rem; margin-bottom: 0.75rem; font-size: 0.875rem;">
                    «<strong>Speichern & Einladen</strong>» → Einladungsmail wird automatisch versendet. Mitarbeiter klickt Link, setzt Passwort — fertig. Im Mitarbeiter-Detail danach: <strong>erlaubte Leistungsarten</strong> freischalten.
                </div>
                <div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.6rem 0.875rem; margin-bottom: 0.75rem; font-size: 0.875rem;">
                    In der Mitarbeitenden-Liste: <strong>Filter nach Anstellungsart</strong> (Fachperson / Pflegender Angehöriger / Freiwillig / Praktikum) — hilfreich um nur Angehörige oder nur Fachpersonen anzuzeigen.
                </div>

                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <a href="{{ route('mitarbeiter.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Mitarbeitende →</a>
                </div>
            </div>
        </div>

        {{-- ── MODUL 3: Klient ── --}}
        <div style="display: flex; gap: 0; border-bottom: 1px solid var(--cs-border); padding: 0.875rem 0;">
            <div style="display: flex; flex-direction: column; align-items: center; margin-right: 1rem; flex-shrink: 0;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem;">3</div>
                <div style="width: 2px; background: var(--cs-border); flex: 1; margin-top: 4px;"></div>
            </div>
            <div style="flex: 1; padding-top: 0.3rem;">
                <div style="display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.4rem;">
                    <span class="text-fett">Ersten Klienten erfassen</span>
                    <span class="badge badge-grau" style="font-size: 0.7rem;">10 min</span>
                </div>
                <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Pflichtfelder zuerst, Rest kann später ergänzt werden — ohne KK und Verordnung aber keine KK-Abrechnung möglich.</div>

                <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
                    <table class="tabelle">
                        <thead><tr><th>Feld</th><th>Was eingeben</th><th>Hinweis</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Vorname / Name *</strong></td><td>Vollständiger Name des Klienten</td><td>Pflichtfeld</td></tr>
                            <tr><td><strong>Region *</strong></td><td>Kanton auswählen</td><td>Pflichtfeld — nur aufgeschaltete Regionen wählbar. Ohne Region kein Einsatz möglich.</td></tr>
                            <tr><td><strong>Adresse</strong></td><td>Strasse, PLZ, Ort</td><td>Für Rechnungsversand und Routenplanung</td></tr>
                            <tr><td><strong>Krankenkasse</strong></td><td>KK aus Stammdaten wählen</td><td>Pflicht für KK-Abrechnung</td></tr>
                            <tr><td><strong>Versichertennummer</strong></td><td>KVG-Versichertennummer</td><td>Erscheint auf XML-Abrechnung</td></tr>
                            <tr><td><strong>Arzt</strong></td><td>Behandelnden Arzt zuweisen</td><td>Für ärztliche Verordnungen nötig</td></tr>
                            <tr><td><strong>Ärztliche Verordnung</strong></td><td>Leistungsart, gültig ab/bis, Verordnungs-Nr.</td><td>Pflicht bei Behandlungspflege für KK-Abrechnung</td></tr>
                        </tbody>
                    </table>
                </div>

                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <a href="{{ route('klienten.create') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">+ Neuer Klient →</a>
                </div>
            </div>
        </div>

        {{-- ── MODUL 4: Einsatzserie ── --}}
        <div id="serie" style="display: flex; gap: 0; border-bottom: 1px solid var(--cs-border); padding: 0.875rem 0; scroll-margin-top: 5rem;">
            <div style="display: flex; flex-direction: column; align-items: center; margin-right: 1rem; flex-shrink: 0;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem;">4</div>
                <div style="width: 2px; background: var(--cs-border); flex: 1; margin-top: 4px;"></div>
            </div>
            <div style="flex: 1; padding-top: 0.3rem;">
                <div style="display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.4rem;">
                    <span class="text-fett">Einsatzserie anlegen</span>
                    <span class="badge badge-grau" style="font-size: 0.7rem;">10 min</span>
                </div>
                <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Im Spitex-Alltag laufen fast alle Einsätze wiederkehrend. Die Serie generiert automatisch alle Einsätze — kein manuelles Nacherfassen. Einzelne Einsätze nur für Ausnahmen. Pro Klient sind <strong>mehrere Serien</strong> möglich — jede mit eigenem Rhythmus, eigenen Zeiten und Leistungsarten (z.B. Grundpflege Mo/Mi/Fr morgens + Hauswirtschaft 1× pro Woche). <strong>Grund:</strong> Eine Serie hat genau einen Rhythmus, ein Zeitfenster und einen Mitarbeiter. Sobald Leistungen an anderen Tagen, zu anderen Zeiten oder durch eine andere Person erfolgen, braucht es dafür eine eigene Serie.</div>

                <div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.6rem 0.875rem; margin-bottom: 0.75rem; font-size: 0.875rem;">
                    Klient öffnen → Sektion <strong>«Serien»</strong> → <strong>«+ Neue Serie»</strong>
                </div>

                <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
                    <table class="tabelle">
                        <thead><tr><th>Feld</th><th>Was eingeben</th><th>Hinweis</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Klient</strong></td><td>Wird aus dem Klienten-Detail übernommen</td><td>—</td></tr>
                            <tr><td><strong>Mitarbeiter</strong></td><td>Zuständige Pflegeperson wählen</td><td>Angehörige erscheinen mit 👪</td></tr>
                            <tr><td><strong>Rhythmus</strong></td><td>Wöchentlich / Täglich</td><td>Wöchentlich: Wochentage anklicken</td></tr>
                            <tr><td><strong>Von / Bis</strong></td><td>Uhrzeit, z.B. 08:00 – 09:30</td><td>5-Minuten-Schritte (KLV-Vorschrift)</td></tr>
                            <tr><td><strong>Gültig ab *</strong></td><td>Startdatum der Serie</td><td>Pflichtfeld</td></tr>
                            <tr><td><strong>Leistungsart</strong></td><td>Grundpflege / Behandlungspflege / Hauswirtschaft usw.</td><td>Bestimmt den Tarif. Mehrere Leistungsarten pro Serie möglich.</td></tr>
                            <tr><td><strong>Automatisch verlängern</strong></td><td>Aktivieren</td><td>Empfohlen — Serie läuft unbegrenzt, kein Eingriff nötig</td></tr>
                            <tr><td><strong>Enddatum</strong></td><td>Nur bei befristeter Serie setzen</td><td>Pflicht wenn «Automatisch verlängern» deaktiviert. z.B. für Kurzzeitpflege.</td></tr>
                        </tbody>
                    </table>
                </div>

                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <a href="{{ route('klienten.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Klienten → Serie anlegen →</a>
                    <a href="{{ route('kalender.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Kalender prüfen →</a>
                </div>
            </div>
        </div>

        {{-- ── MODUL 5: Rapport ── --}}
        <div style="display: flex; gap: 0; border-bottom: 1px solid var(--cs-border); padding: 0.875rem 0;">
            <div style="display: flex; flex-direction: column; align-items: center; margin-right: 1rem; flex-shrink: 0;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem;">5</div>
                <div style="width: 2px; background: var(--cs-border); flex: 1; margin-top: 4px;"></div>
            </div>
            <div style="flex: 1; padding-top: 0.3rem;">
                <div style="display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.4rem;">
                    <span class="text-fett">Rapport schreiben</span>
                    <span class="badge badge-grau" style="font-size: 0.7rem;">5 min</span>
                </div>
                <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Nach jedem Einsatz: Vor-Ort-Seite → Check-in → Leistungen erfassen → Rapport → Check-out.</div>

                <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
                    <table class="tabelle">
                        <thead><tr><th>Methode</th><th>So geht's</th><th>Geeignet für</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Direkt tippen</strong></td><td>Text im Bericht-Feld eingeben</td><td>Kurze Einträge</td></tr>
                            <tr><td><strong>KI Bericht schreiben</strong></td><td>Stichworte in beliebiger Sprache diktieren → «✨ KI Bericht schreiben»</td><td>KI schreibt immer auf Deutsch — egal in welcher Sprache gesprochen</td></tr>
                            <tr><td><strong>Direkt diktieren</strong></td><td>🎙 «Direkt in Bericht diktieren» → sprechen → Stop</td><td>Schnelle Erfassung (Chrome/Safari/Edge)</td></tr>
                        </tbody>
                    </table>
                </div>

                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <a href="{{ route('rapporte.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Rapporte →</a>
                </div>
            </div>
        </div>

        {{-- ── MODUL 6: Abrechnung ── --}}
        <div style="display: flex; gap: 0; padding: 0.875rem 0;">
            <div style="display: flex; flex-direction: column; align-items: center; margin-right: 1rem; flex-shrink: 0;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--cs-primaer); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem;">6</div>
            </div>
            <div style="flex: 1; padding-top: 0.3rem;">
                <div style="display: flex; align-items: baseline; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.4rem;">
                    <span class="text-fett">Monatliche Abrechnung</span>
                    <span class="badge badge-grau" style="font-size: 0.7rem;">15 min</span>
                </div>
                <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Rechnungslauf einmal pro Monat — alle Klienten mit einem Klick abrechnen.</div>

                <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem;">Rechnungen / Auswertung</div>
                <div class="text-klein text-hell" style="margin-bottom: 0.5rem;">Die Rechnungsübersicht ist gleichzeitig die Auswertung — oben vier Kacheln mit Anzahl und Gesamtbetrag pro Status. Klick auf eine Kachel filtert die Liste sofort.</div>
                <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
                    <table class="tabelle">
                        <thead><tr><th>Element</th><th>Was es zeigt / tut</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Entwürfe / Gesendet / Bezahlt / Storniert</strong></td><td>Anzahl Rechnungen + Gesamtbetrag CHF pro Status — Klick filtert Liste</td></tr>
                            <tr><td><strong>↓ Auswertung CSV</strong></td><td>Gefilterte Liste als CSV — für Excel oder Buchhaltungssystem</td></tr>
                            <tr><td><strong>↓ Auswertung PDF</strong></td><td>Gefilterte Liste als PDF — für Ablage oder Übergabe an Treuhänder</td></tr>
                        </tbody>
                    </table>
                </div>
                <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem;">Rechnungslauf erstellen</div>
                <ol style="margin: 0 0 0.75rem 1.25rem; font-size: 0.875rem; line-height: 1.8;">
                    <li>Rechnungsläufe → <strong>«+ Neuer Lauf»</strong></li>
                    <li>Periode Von–Bis wählen → <strong>«Vorschau laden»</strong></li>
                    <li>Vorschau prüfen: grün = wird abgerechnet, grau = keine Einsätze in Periode</li>
                    <li>Klienten abwählen die nicht abgerechnet werden sollen (optional)</li>
                    <li><strong>«Rechnungslauf starten»</strong> — alle Rechnungen werden erstellt</li>
                </ol>
                <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: var(--cs-radius); padding: 0.6rem 0.875rem; margin-bottom: 0.75rem; font-size: 0.875rem;">
                    ⚠ Falls noch nicht abgeschlossene Einsätze in der Periode vorhanden sind, erscheint eine Warnung — direkt per Link zu den offenen Einsätzen springen und Checkouts nacherfassen, dann neu laden.
                </div>
                <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem;">Inhalt der Rechnungs-PDFs</div>
                <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
                    <table class="tabelle">
                        <thead><tr><th>Seite</th><th>Inhalt</th><th>Wann</th></tr></thead>
                        <tbody>
                            <tr><td><strong>1</strong></td><td>Rechnung (Betrag, Klient, Periode, QR-Zahlschein)</td><td>Immer</td></tr>
                            <tr><td><strong>2</strong></td><td>Berechnungs-Beilage (Tarife, KK-Anteil, Eigenanteil)</td><td>Tiers payant</td></tr>
                            <tr><td><strong>3</strong></td><td>Leistungsnachweis — alle Einsätze mit Datum, Zeit, Min, Leistungsart, Mitarbeiter + Monatssummary</td><td>Immer</td></tr>
                        </tbody>
                    </table>
                </div>

                <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem;">Buttons im Lauf-Detail</div>
                <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
                    <table class="tabelle">
                        <thead><tr><th>Button</th><th>Was passiert</th><th>Wann</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Email versenden (N)</strong></td><td>PDF-Rechnung per Mail an N Klienten</td><td>Klienten mit hinterlegter E-Mail</td></tr>
                            <tr><td><strong>Sammel-PDF (N)</strong></td><td>Alle Post-Rechnungen in einem PDF → drucken / per Post versenden</td><td>Klienten ohne E-Mail</td></tr>
                            <tr><td><strong>PDF-ZIP</strong></td><td>Alle PDFs als ZIP herunterladen</td><td>Archivierung</td></tr>
                            <tr><td><strong>✓ Post-Versand bestätigen</strong></td><td>Post-Rechnungen auf «Gesendet» setzen</td><td>Nach dem Ausdrucken / Versenden</td></tr>
                            <tr><td><strong>XML-ZIP</strong></td><td>Alle KVG-Dateien (XML 450.100) als ZIP für MediData</td><td>KVG-Abrechnung mit Krankenkassen</td></tr>
                            <tr><td><strong>MediData Upload</strong></td><td>Direkt-Übertragung an Krankenkassen via MediData</td><td>Nur Tiers payant + MediData konfiguriert</td></tr>
                            <tr><td><strong>✓ XML-Versand bestätigen</strong></td><td>KVG-Rechnungen als versendet markieren</td><td>Nach XML-Einreichung bei MediData</td></tr>
                            <tr><td><strong>Gemeinde Sammel-PDF</strong></td><td>Restfinanzierungsrechnungen für Gemeinden</td><td>Nur Tiers payant</td></tr>
                            <tr><td><strong>Bexio Abgleich</strong></td><td>Zahlungsstatus aller Rechnungen von Bexio abrufen</td><td>Nur wenn Bexio konfiguriert</td></tr>
                            <tr><td><strong>↺ Wiederholen</strong></td><td>Lauf stornieren und gleiche Periode neu erstellen</td><td>Bei Fehler im Lauf</td></tr>
                            <tr><td><strong>Stornieren</strong></td><td>Alle Rechnungen löschen, Einsätze zurück auf «unverrechnet»</td><td>Nur möglich solange nichts versendet wurde</td></tr>
                        </tbody>
                    </table>
                </div>

                <div style="font-size: 0.875rem; font-weight: 600; margin-bottom: 0.4rem;">Personalabrechnung</div>
                <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Personalabrechnung → Monat wählen → pro Mitarbeiter «Detail →» → <strong>«PDF Zeitnachweis»</strong> herunterladen → unterschreiben lassen. CSV-Export für Lohnbuchhaltungssystem möglich.</div>

                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                    <a href="{{ route('rechnungslauf.create') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">+ Neuer Rechnungslauf →</a>
                    <a href="{{ route('personalabrechnung.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Personalabrechnung →</a>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ═══ WEITERE FUNKTIONEN ═══ --}}
<div style="font-size: 1rem; font-weight: 700; margin: 0.5rem 0 1rem;">Weitere Funktionen</div>

{{-- Tourenplanung --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Tourenplanung</div>
    <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Tagesrouten pro Pflegeperson planen und optimieren — alle Einsätze eines Tages in der richtigen Reihenfolge.</div>
    <ol style="margin: 0 0 0.75rem 1.25rem; font-size: 0.875rem; line-height: 1.9;">
        <li>Touren → <strong>«Touren generieren»</strong> — System erstellt pro Pflegeperson eine Tour aus den Einsätzen des Tages, optimiert die Route (Nearest-Neighbor nach GPS) und setzt die Zeiten sequenziell ab der Startzeit</li>
        <li>Tour prüfen: Reihenfolge per Drag &amp; Drop anpassen, Einsatz hinzufügen oder entfernen</li>
        <li><strong>Roter Einsatz</strong> = Zeitkonflikt — überlappende Einsatzzeiten in der Tour → Reihenfolge anpassen</li>
        <li><strong>«Zeiten setzen»</strong> — berechnet alle Einsatzzeiten neu ab der Tour-Startzeit</li>
        <li><strong>«Route optimieren»</strong> — manuelle Neuoptimierung nach GPS (mind. 2 Klienten mit Adresse)</li>
        <li><strong>Orange (unten)</strong> = Einsätze ohne Tour-Zuweisung — diese gehen sonst vergessen</li>
    </ol>
    <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
        <table class="tabelle">
            <thead><tr><th>Feld (manuelle Tour)</th><th>Was eingeben</th></tr></thead>
            <tbody>
                <tr><td><strong>Mitarbeiter *</strong></td><td>Pflegeperson für diese Tour</td></tr>
                <tr><td><strong>Datum *</strong></td><td>Tag der Tour</td></tr>
                <tr><td><strong>Bezeichnung *</strong></td><td>z.B. «Morgenrunde», «Nachmittag West»</td></tr>
                <tr><td><strong>Startzeit *</strong></td><td>Wann startet die Pflegeperson</td></tr>
                <tr><td><strong>Einsätze zuweisen</strong></td><td>Alle offenen Einsätze des Mitarbeiters für diesen Tag werden zur Auswahl angezeigt</td></tr>
            </tbody>
        </table>
    </div>
    <a href="{{ route('touren.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Tourenplanung →</a>
</div>

{{-- Einzelner Einsatz --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Einsatz manuell erfassen</div>
    <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Für Ausnahmen ausserhalb der Serie — z.B. Einmaleinsatz, Notfallbesuch, nachträgliche Korrektur. Schnellste Variante: <strong>Doppelklick auf eine freie Zeitstelle im Kalender</strong> — Mitarbeiter und Zeit werden automatisch übernommen.</div>
    <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
        <table class="tabelle">
            <thead><tr><th>Feld</th><th>Was eingeben</th><th>Hinweis</th></tr></thead>
            <tbody>
                <tr><td><strong>Klient *</strong></td><td>Patient aus Dropdown</td><td>Pflichtfeld</td></tr>
                <tr><td><strong>Leistungsart *</strong></td><td>Grundpflege / Behandlungspflege / Hauswirtschaft usw.</td><td>Bestimmt den Tarif</td></tr>
                <tr><td><strong>Datum *</strong></td><td>Tag des Einsatzes</td><td>Pflichtfeld</td></tr>
                <tr><td><strong>Von / Bis *</strong></td><td>Uhrzeit, z.B. 08:00 – 09:30</td><td>5-Minuten-Schritte (KLV-Vorschrift)</td></tr>
                <tr><td><strong>Mitarbeiter</strong></td><td>Zuständige Pflegeperson</td><td>Angehörige erscheinen mit 👪</td></tr>
                <tr><td><strong>Verordnung</strong></td><td>Ärztliche Verordnung verknüpfen</td><td>Nur bei Behandlungspflege für KK-Abrechnung zwingend</td></tr>
            </tbody>
        </table>
    </div>
    <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
        <a href="{{ route('einsaetze.create') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">+ Neuer Einsatz →</a>
        <a href="{{ route('kalender.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Kalender →</a>
    </div>
</div>

{{-- Ferienvertretung --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Ferienvertretung / MA krank</div>
    <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Alle Einsätze eines ausgefallenen Mitarbeiters mit einem Klick auf eine Vertretung umbuchen.</div>
    <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
        <table class="tabelle">
            <thead><tr><th>Feld</th><th>Was eingeben</th><th>Hinweis</th></tr></thead>
            <tbody>
                <tr><td><strong>Mitarbeiter (fällt aus)</strong></td><td>Wer ist krank / in Ferien</td><td>Pflichtfeld</td></tr>
                <tr><td><strong>Vertretung durch</strong></td><td>Wer übernimmt</td><td>Optional — Qualifikation wird geprüft. Leer lassen wenn noch offen.</td></tr>
                <tr><td><strong>Von / Bis</strong></td><td>Zeitraum des Ausfalls</td><td>Pflichtfeld</td></tr>
            </tbody>
        </table>
    </div>
    <ol style="margin: 0 0 0.75rem 1.25rem; font-size: 0.875rem; line-height: 1.9;">
        <li>«Betroffene Einsätze anzeigen» → Vorschau aller <strong>geplanten</strong> Einsätze im Zeitraum (bereits gestartete/abgeschlossene werden nicht verschoben)</li>
        <li>Qualifikationsprüfung: Einsätze wo die Vertretung die Leistungsart nicht darf, werden als Warnung angezeigt — diese separat behandeln</li>
        <li>Vertretung bestätigen → alle OK-Einsätze werden auf einmal umgebucht</li>
        <li>Kalender prüfen — Doppelbelegungen auflösen, Touren aktualisieren</li>
    </ol>
    <div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.6rem 0.875rem; margin-bottom: 0.75rem; font-size: 0.875rem;">
        <strong>Abwesenheits-Persistenz:</strong> Eingetragene Abwesenheiten werden automatisch gespeichert — Badges in den Listen zeigen aktive Vertretungen auf einen Blick.<br>
        <strong>Archiv:</strong> Alle vergangenen Vertretungen mit Suche nach Mitarbeiter und Zeitraum einsehbar.
    </div>
    <a href="{{ route('vertretung.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Ferienvertretung →</a>
</div>

{{-- Angehörigenpflege --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Angehörigenpflege</div>
    <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">
        Familienmitglied wird als Mitarbeitende/r bei der Spitex angestellt und pflegt den eigenen Angehörigen gegen Lohn (CHF ~37.90/h inkl. Sozialversicherungen).
        Die Spitex übernimmt Aufsicht, Erstbeurteilung und Lohnabrechnung. Nur Grundpflege &amp; Hauswirtschaft (KLV). Reassessment alle 6 Monate.
    </div>
    <div style="background: var(--cs-hintergrund); border: 1px solid var(--cs-border); border-radius: var(--cs-radius); padding: 0.6rem 0.875rem; margin-bottom: 0.75rem; font-size: 0.875rem;">
        <strong>KVG Tagespauschale:</strong> Pro Kanton kann ein KVG-Tagessatz für Angehörigen-Einsätze hinterlegt werden (Stammdaten → Regionen → Kanton → «KVG Angehöriger CHF/Tag»). Wird bei der Abrechnung statt des Stundentarifs für Angehörige verwendet. Der Wert wird automatisch aus der Leistungsart vorausgefüllt.
    </div>
    <ol style="margin: 0 0 0.75rem 1.25rem; font-size: 0.875rem; line-height: 1.9;">
        <li>Angehörigenpflege → <strong>«+ Neuer Angehöriger»</strong> — Name, E-Mail eingeben → Einladungsmail automatisch</li>
        <li>System setzt automatisch Anstellungsart «Pflegender Angehöriger» und Rolle «Pflege» — nur Grundpflege &amp; Hauswirtschaft freigeschaltet</li>
        <li>Einsatz anlegen: Angehöriger erscheint mit 👪 im Mitarbeiter-Dropdown — Leistungserbringer-Typ wird automatisch gesetzt</li>
        <li>Monatliche Abrechnung: Angehörigenpflege → «Arbeitszeit» → <strong>PDF Zeitnachweis</strong> → unterschreiben lassen → Lohn/AHV abrechnen</li>
    </ol>
    <a href="{{ route('angehoerigenpflege.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Angehörigenpflege →</a>
</div>

{{-- Rapporte --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Rapporte</div>
    <div class="text-klein text-hell" style="margin-bottom: 0.75rem;">Pflegerische Dokumentation pro Klient — Verlauf, Zwischenfälle, Medikamente. Rapporte sind die Grundlage für Arztberichte und KK-Anfragen.</div>
    <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
        <table class="tabelle">
            <thead><tr><th>Typ</th><th>Wann verwenden</th></tr></thead>
            <tbody>
                <tr><td><strong>Pflege</strong></td><td>Allgemeiner Pflegebericht</td></tr>
                <tr><td><strong>Verlauf</strong></td><td>Regelmässiger Verlaufsbericht nach Einsatz</td></tr>
                <tr><td><strong>Information</strong></td><td>Allgemeine Information zum Klienten</td></tr>
                <tr><td><strong>Zwischenfall</strong></td><td>Sturz, Notfall, ungewöhnliche Situation — löst automatisch Benachrichtigung an alle Admins aus</td></tr>
                <tr><td><strong>Medikament</strong></td><td>Medikamentengabe oder -änderung dokumentieren</td></tr>
            </tbody>
        </table>
    </div>
    <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
        <table class="tabelle">
            <thead><tr><th>Schaltfläche</th><th>Was passiert</th></tr></thead>
            <tbody>
                <tr><td><strong>PDF</strong></td><td>Einzelnen Rapport als PDF — für Ablage oder Weiterleitung</td></tr>
                <tr><td><strong>Sammel-PDF</strong></td><td>Alle gefilterten Rapporte in einem PDF — Filter: Klient + Zeitraum + Typ setzen, dann klicken. Ideal für Arztberichte oder KK-Anfragen.</td></tr>
                <tr><td><strong>Vertraulich</strong></td><td>Nur für Admin sichtbar — nicht für Pflege-Rolle</td></tr>
            </tbody>
        </table>
    </div>
    <a href="{{ route('rapporte.index') }}" class="btn btn-sekundaer" style="font-size: 0.775rem; padding: 0.2rem 0.6rem;">Rapporte →</a>
</div>

{{-- Rollen-Übersicht --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Wer sieht was? — Rollenübersicht</div>
    <div class="tabelle-wrapper">
        <table class="tabelle">
            <thead>
                <tr>
                    <th>Funktion</th>
                    <th class="text-mitte">Admin</th>
                    <th class="text-mitte">Pflege</th>
                    <th class="text-mitte">Buchhaltung</th>
                </tr>
            </thead>
            <tbody>
                @php
                $zugaenge = [
                    ['Dashboard, Nachrichten', true, true, true],
                    ['Klienten ansehen', true, true, false],
                    ['Einsätze ansehen / Vor-Ort', true, true, false],
                    ['Rapporte schreiben', true, true, false],
                    ['Touren / Kalender', true, 'nur eigene', false],
                    ['Meine Arbeitszeit (Zeitnachweis)', true, true, false],
                    ['Rechnungen / Auswertung, Rechnungsläufe', true, false, true],
                    ['Personalabrechnung (alle MA)', true, false, true],
                    ['Mitarbeitende verwalten', true, false, false],
                    ['Stammdaten, Einstellungen', true, false, false],
                    ['Angehörigenpflege-Übersicht', true, false, false],
                    ['Audit-Log', true, false, false],
                ];
                @endphp
                @foreach($zugaenge as $z)
                <tr>
                    <td class="text-klein">{{ $z[0] }}</td>
                    <td class="text-mitte">
                        @if($z[1] === true) <span style="color: var(--cs-erfolg); font-weight: 700;">✓</span>
                        @elseif($z[1] === false) <span class="text-hell">—</span>
                        @else <span class="text-mini" style="color: var(--cs-warnung);">{{ $z[1] }}</span>
                        @endif
                    </td>
                    <td class="text-mitte">
                        @if($z[2] === true) <span style="color: var(--cs-erfolg); font-weight: 700;">✓</span>
                        @elseif($z[2] === false) <span class="text-hell">—</span>
                        @else <span class="text-mini" style="color: var(--cs-warnung);">{{ $z[2] }}</span>
                        @endif
                    </td>
                    <td class="text-mitte">
                        @if($z[3] === true) <span style="color: var(--cs-erfolg); font-weight: 700;">✓</span>
                        @elseif($z[3] === false) <span class="text-hell">—</span>
                        @else <span class="text-mini" style="color: var(--cs-warnung);">{{ $z[3] }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Tagesabläufe --}}
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Tagesablauf Admin</div>
        <ol style="margin: 0 0 0 1.1rem; padding: 0; line-height: 2; font-size: 0.875rem;">
            <li>Dashboard → Einsätze heute prüfen</li>
            <li>Kalender → Lücken / Doppelbelegungen</li>
            <li>Nachrichten prüfen</li>
            <li>Neue Klienten / Rapporte bearbeiten</li>
            <li><em>Monatlich:</em> Rechnungslauf starten</li>
        </ol>
    </div>
    <div class="karte">
        <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Tagesablauf Pflege</div>
        <ol style="margin: 0 0 0 1.1rem; padding: 0; line-height: 2; font-size: 0.875rem;">
            <li>Dashboard → Einsätze heute</li>
            <li>Vor Ort → Check-in beim Klienten</li>
            <li>Leistungen erfassen (Checkliste)</li>
            <li>Rapport schreiben (KI hilft)</li>
            <li>Check-out → nächster Einsatz</li>
        </ol>
    </div>
</div>

{{-- FAQ --}}
<div class="karte" style="margin-bottom: 1.25rem;">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Audit-Log & Datenschutz</div>
    <div style="font-size: 0.9rem; line-height: 1.7; display: flex; flex-direction: column; gap: 0.75rem;">

        <div><strong>Was ist der Audit-Log?</strong><br>
            CuraSoft protokolliert automatisch alle sicherheitsrelevanten Aktionen — wer hat wann was geändert, mit Zeitstempel und IP-Adresse. Nur Admins können das Log einsehen unter <strong>Menü → Audit-Log</strong>.
        </div>
        <div class="abschnitt-trenn"></div>

        <div><strong>Gesetzliche Pflicht</strong><br>
            Spitex-Organisationen unterliegen dem <strong>nDSG</strong> (Datenschutzgesetz) und dem <strong>KVG</strong>. Beide verlangen die Protokollierung von Zugriffen auf Gesundheitsdaten sowie eine Aufbewahrung von <strong>10 Jahren</strong>. CuraSoft erfüllt diese Anforderungen automatisch — kein manueller Aufwand.
        </div>
        <div class="abschnitt-trenn"></div>

        <div><strong>Was wird geloggt?</strong><br>
            <ul style="margin: 0.3rem 0 0 1.2rem;">
                <li>Login / Logout aller Benutzer</li>
                <li>Check-in / Check-out beim Klienten</li>
                <li>Änderungen an Klienten, Rechnungen, Mitarbeitenden</li>
                <li>Änderungen an Stammdaten (Leistungsarten, Krankenkassen, Tarife)</li>
            </ul>
        </div>
        <div class="abschnitt-trenn"></div>

        <div><strong>Bei Verdacht auf Datenmissbrauch</strong><br>
            <ol style="margin: 0.3rem 0 0 1.2rem;">
                <li>Audit-Log nach verdächtiger IP oder Benutzer filtern</li>
                <li>Betroffenen Account sofort sperren / Passwort zurücksetzen</li>
                <li>Vorfall dokumentieren</li>
                <li>Meldung an <strong>EDÖB</strong> innert <strong>72 Stunden</strong> (Pflicht gemäss nDSG Art. 24)</li>
            </ol>
        </div>

    </div>
</div>

<div class="karte">
    <div class="abschnitt-label" style="margin-bottom: 0.75rem;">Häufige Fragen — Kurzantworten</div>

    @php
    $faq = [
        ['F', 'Wie loggt sich eine neue Pflegerin ein?', 'Sie erhält automatisch einen Einladungslink per E-Mail — einfach anklicken, Passwort setzen, fertig. Kein IT-Aufwand.'],
        ['F', 'Kann ich das Handy verwenden?', 'Ja — die Vor-Ort-Ansicht ist für Mobile optimiert. Check-in, Rapport, Leistungen — alles geht am Telefon.'],
        ['F', 'Was ist der Unterschied Einsatz / Tour?', 'Einsatz = ein Besuch bei einem Klienten. Tour = Tagesroute einer Pflegerin mit mehreren Einsätzen. Eine Pflegerin hat eine Tour, darin mehrere Einsätze.'],
        ['F', 'Warum erscheint ein Klient nicht im Rechnungslauf?', 'Mögliche Gründe: keine abgeschlossenen Einsätze in der Periode (kein Check-out), alle Einsätze bereits verrechnet, Klient ist inaktiv, oder Region ist nicht konfiguriert.'],
        ['F', 'Was ist eine Einsatzserie?', 'Eine Serie generiert automatisch alle wiederkehrenden Einsätze — z.B. jeden Montag und Mittwoch. Mit «Automatisch verlängern» läuft sie unbegrenzt weiter, ohne dass jemand manuell eingreifen muss. Serien können nicht gelöscht werden, nur inaktiviert («Serie beenden») — zukünftige Einsätze werden dabei gestoppt, die Serie bleibt als Historie erhalten. Passen die Zeiten oder Wochentage nicht mehr? Alte Serie inaktivieren und eine neue Serie mit den neuen Angaben erfassen.'],
        ['F', 'Kann die Pflegerin in einer anderen Sprache diktieren?', 'Ja — die KI versteht beliebige Sprachen (Serbisch, Albanisch, Portugiesisch usw.) und schreibt den Bericht immer auf Deutsch.'],
        ['F', 'Was ist Tiers payant / Tiers garant?', 'Wird auf Firmen-Ebene eingestellt — nur eine Methode pro Organisation. Tiers garant: Klient zahlt Spitex, reicht Rapportblatt bei KK + Gemeinde ein. Tiers payant: Spitex rechnet direkt mit KK + Gemeinde ab.'],
        ['F', 'Wie ändere ich Tarife?', 'Stammdaten → Regionen → Kanton wählen → Tarif für Leistungsart anpassen. Immer neuen Eintrag mit «Gültig ab» erstellen — alte Tarife bleiben als Historie erhalten.'],
        ['F', 'Was ist Angehörigenpflege?', 'Familienangehörige werden als Mitarbeitende angestellt und pflegen ihre Verwandten gegen Lohn (CHF ~37.90/h). Die Spitex übernimmt Aufsicht und Lohnabrechnung.'],
        ['F', 'MA krank / Ferien?', 'Stammdaten → Ferienvertretung — alle Einsätze mit einem Klick auf eine Vertretung umbuchen. Zeitraum und Vertretungsperson wählen, fertig.'],
    ];
    @endphp

    @foreach($faq as $f)
    <div style="{{ !$loop->last ? 'border-bottom: 1px solid var(--cs-border);' : '' }} padding: 0.7rem 0;">
        <div style="display: flex; gap: 0.6rem; align-items: flex-start;">
            <span class="badge badge-primaer" style="flex-shrink: 0; margin-top: 0.1rem; font-size: 0.7rem;">{{ $f[0] }}</span>
            <div>
                <div class="text-fett text-klein">{{ $f[1] }}</div>
                <div class="text-klein text-hell" style="margin-top: 0.15rem; line-height: 1.5;">{{ $f[2] }}</div>
            </div>
        </div>
    </div>
    @endforeach

</div>

</div>

@push('styles')
<style>
@media (max-width: 768px) {
    .schulung-grid { grid-template-columns: 1fr !important; }
}
</style>
@endpush



</x-layouts.app>
