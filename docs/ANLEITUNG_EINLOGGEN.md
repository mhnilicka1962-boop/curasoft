# Betriebsanweisung — Kapitel 1: Einloggen

**Gültig für:** Alle Mitarbeitenden
**URL Demo:** `https://www.curasoft.ch/login`
**URL Lokal:** `http://spitex.test/login`

---

## Übersicht — 3 Wege zum Login

| Methode | Geeignet für | Aufwand |
|---------|-------------|---------|
| **Link per E-Mail** (Magic Link) | Alle Geräte, alle Benutzer | Tief — empfohlen |
| **Face ID / Fingerabdruck** | iPhone, Android, Windows Hello | Einmalige Einrichtung |
| **Passwort** | Fallback | Klassisch |

---

## Methode 1 — Link per E-Mail (empfohlen)

Der einfachste Weg. Funktioniert auf jedem Gerät ohne Konfiguration.

1. Login-Seite öffnen → Tab **„Link per E-Mail"** ist vorausgewählt
2. E-Mail-Adresse eingeben
3. **„Login-Link senden"** klicken
4. E-Mail öffnen → auf den Link klicken
5. Fertig — du bist eingeloggt

> Der Link ist **15 Minuten** gültig. Danach neuen Link anfordern.

---

## Methode 2 — Face ID / Fingerabdruck (Passkey)

Einmal einrichten, danach immer mit Gesicht oder Finger einloggen — kein Passwort nötig.

### Einmalige Einrichtung

1. Zuerst normal einloggen (Magic Link oder Passwort)
2. **Profil** öffnen (oben rechts → Profil, oder `…/profil`)
3. **„+ Passkey registrieren"** tippen
4. Gerätename eingeben (optional, z.B. „iPhone Sandra")
5. Dialog erscheint → **„In Passwörter sichern"** wählen *(nicht Authenticator!)*
6. **„Passkey hinzufügen"** tippen
7. Face ID / Fingerabdruck bestätigen
8. Fertig — Passkey ist registriert

### Ab sofort einloggen

1. Login-Seite öffnen → Tab **„Face ID"** tippen
2. **„Face ID / Fingerabdruck"** tippen
3. Ins Gesicht schauen (oder Finger auflegen)
4. Fertig — eingeloggt

---

## Methode 3 — App-Icon auf dem Homescreen (empfohlen für tägl. Nutzung)

Die App kann wie eine native App auf dem Homescreen installiert werden.

### iPhone (Safari)

1. Safari öffnen → `https://www.curasoft.ch`
2. Unten das **Teilen-Symbol** tippen (Quadrat mit Pfeil nach oben)
3. **„Zum Home-Bildschirm"** tippen
4. Name bestätigen → **„Hinzufügen"**
5. Icon erscheint auf dem Homescreen

→ App öffnet sich ohne Browser-Leiste, direkt auf der Login-Seite. Mit Face ID in Sekunden drin.

### Android (Chrome)

1. Chrome öffnen → `https://www.curasoft.ch`
2. Menü (drei Punkte) → **„Zum Startbildschirm hinzufügen"**
3. Bestätigen

---

## Bekannte Probleme & Lösungen

### Face ID zeigt „Authenticator" statt iCloud

**Problem:** Microsoft Authenticator ist auf dem iPhone installiert und fängt den Passkey ab.

**Lösung:**
1. iPhone **Einstellungen** öffnen
2. **Passwörter** → **Passwörter automatisch ausfüllen**
3. **„Passwörter (Passkeys, Passwörter und Codes)"** aktivieren
4. Danach Passkey-Registrierung nochmals versuchen → **„In Passwörter sichern"** wählen

---

### Magic Link kommt nicht an

| Ursache | Lösung |
|---------|--------|
| Spam-Ordner | Im Spam-Ordner nachschauen |
| Falsche E-Mail | Admin fragen welche E-Mail hinterlegt ist |
| Link abgelaufen | Neuen Link anfordern (15 Min. Gültigkeit) |

---

### „Zu viele Versuche"

Nach 5 falschen Passwort-Eingaben wird der Zugang für **15 Minuten** gesperrt.
→ 15 Minuten warten, dann erneut versuchen.
→ Oder Magic Link verwenden (separate Rate-Limite).

---

### Passwort vergessen

Kein Problem — Magic Link verwenden (kein Passwort nötig).
Oder Admin bitten, eine neue Einladung zu senden.

---

## Login-Daten Demo-Umgebung

| Rolle | E-Mail | Passwort |
|-------|--------|----------|
| Admin | `demo@curasoft.ch` | `Admin2026!` |
| Pflege | `sandra.huber@test.spitex` | `test1234` |
| Buchhaltung | `lisa.bauer@test.spitex` | `test1234` |

> Alle Testbenutzer Pflege: Passwort `test1234`

---

*Stand: 2026-02-24*
