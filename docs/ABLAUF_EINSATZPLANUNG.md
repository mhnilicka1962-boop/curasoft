# Ablauf Einsatzplanung — Wer, Wo, Was

Übersicht des Planungs- und Ausführungsablaufs für Einsätze in Spitex.

---

## 1. Einsatz planen (anlegen)

| Aspekt | Details |
|--------|---------|
| **Wer** | Admin oder Pflege |
| **Wo** | `/einsaetze/create` oder Klient-Detail → „+ Einsatz“ / Sektion „Einsätze“ → „Einsatz planen“ |
| **Eingaben** | Klient*, Leistungsart*, Datum*, evtl. Zeit (von/bis), evtl. Datum bis (bei Tagespauschale), Mitarbeiter (nur Admin), Bemerkung |
| **Regel** | **Pflege**: Kann nur sich selbst als Mitarbeiter haben (oder leer → eigener Account) |
| | **Admin**: Kann beliebigen Mitarbeiter zuweisen |
| **Ergebnis** | Einsatz mit Status `geplant`, `benutzer_id` gesetzt |

---

## 2. Tourenplanung

| Aspekt | Details |
|--------|---------|
| **Wer** | Admin oder Pflege |
| **Wo** | `/touren` (Tagesansicht) → „+ Neue Tour“ |
| **Eingaben** | Mitarbeiter*, Datum*, Bezeichnung, evtl. Startzeit, Bemerkung |
| **Sicht** | **Pflege**: Nur eigene Touren |
| | **Admin**: Alle Touren, Filter nach Mitarbeiter |
| **Tour anlegen** | Tour = „Tagesroute“ für einen Mitarbeiter an einem Tag |
| **Einsätze zuweisen** | Auf Tour-Detail: „+ Einsatz zuweisen“ |
| | Es werden nur Einsätze angezeigt, die: gleicher Tag, gleicher Mitarbeiter, noch keiner Tour zugewiesen |
| | Reihenfolge per Nummer setzbar |
| **Einsatz entfernen** | Aus Tour entfernen → Einsatz bleibt bestehen, `tour_id` = null |

---

## 3. Check-in / Check-out (Durchführung)

| Aspekt | Details |
|--------|---------|
| **Wer** | Der zugewiesene Mitarbeiter (oder Admin) |
| **Wo** | **Via QR**: Klient hat QR-Code → Mitarbeiter scannt → `/checkin/{token}` zeigt heutige Einsätze für diesen Klienten |
| | **Via Einsatz-Detail**: Check-in GPS / manuell auf Einsatz-Seite |
| **Check-in** | Status → `aktiv`, `checkin_zeit` gesetzt (QR/GPS/manuell) |
| **Check-out** | Auf Seite „Aktiv“ → Check-out → Status `abgeschlossen`, `checkout_zeit` gesetzt |
| **Ergebnis** | Tatsächliche Dauer aus `checkin_zeit` und `checkout_zeit` berechnet |

---

## 4. Übersichten

| Seite | Wer sieht was |
|-------|----------------|
| **Einsätze** `/einsaetze` | **Pflege**: Nur eigene Einsätze |
| | **Admin**: Alle, Filter nach Mitarbeiter |
| | Tabs: Anstehend / Vergangen |
| **Touren** `/touren` | **Pflege**: Nur eigene Touren |
| | **Admin**: Alle, Filter nach Mitarbeiter |
| | Tages-Navigation |
| **Kalender** `/kalender` | **Nur Admin** |
| | Resource Timeline: alle Mitarbeiter als Zeilen, Einsätze als Balken |
| | Woche / 2 Wochen / Monat / Tag |
| | Doppelbelegungen rot markiert |
| | Drag & Drop: Einsatz auf anderen MA oder andere Zeit ziehen |
| **Dashboard** | Eigene Touren heute, letzte Rapporte, Kennzahlen |

---

## 5. Status-Flow

```
geplant → aktiv → abgeschlossen
    ↓
  storniert
```

| Status | Bedeutung |
|--------|-----------|
| `geplant` | Einsatz angelegt, noch nicht gestartet |
| `aktiv` | Check-in erfolgt, Einsatz läuft |
| `abgeschlossen` | Check-out erfolgt |
| `storniert` | Einsatz wurde abgebrochen |

---

## 6. Datenfluss (kurz)

```
Klient (+ Region)
    ↓
Einsatz (Klient, Leistungsart, Benutzer, Datum, Zeit)
    ↓
[optional] Tour (Benutzer, Datum) ← Einsatz zuweisen
    ↓
Check-in (QR/GPS/manuell) → Check-out
```

---

## 7. Hinweise

- **Tourenplanung Reihenfolge**: per Nummer setzbar + "Route optimieren" Button (Nearest-Neighbor)
- **Drag & Drop**: im Kalender (`/kalender`) verfügbar — nicht auf der Touren-Seite
- **Einsatzarten (leistungstypen)**: 30 Stück, Einsätze verknüpfen `leistungsart_id` (5 Leistungsarten); Leistungserfassung (Aktivitäten-Checkliste) auf der Vor-Ort-Seite
- **Klient-Detail**: Einsatz-Formular direkt auf Seite; Alternative: redirect zu `/einsaetze/create?klient_id=...`
