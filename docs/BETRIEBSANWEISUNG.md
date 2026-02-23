# Spitex — Betriebsanweisung
**Stand: 22.02.2026**

---

## Einloggen

| Weg | Beschreibung |
|-----|-------------|
| **Passwort** | E-Mail + Passwort eingeben |
| **Link per E-Mail** | E-Mail eingeben → Login-Link kommt per Mail → anklicken |
| **Face ID / Fingerabdruck** | Nur wenn Passkey registriert (unter Profil einrichten) |

> Auf Handy: App installierbar über "Zum Homescreen hinzufügen" (iOS) oder Install-Banner (Android)

---

## Tagesablauf Admin

### Morgens: Planen

**Schritt 1 — Einsätze anlegen** (falls noch nicht vorhanden)
1. `Klienten` → Patient öffnen
2. Oben im Pflegeplan: **"+ Einsatz planen"** klicken
3. Mitarbeiter, Datum, Zeit, Leistungsart wählen → **Einsatz planen**
4. Für regelmässige Einsätze: Wiederholung = Wöchentlich, Wochentage wählen, Enddatum setzen

**Schritt 2 — Touren erstellen**
1. `Touren` → Datum wählen
2. Gelbe Warnung "Nicht eingeplante Einsätze" zeigt offene Einsätze pro Mitarbeiter
3. **"+ Tour erstellen"** klicken → alle Einsätze des MA sind vorgehakt
4. Bezeichnung prüfen → **Tour erstellen**

---

### Tagsüber: Überblick

**Touren-Übersicht**
- `Touren` → Datum wählen
- Jede Tour zeigt: Einsätze, Status (Geplant / Gestartet / Abgeschlossen)
- Detail-Button → Check-in/out-Zeiten, Abweichungen, Rapporte

**Pflegeplan Klient**
- `Klienten` → Patient öffnen
- Ganz oben: 14-Tage-Übersicht wer, wann, welche Leistung
- Tage ohne Einsatz: grau, mit "Kein Einsatz geplant"

---

### Abends: Nachkontrolle

**Rapporte prüfen**
- `Rapporte` → Heute filtern
- Zwischenfälle erscheinen als rotes Badge

**Einsätze ohne Check-in prüfen**
- `Touren` → Tour-Detail öffnen
- Orange = zu spät eingecheckt (>5 Min.)
- Kein Check-in = Mitarbeiter hat nicht eingecheckt → nachfragen

---

## Wiederkehrende Einsätze

### Anlegen
1. `Klienten` → Patient → **"+ Einsatz planen"**
   oder direkt: `Einsätze` → **"+ Neuer Einsatz"**
2. Leistungsart, Mitarbeiter, Startdatum, Zeit wählen
3. **Wiederholung: Wöchentlich** wählen
4. Wochentage anklicken (z.B. Mo / Mi / Fr)
5. **"Wiederholen bis"** Datum setzen
6. Preview zeigt Anzahl → **Einsätze anlegen**

### Löschen (ganze Serie)
1. `Klienten` → Patient → Pflegeplan
2. Beim ersten Serien-Einsatz: **"× Serie löschen"** klicken
3. Bestätigen → alle zukünftigen Einsätze der Serie gelöscht
4. Bereits abgeschlossene Einsätze bleiben erhalten

---

## Neuer Patient (Klient)

1. `Klienten` → **"+ Neuer Klient"**
2. Pflichtfelder: Vorname, Nachname, Region (Kanton)
3. Nach dem Speichern im Klienten-Detail ergänzen:
   - Adresse
   - Krankenkasse (KVG + ggf. VVG)
   - Pflegestufe (BESA-Einstufung)
   - Behandelnder Arzt
   - Kontaktperson / Angehörige
4. Ersten Einsatz planen (Pflegeplan → "+ Einsatz planen")

---

## Neuer Mitarbeiter

1. `Mitarbeiter` → **"+ Neuer Mitarbeiter"**
2. E-Mail-Adresse, Rolle (Pflege / Buchhaltung / Admin)
3. Einladungs-Mail wird automatisch verschickt (48h gültig)
4. Mitarbeiter setzt Passwort über Link in der Mail
5. Im Mitarbeiter-Detail: Qualifikationen + Klienten-Zuweisung ergänzen

---

## Face ID einrichten (Mitarbeiter)

1. Einloggen (Passwort oder Magic Link)
2. Oben rechts auf Namen klicken → **Profil**
3. Geräte-Name eingeben (z.B. "iPhone Sandra")
4. **"Passkey registrieren"** klicken → Face ID / Fingerabdruck bestätigen
5. Ab sofort: Login mit Face ID möglich (Tab "Face ID" auf Loginseite)

---

## Rapport schreiben (Mitarbeiter Pflege)

1. Nach dem Check-out: `Rapporte` → **"+ Neuer Rapport"**
2. Klient wählen, Typ (Verlaufsbericht / Zwischenfall / Übergabe)
3. Text eingeben → speichern
4. Zwischenfälle → Admin sieht rotes Badge in der Tour-Übersicht

---

## Rechnung erstellen

1. `Rechnungen` → **"+ Neue Rechnung"**
2. Klient wählen → Leistungsperiode (Von–Bis)
3. Einsätze werden automatisch einbezogen
4. XML-Export für Krankenkasse: Rechnung öffnen → **"XML exportieren"**

---

## Häufige Fragen

**Einsatz falsch zugewiesen (falscher Mitarbeiter)?**
→ `Einsätze` → Einsatz öffnen → Bearbeiten → Mitarbeiter ändern

**Tour-Einsatz entfernen?**
→ `Touren` → Tour-Detail → × beim Einsatz klicken

**Einsatz einer anderen Tour zuweisen?**
→ Einsatz aus alter Tour entfernen → in neuer Tour unten "Einsatz hinzufügen"

**Serie: einzelnen Einsatz löschen, Rest behalten?**
→ `Einsätze` → Einsatz öffnen → Status auf "Storniert" setzen
(Komplettes Löschen einzelner Einsätze noch nicht implementiert)

**Klient kurz abwesend (Spital)?**
→ Einsätze für diesen Zeitraum stornieren oder nicht anlegen

---

## URL-Übersicht

| Bereich | URL |
|---------|-----|
| Dashboard | `/dashboard` |
| Klienten | `/klienten` |
| Einsätze | `/einsaetze` |
| Tourenplanung | `/touren` |
| Rapporte | `/rapporte` |
| Rechnungen | `/rechnungen` |
| Mitarbeiter | `/mitarbeiter` |
| Nachrichten | `/nachrichten` |
| Firma / Einstellungen | `/firma` |
| Mein Profil / Passkeys | `/profil` |
