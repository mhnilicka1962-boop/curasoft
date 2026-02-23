# Ablauf Rechnung — DB, Testdaten, Flow

---

## DB-Struktur

### Tabelle `rechnungen`
| Spalte | Typ | Bedeutung |
|--------|-----|-----------|
| organisation_id | FK | Organisation |
| klient_id | FK | Rechnungsempfänger (Klient) |
| rechnungsnummer | string | z.B. RE-2026-0001 (unique) |
| periode_von / periode_bis | date | Abrechnungszeitraum |
| rechnungsdatum | date | Ausstellungsdatum |
| betrag_patient | decimal | Selbstbehalt Klient |
| betrag_kk | decimal | Kassenanteil |
| betrag_total | decimal | patient + kk |
| status | enum | entwurf, gesendet, bezahlt, storniert |
| pdf_pfad | string | optional |
| bexio_rechnung_id | int | Bexio-Sync |
| xml_export_datum, xml_export_pfad | | KK-XML-Export |
| tarmed_fall_nr | string | Fall-Nr. für Kassenabrechnung |

### Tabelle `rechnungs_positionen`
| Spalte | Typ | Bedeutung |
|--------|-----|-----------|
| rechnung_id | FK | Zugehörige Rechnung |
| einsatz_id | FK | Einsatz (Pflegebesuch) |
| leistungstyp_id | FK | nullable — Einsatzart für Position |
| datum | date | Einsatzdatum |
| menge | int | Minuten (bei einheit=minuten) |
| einheit | string | minuten, stunden, tage |
| tarif_patient, tarif_kk | decimal | Tarif pro Stunde |
| betrag_patient, betrag_kk | decimal | menge/60 * tarif |

**Berechnung:** `betrag = (menge / 60) * tarif` pro Stunde.

---

## Ablauf Rechnung erstellen

| Schritt | Wo | Was |
|---------|-----|-----|
| 1 | `/rechnungen/create` | Klient + Periode (von/bis) wählen → «Einsätze laden» |
| 2 | — | System lädt: Einsätze mit `verrechnet=false`, `checkout_zeit` gesetzt, `datum` in Periode |
| 3 | Tabelle | Einsätze auswählen (Checkboxen), alle vorausgewählt |
| 4 | «Rechnung erstellen» | Rechnung anlegen, pro Einsatz eine Position (`menge` = `einsatz.minuten`) |
| 5 | — | Einsätze werden `verrechnet=true` gesetzt |
| 6 | Rechnungs-Detail | Bei Status «Entwurf»: Tarife (Patient/KK pro Std.) editierbar → Beträge werden neu berechnet |

### Voraussetzungen für verrechenbare Einsätze
- `checkout_zeit` muss gesetzt sein (Check-out erfolgt)
- `verrechnet = false`
- `datum` innerhalb der gewählten Periode

### Einsatz.minuten
- Wird beim Check-out aus `dauerMinuten()` (checkin → checkout) gespeichert
- Falls null: RechnungsPosition verwendet 0 → manuell prüfen

---

## Testdaten (TestdatenSeeder)

### Rechnung
- **Eine Rechnung** für Maria Schmidt
- Periode: letzter Monat (1.–letzter Tag)
- Beträge: 180 (Patient) + 720 (KK) = 900 CHF
- Status: entwurf

### Hinweis Testdaten
- Die Test-Rechnung hat **keine rechnungs_positionen**
- Sie wurde direkt mit Beträgen eingefügt (ohne Einsätze → Positionen)
- In der Rechnungs-Detailansicht erscheint daher eine **leere Positionstabelle**

### Einsätze für Rechnung nutzbar
- Maria: 4 abgeschlossene Einsätze im letzten Monat (je 120 Min, verrechnet=false)
- Hans: 4 abgeschlossene Einsätze (verrechnet=false)

**Rechnung «richtig» testen:** `/rechnungen/create` → Klient Maria → Periode letzter Monat → Einsätze laden → Rechnung erstellen. Dann hat die Rechnung echte Positionen mit Minuten, Tarife können gesetzt werden.

---

## Rechnungsnummer
`Rechnung::naechsteNummer($orgId)` → Format: `RE-{Jahr}-{0001}`  
Zähler pro Jahr, 4-stellig.

---

## Status-Workflow
```
entwurf → gesendet → bezahlt
    ↓
  storniert (von entwurf oder gesendet)
```

---

## Offene Punkte (CLAUDE.md)
- PDF-Button: Placeholder, «Folgt bald»
- XML-Export: tarmed_code auf leistungsarten fehlt
- Tarife: Aktuell manuell pro Position; evtl. später aus leistungsregionen/Tarif-Setup übernehmen
