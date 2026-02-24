# Testscript — Vollständiger Fall Spitex

**Datum:** 24.02.2026
**Ziel:** Einen kompletten Pflegefall von A bis Z durchspielen
**URL:** http://spitex.test
**Admin-Login:** admin@spitex.ch / Admin2026!

---

## SCHRITT 1 — Arzt erfassen
**URL:** http://spitex.test/aerzte → + Neuer Arzt

| Feld | Wert |
|---|---|
| Anrede | Dr. Herr |
| Vorname | Andreas |
| Nachname | Meier |
| Fachrichtung | Allgemeinmedizin |
| Praxis | Arztpraxis Meier |
| Strasse | Bahnhofstrasse 14 |
| PLZ | 5400 |
| Ort | Baden |
| Telefon | 056 222 33 44 |
| E-Mail | a.meier@praxis-meier.ch |
| ZSR-Nr. | K123456 |

→ **Speichern**

---

## SCHRITT 2 — Patientin erfassen
**URL:** http://spitex.test/klienten → + Neuer Klient

### Persönliche Daten
| Feld | Wert |
|---|---|
| Anrede | Frau |
| Vorname | Elisabeth |
| Nachname | Brunner |
| Klient-Typ | Patient (Standard) |
| Geburtsdatum | 12.03.1942 |
| Geschlecht | Weiblich |
| Zivilstand | Verwitwet |

### Kontakt & Adresse
| Feld | Wert |
|---|---|
| Strasse | Rosenweg 7 |
| PLZ | 5400 |
| Ort | Baden |
| Kanton | AG |
| Telefon | 056 444 55 66 |
| Notfallnummer | 079 333 22 11 |
| E-Mail | — (leer lassen) |

### Einsatz-Planung
| Feld | Wert |
|---|---|
| Datum Erstkontakt | 20.02.2026 |
| Einsatz geplant ab | 24.02.2026 |
| Einsatz geplant bis | 30.06.2026 |
| Zuständig | Sandra Huber |

### Krankenkasse & AHV
| Feld | Wert |
|---|---|
| Krankenkasse | CSS |
| Krankenkassen-Nr. | 756.4321.8765.09 |
| AHV-Nummer | 756.4321.8765.09 |
| Zahlbar (Tage) | 30 |

→ **Speichern**

---

## SCHRITT 3 — Krankenkasse zuweisen
**URL:** http://spitex.test/klienten/{id} → Krankenkassen → + Zuweisen

| Feld | Wert |
|---|---|
| Krankenkasse | CSS Kranken-Versicherung AG |
| Versicherungstyp | KVG |
| Versichertennummer | 756.4321.8765.09 |
| Deckungstyp | Grundversicherung |
| Tiers | Tiers garant |
| Gültig ab | 01.01.2026 |

→ **Zuweisen**

---

## SCHRITT 4 — Arzt zuweisen
**URL:** http://spitex.test/klienten/{id} → Behandelnde Ärzte → + Arzt zuweisen

| Feld | Wert |
|---|---|
| Arzt | Dr. Andreas Meier |
| Rolle | Hauptarzt |

→ **Zuweisen**

---

## SCHRITT 5 — Notfallkontakt erfassen
**URL:** http://spitex.test/klienten/{id} → Kontakte & Angehörige → + Kontakt

| Feld | Wert |
|---|---|
| Vorname | Thomas |
| Nachname | Brunner |
| Rolle | Sohn |
| Telefon | 079 555 44 33 |
| E-Mail | thomas.brunner@gmail.com |
| Notfallkontakt | ✅ Ja |
| Bevollmächtigt | ✅ Ja |
| Erhält Rechnungen | ✅ Ja |

→ **Speichern**

---

## SCHRITT 6 — Diagnose erfassen
**URL:** http://spitex.test/klienten/{id} → Diagnosen → + Diagnose

| Feld | Wert |
|---|---|
| ICD-10 Code | I10 |
| Bezeichnung | Essentielle (primäre) Hypertonie |
| Typ | Hauptdiagnose |
| Datum | 20.02.2026 |

→ **Speichern**

Zweite Diagnose:

| Feld | Wert |
|---|---|
| ICD-10 Code | E11 |
| Bezeichnung | Diabetes mellitus Typ 2 |
| Typ | Nebendiagnose |
| Datum | 20.02.2026 |

→ **Speichern**

---

## SCHRITT 7 — Ärztliche Verordnung erfassen
**URL:** http://spitex.test/klienten/{id} → Ärztliche Verordnungen → + Verordnung

| Feld | Wert |
|---|---|
| Leistungsart | Grundpflege |
| Verordnungs-Nr. | VO-2026-0124 |
| Arzt | Dr. Andreas Meier |
| Gültig ab | 24.02.2026 |
| Gültig bis | 23.05.2026 |
| Bemerkung | Körperpflege morgens, Unterstützung beim Ankleiden |

→ **Speichern**

---

## SCHRITT 8 — Mitarbeiterin prüfen
**URL:** http://spitex.test/mitarbeiter → Sandra Huber

Prüfen ob Leistungsarten korrekt gesetzt:
- → Abschnitt "Erlaubte Leistungsarten"
- ✅ Grundpflege ankreuzen
- ✅ Hauswirtschaft ankreuzen
- ❌ Behandlungspflege NICHT ankreuzen
→ **Leistungsarten speichern**

---

## SCHRITT 9 — Einsatz planen
**URL:** http://spitex.test/einsaetze → + Neuer Einsatz

| Feld | Wert |
|---|---|
| Klient | Brunner Elisabeth |
| Leistungsart | Grundpflege |
| Datum | 24.02.2026 |
| Von (geplant) | 08:00 |
| Bis (geplant) | 08:45 |
| Mitarbeiter | Sandra Huber |
| Ärztliche Verordnung | Grundpflege · gültig 24.02.2026 (VO-2026-0124) |
| Leistungserbringer | Fachperson (Standard) |
| Bemerkung | Schlüssel unter der Fussmatte. Hund heisst Bello — harmlos. |

→ **Einsatz anlegen**

---

## SCHRITT 10 — Tour erstellen (als Admin)
**URL:** http://spitex.test/touren → + Neue Tour

| Feld | Wert |
|---|---|
| Mitarbeiter | Sandra Huber |
| Datum | 24.02.2026 |
| Bezeichnung | Morgentour Sandra 24.02. |
| Startzeit | 07:45 |

→ Den soeben erstellten Einsatz (Brunner Elisabeth) ankreuzen
→ **Tour erstellen**

---

## SCHRITT 11 — Sandra loggt sich ein
**URL:** http://spitex.test/login

- Tab "Link per E-Mail" → sandra.huber@test.curasoft → Link senden
- *(Admin holt Link aus Log)*
- Sandra öffnet Link → landet direkt auf Tourenplan

**Was Sandra sieht:**
- "Deine Tour heute"
- Tour "Morgentour Sandra 24.02."
- Einsatz: Brunner Elisabeth — Grundpflege — 08:00

---

## SCHRITT 12 — Vor-Ort-Ansicht
- Sandra klickt auf "Brunner Elisabeth" in der Tour
- Vor-Ort-Ansicht öffnet sich:
  - Adresse: Rosenweg 7, 5400 Baden → Maps-Link
  - Notfall: 079 333 22 11
  - Hinweis: "Schlüssel unter der Fussmatte..."
  - Diagnosen: I10, E11

→ **▶ Check-in jetzt** klicken

---

## SCHRITT 13 — Rapport schreiben
**URL:** Vor-Ort-Ansicht → "+ Rapport" (unten)

| Feld | Wert |
|---|---|
| Klient | Brunner Elisabeth |
| Typ | Pflegerapport |
| Datum | 24.02.2026 |
| Inhalt | Frau Brunner wurde vollständig gepflegt. Körperpflege durchgeführt, beim Ankleiden assistiert. Blutdruck gemessen: 138/82 mmHg. Frau Brunner ist wohlauf und guter Stimmung. Hund wurde ebenfalls kurz rausgelassen. Nächster Einsatz morgen 08:00 Uhr. |

→ **Rapport speichern**

---

## SCHRITT 14 — Check-out
- Zurück zur Vor-Ort-Ansicht
- → **■ Check-out** klicken
- Einsatz ist abgeschlossen ✅

---

## SCHRITT 15 — Rechnung erstellen (als Admin)
**URL:** http://spitex.test/rechnungen → + Neue Rechnung

| Feld | Wert |
|---|---|
| Klient | Brunner Elisabeth |
| Rechnungsdatum | 28.02.2026 |
| Leistungsperiode von | 24.02.2026 |
| Leistungsperiode bis | 28.02.2026 |

→ Einsatz "24.02. Grundpflege 45 min" erscheint als Position
→ **Rechnung erstellen**

---

## SCHRITT 16 — XML 450.100 exportieren
**URL:** http://spitex.test/rechnungen/{id}

→ Button **📋 XML** klicken
→ XML-Datei wird heruntergeladen
→ Öffnen und prüfen:
- Root: `generalInvoiceRequest` ✅
- `tiers_garant` (weil CSS Tiers garant) ✅
- Patient: Brunner Elisabeth, Geburtsdatum 1942-03-12 ✅
- Diagnosen: I10, E11 ✅
- Service: tariff_type=311, Minuten=45 ✅
- Verordnungs-Nr: VO-2026-0124 ✅

---

## CHECKLISTE — Alles getestet?

| | Feature |
|---|---|
| ☐ | Arzt erfasst |
| ☐ | Klientin erfasst mit allen Daten |
| ☐ | Krankenkasse zugewiesen |
| ☐ | Notfallkontakt erfasst |
| ☐ | Diagnosen erfasst |
| ☐ | Ärztliche Verordnung erfasst |
| ☐ | Mitarbeiterin Leistungsarten gesetzt |
| ☐ | Einsatz geplant mit Verordnung |
| ☐ | Tour erstellt und Einsatz zugewiesen |
| ☐ | Sandra eingeloggt → direkt auf Tourenplan |
| ☐ | Vor-Ort-Ansicht geöffnet |
| ☐ | Check-in durchgeführt |
| ☐ | Rapport geschrieben |
| ☐ | Check-out durchgeführt |
| ☐ | Rechnung erstellt |
| ☐ | XML 450.100 exportiert und geprüft |
