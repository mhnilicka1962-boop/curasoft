# AGB & Pilotpartner-Vereinbarung — Entwurf

## Status
Entwurf / Gedankenstütze. Vor erstem produktiven Einsatz durch Anwalt prüfen lassen
(einmalig, Fokus: nDSG-Konformität + Haftungsklauseln nach OR Art. 100).

---

## Relevante Schweizer Gesetze

| Gesetz | Was es regelt |
|--------|--------------|
| OR Art. 97 ff. | Haftung bei Nicht-/Schlechterfüllung |
| OR Art. 100 | Haftungsausschluss — Grenze bei Grobfahrlässigkeit (nicht wegbedingbar!) |
| URG | Urheberrecht Software — Schutz des Codes |
| nDSG (seit 01.09.2023) | Datenschutz, Gesundheitsdaten, Auftragsbearbeitung |

---

## Kern-Klauseln AGB (SaaS)

### 1. Eigentümerschaft Software
- Alle Rechte (Code, Weiterentwicklungen, Patches) bleiben bei Mathias Hnilicka / CuraSoft
- Kunde erhält ausschliesslich ein einfaches, nicht übertragbares Nutzungsrecht
- Kein Reverse Engineering, keine Weitergabe, keine Sublizenzierung
- Feedback des Kunden (Verbesserungsvorschläge, Bug-Reports) darf CuraSoft
  frei ins Produkt einfliessen lassen — keine Vergütungspflicht

### 2. Haftung bei Ausfall (kritisch)
**Was gilt zwingend (OR Art. 100):**
- Vorsatz: Haftungsausschluss nichtig → haftet immer
- Grobe Fahrlässigkeit: Haftungsausschluss nichtig → haftet immer
- Leichte Fahrlässigkeit: **kann ausgeschlossen werden** → hier ansetzen

**Formulierung Entwurf:**
> CuraSoft haftet nicht für Schäden, die durch leichte Fahrlässigkeit entstehen,
> insbesondere nicht für: Datenverlust bei Systemausfall (wenn Kunde eigene Backups führt),
> entgangenen Gewinn, Folgeschäden, Betriebsunterbrechungen.
> Die Haftung ist in jedem Fall auf den Betrag der geleisteten Jahresgebühr begrenzt.

**Praktisch:** Hosting auf cPanel devitjob.ch — tägliche Backups dokumentieren.
Je besser die Backup-Doku, desto stärker der Haftungsausschluss.

### 3. Datenschutz / nDSG (besonders wichtig — Gesundheitsdaten!)
Gesundheitsdaten = "besonders schützenswerte Personendaten" → Busse bis CHF 250'000

**Zwingend als AVV (Auftragsbearbeitungsvertrag) — Anhang zu AGB:**
- CuraSoft = Auftragsbearbeiter (verarbeitet nur auf Weisung des Kunden)
- Kunde (Spitex) = Verantwortlicher (trägt Hauptverantwortung)
- Datenspeicherort: Schweiz / EU (cPanel Schweiz ✅)
- Technische Massnahmen: HTTPS, Verschlüsselung, Zugriffskontrolle pro Tenant ✅
- Datenpannen: CuraSoft meldet "sobald als möglich" an den Kunden
- Nach Vertragsende: Daten innert 30 Tagen löschen oder exportieren + Bestätigung
- Sub-Auftragsbearbeiter: devitjob.ch als Hoster offenlegen

### 4. Verfügbarkeit / SLA
- Angestrebte Verfügbarkeit: 99% (Ausnahme: angekündigte Wartungsfenster)
- Support: direkt per E-Mail/Telefon an Mathias — Reaktionszeit Werktage innert 24h
- Bei SLA-Unterschreitung: Gutschrift (kein Schadenersatz)
- **Pilotphase:** Kein SLA — Software wird "as is" bereitgestellt

### 5. Kündigung
- Mindestlaufzeit: 12 Monate (nach Pilotphase)
- Kündigungsfrist: 3 Monate zum Ende der Laufzeit, schriftlich (E-Mail genügt)
- Automatische Verlängerung um 12 Monate wenn nicht fristgerecht gekündigt
- Sofortige Kündigung durch CuraSoft bei: Zahlungsrückstand >30 Tage, Missbrauch
- Nach Kündigung: 30 Tage Datenexport-Fenster, danach vollständige Löschung

### 6. Gerichtsstand & Recht
- Anwendbares Recht: Schweizerisches Recht
- Gerichtsstand: Sitz von CuraSoft (Wohnsitz Mathias Hnilicka)

---

## Pilotpartner-Vereinbarung (separate kurze Vereinbarung)

### Was drin steht

**Leistung CuraSoft:**
- Kostenlose Nutzung für 12 Monate
- Eigene Instanz, Grundset eingerichtet
- Direkter Support

**Gegenleistung Pilotpartner (explizit):**
- Aktive Nutzung im echten Betrieb
- Monatliches Feedback (mind. 30 Min Gespräch oder strukturierter Fragebogen)
- Sofortige Meldung von Fehlern
- Erlaubnis zur Nennung als Referenzkunde (Name + Branche) auf Website/Marketing
- Bereitschaft zu 1–2 Referenzgesprächen mit Interessenten pro Jahr

**Rechte Software:**
- Alle Rechte bleiben bei CuraSoft — Pilotpartner erhält nur Nutzungsrecht
- Feedback geht als unwiderrufliche Lizenz an CuraSoft über

**Haftung in Pilotphase:**
- Software wird "as is" bereitgestellt — Beta-Charakter explizit
- Kein SLA, kein Verfügbarkeitsversprechen
- Kunde verantwortlich für eigene Datensicherung
- OR Art. 100 gilt trotzdem (Vorsatz/grobe Fahrlässigkeit nicht ausschliessbar)

**Vertraulichkeit:**
- Beide Seiten behandeln Infos über die andere Partei vertraulich
- CuraSoft nutzt Kundendaten nicht für eigene Zwecke (ausser aggregierte, anonyme Statistiken)

**Kündigung:**
- Beidseits mit 30 Tagen Frist kündbar
- Nach Kündigung: 30 Tage Datenexport, dann Löschung

**Übergang nach Pilotphase:**
- 90 Tage vor Ende: CuraSoft unterbreitet Angebot zu regulären Konditionen
- Wenn kein Vertrag: Zugang endet, Daten werden gelöscht

**Datenschutz:**
- Gleicher AVV wie in regulärer AGB — auch in Pilotphase vollumfänglich gültig
- Keine Ausnahme bei Gesundheitsdaten nur weil "Pilot"

---

## Empfehlung: Anwalt einschalten für

1. AVV / nDSG-Konformität bei Gesundheitsdaten (einmalig)
2. Haftungsklauseln auf Zulässigkeit nach OR Art. 100 prüfen
3. Pilotpartner-Vereinbarung auf 1–2 Seiten aufsetzen lassen

**Kosten:** Schätzung CHF 500–1500 einmalig — Investition lohnt sich vor erstem Produktivkunden.

---

## Basis-Vorlage empfohlen
**Swico / swissICT Modellverträge** — paritätisch für CH-IT-Recht, seriöseste Grundlage.
Erhältlich über Swico (Branchenverband Schweizer ICT-Unternehmen).
