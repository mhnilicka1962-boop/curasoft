# Script: Ich bin eine Mitarbeiterin (Pflege)

Täglicher Ablauf in Spitex — Schritt für Schritt aus Sicht einer Pflegefachperson.

---

## Morgens — Übersicht holen

1. **Einloggen**  
   `http://spitex.test/login` → E-Mail + Passwort  
   → Detaillierte Anleitung: `docs/ANLEITUNG_EINLOGGEN.md`

2. **Dashboard**  
   - Touren heute
   - Letzte Rapporte
   - Nachrichten (falls vorhanden)

3. **Einsätze** `/einsaetze`  
   - Tab „Anstehend“
   - „Meine nächsten 14 Tage“ (Übersicht)
   - Oder Filter: Datum, Status

4. **Touren** `/touren`  
   - Tag wählen (‹ ›)
   - Tour-Detail: Liste der Einsätze in Reihenfolge

---

## Bei einem Klienten — Check-in

**Variante A: QR-Code**
- QR-Code beim Klienten scannen
- Link öffnet Check-in-Seite
- Einsätze für heute und diesen Klienten werden angezeigt
- „Jetzt einchecken“ klicken

**Variante B: Einsatz-Detail**
- `/einsaetze` → gewünschter Einsatz → Detail
- „Check-in“ (GPS oder Manuell)

---

## Während des Einsatzes

- Seite **„Aktiv“** zeigt Laufzeit
- „GPS Check-out“ oder „Manuell eintragen“ für Check-out

---

## Nach dem Einsatz — Check-out

- „✓ GPS Check-out — Einsatz beenden“ (empfohlen)
- Oder: „Manuell eintragen“ → Uhrzeit eintragen → Eintragen

Einsatzstatus: **abgeschlossen**, Dauer wird gespeichert.

---

## Rapport schreiben

1. **Rapporte** `/rapporte` → „+ Neuer Rapport“
2. Klient wählen
3. Einsatz (optional)
4. Typ (Pflege, Verlauf, Zwischenfall, Medikament …)
5. Inhalt
6. Speichern

Auch möglich: Klient-Detail → Sektion Rapporte → Link zu neuem Rapport

---

## Eigene Einsätze planen (optional)

1. **Einsätze** → „+ Neuer Einsatz“
2. Klient, Leistungsart, Datum, Zeit
3. Mitarbeiter bleibt dein Account
4. Speichern

---

## Eigene Tour anlegen (optional)

1. **Touren** `/touren` → „+ Neue Tour“
2. Datum, Bezeichnung (z.B. „Tour Morgen“)
3. Auf Tour-Detail: „+ Einsatz zuweisen“
4. Offene Einsätze wählen und zuweisen

---

## Was ich als Mitarbeiterin sehe

| Bereich      | Zugriff                               |
|--------------|----------------------------------------|
| Dashboard    | Ja                                     |
| Nachrichten  | Ja                                     |
| Klienten     | Ja                                     |
| Einsätze     | Nur meine                              |
| Touren       | Nur meine                              |
| Rapporte     | Ja                                     |
| Rechnungen   | Nein (Admin/Buchhaltung)               |
| Stammdaten   | Nein (Admin)                           |

---

## Kurz-Checkliste Tag

- [ ] Einloggen
- [ ] Touren/Einsätze für heute prüfen
- [ ] Bei jedem Klienten: Check-in (QR oder Einsatz-Detail)
- [ ] Nach Einsatz: Check-out (GPS oder manuell)
- [ ] Rapport schreiben
- [ ] Nachrichten prüfen
