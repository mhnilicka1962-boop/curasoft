# Curasoft — Betriebsanweisung
**Stand: 2026-02-24**

---

## Kapitel 1 — Einloggen

### 3 Möglichkeiten

| Methode | Für wen | Aufwand |
|---------|---------|---------|
| **Link per E-Mail** (Magic Link) | Alle | Kein Passwort nötig — empfohlen |
| **Face ID / Fingerabdruck** | iPhone, Android, Windows Hello | Einmalige Einrichtung |
| **Passwort** | Fallback | Klassisch |

---

### 1.1 Link per E-Mail (empfohlen)

Funktioniert auf jedem Gerät, keine Konfiguration nötig.

1. Login-Seite öffnen → Tab **„Link per E-Mail"** ist vorausgewählt
2. E-Mail-Adresse eingeben
3. **„Login-Link senden"** klicken
4. E-Mail öffnen → auf den Link klicken → eingeloggt

> Der Link ist **15 Minuten** gültig.

---

### 1.2 Face ID / Fingerabdruck einrichten (einmalig)

1. Zuerst normal einloggen (Magic Link oder Passwort)
2. Oben rechts → **Profil** öffnen (oder `/profil`)
3. **„+ Passkey registrieren"** tippen
4. Gerätename eingeben (optional, z.B. „iPhone Sandra")
5. Dialog erscheint → **„In Passwörter sichern"** wählen
6. **„Passkey hinzufügen"** tippen → Face ID bestätigen
7. Fertig — ab sofort mit Face ID einloggen

**Ab sofort einloggen:**
1. Login-Seite → Tab **„Face ID"** tippen
2. **„Face ID / Fingerabdruck"** tippen → ins Gesicht schauen → eingeloggt

---

### 1.3 App als Icon auf dem Homescreen

**iPhone (Safari):**
1. Safari → `https://www.curasoft.ch` öffnen
2. Unten Teilen-Symbol tippen (Quadrat mit Pfeil)
3. **„Zum Home-Bildschirm"** → Hinzufügen
4. Icon erscheint — tippen → App öffnet ohne Browser, Face ID → drin

**Android (Chrome):**
1. Chrome → Menü (drei Punkte) → **„Zum Startbildschirm hinzufügen"**

---

### 1.4 Probleme beim Einloggen

| Problem | Lösung |
|---------|--------|
| Face ID zeigt „Authenticator" | iOS Einstellungen → Passwörter → AutoFill → „Passwörter (Passkeys)" aktivieren |
| Magic Link kommt nicht an | Spam-Ordner prüfen. Oder Admin fragen. |
| „Zu viele Versuche" | 15 Minuten warten, dann erneut versuchen |
| Passwort vergessen | Magic Link verwenden — kein Passwort nötig |

---

## Kapitel 2 — Tagesablauf Admin

### Morgens: Planen

**Schritt 1 — Einsätze anlegen**
1. `Klienten` → Patient öffnen
2. Oben im Pflegeplan: **„+ Einsatz planen"** klicken
3. Mitarbeiter, Datum, Zeit, Leistungsart wählen → **Einsatz planen**
4. Für regelmässige Einsätze: Wiederholung = Wöchentlich, Wochentage wählen, Enddatum setzen

**Schritt 2 — Touren erstellen**
1. `Touren` → Datum wählen
2. Gelbe Warnung zeigt offene Einsätze pro Mitarbeiter
3. **„+ Tour erstellen"** klicken → alle Einsätze des MA sind vorgehakt
4. Bezeichnung prüfen → **Tour erstellen**

### Tagsüber: Überblick

- `Touren` → Datum wählen → Status pro Tour sehen (Geplant / Gestartet / Abgeschlossen)
- `Klienten` → Patient → 14-Tage-Pflegeplan oben

### Abends: Nachkontrolle

- `Rapporte` → Heute filtern → Zwischenfälle (rotes Badge)
- `Touren` → Tour-Detail → Orange = zu spät, kein Check-in = nicht eingecheckt

---

## Kapitel 3 — Neuer Patient (Klient)

1. `Klienten` → **„+ Neuer Klient"**
2. Pflichtfelder: Vorname, Nachname, Region (Kanton)
3. Danach im Klienten-Detail ergänzen:
   - Adresse
   - Krankenkasse (KVG + ggf. VVG)
   - Pflegestufe (BESA-Einstufung)
   - Behandelnder Arzt
   - Kontaktperson / Angehörige
4. Ersten Einsatz planen (Pflegeplan → „+ Einsatz planen")

---

## Kapitel 4 — Neuer Mitarbeiter

1. `Mitarbeiter` → **„+ Neuer Mitarbeiter"**
2. E-Mail, Rolle (Pflege / Buchhaltung / Admin) eingeben
3. Einladungs-Mail wird automatisch verschickt (48h gültig)
4. Mitarbeiter setzt Passwort über Link in der Mail
5. Im Mitarbeiter-Detail: Qualifikationen + Klienten-Zuweisung ergänzen

---

## Kapitel 5 — Rapport schreiben (Pflege)

1. Nach dem Einsatz: `Rapporte` → **„+ Neuer Rapport"**
2. Klient wählen, Typ (Verlaufsbericht / Zwischenfall / Übergabe)
3. Text eingeben → speichern
4. Zwischenfälle → Admin sieht rotes Badge in der Tour-Übersicht

---

## Kapitel 6 — Rechnung erstellen

1. `Rechnungen` → **„+ Neue Rechnung"**
2. Klient wählen → Leistungsperiode (Von–Bis)
3. Einsätze werden automatisch einbezogen
4. XML-Export für Krankenkasse: Rechnung öffnen → **„XML exportieren"**

---

## Kapitel 7 — Häufige Fragen

**Einsatz falsch zugewiesen?**
→ `Einsätze` → Einsatz öffnen → Bearbeiten → Mitarbeiter ändern

**Tour-Einsatz entfernen?**
→ `Touren` → Tour-Detail → × beim Einsatz klicken

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
| **Hilfe / Betriebsanweisung** | `/hilfe` |
