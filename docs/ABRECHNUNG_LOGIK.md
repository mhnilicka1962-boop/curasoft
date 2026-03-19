# Abrechnungslogik Schweizer Spitex — vollständige Dokumentation
# Stand: 2026-03-19 (Session 26)

---

## 1. Grundprinzip — 2 Abrechnungslogiken

Die Einstellung erfolgt **pro Organisation** (eine Spitex = eine Logik):

| | Logik 1: Tiers garant | Logik 2: Tiers payant |
|---|---|---|
| Wer zahlt Spitex | Patient (alles) | KK + Patient + Gemeinde (direkt) |
| KK-Kontakt | keiner | direkt, XML 450.100 |
| Gemeinde-Kontakt | keiner (Patient macht selbst) | direkt, Restfinanzierung |
| Voraussetzung | KVG-Anerkennung | KVG + Tarifvertrag + Verwaltungsvertrag santésuisse |
| Typisch für | kleine/private Spitex | grosse/öffentliche Spitex |
| Beispiel | CuraPflege GmbH | Spitex Stadt Zürich |

---

## 2. Was bei beiden Logiken identisch ist (95%)

- Einsätze erfassen / Leistungsarten / Einsatzarten
- Check-in / Check-out (Minuten pro Leistungsart)
- Rapporte
- Touren / Kalender
- Klienten / Stammdaten
- Mitarbeiterrapportierung
- Angehörigenpflege

**Nur der Abrechnungs-Output am Ende unterscheidet sich.**

---

## 3. Leistungsarten (gesetzlich fix, KLV Art. 7)

5 Leistungsarten — gleich für alle Spitex in der Schweiz:

| Leistungsart | KVG-pflichtig | KK zahlt | Patient zahlt |
|---|---|---|---|
| Grundpflege | ja | kantonal (z.B. 52.60/h) | Rest (z.B. 39.30/h) |
| Untersuchung/Behandlung | ja | kantonal (z.B. 65.40/h) | Rest |
| Abklärung/Beratung | ja | kantonal (z.B. 79.80/h) | Rest |
| Hauswirtschaft | **nein** | **0** | **alles** |
| Pauschale | — | je nach Typ | je nach Typ |

**Tarife (Ansätze) sind kantonal verschieden** — pro Leistungsart, pro Kanton in `leistungsregionen`.

---

## 4. Das `verrechnung` Flag

Bedeutung: **"Wird diese Leistungsart separat verrechnet?"**

| Leistungsart | Tiers garant (CuraPflege) | Tiers payant |
|---|---|---|
| Grundpflege | `false` — in Pauschale inbegriffen | `true` |
| Hauswirtschaft | `false` — in Pauschale inbegriffen | `true` — direkt an Patient |
| Abklärung/Beratung | `true` — separat verrechnet | `true` |
| Untersuchung/Behandlung | `true` — separat verrechnet | `true` |
| Pauschale | `false` — eigene Logik | — |

**Einstellung pro Organisation** — jede Spitex setzt es selbst entsprechend ihrem Modell.

---

## 5. Patientenbeitrag (KVG Art. 25a Abs. 5)

Max. **20% des höchsten Bundesratstarifs** — kantonal unterschiedlich:

| Kanton | Max. Patientenbeitrag/Tag |
|---|---|
| Zürich | CHF 7.65 |
| Luzern | CHF 15.35 |
| Aargau | ~CHF 10-15 |

**Berechnung pro Tag:**
- Patientenanteil = Vollkosten - KK-Anteil
- Patient zahlt: `min(max_tag, 20% * Patientenanteil)`
- Wenn 20% < max → Stern (*) auf Rapportblatt
- Gemeinde zahlt: Patientenanteil - was Patient zahlt

---

## 6. Logik 1 — Tiers garant (z.B. CuraPflege)

### Geldfluss
```
Spitex erbringt Leistung
       ↓
Rechnung 1 → Patient: Pauschale (CHF 8'500/Monat, fix)
Rechnung 2 → Patient: Zusatzleistungen (Abklärung + Untersuchung, wenn vorhanden)
       ↓
Patient zahlt alles direkt an Spitex
       ↓
Patient reicht Rapportblatt ein bei:
  → KK        → bekommt KVG-Anteil zurück
  → Gemeinde  → bekommt Restfinanzierung zurück
  → evtl. Arzt
```

### Dokumente pro Patient pro Monat

**Dokument 1 — Pauschalen-Rechnung (immer):**
- Fixer Monatsbetrag (z.B. CHF 8'500)
- Zahlbar direkt an Spitex

**Dokument 2 — Rapportblatt (immer, 3 Seiten):**

*Seite 1 — Anschrift + Rechnung:*
- Adresse Spitex + Patient
- Rechnung für Zusatzleistungen (Abklärung/Beratung + Untersuchung/Behandlung)
- CHF 0.00 wenn keine Zusatzleistungen im Monat

*Seite 2 — QR-Zahlteil:*
- Swiss QR-Code
- Nur wenn Betrag > CHF 0

*Seite 3 — Tagesaufstellung:*
```
Tag | Abkl.Min | Unt.Min | GP.Min | Taxe Abkl | Taxe Unt | Taxe GP | KK Abkl | KK Unt | KK GP | Pat.Beitrag max 15.35/20% | Beitrag Total
1   | -        | -       | 120    | -         | -        | 191.80  | -       | -      | 105.20| 15.35                     | 71.25
...
Total ...
```

---

## 7. Logik 2 — Tiers payant (grosse/öffentliche Spitex)

### Geldfluss
```
Spitex erbringt Leistung
       ↓
Rechnung 1 → KK (XML 450.100 via MediData):
  KVG-Leistungen: KK-Anteil direkt
Rechnung 2 → Patient (PDF):
  KVG-Patientenanteil (max. 15.35/Tag oder 20%)
  + Hauswirtschaft voll
Rechnung 3 → Gemeinde/Kanton:
  Restfinanzierung = Vollkosten - KK - Patient
  (nur KVG-Leistungen, nicht Hauswirtschaft)
```

### Gemeinde-Abrechnung
- Zuständig: Wohnkanton des Patienten
- Format: elektronisch (XML) oder PDF je nach Kanton
- Ab 2026: zunehmend elektronisch (z.B. Kanton Solothurn)
- Ausnahme: < CHF 10'000/Jahr → Papier erlaubt

### KK-Abrechnung
- Standard: XML 450.100 via MediData — **Pflicht**
- Ausnahme: < CHF 10'000/Jahr → Papier/PDF möglich
- PDF an KK: nur für sehr kleine Anbieter als Ausnahme

---

## 8. Hauswirtschaft — Sonderfall

- **Nicht KVG-pflichtig** → KK zahlt nichts
- Bei tiers garant: in Pauschale inbegriffen oder separat an Patient
- Bei tiers payant: **immer direkt an Patient** — nie an KK oder Gemeinde

---

## 9. Fehler im aktuellen System (zu beheben)

| Fehler | Beschreibung | Priorität |
|---|---|---|
| `verrechnung` Flag ignoriert | Billing-Logik prüft nie ob verrechnung=true/false | hoch |
| `einsatz_minuten/stunden/tage` ignoriert | Immer Stundenberechnung, Flags wirkungslos | mittel |
| `tiers_payant` in PDF ignoriert | QR-Zahlteil zeigt immer betrag_total | hoch |
| `kassenpflichtig` ignoriert | Feld existiert aber nie geprüft | mittel |
| Tagespauschale nur kvg/klient | Kein kombiniert möglich | mittel |
| **Rapportblatt fehlt komplett** | Muss neu gebaut werden | **kritisch für CuraPflege** |
| Keine Organisations-Einstellung | Logik 1 vs. 2 nicht steuerbar | hoch |

---

## 10. Noch offen / zu klären

- [ ] Wie genau Gemeinde-Rechnung Format pro Kanton?
- [ ] `organisationen.abrechnungslogik` = `tiers_garant` | `tiers_payant` als neue Einstellung?
- [ ] Rapportblatt: exakte Spaltenstruktur aus Altsystem übernehmen
- [ ] Patientenbeitrag-Berechnung: woher kommt der max. Betrag pro Kanton? (aus `klient_beitraege`?)
