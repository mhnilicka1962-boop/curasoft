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

## Testdaten (CurasoftDemoSeeder)

- 5 Klienten (Brunner, Weber, Schneider, Keller, Gerber)
- Einsätze 4 Monate zurück + 6 Wochen voraus
- **4 Rechnungsläufe** bereits erstellt:
  - Dez: abgeschlossen, Rechnungen bezahlt
  - Jan: abgeschlossen, Rechnungen gesendet
  - Feb: abgeschlossen, Rechnungen entwurf
  - März: laufend (bis heute), Rechnungen entwurf
- Verrechnet=true für alle Einsätze aus abgeschlossenen Perioden

**Rechnungslauf testen:** `/rechnungslaeufe/create` → nächsten Monat als Periode → Vorschau zeigt Klienten mit aktuellen Einsätzen.

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

## Implementierte Features

- **PDF-Export**: Button auf Rechnungs-Detail → generiert A4-PDF mit QR-Zahlteil (Swiss QR Bill)
- **XML 450.100**: `generalInvoiceRequest` konform, tiers_garant/payant dynamisch, tarmed_code aus leistungsarten
- **Tarife**: werden aus `leistungsregionen` übernommen und in `rechnungs_positionen` eingefroren
- **Rechnungslauf**: Batch-Erstellung für alle Klienten, E-Mail-Versand, Sammel-PDF
- **Einzelstornierung**: Rechnung stornieren → Einsätze wieder verrechenbar
- **Bexio-Sync**: Rechnungen nach Bexio übertragen + Zahlungsstatus zurücklesen
