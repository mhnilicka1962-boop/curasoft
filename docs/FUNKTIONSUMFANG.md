# FUNKTIONSUMFANG — CuraSoft / Spitex
# Stand: 2026-06-17
# ✅ Fertig | 🚧 In Arbeit | 📋 Geplant / Offen

---

## 1. Authentifizierung & Zugang

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Magic Link Login (E-Mail) | ✅ | Feb 2026 | 15 Min. gültig, kein Passwort nötig |
| Passwort-Login | ✅ | Feb 2026 | Fallback |
| Passkeys / Face ID / Fingerabdruck | ✅ | Feb 2026 | WebAuthn, einmalig einrichten |
| Benutzer-Einladung per E-Mail | ✅ | Feb 2026 | Token 48 h gültig |
| Demo-Auto-Login | ✅ | Mrz 2026 | `/demo/admin`, `/demo/pflege` |
| Rollen: admin / pflege / buchhaltung | ✅ | Feb 2026 | Routen-Middleware pro Rolle |
| Multi-Tenant (Subdomain → DB) | ✅ | Feb 2026 | TenantMiddleware → `tenants`-Tabelle in Master-DB |

---

## 2. Dashboard

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Rollenbasierte Tagesübersicht | ✅ | Feb 2026 | Pflege: Heute-Einsätze; Admin: Gesamtüberblick |

---

## 3. Klienten (Patienten)

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Stammdaten (Name, Adresse, Geburtsdatum) | ✅ | Feb 2026 | |
| Krankenkasse(n) + Policenummer | ✅ | Feb 2026 | KVG + optional VVG |
| Behandelnde Ärzte | ✅ | Feb 2026 | Mehrere möglich |
| Notfallkontakte | ✅ | Feb 2026 | |
| Pflegestufen (BESA / RAI-HC) | ✅ | Jun 2026 | Historisiert mit Datum |
| Diagnosen (ICD-10) | ✅ | Jun 2026 | Optional |
| Verordnungen | ✅ | Jun 2026 | Arzt + Gültigkeitszeitraum |
| Eigenanteil-Deckel | ✅ | Apr 2026 | % + CHF/Tag-Limit → `klient_beitraege`; max. 20 % Bundeshöchsttarif |
| Gemeinde / Zuständigkeit | ✅ | Feb 2026 | |
| 14-Tage-Pflegeplan-Vorschau | ✅ | Feb 2026 | |
| Schnellerfassung | ✅ | Mrz 2026 | |

---

## 4. Einsatzplanung

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Einzeleinsatz erstellen | ✅ | Feb 2026 | Klient + Mitarbeiter + Zeit + Leistungsarten (1:n) |
| Einsatzserie (wiederkehrend) | ✅ | Mrz 2026 | Wöchentlich / Werktags / Enddatum; Cronjob `einsaetze:generieren` 02:00 |
| Serie automatisch verlängern | ✅ | Mrz 2026 | Rolling Window (`auto_verlaengern`) |
| Kalenderansicht (Admin) | ✅ | Feb 2026 | Visuelle Wochenplanung |
| Einsatz bearbeiten / verschieben | ✅ | Feb 2026 | |
| Vertretung / Abwesenheiten | ✅ | Jun 2026 | Abwesenheit erfassen → Vertretung zuweisen |

---

## 5. Touren

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Tour erstellen | ✅ | Feb 2026 | Mitarbeiter + Datum → prüft Einsätze automatisch |
| Reihenfolge optimieren | ✅ | Mrz 2026 | Nearest-Neighbor + Nominatim-Geocoding |
| Reihenfolge manuell anpassen | ✅ | Mrz 2026 | Drag & Drop |
| Tour-Status (Geplant / Gestartet / Abgeschlossen) | ✅ | Feb 2026 | |
| Verspätungs-Indikator | ✅ | Feb 2026 | Orange = verspätet / kein Check-in |
| Einsatz aus Tour entfernen | ✅ | Feb 2026 | |

---

## 6. Vor-Ort / Check-in

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| QR-Code Check-in | ✅ | Feb 2026 | Token pro Klient, `/checkin/{token}` |
| GPS Check-in | ✅ | Feb 2026 | Koordinaten werden geprüft |
| Manueller Check-in (Admin) | ✅ | Feb 2026 | |
| Aktivitäten vor Ort erfassen | ✅ | Mrz 2026 | Pro Einsatz nach Check-in |
| Check-out mit Zeiterfassung | ✅ | Feb 2026 | |

---

## 7. Rapporte

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Pflegebericht erstellen | ✅ | Feb 2026 | Typen: Verlauf / Vorfall / Übergabe |
| Vorfall-Badge (rot) | ✅ | Feb 2026 | Sichtbar in Touren- und Listenansicht |
| KI-Rapport-Vorschlag | ✅ | Mrz 2026 | Freitext-Assistent, `/ki/rapport` |
| PDF-Export Einzel + Sammel | ✅ | Feb 2026 | DomPDF |

---

## 8. Rapportierung (Monatsraster)

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Monatsraster pro Klient | ✅ | Feb 2026 | Tagesauflösung, alle Einsätze + Leistungen |
| PDF-Rapportblatt (Tiers garant) | ✅ | Apr 2026 | 3 Seiten: Quittung + QR + Tagesübersicht |
| Korrekturfunktion | ✅ | Mrz 2026 | |

---

## 9. Abrechnung

→ Detaillogik: [`docs/ABRECHNUNG_LOGIK.md`](ABRECHNUNG_LOGIK.md)

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| **Modell Tiers garant** | ✅ | Feb 2026 | Patient zahlt alles, reicht selbst bei KK ein |
| **Modell Tiers payant** | ✅ | Mai 2026 | 3 Zahlungsströme: KK / Patient / Gemeinde |
| Rechnungslauf (Batch) | ✅ | Mrz 2026 | Periode → Vorschau → Start → alle Rechnungen |
| Einzelrechnung manuell | ✅ | Feb 2026 | Klient + Periode wählen |
| PDF-Rechnung Patient | ✅ | Mai 2026 | Tiers payant: inkl. Beilage Eigenanteil-Berechnung |
| XML 450.100 → MediData (KK) | ✅ | Apr 2026 | Auto-Upload, Badge OK/Fehler, Retry pro Zeile |
| Gemeinde-PDF + E-Mail | ✅ | Mai 2026 | Einzel + Sammel, Batch-Druck |
| Tarif-Historisierung | ✅ | Feb 2026 | `gueltig_ab` — immer `create()`, kein `updateOrCreate()` |
| Eigenanteil-Deckel | ✅ | Apr 2026 | `klient_beitraege` → `betrag_patient` gedeckelt in DB |
| Bexio-Sync | ✅ | Mrz 2026 | Push/Pull Rechnungen, Status-IDs |
| Tagespauschalen | ✅ | Mrz 2026 | Separate Verwaltung + eigene Abrechnung |
| Hauswirtschaft-Subvention Gemeinde | 🚧 | — | **Situation:** Konzept fehlt. Unklar ob/wie Kanton den Hauswirtschaftsanteil mitfinanziert — ist kantonal unterschiedlich. Vor Umsetzung klären welche Kantone das verlangen und wie der Betrag berechnet wird. |
| Angehörige Abrechnung | ✅ | Jun 2026 | Im Rechnungslauf integriert; `kkasse_angehoerig` Tarif, eigene Logik pro Einsatz |

---

## 10. Personalabrechnung

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Zeitnachweis pro Mitarbeiter | ✅ | Feb 2026 | |
| CSV-Export | ✅ | Feb 2026 | |
| PDF + Sammel-PDF | ✅ | Mrz 2026 | |
| Batch-Mail an Mitarbeiter | ✅ | Mrz 2026 | |

---

## 11. Angehörigenpflege

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Angehörige als Klient-Typ | ✅ | Mai 2026 | `anstellungsart = 'angehoerig'` |
| KVG-Angehörig-Tarife (separat) | ✅ | Jun 2026 | Eigene Konfiguration |
| Vollständig getestet | ✅ | Jun 2026 | Stand 2026-06-10 |
| Abrechnung Angehörige | ✅ | Jun 2026 | Im Rechnungslauf integriert; `kkasse_angehoerig` Tarif, eigene Logik pro Einsatz |

---

## 12. Stammdaten

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Firma / Organisation | ✅ | Feb 2026 | Logo, Bexio-API-Key, Einsatz-Vorlauf |
| Leistungsarten (5 KLV-Typen) | ✅ | Feb 2026 | Grundpflege / Untersuchung / Abklärung / Hauswirtschaft / Pauschale |
| Regionen + Kantonaltarife | ✅ | Feb 2026 | `leistungsregionen` mit `gueltig_ab`; Historisierung |
| Ärzte-Stamm | ✅ | Feb 2026 | |
| Krankenkassen | ✅ | Feb 2026 | |
| Einsatzarten | ✅ | Feb 2026 | |

---

## 13. Mitarbeiter

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Mitarbeiter-Stammdaten | ✅ | Feb 2026 | |
| Qualifikationen | ✅ | Feb 2026 | |
| Leistungsarten-Zuweisung (wer darf was) | ✅ | Feb 2026 | |
| Klienten-Zuweisung | ✅ | Feb 2026 | |
| Einladung per E-Mail | ✅ | Feb 2026 | Token 48 h gültig |

---

## 14. Chat & Nachrichten

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Team-Chat | ✅ | Mrz 2026 | Threads + Teilnehmer |
| Nachrichten-Threads | ✅ | Feb 2026 | |
| System-Benachrichtigungen (Badges) | ✅ | Feb 2026 | |

---

## 15. Dokumente

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Dokument-Upload pro Klient | ✅ | Feb 2026 | |
| Anzeige + Download | ✅ | Feb 2026 | |

---

## 16. Sicherheit & Audit

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Audit-Log (Observer-basiert) | ✅ | Feb 2026 | Erstellt / Geändert / Gelöscht |
| Audit-Log Retention (10 Jahre) | 📋 | — | Kein Pruning-Job implementiert — Einträge bleiben bis manuell gelöscht |
| Audit-Log-Viewer (Admin) | ✅ | Feb 2026 | `/audit-log` |
| Datentrennung pro Tenant | ✅ | Feb 2026 | nDSG-konform |
| Performance-Indexe Audit | ✅ | Jun 2026 | Nachträglich optimiert |

---

## 17. Integrationen

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| MediData XML-Upload (KK-Abrechnung) | ✅ | Apr 2026 | Format 450.100 |
| Bexio (Buchhaltung) | ✅ | Mrz 2026 | Rechnungen Push/Pull |
| Nominatim (Geocoding + Routenoptimierung) | ✅ | Mrz 2026 | Nearest-Neighbor |

---

## 18. Setup & Onboarding

| Feature | Status | Stand | Logik-Notiz |
|---|---|---|---|
| Setup-Wizard (Neukunde) | ✅ | Feb 2026 | `/setup` — Erste-Schritte-Assistent |
| Demo-Modus | ✅ | Mrz 2026 | `is_demo`-Flag, `CurasoftDemoSeeder` |
| PWA (Add to Home Screen) | ✅ | Feb 2026 | iPhone Safari + Android Chrome |

---

## Strategische Lücken

| Thema | Status | Situation |
|---|---|---|
| Pflegeplanung / interRAI | 📋 | Markt verlangt es; Konkurrenz (SHC, NEXUS, myneva) hat es; wiederkehrendes Thema bei Interessenten (Lakic, Daniel). Dokument-Kategorie "Pflegeplanung" existiert als Ablage-Typ — aber keine echte Funktionalität. Grosses Thema — vor Umsetzung Scope definieren. |
| Angehörige Abrechnung | ✅ | Jun 2026 | Im Rechnungslauf integriert mit eigenem Tarif (`kkasse_angehoerig`) |
| Hauswirtschaft-Subvention Gemeinde | 🚧 | Kantonal unterschiedlich — Konzept muss vor Umsetzung pro Kanton geklärt werden. |
