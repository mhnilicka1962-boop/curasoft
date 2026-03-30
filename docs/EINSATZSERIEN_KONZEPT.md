# Einsatzserien — Konzept Überarbeitung

## Probleme mit der aktuellen Lösung
- Zu viele Einsätze werden weit in die Zukunft generiert (unnötig)
- Wenn Serie abläuft → Klient verschwindet still aus der Planung
- Kein Hinweis wenn Planungshorizont überschritten ist

---

## Neues Konzept

### Serie erstellen
- **Gültig ab/bis**: Default = aktueller Monat (z.B. 01.04. – 30.04.)
- **Checkbox: "Automatisch verlängern"**
  - AN: Cronjob hält immer X Tage voraus aufgefüllt (konfigurierbar)
  - AUS: Generierung nur bis Enddatum, max. **30 Einsätze** voraus (UI + serverseitig validiert)

### Zeitfenster
- Konfigurierbar pro Firma in Firmengrunddaten
- Default: **10 Tage** voraus
- Maximum: **30 Tage** voraus (passend zur Kalenderansicht)

### Klient inaktiv
- Klient auf inaktiv setzen → taucht nirgends mehr auf (Kalender, Planung, Rechnungslauf-Vorschau)
- Bestehende Daten (vergangene Einsätze, Rechnungen) bleiben unangetastet
- Reaktivierung: einfach wieder aktiv setzen → läuft sofort wieder, kein Chaos

---

## Kalender — Visuelle Markierung
- Ab dem Tag wo keine generierten Einsätze mehr vorhanden → **hellrot hinterlegt**
- Hinweistext oben: "Ab [Datum] keine Planung — Serie abgelaufen"
- Sofort sichtbar, kein Suchen

---

## Firmengrunddaten — neues Feld
- **"Einsatz-Vorlauf generieren"**: Anzahl Tage (Default: 10, Max: 30)
- **Button "Jetzt generieren"**: nur für Admin — manuelle Auslösung ohne auf Nacht warten
- **Anzeige letzter Lauf**: "Zuletzt generiert: heute 02:14 Uhr — 12 Einsätze erstellt"

---

## Cronjob / Batch-Command

### Architektur
- **Ein Command für alle Tenants**: `php artisan einsaetze:generieren`
- Läuft durch alle aktiven Tenants (wie `tenant:migrate`)
- Läuft **täglich nachts** (z.B. 02:00 Uhr)

### Pro Tenant
1. Firmengrunddaten lesen → Vorlauf-Tage
2. Alle aktiven Serien mit "Automatisch verlängern = AN" holen
3. Nur für **aktive Klienten**
4. Fehlende Einsätze bis Horizon (heute + Vorlauf-Tage) generieren
5. Log-Eintrag: Anzahl generierter Einsätze, Fehler

### Fehlerbehandlung
- Bei Fehler → Log-Eintrag mit Details
- Email an `info@itjob.ch` (System-Admin)
- Email an den jeweiligen Tenant-Admin

---

## Serien-Liste — Status-Badges
Gilt für alle Typen: Serien, Einzeleinsätze, Pauschalen

| Badge | Farbe | Bedeutung |
|---|---|---|
| `● Aktiv` | grün | Auto-Verlängerung AN, läuft |
| `● Läuft aus` | orange | Enddatum in ≤ 5 Tagen |
| `● Abgelaufen` | rot | Enddatum überschritten |

- Filter/Checkbox in der Liste: "Inaktive / ablaufende anzeigen"
