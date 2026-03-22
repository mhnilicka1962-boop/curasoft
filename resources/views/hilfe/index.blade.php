<x-layouts.app titel="Hilfe & Betriebsanweisung">
<div style="max-width: 860px;">

    <div class="seiten-kopf" style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.25rem; font-weight: 700; margin: 0;">Hilfe & Betriebsanweisung</h1>
        <span class="text-hell text-klein">Stand: {{ date('d.m.Y') }}</span>
    </div>

    {{-- Sprungnavigation --}}
    <div class="karte" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 0.5rem;">Schnellzugriff</div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="#scripts"  class="badge badge-primaer"  style="text-decoration: none;">▶ Geführte Abläufe</a>
            <a href="#kap1"     class="badge badge-grau"      style="text-decoration: none;">1 — Einloggen</a>
            <a href="#kap2"     class="badge badge-grau"      style="text-decoration: none;">2 — Tagesablauf Admin</a>
            <a href="#kap3"     class="badge badge-grau"      style="text-decoration: none;">3 — Neuer Patient</a>
            <a href="#kap4"     class="badge badge-grau"      style="text-decoration: none;">4 — Neuer Mitarbeiter</a>
            <a href="#kap5"     class="badge badge-grau"      style="text-decoration: none;">5 — Rapport</a>
            <a href="#kap6"     class="badge badge-grau"      style="text-decoration: none;">6 — Rechnung</a>
            <a href="#kap6b"    class="badge badge-grau"      style="text-decoration: none;">6b — Rechnungslauf</a>
            <a href="#kap7"     class="badge badge-grau"      style="text-decoration: none;">7 — FAQ</a>
            <a href="#script-angehoerig" class="badge badge-info" style="text-decoration: none;">▶ Angehörigenpflege</a>
            <a href="#script-lohnabrechnung" class="badge badge-info" style="text-decoration: none;">▶ Lohnabrechnung</a>
            <a href="#script-einsatz" class="badge badge-info" style="text-decoration: none;">▶ Einsatz erfassen</a>
            <a href="#kap-rapportierung" class="badge badge-info" style="text-decoration: none;">▶ Rapportierung</a>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- GEFÜHRTE ABLÄUFE                                               --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <div id="scripts">
        <div style="font-size: 1.0625rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
            ▶ Geführte Abläufe — Scripts
            <span class="text-klein text-hell" style="font-weight: 400;">Schritt-für-Schritt-Checklisten für alle kritischen Prozesse</span>
        </div>

        {{-- SCRIPT 1: Neuer Klient --}}
        <div class="karte script-karte" id="script-klient" style="margin-bottom: 1rem;">
            <div class="script-kopf" onclick="toggleScript('s1')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 1rem; font-weight: 600;">📋 Neuer Klient aufnehmen</span>
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">Vom Erstkontakt bis zum ersten Einsatz</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="badge badge-grau text-klein" id="fortschritt-s1">0 / 8</span>
                    <span id="pfeil-s1" style="transition: transform 0.2s;">▼</span>
                </div>
            </div>
            <div id="body-s1" style="display: none; margin-top: 1rem;">
                <ol class="script-liste" data-script="s1">
                    <li data-step="0">
                        <label><input type="checkbox" data-script="s1" data-step="0">
                            Stammdaten erfassen: Vorname, Nachname, Geburtsdatum, Region (Kanton)
                        </label>
                        <a href="{{ route('klienten.create') }}" class="script-link" target="_blank">+ Neuer Klient öffnen →</a>
                    </li>
                    <li data-step="1">
                        <label><input type="checkbox" data-script="s1" data-step="1">
                            Adresse, Telefon, Notfallnummer erfassen (Klient-Detail → Kontakt & Adresse)
                        </label>
                    </li>
                    <li data-step="2">
                        <label><input type="checkbox" data-script="s1" data-step="2">
                            Krankenkasse (KVG) zuweisen — Versichertennummer, Tiers payant/garant
                        </label>
                    </li>
                    <li data-step="3">
                        <label><input type="checkbox" data-script="s1" data-step="3">
                            Behandelnden Arzt zuweisen
                        </label>
                        <a href="{{ route('aerzte.index') }}" class="script-link" target="_blank">Ärzte-Stammdaten →</a>
                    </li>
                    <li data-step="4">
                        <label><input type="checkbox" data-script="s1" data-step="4">
                            Ärztliche Verordnung erfassen (Leistungsart, gültig ab/bis, Verordnungs-Nr.)
                        </label>
                    </li>
                    <li data-step="5">
                        <label><input type="checkbox" data-script="s1" data-step="5">
                            Pflegebedarf / Einstufung erfassen (BESA / RAI / Manuell)
                        </label>
                    </li>
                    <li data-step="6">
                        <label><input type="checkbox" data-script="s1" data-step="6">
                            Wiederkehrende Einsätze planen (Wochentage, Zeiten, Mitarbeiter)
                        </label>
                        <a href="{{ route('einsaetze.create') }}" class="script-link" target="_blank">+ Einsatz planen →</a>
                    </li>
                    <li data-step="7">
                        <label><input type="checkbox" data-script="s1" data-step="7">
                            Tour einplanen — Einsätze der Tour des zuständigen Mitarbeiters zuweisen
                        </label>
                        <a href="{{ route('touren.index') }}" class="script-link" target="_blank">Tourenplanung →</a>
                    </li>
                </ol>
                <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem;">
                    <button onclick="resetScript('s1')" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Script zurücksetzen</button>
                </div>
            </div>
        </div>

        {{-- SCRIPT 2: Klient verlässt Spitex --}}
        <div class="karte script-karte" id="script-austritt" style="margin-bottom: 1rem;">
            <div class="script-kopf" onclick="toggleScript('s2')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 1rem; font-weight: 600;">🚪 Klient verlässt Spitex</span>
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">Austritt, Spital, Pflegeheim, Todesfall</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="badge badge-grau text-klein" id="fortschritt-s2">0 / 6</span>
                    <span id="pfeil-s2">▼</span>
                </div>
            </div>
            <div id="body-s2" style="display: none; margin-top: 1rem;">
                <ol class="script-liste" data-script="s2">
                    <li data-step="0">
                        <label><input type="checkbox" data-script="s2" data-step="0">
                            Offene und zukünftige Einsätze prüfen und stornieren
                        </label>
                        <a href="{{ route('einsaetze.index') }}?ansicht=anstehend" class="script-link" target="_blank">Einsätze anstehend →</a>
                    </li>
                    <li data-step="1">
                        <label><input type="checkbox" data-script="s2" data-step="1">
                            Serien löschen (Einsatz-Detail → × Serie löschen)
                        </label>
                    </li>
                    <li data-step="2">
                        <label><input type="checkbox" data-script="s2" data-step="2">
                            Offene Rechnungen prüfen — noch nicht abgerechnete Einsätze abrechnen
                        </label>
                        <a href="{{ route('rechnungslauf.create') }}" class="script-link" target="_blank">Rechnungslauf erstellen →</a>
                    </li>
                    <li data-step="3">
                        <label><input type="checkbox" data-script="s2" data-step="3">
                            Abschlussbericht / letzten Rapport schreiben
                        </label>
                        <a href="{{ route('rapporte.create') }}" class="script-link" target="_blank">Rapport schreiben →</a>
                    </li>
                    <li data-step="4">
                        <label><input type="checkbox" data-script="s2" data-step="4">
                            Klient auf «Inaktiv» setzen (Klient-Detail → Bearbeiten → Aktiv abwählen)
                        </label>
                        <a href="{{ route('klienten.index') }}" class="script-link" target="_blank">Klienten →</a>
                    </li>
                    <li data-step="5">
                        <label><input type="checkbox" data-script="s2" data-step="5">
                            Dokumente archiviert / Akte abgelegt ✓
                        </label>
                    </li>
                </ol>
                <div style="margin-top: 0.75rem;">
                    <button onclick="resetScript('s2')" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Script zurücksetzen</button>
                </div>
            </div>
        </div>

        {{-- SCRIPT 3: Neue Mitarbeiterin --}}
        <div class="karte script-karte" id="script-mitarbeiter" style="margin-bottom: 1rem;">
            <div class="script-kopf" onclick="toggleScript('s3')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 1rem; font-weight: 600;">👩‍⚕️ Neue Mitarbeiterin einrichten</span>
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">Stammdaten bis erster Einsatz</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="badge badge-grau text-klein" id="fortschritt-s3">0 / 6</span>
                    <span id="pfeil-s3">▼</span>
                </div>
            </div>
            <div id="body-s3" style="display: none; margin-top: 1rem;">
                <ol class="script-liste" data-script="s3">
                    <li data-step="0">
                        <label><input type="checkbox" data-script="s3" data-step="0">
                            Stammdaten erfassen: Name, E-Mail, Rolle (Pflege / Buchhaltung / Admin)
                        </label>
                        <a href="{{ route('mitarbeiter.index') }}" class="script-link" target="_blank">+ Neue Mitarbeiterin →</a>
                    </li>
                    <li data-step="1">
                        <label><input type="checkbox" data-script="s3" data-step="1">
                            Einladungsmail wurde automatisch versendet — Mitarbeiterin bestätigt Passwort (48h gültig)
                        </label>
                        <div class="text-klein text-hell" style="margin: 0.2rem 0 0 1.5rem;">Falls abgelaufen: Mitarbeiter-Detail → Einladung erneut senden</div>
                    </li>
                    <li data-step="2">
                        <label><input type="checkbox" data-script="s3" data-step="2">
                            Qualifikationen zuweisen (FaGe, HF Pflege, DN I/II usw.)
                        </label>
                    </li>
                    <li data-step="3">
                        <label><input type="checkbox" data-script="s3" data-step="3">
                            Erlaubte Leistungsarten freischalten (Checkboxen im Mitarbeiter-Detail)
                        </label>
                    </li>
                    <li data-step="4">
                        <label><input type="checkbox" data-script="s3" data-step="4">
                            Klienten zuweisen — falls pflegender Angehöriger: Beziehungstyp «Angehörig pflegend»
                        </label>
                    </li>
                    <li data-step="5">
                        <label><input type="checkbox" data-script="s3" data-step="5">
                            Ersten Einsatz planen und Tour zuweisen
                        </label>
                        <a href="{{ route('einsaetze.create') }}" class="script-link" target="_blank">+ Einsatz planen →</a>
                    </li>
                </ol>
                <div style="margin-top: 0.75rem;">
                    <button onclick="resetScript('s3')" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Script zurücksetzen</button>
                </div>
            </div>
        </div>

        {{-- SCRIPT 4: MA krank / Ferien --}}
        <div class="karte script-karte" id="script-vertretung" style="margin-bottom: 1rem;">
            <div class="script-kopf" onclick="toggleScript('s4')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 1rem; font-weight: 600;">🏖 Mitarbeiter krank / Ferien</span>
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">Alle Einsätze auf Vertretung umbuchen</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="badge badge-grau text-klein" id="fortschritt-s4">0 / 4</span>
                    <span id="pfeil-s4">▼</span>
                </div>
            </div>
            <div id="body-s4" style="display: none; margin-top: 1rem;">
                <ol class="script-liste" data-script="s4">
                    <li data-step="0">
                        <label><input type="checkbox" data-script="s4" data-step="0">
                            Ausfall-Zeitraum klären (Von–Bis Datum)
                        </label>
                    </li>
                    <li data-step="1">
                        <label><input type="checkbox" data-script="s4" data-step="1">
                            Vertretungsperson wählen — Qualifikationen prüfen (Leistungsartenfreigabe!)
                        </label>
                        <a href="{{ route('mitarbeiter.index') }}" class="script-link" target="_blank">Mitarbeitende →</a>
                    </li>
                    <li data-step="2">
                        <label><input type="checkbox" data-script="s4" data-step="2">
                            Ferienvertretung-Tool ausführen — alle Einsätze mit einem Klick umbuchen
                        </label>
                        <a href="{{ route('vertretung.index') }}" class="script-link btn btn-primaer" style="display: inline-block; text-decoration: none; margin-top: 0.4rem;">▶ Ferienvertretung starten →</a>
                    </li>
                    <li data-step="3">
                        <label><input type="checkbox" data-script="s4" data-step="3">
                            Kalender prüfen — Doppelbelegungen auflösen, Touren aktualisieren
                        </label>
                        <a href="{{ route('kalender.index') }}" class="script-link" target="_blank">Kalender →</a>
                    </li>
                </ol>
                <div style="margin-top: 0.75rem;">
                    <button onclick="resetScript('s4')" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Script zurücksetzen</button>
                </div>
            </div>
        </div>

        {{-- SCRIPT 5: Monatsabschluss --}}
        <div class="karte script-karte" id="script-monatsabschluss" style="margin-bottom: 1rem;">
            <div class="script-kopf" onclick="toggleScript('s5')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 1rem; font-weight: 600;">📆 Monatsabschluss</span>
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">Kontrolle, Rechnungslauf, Versand</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="badge badge-grau text-klein" id="fortschritt-s5">0 / 7</span>
                    <span id="pfeil-s5">▼</span>
                </div>
            </div>
            <div id="body-s5" style="display: none; margin-top: 1rem;">
                <ol class="script-liste" data-script="s5">
                    <li data-step="0">
                        <label><input type="checkbox" data-script="s5" data-step="0">
                            Vergangene Einsätze prüfen — alle abgeschlossen? Fehlende Checkouts nacherfassen
                        </label>
                        <a href="{{ route('einsaetze.index') }}?ansicht=vergangen&status=geplant" class="script-link" target="_blank">Offene Einsätze vergangen →</a>
                    </li>
                    <li data-step="1">
                        <label><input type="checkbox" data-script="s5" data-step="1">
                            Rapporte vollständig? Zwischenfälle bearbeitet?
                        </label>
                        <a href="{{ route('rapporte.index') }}" class="script-link" target="_blank">Rapporte →</a>
                    </li>
                    <li data-step="2">
                        <label><input type="checkbox" data-script="s5" data-step="2">
                            Ablaufende Verordnungen prüfen — Arzt kontaktiert?
                        </label>
                    </li>
                    <li data-step="3">
                        <label><input type="checkbox" data-script="s5" data-step="3">
                            Rechnungslauf erstellen — Periode wählen, Vorschau prüfen, Lauf starten
                        </label>
                        <a href="{{ route('rechnungslauf.create') }}" class="script-link" target="_blank">+ Rechnungslauf →</a>
                    </li>
                    <li data-step="4">
                        <label><input type="checkbox" data-script="s5" data-step="4">
                            Rechnungen per Email versenden (automatisch) und/oder Sammel-PDF drucken
                        </label>
                    </li>
                    <li data-step="5">
                        <label><input type="checkbox" data-script="s5" data-step="5">
                            XML-ZIP für KK-Abrechnung erstellen und bei MediData einreichen
                        </label>
                    </li>
                    <li data-step="6">
                        <label><input type="checkbox" data-script="s5" data-step="6">
                            Bexio-Zahlungsabgleich durchführen (falls Bexio konfiguriert)
                        </label>
                        <a href="{{ route('rechnungslauf.index') }}" class="script-link" target="_blank">Rechnungsläufe →</a>
                    </li>
                </ol>
                <div style="margin-top: 0.75rem;">
                    <button onclick="resetScript('s5')" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Script zurücksetzen</button>
                </div>
            </div>
        </div>

        {{-- SCRIPT 6: Verordnung abgelaufen --}}
        <div class="karte script-karte" style="margin-bottom: 1.5rem;">
            <div class="script-kopf" onclick="toggleScript('s6')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 1rem; font-weight: 600;">📄 Ärztliche Verordnung erneuern</span>
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">Ablaufende oder abgelaufene Verordnung</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="badge badge-grau text-klein" id="fortschritt-s6">0 / 5</span>
                    <span id="pfeil-s6">▼</span>
                </div>
            </div>
            <div id="body-s6" style="display: none; margin-top: 1rem;">
                <ol class="script-liste" data-script="s6">
                    <li data-step="0">
                        <label><input type="checkbox" data-script="s6" data-step="0">
                            Klient identifizieren — Hinweis «Verordnung abgelaufen» auf Vor-Ort-Seite oder Dashboard
                        </label>
                    </li>
                    <li data-step="1">
                        <label><input type="checkbox" data-script="s6" data-step="1">
                            Arzt kontaktieren — Vorlaufzeit beachten (2–4 Wochen bis neue Verordnung)
                        </label>
                        <div class="text-klein text-hell" style="margin: 0.2rem 0 0 1.5rem;">Tipp: Formular für ärztliche Verordnung im Klient-Detail ausdrucken</div>
                    </li>
                    <li data-step="2">
                        <label><input type="checkbox" data-script="s6" data-step="2">
                            Neue Verordnung erhalten — im Klient-Detail unter «Ärztliche Verordnungen» erfassen
                        </label>
                        <a href="{{ route('klienten.index') }}" class="script-link" target="_blank">Klienten →</a>
                    </li>
                    <li data-step="3">
                        <label><input type="checkbox" data-script="s6" data-step="3">
                            Alte Verordnung auf «Inaktiv» setzen
                        </label>
                    </li>
                    <li data-step="4">
                        <label><input type="checkbox" data-script="s6" data-step="4">
                            Neue Verordnung bei zukünftigen Einsätzen des Klienten verknüpfen
                        </label>
                    </li>
                </ol>
                <div style="margin-top: 0.75rem;">
                    <button onclick="resetScript('s6')" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Script zurücksetzen</button>
                </div>
            </div>
        </div>
        {{-- SCRIPT 7: Angehörigenpflege --}}
        <div class="karte script-karte" id="script-angehoerig" style="margin-bottom: 1rem;">
            <div class="script-kopf" onclick="toggleScript('s7')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 1rem; font-weight: 600;">👪 Angehörigenpflege einrichten</span>
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">Familienmitglied als Pflegeperson anstellen</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="badge badge-grau text-klein" id="fortschritt-s7">0 / 7</span>
                    <span id="pfeil-s7">▼</span>
                </div>
            </div>
            <div id="body-s7" style="display: none; margin-top: 1rem;">
                <div class="info-box" style="margin-bottom: 0.75rem; font-size: 0.875rem;">
                    <strong>Konzept:</strong> Das Familienmitglied wird als Mitarbeitende/r bei Ihnen angestellt und erbringt Pflegeleistungen.
                    Die Spitex übernimmt Aufsicht, Erstbeurteilung und Lohnabrechnung.
                    Vergütung ca. <strong>CHF 37.90/h</strong> inkl. Sozialversicherungen · Nur Grundpflege &amp; Hauswirtschaft (KLV) · Reassessment alle 6 Monate.
                </div>
                <ol class="script-liste" data-script="s7">
                    <li data-step="0">
                        <label><input type="checkbox" data-script="s7" data-step="0">
                            Angehörigen unter Mitarbeitende als neue Person erfassen —
                            Anstellungsart: <strong>«Pflegender Angehöriger»</strong>, Rolle: <strong>«Pflege»</strong>, E-Mail-Adresse eingeben
                        </label>
                        <a href="{{ route('mitarbeiter.index') }}" class="script-link" target="_blank">Mitarbeitende → + Neu →</a>
                    </li>
                    <li data-step="1">
                        <label><input type="checkbox" data-script="s7" data-step="1">
                            Im Mitarbeiter-Detail: Erlaubte Leistungsarten prüfen —
                            System setzt automatisch nur <em>Grundpflege</em> und <em>Hauswirtschaft</em> (KLV-Pflicht)
                        </label>
                    </li>
                    <li data-step="2">
                        <label><input type="checkbox" data-script="s7" data-step="2">
                            Im Mitarbeiter-Detail → <strong>«Zugewiesene Klienten»</strong> → betreuten Klienten hinzufügen,
                            Beziehungstyp: <strong>«Pflegend tätig»</strong>
                        </label>
                    </li>
                    <li data-step="3">
                        <label><input type="checkbox" data-script="s7" data-step="3">
                            Einladungs-E-Mail an Angehörigen senden (Button «Einladung senden» im Mitarbeiter-Detail) —
                            er/sie loggt sich ein und sieht sofort seine Einsätze + «Meine Arbeitszeit»
                        </label>
                    </li>
                    <li data-step="4">
                        <label><input type="checkbox" data-script="s7" data-step="4">
                            Ersten Einsatz planen: Klient wählen → Mitarbeiter = Angehöriger (erscheint mit 👪 im Dropdown) →
                            Leistungserbringer-Typ springt automatisch auf «Pflegender Angehöriger»
                        </label>
                        <a href="{{ route('einsaetze.create') }}" class="script-link" target="_blank">+ Einsatz planen →</a>
                    </li>
                    <li data-step="5">
                        <label><input type="checkbox" data-script="s7" data-step="5">
                            Wiederkehrende Einsätze einrichten — Wochentage + Zeiten festlegen, Wiederholung «Wöchentlich»
                        </label>
                    </li>
                    <li data-step="6">
                        <label><input type="checkbox" data-script="s7" data-step="6">
                            Monatliche Lohnabrechnung: Personalabrechnung → Mitarbeiter wählen → <strong>PDF Zeitnachweis</strong> erstellen → unterschreiben lassen
                        </label>
                        <a href="{{ route('personalabrechnung.index') }}" class="script-link" target="_blank">Personalabrechnung →</a>
                    </li>
                </ol>
                <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button onclick="resetScript('s7')" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Script zurücksetzen</button>
                    <a href="{{ route('angehoerigenpflege.index') }}" class="btn btn-primaer" style="font-size: 0.8125rem;">Angehörigenpflege-Übersicht →</a>
                </div>
            </div>
        </div>

        {{-- SCRIPT 8: Monatliche Lohnabrechnung --}}
        <div class="karte script-karte" id="script-lohnabrechnung" style="margin-bottom: 1.5rem;">
            <div class="script-kopf" onclick="toggleScript('s8')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 1rem; font-weight: 600;">💰 Monatliche Lohnabrechnung</span>
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">Stundennachweis für alle Mitarbeitenden</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="badge badge-grau text-klein" id="fortschritt-s8">0 / 5</span>
                    <span id="pfeil-s8">▼</span>
                </div>
            </div>
            <div id="body-s8" style="display: none; margin-top: 1rem;">
                <ol class="script-liste" data-script="s8">
                    <li data-step="0">
                        <label><input type="checkbox" data-script="s8" data-step="0">
                            Monat in der Personalabrechnung wählen — Übersicht zeigt alle Mitarbeitenden mit geplanten und geleisteten Stunden
                        </label>
                        <a href="{{ route('personalabrechnung.index') }}" class="script-link" target="_blank">Personalabrechnung öffnen →</a>
                    </li>
                    <li data-step="1">
                        <label><input type="checkbox" data-script="s8" data-step="1">
                            Auffällige Abweichungen prüfen (rot = deutlich weniger geleistet als geplant) —
                            fehlende Check-outs im Einsatz nacherfassen
                        </label>
                        <a href="{{ route('einsaetze.index') }}?ansicht=vergangen" class="script-link" target="_blank">Vergangene Einsätze →</a>
                    </li>
                    <li data-step="2">
                        <label><input type="checkbox" data-script="s8" data-step="2">
                            Pro Mitarbeitenden: «Detail →» klicken → <strong>«PDF Zeitnachweis»</strong> herunterladen
                        </label>
                        <div class="text-klein text-hell" style="margin: 0.2rem 0 0 1.5rem;">PDF zeigt alle Tage, Einsätze, Geplant vs. Ist, Wochentotale und Unterschriftsblock</div>
                    </li>
                    <li data-step="3">
                        <label><input type="checkbox" data-script="s8" data-step="3">
                            Zeitnachweis-PDF von Mitarbeitenden unterschreiben lassen (oder per E-Mail zustellen) —
                            CSV-Export für Lohnbuchhaltungssystem
                        </label>
                    </li>
                    <li data-step="4">
                        <label><input type="checkbox" data-script="s8" data-step="4">
                            Pflegende Angehörige gesondert abrechnen:
                            Angehörigenpflege-Übersicht → «Arbeitszeit» → PDF Zeitnachweis → AHV/Lohn abrechnen
                        </label>
                        <a href="{{ route('angehoerigenpflege.index') }}" class="script-link" target="_blank">Angehörigenpflege →</a>
                    </li>
                </ol>
                <div style="margin-top: 0.75rem;">
                    <button onclick="resetScript('s8')" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Script zurücksetzen</button>
                </div>
            </div>
        </div>

        {{-- SCRIPT 9: Einsatz erfassen --}}
        <div class="karte script-karte" id="script-einsatz" style="margin-bottom: 1.5rem;">
            <div class="script-kopf" onclick="toggleScript('s9')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 1rem; font-weight: 600;">📅 Einsatz erfassen</span>
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">Einzelner oder wiederkehrender Einsatz</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="badge badge-grau text-klein" id="fortschritt-s9">0 / 6</span>
                    <span id="pfeil-s9">▼</span>
                </div>
            </div>
            <div id="body-s9" style="display: none; margin-top: 1rem;">
                <div class="tabelle-wrapper" style="margin-bottom: 0.75rem;">
                    <table class="tabelle">
                        <thead><tr><th>Feld</th><th>Was eingeben</th><th>Hinweis</th></tr></thead>
                        <tbody>
                            <tr><td><strong>Klient</strong></td><td>Patient aus Dropdown wählen</td><td>Pflichtfeld</td></tr>
                            <tr><td><strong>Leistungsart</strong></td><td>Grundpflege / Behandlungspflege / Hauswirtschaft usw.</td><td>Pflichtfeld — bestimmt den Tarif</td></tr>
                            <tr><td><strong>Datum</strong></td><td>Datum des Einsatzes</td><td>Pflichtfeld</td></tr>
                            <tr><td><strong>Von / Bis</strong></td><td>Geplante Uhrzeit, z.B. 08:00 – 09:30</td><td>5-Minuten-Schritte (KLV-Vorschrift)</td></tr>
                            <tr><td><strong>Mitarbeiter</strong></td><td>Zuständige Pflegeperson wählen</td><td>Angehörige erscheinen mit 👪</td></tr>
                            <tr><td><strong>Verordnung</strong></td><td>Ärztliche Verordnung verknüpfen (für KK-Abrechnung)</td><td>Nur nötig bei Behandlungspflege</td></tr>
                            <tr><td><strong>Leistungserbringer</strong></td><td>Fachperson oder Pflegender Angehöriger</td><td>Wird automatisch gesetzt wenn Angehöriger gewählt</td></tr>
                            <tr><td><strong>Wiederholung</strong></td><td>Wöchentlich + Wochentage wählen + Enddatum</td><td>Erstellt alle Einsätze der Serie auf einmal</td></tr>
                        </tbody>
                    </table>
                </div>
                <ol class="script-liste" data-script="s9">
                    <li data-step="0">
                        <label><input type="checkbox" data-script="s9" data-step="0">
                            Einsätze → <strong>«+ Neuer Einsatz»</strong> — oder direkt vom Klienten-Detail aus
                        </label>
                        <a href="{{ route('einsaetze.create') }}" class="script-link btn btn-primaer" style="display:inline-block; text-decoration:none; margin-top:0.4rem;" target="_blank">+ Neuer Einsatz →</a>
                    </li>
                    <li data-step="1">
                        <label><input type="checkbox" data-script="s9" data-step="1">
                            Klient, Leistungsart, Datum und Zeit eingeben (Von/Bis in 5-Minuten-Schritten)
                        </label>
                    </li>
                    <li data-step="2">
                        <label><input type="checkbox" data-script="s9" data-step="2">
                            Mitarbeiter wählen — bei Angehörigen: 👪 im Dropdown, Leistungserbringer-Typ wird automatisch gesetzt
                        </label>
                    </li>
                    <li data-step="3">
                        <label><input type="checkbox" data-script="s9" data-step="3">
                            Bei Behandlungspflege: Ärztliche Verordnung verknüpfen (für KK-Abrechnung zwingend)
                        </label>
                    </li>
                    <li data-step="4">
                        <label><input type="checkbox" data-script="s9" data-step="4">
                            Wiederkehrender Einsatz: Wiederholung «Wöchentlich» wählen → Wochentage anklicken → Enddatum →
                            Vorschau zeigt Anzahl Einsätze
                        </label>
                    </li>
                    <li data-step="5">
                        <label><input type="checkbox" data-script="s9" data-step="5">
                            Einsatz speichern → danach Tour zuweisen (Tourenplanung) oder im Kalender prüfen
                        </label>
                        <a href="{{ route('kalender.index') }}" class="script-link" target="_blank">Kalender prüfen →</a>
                    </li>
                </ol>
                <div style="margin-top: 0.75rem;">
                    <button onclick="resetScript('s9')" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Script zurücksetzen</button>
                </div>
            </div>
        </div>

        {{-- SCRIPT 10: Monatsrapportierung --}}
        <div class="karte script-karte" id="script-rapportierung" style="margin-bottom: 1.5rem;">
            <div class="script-kopf" onclick="toggleScript('s10')" style="cursor: pointer; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <span style="font-size: 1rem; font-weight: 600;">📋 Monatsrapportierung erfassen</span>
                    <span class="text-klein text-hell" style="margin-left: 0.75rem;">Minuten pro Leistungstyp und Tag eintragen (Büro-Modus)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span class="badge badge-grau text-klein" id="fortschritt-s10">0 / 5</span>
                    <span id="pfeil-s10">▼</span>
                </div>
            </div>
            <div id="body-s10" style="display: none; margin-top: 1rem;">
                <div class="info-box" style="margin-bottom: 0.75rem; font-size: 0.875rem;">
                    <strong>Rapportierung = Büro-Modus:</strong> Admin trägt Minuten nachträglich im Monatsraster ein — kein Check-in/out nötig.
                    Die Einsätze werden automatisch erstellt und im nächsten Rechnungslauf normal abgerechnet.
                </div>
                <ol class="script-liste" data-script="s10">
                    <li data-step="0">
                        <label><input type="checkbox" data-script="s10" data-step="0">
                            Klient öffnen → Sektion «Einsätze» → <strong>«Rapportierung»</strong>-Button klicken
                        </label>
                        <a href="{{ route('klienten.index') }}" class="script-link" target="_blank">Klienten →</a>
                    </li>
                    <li data-step="1">
                        <label><input type="checkbox" data-script="s10" data-step="1">
                            Monat und Jahr wählen — Navigation mit <strong>‹ ›</strong> Buttons oder Dropdown
                        </label>
                    </li>
                    <li data-step="2">
                        <label><input type="checkbox" data-script="s10" data-step="2">
                            Minuten pro Leistungstyp und Tag eintragen — grüne Felder = bereits erfasst, blaue Buttons = App-Einsatz vorhanden
                        </label>
                        <div class="text-klein text-hell" style="margin: 0.2rem 0 0 1.5rem;">
                            App-Einsätze (blau) können bei Bedarf korrigiert werden — Popup öffnet sich beim Klick.
                        </div>
                    </li>
                    <li data-step="3">
                        <label><input type="checkbox" data-script="s10" data-step="3">
                            <strong>«Speichern»</strong> klicken — Einsätze werden automatisch als Rapportierung erstellt
                        </label>
                    </li>
                    <li data-step="4">
                        <label><input type="checkbox" data-script="s10" data-step="4">
                            Beim nächsten Rechnungslauf werden diese Einsätze automatisch einbezogen — keine weitere Aktion nötig
                        </label>
                        <a href="{{ route('rechnungslauf.create') }}" class="script-link" target="_blank">Rechnungslauf erstellen →</a>
                    </li>
                </ol>
                <div style="margin-top: 0.75rem;">
                    <button onclick="resetScript('s10')" class="btn btn-sekundaer" style="font-size: 0.8125rem;">Script zurücksetzen</button>
                </div>
            </div>
        </div>

    </div>
    {{-- Ende Scripts --}}

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- KAPITEL 1–7 (bestehend, unverändert)                           --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}

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
            <li>Einsatzplanung (Kalender) → Doppelklick auf leere Zelle → Einsatz direkt erfassen</li>
            <li>Oder: Klienten → Patient → <strong>„+ Einsatz planen"</strong> klicken</li>
            <li>Touren → <strong>„+ Tour erstellen"</strong> → Einsätze zuweisen → Route optimieren</li>
        </ol>
        <div class="abschnitt-trenn"></div>
        <div style="font-weight: 600; margin: 1rem 0 0.5rem;">Abends: Nachkontrolle</div>
        <ul style="margin: 0 0 0 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Rapporte → Zwischenfälle (rotes Badge)</li>
            <li>Touren → Orange = zu spät; kein Check-in = nachfragen</li>
            <li>Dashboard → Stat-Chips: offene Einsätze, fehlende Checkouts</li>
        </ul>
    </div>

    {{-- Kapitel 3: Neuer Patient --}}
    <div class="karte" id="kap3" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 3 — Neuer Patient</div>
        <p style="font-size: 0.9375rem; margin-bottom: 0.75rem;">→ Vollständiger Ablauf: <a href="#script-klient" onclick="toggleScript('s1'); document.getElementById('script-klient').scrollIntoView({behavior:'smooth'});" class="link-primaer">▶ Script «Neuer Klient aufnehmen» oben</a></p>
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
        <p style="font-size: 0.9375rem; margin-bottom: 0.75rem;">→ Vollständiger Ablauf: <a href="#script-mitarbeiter" onclick="toggleScript('s3'); document.getElementById('script-mitarbeiter').scrollIntoView({behavior:'smooth'});" class="link-primaer">▶ Script «Neue Mitarbeiterin einrichten» oben</a></p>
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
                <tr><td><strong>Direkt tippen</strong></td><td>Text im Bericht-Feld eingeben</td><td>Kurze Einträge</td></tr>
                <tr><td><strong>Diktieren in Bericht</strong></td><td>🎙 <strong>„Direkt in Bericht diktieren"</strong> antippen → sprechen → Stop</td><td>Schnelle Bericht-Erfassung</td></tr>
                <tr><td><strong>KI Bericht schreiben</strong></td><td>Stichworte oben diktieren oder tippen → <strong>„✨ KI Bericht schreiben"</strong> klicken</td><td>Ausformulierter Bericht aus Stichworten</td></tr>
            </tbody>
        </table>
        </div>
        <div class="info-box" style="margin-bottom: 1rem;">Das Diktat funktioniert nur in <strong>Chrome, Edge oder Safari</strong> — nicht in Firefox.</div>
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
                <tr><td><strong>📋 XML</strong></td><td>Elektronische Abrechnung an Krankenkasse (MediData)</td><td>XML 450.100 (Schweizer Standard)</td></tr>
                <tr><td><strong>📄 PDF</strong></td><td>Druckbare Rechnung für Klient oder Ablage</td><td>PDF, A4</td></tr>
                <tr><td><strong>→ Bexio</strong></td><td>Rechnung in Bexio-Buchhaltung übertragen</td><td>Nur wenn Bexio konfiguriert</td></tr>
                <tr><td><strong>✓ Bexio bezahlt?</strong></td><td>Zahlungsstatus von Bexio abrufen</td><td>Erscheint nach Bexio-Sync</td></tr>
            </tbody>
        </table>
        </div>
        <div class="info-box" style="margin-top: 1rem;">Die Tarife in der Rechnung sind <strong>eingefroren</strong> — Tarifänderungen betreffen nur neue Rechnungen.</div>
    </div>

    {{-- Kapitel 6b: Rechnungslauf --}}
    <div class="karte" id="kap6b" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Kapitel 6b — Rechnungslauf (Monatliche Sammelabrechnung)</div>
        <p style="font-size: 0.9375rem; margin-bottom: 1rem;">→ Vollständiger Ablauf: <a href="#script-monatsabschluss" onclick="toggleScript('s5'); document.getElementById('script-monatsabschluss').scrollIntoView({behavior:'smooth'});" class="link-primaer">▶ Script «Monatsabschluss» oben</a></p>
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
                <tr><td><strong>Email versenden</strong></td><td>PDF-Rechnung per Mail an Klient</td></tr>
                <tr><td><strong>Sammel-PDF drucken</strong></td><td>Alle Post-Rechnungen zusammen → Druckdialog</td></tr>
                <tr><td><strong>XML-ZIP KVG</strong></td><td>Alle XML 450.100-Dateien als ZIP für MediData-Upload</td></tr>
                <tr><td><strong>✓ Bexio Zahlungsabgleich</strong></td><td>Prüft alle Rechnungen des Laufs in Bexio auf Zahlungseingang</td></tr>
            </tbody>
        </table>
        </div>
        <div class="info-box">Solange keine Rechnung im Status «Gesendet» oder «Bezahlt» ist, kann der ganze Lauf storniert werden.</div>
    </div>

    {{-- Kapitel Rapportierung --}}
    <div class="karte" id="kap-rapportierung" style="margin-bottom: 1.25rem;">
        <div class="abschnitt-label" style="margin-bottom: 1rem;">Rapportierung — Büro-Modus (ohne App-Check-in)</div>
        <p style="font-size: 0.9375rem; margin-bottom: 0.75rem;">→ Vollständiger Ablauf: <a href="#script-rapportierung" onclick="toggleScript('s10'); document.getElementById('script-rapportierung').scrollIntoView({behavior:'smooth'});" class="link-primaer">▶ Script «Monatsrapportierung erfassen» oben</a></p>
        <p style="font-size: 0.9375rem; margin-bottom: 1rem;">Die Rapportierung ist der Erfassungsweg für Spitex-Organisationen die ohne App-Einsatz arbeiten. Admin füllt monatlich ein Raster aus — Zeilen sind Leistungstypen, Spalten sind Tage 1–31, Zellen enthalten Minuten.</p>
        <div class="tabelle-wrapper" style="margin-bottom: 1rem;">
        <table class="tabelle">
            <thead><tr><th>Farbe / Anzeige</th><th>Bedeutung</th><th>Aktion</th></tr></thead>
            <tbody>
                <tr><td><span class="badge badge-primaer">Zahl</span> (blau)</td><td>App-Einsatz vorhanden (Check-in/out)</td><td>Klick → Popup zum Korrigieren</td></tr>
                <tr><td>Grünes Eingabefeld mit Zahl</td><td>Rapportierung gespeichert</td><td>Direkt bearbeiten</td></tr>
                <tr><td>Leeres Eingabefeld</td><td>Noch nicht erfasst</td><td>Minuten eintragen</td></tr>
                <tr><td>Orange ● (blinkend)</td><td>App-Einsatz aktiv — Mitarbeiter noch vor Ort</td><td>Klick → Checkout-Zeit eintragen</td></tr>
            </tbody>
        </table>
        </div>
        <div class="abschnitt-trenn"></div>
        <div style="font-weight: 600; margin: 1rem 0 0.5rem;">Wichtige Hinweise</div>
        <ul style="margin: 0 0 0 1.25rem; line-height: 1.8; font-size: 0.9375rem;">
            <li>Rapportierungs-Einsätze werden beim <strong>Rechnungslauf automatisch einbezogen</strong> — kein spezieller Filter nötig</li>
            <li>Die Tagessummen pro Leistungsart erscheinen als weisse Zahl im blauen Leistungsart-Header</li>
            <li>Korrekturen an App-Einsätzen werden mit Zeitstempel in der History protokolliert (ℹ-Button)</li>
            <li>URL: Klient-Detail → Einsätze → <strong>«Rapportierung»</strong>-Button</li>
        </ul>
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
            <div><strong>MA krank / Ferien?</strong><br>Betrieb → <a href="{{ route('vertretung.index') }}" class="link-primaer">Ferienvertretung</a> — alle Einsätze mit einem Klick umbuchen</div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Rechnung ist in Bexio bezahlt, aber in Spitex noch «Gesendet»?</strong><br>Rechnung öffnen → <strong>„✓ Bexio bezahlt?"</strong> klicken</div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Rechnung wurde doppelt erstellt?</strong><br>Den neueren Eintrag öffnen → «Stornieren»</div>
            <div class="abschnitt-trenn"></div>
            <div><strong>Rechnungslauf zeigt Klient nicht in der Vorschau?</strong><br>Mögliche Gründe: keine abgeschlossenen Einsätze (kein Check-out), alle Einsätze bereits verrechnet, oder der Klient ist inaktiv</div>
        </div>
    </div>

</div>

@push('scripts')
<script>
// Script-Toggle
function toggleScript(id) {
    const body  = document.getElementById('body-' + id);
    const pfeil = document.getElementById('pfeil-' + id);
    if (!body) return;
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    if (pfeil) pfeil.style.transform = open ? '' : 'rotate(180deg)';
}

// Checkbox-Persistenz via localStorage + Fortschritt
function ladeScript(id) {
    const checkboxes = document.querySelectorAll('[data-script="' + id + '"]');
    const gespeichert = JSON.parse(localStorage.getItem('script_' + id) || '{}');
    checkboxes.forEach(cb => {
        if (gespeichert[cb.dataset.step]) cb.checked = true;
    });
    aktualisiereForschritt(id);
}

function aktualisiereForschritt(id) {
    const checkboxes = document.querySelectorAll('[data-script="' + id + '"]');
    const gesetzt    = [...checkboxes].filter(c => c.checked).length;
    const gesamt     = checkboxes.length;
    const el         = document.getElementById('fortschritt-' + id);
    if (!el) return;
    el.textContent = gesetzt + ' / ' + gesamt;
    el.className   = 'badge text-klein ' + (gesetzt === gesamt && gesamt > 0 ? 'badge-erfolg' : 'badge-grau');
}

function speichereSchritt(id, step, checked) {
    const data = JSON.parse(localStorage.getItem('script_' + id) || '{}');
    if (checked) data[step] = true; else delete data[step];
    localStorage.setItem('script_' + id, JSON.stringify(data));
    aktualisiereForschritt(id);
}

function resetScript(id) {
    localStorage.removeItem('script_' + id);
    document.querySelectorAll('[data-script="' + id + '"]').forEach(cb => cb.checked = false);
    aktualisiereForschritt(id);
}

// Event-Listener für alle Checkboxen
['s1','s2','s3','s4','s5','s6','s7','s8','s9','s10'].forEach(id => {
    ladeScript(id);
    document.querySelectorAll('[data-script="' + id + '"]').forEach(cb => {
        cb.addEventListener('change', () => speichereSchritt(id, cb.dataset.step, cb.checked));
    });
});
</script>
<style>
.script-liste {
    margin: 0 0 0 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.script-liste li {
    padding: 0.625rem 0.875rem;
    background: var(--cs-hintergrund);
    border-radius: var(--cs-radius);
    border: 1px solid var(--cs-border);
}
.script-liste li label {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    cursor: pointer;
    font-size: 0.9375rem;
    line-height: 1.5;
}
.script-liste li label input[type="checkbox"] {
    margin-top: 0.2rem;
    flex-shrink: 0;
    width: 1rem;
    height: 1rem;
    cursor: pointer;
}
.script-link {
    display: inline-block;
    margin: 0.3rem 0 0 1.5rem;
    font-size: 0.8125rem;
    color: var(--cs-primaer);
    text-decoration: none;
    font-weight: 500;
}
.script-link:hover { text-decoration: underline; }
.script-link.btn { color: #fff; }
.script-link.btn:hover { text-decoration: none; }
</style>
@endpush
</x-layouts.app>
