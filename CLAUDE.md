# CLAUDE.md — ✅ PROJEKT: CURASOFT / SPITEX (Laravel)
# DEPLOY-PFAD: ftp://ftp.devitjob.ch/public_html/spitex/
# NIEMALS /public_html/itjob/ verwenden — das ist ein anderes Projekt!
# Lokales Verzeichnis: C:\laragon\www\spitex

## ⚠️ SESSION-START — ZWINGEND
# Bei JEDEM Neustart diese Datei vollständig lesen BEVOR irgendeine Aktion ausgeführt wird.
# Insbesondere: Deploy-Regeln, Arbeitsablauf, bekannte Fallstricke.
# NIEMALS aus dem Gedächtnis arbeiten — immer zuerst hier nachschlagen.

## Stand: 2026-03-10 (Session 18 — Rechnungslauf UX + Bugfixes)

---

## Login-Daten (lokal)

| | |
|---|---|
| **URL** | `http://spitex.test/login` |
| **Admin E-Mail** | `mhn@itjob.ch` |
| **Admin Passwort** | `Admin2026!` |
| **Rolle** | admin |
| **Pflege (Test)** | `1234@itjob.ch` / `Sandra2026!` (Sandra Huber) |
| **CuraPflege lokal** | `http://curapflege.spitex.test/login` — `mhn@itjob.ch` / `Admin2026!` |

## Login-Daten (Server)

| Instanz | URL | Email | Passwort |
|---------|-----|-------|---------|
| Demo | `https://curasoft.ch/login` | `mhn@itjob.ch` | `Admin2026!` |
| CuraPflege | `https://curapflege.curasoft.ch/login` | `mhn@itjob.ch` | `Admin2026!` |
| Pflege (Demo) | `https://curasoft.ch/login` | `1234@itjob.ch` | `Sandra2026!` |
| Buchhaltung (Demo) | `https://curasoft.ch/login` | `lisa.bauer@test.spitex` | `test1234` |

---

## Datenbank-Stand

### Organisationen
- **Genau 1 Organisation** in der DB (ID 1)
- Multi-Tenant-Code ist vorhanden aber irrelevant — es gibt nur eine Spitex
- Nie eine zweite Organisation anlegen

### Alle Migrationen gelaufen

| Migration | Inhalt |
|-----------|--------|
| `2026_02_21_000001` | organisationen |
| `2026_02_21_000002` | benutzer |
| `2026_02_21_000003` | klienten |
| `2026_02_21_000004` | regionen |
| `2026_02_21_000005` | leistungsarten |
| `2026_02_21_000006` | leistungstypen (Einsatzarten) |
| `2026_02_22_230000` | Einsätze redesign: leistungsart_id, status ENUM, region_id |
| `2026_02_22_240000` | Klienten erweitert: anrede, zustaendig_id, datum_erstkontakt usw. |
| `2026_02_22_250000` | klient_adressen: einsatzort/rechnung/notfall/korrespondenz |
| `2026_02_22_260000` | aerzte + klient_aerzte |
| `2026_02_22_270000` | krankenkassen + klient_krankenkassen |
| `2026_02_22_280000` | klient_kontakte |
| `2026_02_22_290000` | klient_pflegestufen + klient_diagnosen |
| `2026_02_22_300000` | rapporte |
| `2026_02_22_310000` | touren + tour_id/tour_reihenfolge auf einsaetze |
| `2026_02_22_320000` | dokumente |
| `2026_02_22_330000` | bexio_sync + bexio-Felder auf organisationen/klienten/rechnungen |
| `2026_02_22_340000` | klient_beitraege |
| `2026_02_22_350000` | Performance-Indizes (25 Indizes auf allen relevanten Tabellen) |
| `2026_02_22_360000` | leistungsarten: gueltig_ab/bis + Default-Ansätze; leistungsregionen: verrechnung/einsatz_minuten/stunden/tage/mwst |
| `2026_02_22_370000` | leistungsregionen: Unique-Constraint (leistungsart_id, region_id) entfernt → Historisierung |
| `2026_02_22_380000` | benutzer: neue Felder (anrede, geschlecht, zivilstand, strasse, telefax, email_privat, ahv_nr, iban, bank, notizen, einladungs_token) |
| `2026_02_22_390000` | qualifikationen + benutzer_qualifikation |
| `2026_02_22_400000` | klient_benutzer (Klient-Mitarbeiter-Zuweisung) |
| `2026_02_23_000001` | webauthn_credentials (Face ID / Passkey) |
| `2026_02_23_000002` | benutzer: einladungs_token_ablauf |
| `2026_02_23_100000` | login_tokens (Magic Link) |
| `2026_02_23_120000` | klient_krankenkassen: tiers_payant boolean (Tiers payant vs. Tiers garant) |
| `2026_02_23_130000` | klient_verordnungen (Ärztliche Verordnungen); einsaetze: verordnung_id FK |
| `2026_02_23_140000` | leistungsarten: tarmed_code varchar(20) nullable |
| `2026_02_23_150000` | klienten: klient_typ; klient_benutzer: beziehungstyp; benutzer: anstellungsart; einsaetze: leistungserbringer_typ |
| `2026_02_23_125201` | benutzer_leistungsarten (Pivot: erlaubte Leistungsarten pro Mitarbeiter) |
| `2026_02_24_215401` | nachrichten: parent_id (nullable FK Self-Reference → Threading) |
| `2026_02_25_300000` | klienten: versandart_patient, versandart_kk (varchar nullable) |
| `2026_02_26_100000` | rechnungen: email_versand_datum (timestamp nullable), email_versand_an (varchar nullable) |
| `2026_02_26_110000` | rechnungen: email_fehler (text nullable) |
| `2026_02_26_200000` | tagespauschalen: id, organisation_id, klient_id, rechnungstyp, datum_von, datum_bis, ansatz (decimal 10,4), text, erstellt_von |
| `2026_02_26_210000` | einsaetze: tagespauschale_id (nullable FK → tagespauschalen, nullOnDelete) |
| `2026_02_26_220000` | rechnungs_positionen: beschreibung (TEXT nullable); leistungstyp_id nullable |

### Seeders (bereits eingespielt)
- `LeistungsartenSeeder` — 5 Leistungsarten mit Default-Ansätzen
- `EinsatzartenSeeder` — 30 Einsatzarten, je einer Leistungsart zugeordnet
- `KrankenkassenSeeder` — 39 Schweizer KVG-Krankenkassen (BAG-Nr + EAN) — per Tinker eingespielt

### DB-Inhalt (Testdaten — lokal + Demo identisch, Stand 2026-03-10)

| Tabelle | Anzahl |
|---------|--------|
| klienten | 58 (50 normal + 38 TEST, davon 4 Pausch + 4 Mix) |
| einsaetze | ~1541 |
| tagespauschalen | 8 (4 Pausch + 4 Mix, Feb 2026) |
| rechnungslaeufe | 1 |
| rechnungen | 24 |
| rechnungs_positionen | 78 |
| regionen | 4 (AG, BE, SG, ZH) |
| leistungsregionen | 19 |
| benutzer | ~16 |
| krankenkassen | 5 |
| touren | 8 |
| rapporte | 90 |

Demo-DB: `devitjob_curasoft` — zuletzt synchronisiert 2026-03-10 via `./deploy.sh db`.

---

## Multi-Tenant Architektur (Entscheid Session 12 — 2026-02-25)

### Entscheid: Subdomain + separate DB pro Organisation

**Gewählt:** `kundenname.curasoft.ch` → eigene PostgreSQL-DB pro Kunde
**Verworfen:** Shared DB mit `organisation_id` (Datenleck-Risiko bei Gesundheitsdaten, nDSG)
**Verworfen:** Separate Code-Instanz pro Kunde (zu aufwändig im Betrieb)

### Konzept

```
*.curasoft.ch  →  Wildcard DNS  →  gleicher Server / gleiche Laravel-App
                                         ↓
                               TenantMiddleware liest Subdomain
                                         ↓
                               Master-DB: subdomains-Tabelle
                               subdomain → db_name, db_user, db_password
                                         ↓
                               config()->set('database.connections.tenant', ...)
                               DB::setDefaultConnection('tenant')
```

### Master-DB (`curasoft_master`)
- Tabelle `tenants`: `subdomain`, `db_name`, `db_user`, `db_password`, `aktiv`, `erstellt_am`
- Einzige zentrale DB — enthält nur Routing-Infos, keine Patientendaten
- Lokal und auf Demo-Server je eine Master-DB einrichten

### Tenant-DB (z.B. `curasoft_aarau`)
- Komplette Migrations-Struktur wie jetzt
- Seeders: LeistungsartenSeeder, EinsatzartenSeeder, KrankenkassenSeeder
- Eine Organisation, ein Admin-Benutzer (per Provisioning-Script anlegen)

### Provisioning — neuer Kunde
```bash
# 1. DB anlegen
createdb curasoft_aarau

# 2. Migrations + Basis-Seeders
php artisan migrate --database=tenant_aarau
php artisan db:seed --class=LeistungsartenSeeder --database=tenant_aarau
# etc.

# 3. Master-DB Eintrag
INSERT INTO tenants (subdomain, db_name, ...) VALUES ('spitex-aarau', 'curasoft_aarau', ...)

# 4. DNS: spitex-aarau.curasoft.ch → Server (Wildcard deckt das ab)
```
→ Wird zu einem einzigen Artisan-Command (`tenant:create spitex-aarau "Spitex Aarau"`)

### Migrations über alle Tenants
```bash
# Bei Schema-Änderung: Loop über alle aktiven Tenants
php artisan tenant:migrate  # custom Command, iteriert tenants-Tabelle
```

### Demo-Server — aktueller Stand (single-tenant)
- `www.curasoft.ch` läuft als **single-tenant Demo** (DB: `devitjob_curasoft`)
- Bleibt vorerst so — dient als Vorführ-Instanz für Interessenten
- Wenn Multi-Tenant live geht: `demo.curasoft.ch` → eigene Demo-DB, `www.curasoft.ch` → Landing Page

### Hosting
- Provider: devitjob.ch (cPanel)
- Wildcard-Subdomain `*.curasoft.ch` → beim Provider anfragen / konfigurieren
- Max. ~50 Subdomains laut Provider — ausreichend für Pilotphase

## Neu in Session 18 (2026-03-10) — Rechnungslauf UX + Bugfixes

### Rechnungslauf — Bugfixes
- **Doppellauf-Bug behoben**: `store()` rief `Rechnungslauf::create()` UND `erstelleLauf()` auf → immer 2 Läufe. Fix: nur `erstelleLauf()` erstellt den Lauf.
- **Leerer Lauf (CHF 0)**: `erstelleLauf()` erstellte Lauf-Record BEVOR Einsätze geprüft wurden. Fix: erst Einsätze sammeln, dann Lauf anlegen.
- **Tagespauschalen getrennt**: Pro Klient mit Mischbetrieb (normale Einsätze + Tagespauschale) werden **2 separate Rechnungen** erstellt.
- **Lauf wiederholen**: Storniert alten Lauf + sucht ALLE Klienten mit verrechenbaren Einsätzen (nicht nur Klienten des alten Laufs).

### Rechnungslauf — UX-Verbesserungen
- **Vorschau-Tabelle**: Alle Spalten ausser Klient zentriert (CSS-Fix: `.tabelle th.text-mitte` überschreibt Default `text-align: left`)
- **Jahr-Filter**: Rechnungsläufe-Index hat Jahres-Dropdown → filtert nach `periode_von`
- **Live-Suche**: Rechnungslauf-Detail hat Suchfeld → filtert Tabelle nach Name/Rechnungsnummer (JS, kein Reload)
- **Pauschale-Badge**: Tagespauschalen-Rechnungen im Lauf-Detail haben hellblaues "Pauschale"-Badge
- **Back-Link**: Rechnung-Detail zeigt "← Rechnungslauf #X" wenn Rechnung einem Lauf gehört
- **"Alle Rechnungen ansehen"-Button** aus Lauf-Detail entfernt (war verwirrend)

### Testdaten
- `RechnungslaufTestSeeder`: 4 TEST-Pausch-* (CHF 98/Tag) + 4 TEST-Mix-* (CHF 82/Tag) — realistische Schweizer Spitex-Tagespauschalen-Ansätze
- Cleanup: `DB::statement("DELETE FROM klienten WHERE nachname LIKE 'TEST-%'")`

### CSS-Fix
- `.tabelle th.text-mitte` + `.tabelle th.text-rechts` in `app.css` — `.tabelle th { text-align: left }` hatte höhere Spezifität

---

### Implementiert (Session 17 — 2026-03-10)
- [x] `TenantMiddleware` ✅ — Subdomain → DB-Connection
- [x] Master-DB (`tenants`-Tabelle in `devitjob_curasoft`) ✅
- [x] `master:init` Artisan-Command ✅ — einmalig `tenants`-Tabelle anlegen
- [x] `tenant:create` Artisan-Command ✅ — neue Tenant-Instanz komplett provisionieren
- [x] `tenant:seed` Artisan-Command ✅ — Seeders nachträglich in Tenant-DB einspielen
- [x] `tenant:migrate` Artisan-Command ✅ — alle Tenants migrieren
- [x] Login-Seite pro Subdomain ✅ — Org-Name + Theme aus Tenant-DB
- [x] `www.curasoft.ch` Landing Page ✅ — bleibt so, kein Login direkt auf Root
- [x] Erste produktive Instanz: `curapflege.curasoft.ch` ✅

### Artisan-Commands für Tenant-Verwaltung

```bash
# Neuen Tenant anlegen (cPanel: DB manuell erstellen, dann:)
php artisan tenant:create curapflege "CuraPflege GmbH" admin@email.ch --skip-create-db --db=devitjob_curapflege

# Seeders nachträglich einspielen (z.B. nach Erstanlage fehlende Daten)
php artisan tenant:seed curapflege --db=devitjob_curapflege

# Alle Tenants migrieren (nach Code-Deploy)
php artisan tenant:migrate
```

### Seeders die bei jedem neuen Tenant automatisch laufen
`tenant:create` und `tenant:seed` spielen folgende Daten ein — immer vollständig, keine manuelle Nacharbeit nötig:

| Seeder | Inhalt |
|--------|--------|
| `LeistungsartenSeeder` | 5 Leistungsarten mit Default-Ansätzen |
| `EinsatzartenSeeder` | 30 Einsatzarten |
| `KrankenkassenSeeder` | 39 Schweizer KVG-Krankenkassen |
| `QualifikationenSeeder` | Pflegequalifikationen (FaGe, HF, DN I/II usw.) |

### cPanel Terminal — WICHTIG: Kein Copy-Paste mit Zeilenumbrüchen!
- cPanel Terminal aktiviert "Bracketed Paste Mode" → Zeilenumbrüche im eingefügten Text werden als Befehlsende interpretiert
- **Lange Befehle immer manuell tippen** (nicht aus diesem Dokument einfügen)
- Beispiel Problem: `php artisan tenant:seed curapflege --db=...` → Terminal trennt bei Leerzeichen nach dem Zeilenumbruch
- Lösung: Einzeiler eintippen, nicht einfügen

### Bestehende Tenants (Server)

| Subdomain | DB | Status |
|-----------|-----|--------|
| `www.curasoft.ch` | `devitjob_curasoft` | Demo — NICHT ANFASSEN |
| `curapflege.curasoft.ch` | `devitjob_curapflege` | Produktiv |

---

## Module und URLs

| Modul | URL | Controller | Rollen |
|-------|-----|------------|--------|
| Dashboard | `/dashboard` | Route-Closure | alle |
| Klienten | `/klienten` | KlientenController | admin, pflege |
| Klient Bexio-Sync | `POST /klienten/{id}/bexio/sync` | KlientenController | admin, pflege |
| Einsätze | `/einsaetze` | EinsaetzeController | admin, pflege |
| Check-In/Out | `/checkin/{token}` | CheckInController | admin, pflege |
| Rapporte | `/rapporte` | RapporteController | admin, pflege |
| Tourenplanung | `/touren` | TourenController | admin, pflege |
| Rechnungen | `/rechnungen` | RechnungenController | admin, buchhaltung |
| Rechnungsläufe | `/rechnungslaeufe` | RechnungslaufController | admin, buchhaltung |
| Tagespauschalen | `/tagespauschalen` | TagespauschaleController | admin, buchhaltung |
| XML-Export 450.100 | `GET /rechnungen/{id}/xml` | RechnungenController | admin, buchhaltung |
| Rechnung Bexio-Sync | `POST /rechnungen/{id}/bexio/sync` | RechnungenController | admin, buchhaltung |
| Firma | `/firma` | FirmaController | admin |
| Leistungsarten Grundset | `/leistungsarten` | LeistungsartenController | admin |
| Leistungsart Tarife | `/leistungsarten/{id}` | LeistungsartenController | admin |
| Einsatzarten | `/einsatzarten` | EinsatzartenController | admin |
| Regionen / Kantone | `/regionen` | RegionenController | admin |
| Kanton Leistungsarten | `/regionen/{id}` | RegionenController | admin |
| Ärzte | `/aerzte` | AerzteController | admin |
| Krankenkassen | `/krankenkassen` | KrankenkassenController | admin |
| Audit-Log | `/audit-log` | AuditLogController | admin |
| Nachrichten | `/nachrichten` | NachrichtenController | alle |

---

## Leistungsarten / Tarif-System

### Struktur
```
leistungsarten (5 Grundset)
  id, bezeichnung, einheit, kassenpflichtig, aktiv
  gueltig_ab, gueltig_bis
  ansatz_default, kvg_default, ansatz_akut_default, kvg_akut_default
  tarmed_code (z.B. '00.0010') → für XML 450.100 Tarif 311

leistungstypen (30 Einsatzarten)
  id, leistungsart_id, bezeichnung, gueltig_ab, gueltig_bis, aktiv
  → KEIN Kanton-Bezug, globales Set

leistungsregionen (Tarife pro Leistungsart + Kanton)
  id, leistungsart_id, region_id
  gueltig_ab, gueltig_bis
  ansatz, kkasse, ansatz_akut, kkasse_akut, kassenpflichtig
  verrechnung, einsatz_minuten, einsatz_stunden, einsatz_tage, mwst
  → KEIN unique constraint → Historisierung möglich
  → aktueller Tarif = höchstes gueltig_ab pro leistungsart+region
```

### Kanton anlegen → Auto-Copy
Wenn neue Region in `/regionen` angelegt wird → `RegionenController::store()` kopiert alle 5 aktiven Leistungsarten mit ihren Default-Ansätzen automatisch in `leistungsregionen`.

### Historisierung
Jedes Speichern eines Tarifs erzeugt einen **neuen Eintrag** (kein Update des alten).
Alte Einträge bleiben als Historie erhalten, ausgegraut in der Ansicht.
"aktuell"-Badge zeigt den neuesten Eintrag pro Leistungsart+Kanton.

---

## Klient-Detail: Sektionen

Die Klient-Detailseite (`/klienten/{id}`) zeigt folgende Sektionen:

1. **Name & Basis-Info** — Vollname, Geburtsdatum, Geschlecht, Zivilstand, Kanton-Badge, Planungsdaten, Zuständig
2. **Kontakt & Adresse** — Hauptadresse, Telefon, Notfall, E-Mail
3. **Krankenkasse & AHV** — Legacy-Felder (Fallback wenn keine KK-Verknüpfung)
4. **Adressen** — einsatzort / rechnung / notfall / korrespondenz (Cards + Formular)
5. **Behandelnde Ärzte** — mit Rolle (Hauptarzt / Einweisend / Konsultierend)
6. **Krankenkassen** — KVG / VVG, Deckungstyp, Versichertennummer, **Tiers payant/garant Badge**
7. **Ärztliche Verordnungen** — NEU: Verordnungs-Nr, Arzt, Leistungsart, gültig ab/bis, Status-Badge (Aktiv/Läuft ab/Abgelaufen)
8. **Beiträge** — Ansatz Kunde, Limit %, Ansatz SPITEX, Kanton, historisiert nach gültig_ab
9. **Kontakte & Angehörige** — Rolle, Bevollmächtigt, Erhält Rechnungen
10. **Pflegebedarf / Einstufungen** — BESA / RAI-HC / IBM / Manuell, Stufe, Punkte, Nächste Prüfung
11. **Diagnosen (ICD-10)** — Code, Bezeichnung, Typ (Haupt/Neben/Einweisung)
12. **Dokumente** — Upload (PDF/DOCX/XLSX/Bilder, max 20 MB), Download
13. **Rapporte** — letzte 5, Link zu neuen Rapport
14. **Letzte Einsätze** — letzte 5

---

## Services

| Datei | Klasse | Zweck |
|-------|--------|-------|
| `app/Services/BexioService.php` | `App\Services\BexioService` | Bexio API: Verbindungstest, Kontakt-Sync, Rechnungs-Sync |
| `app/Services/XmlExportService.php` | `App\Services\XmlExportService` | XML 450.100 für KK-Abrechnung (DOMDocument) |

---

## Prozesse: Mitarbeiter & Angehörigenpflege

### Neue Mitarbeiterin erfasst — Ablauf

| Schritt | Wo | Was |
|---------|-----|-----|
| 1 | `/mitarbeiter` → + Neu | Anrede, Vorname, Name, E-Mail*, Rolle*, evtl. Pensum, Eintrittsdatum |
| 2 | — | Einladungs-Mail automatisch versendet (48h gültig) |
| 3 | E-Mail → Link | Passwort setzen → Login |
| 4 | Mitarbeiter-Detail | Stammdaten, Qualifikationen, Klient-Zuweisung |
| 5 | Behörden | Berufsausübungsbewilligung (Kanton) falls Pflegefachperson, ca. 2 Monate vor Tätigkeitsbeginn |

**Aargau:** [ag.ch – SPITEX Betriebsbewilligung](https://ag.ch/de/themen/gesundheit/gesundheitsberufe/betriebsbewilligungen/spitex)

### Person pflegt Angehörigen (Angehörigenpflege)

| Fall | Bedeutung | In Spitex |
|------|-----------|-------------|
| **A: Kontakt** | Angehöriger als Kontaktperson (nicht pflegend) | Klient → Kontakte & Angehörige → + Kontakt, Rolle „Angehöriger“ |
| **B: Pflegend tätig** | Angehöriger wird angestellt, pflegt gegen Lohn | **Als Mitarbeiter erfassen** + Klient-Zuweisung beim gepflegten Klienten |

Regelung CH: Seit 1.5.2023 können Angehörige pflegen, wenn mit SPITEX Zusammenarbeit vereinbart. Kantonal unterschiedlich.

### KlientKontakt vs. Benutzer

| | KlientKontakt (Angehöriger) | Benutzer (Mitarbeiter) |
|---|----------------------------|-------------------------|
| Zweck | Kontakt, Rechnungsempfänger, Bevollmächtigter | Pflegeperson mit Einsätzen |
| Login | Nein | Ja |
| Wo | Klient-Detail → Kontakte | Stammdaten → Mitarbeitende |

→ Detaillierte Checkliste: `docs/CHECKLISTE_MORGEN.md`  
→ Ablauf Einsatzplanung: `docs/ABLAUF_EINSATZPLANUNG.md`  
→ Script Mitarbeiterin (täglicher Ablauf): `docs/SCRIPT_MITARBEITERIN.md`  
→ Ablauf Rechnung: `docs/ABLAUF_RECHNUNG.md`  
→ Anleitung Einloggen (für neue User): `docs/ANLEITUNG_EINLOGGEN.md`

---

## ZUM TESTEN — Offen (2026-02-23)

### Noch nicht vollständig getestet

| Feature | URL | Was testen |
|---------|-----|------------|
| **Schnellerfassung** | `/klienten` → "+ Neuer Patient" | Patient + Betreuer + Wochentage eingeben → 1 Klick → Pflegeplan prüfen |
| **Wiederkehrende Einsätze** | `/einsaetze/create?klient_id=X` | Wiederholung Wöchentlich, Mo+Mi+Fr, Preview zeigt Anzahl, Speichern |
| **5-Minuten-Takt** | `/einsaetze/create` | Zeit 08:07 eingeben → Fehler; 08:05 → OK; Dauer < 10 min → Fehler |
| **Tiers payant** | `/klienten/{id}` → Krankenkassen | Badge "Tiers payant" / "Tiers garant" sichtbar |
| **Ärztliche Verordnung** | `/klienten/{id}` → Verordnungen | Verordnung anlegen, Status-Badge, Einsatz erstellen → Verordnung wählen |
| **XML 450.100** | `/rechnungen/{id}` → 📋 XML | XML herunterladen, Struktur `generalInvoiceRequest` prüfen |
| **Bexio-Button** | `/klienten/{id}` oder `/rechnungen/{id}` | Nur sichtbar wenn API-Key gesetzt; `→ Bexio` klicken |
| **tarmed_code** | `/leistungsarten/{id}` → Bearbeiten | Code `00.0010` eingeben, speichern, in Show-Ansicht `T311:` sehen |
| **Face ID / Passkey** | `/profil` → Passkey registrieren | Gerätename eingeben → Face ID → Login-Test |
| **Lücken-Warnung Touren** | `/touren` | ⚠ Banner mit Mitarbeitern ohne Tour |
| **klient_typ** | `/klienten/{id}` → Bearbeiten | Typ "Pflegender Angehöriger" wählen → Badge im Header sichtbar |
| **leistungserbringer_typ** | `/einsaetze/create` | Leistungserbringer "Pflegender Angehöriger" wählen → XML specialty=39 |
| **anstellungsart** | `/mitarbeiter/{id}` | Anstellungsart "Angehörig" setzen, speichern |
| **beziehungstyp** | `/mitarbeiter/{id}` → Klient zuweisen | Beziehungstyp "Angehörig pflegend" wählen |
| **Rate Limiter** | `/login` | 6× falsches PW → "Zu viele Versuche"-Meldung |
| **Pflege-Login-Redirect** | Magic Link als Sandra | Landet direkt auf Tourenplan heute |
| **Vor-Ort-Ansicht** | Tour-Detail → Klientenname klicken | Mobile Seite mit Adresse, Notfall, Check-in |
| **Leistungsart-Freigabe** | `/mitarbeiter/{id}` → Checkboxen | Nur freigegebene wählen; Einsatz mit gesperrter → Warnung |
| **Offene Vergangen.** | Als Sandra einloggen | Rote Karte wenn vergangene Einsätze offen |

---

## Neu in Session 17 (2026-03-10) — Multi-Tenant live

### Multi-Tenant implementiert und produktiv

**Architektur:** Ein Laravel-Code, separate PostgreSQL-DB pro Kunde, Subdomain-Routing.

| Domain | DB | Status |
|--------|-----|--------|
| `curasoft.ch` | `devitjob_curasoft` | Demo ✅ |
| `curapflege.curasoft.ch` | `devitjob_curapflege` | Tenant 1 ✅ |

### Neue Dateien
| Datei | Zweck |
|-------|-------|
| `app/Http/Middleware/TenantMiddleware.php` | Subdomain → DB-Switch + Theme laden |
| `app/Console/Commands/MasterInit.php` | `tenants`-Tabelle einmalig anlegen |
| `app/Console/Commands/TenantCreate.php` | erweitert: `--skip-create-db`, `--db=`, Seeders automatisch |
| `app/Console/Commands/TenantMigrate.php` | Migrationen auf allen Tenant-DBs |
| `database/migrations_telescope/` | Telescope-Migration SEPARAT (nicht für Tenants) |

### Neuen Kunden einrichten (Server)
```bash
# 1. cPanel: Subdomain X.curasoft.ch → Document Root /home/devitjob/public_html/spitex/public
#    cPanel erstellt falschen Pfad → Terminal:
rm -rf ~/X.curasoft.ch && ln -s ~/public_html/spitex/public ~/X.curasoft.ch

# 2. cPanel: DB devitjob_X anlegen, User devitjob_csapp berechtigen

# 3. Terminal:
php artisan tenant:create X "Name GmbH" admin@x.ch --skip-create-db --db=devitjob_X
```

### Einmalig auf Server (bereits ausgeführt)
```bash
php artisan master:init   # tenants-Tabelle in devitjob_curasoft angelegt
```

### Login-Seite pro Tenant
- `TenantMiddleware` lädt Org-Name + Theme aus Tenant-DB → Login-Seite zeigt richtigen Namen
- Tenant-Root-URL `/` → Redirect auf `/login` (kein Landing Page für Tenants)

### Fixes
- `AuthController`: Passwort beim Login trimmen (Leerzeichen am Ende)
- Audit-Log: vollständig implementiert und getestet ✅

---

## Neu in Session 16 (2026-02-27) — Deploy-Automatisierung

### Problem: Code/DB-Drift zwischen lokal und Demo
- Demo-Server hatte lokale FTP-Änderungen die nicht in git waren → `git pull` schlug fehl
- `git reset --hard origin/master` als Standard statt `git pull` — vermeidet Konflikte immer
- `organisationen`-Tabelle war nie Teil des DB-Syncs → Firma-Daten auf Demo fehlten
- `maennchen/zipstream-php` v3.2.1 erfordert PHP 8.3 — Demo läuft auf 8.2.29 → downgrade auf ^2.4

### Lösung: deploy.sh — Ein Befehl für alles
```bash
./deploy.sh        # Code + Assets (bei jeder Code-Änderung)
./deploy.sh db     # + vollständiger DB-Sync (Testdaten + Organisation)
```

### Neue Dateien:
| Datei | Zweck |
|-------|-------|
| `deploy.sh` | Haupt-Deploy-Script (ausführbar) |
| `deploy/server.php` | Server-seitiges Script: git reset + composer + migrate + cache |
| `deploy/db_sync.php` | Exportiert lokale DB → generiert `deploy/db_import.php` |
| `deploy/db_import.php` | Temporär generiert, gitignored, wird nach Sync gelöscht |

### Regel ab sofort: NIEMALS einzelne Dateien per FTP hochladen
Alles über git + `./deploy.sh`. Keine Ausnahmen.

---

## Neu in Session 15 — Abend / Deploy (2026-02-26)

### Deploy-Lektion: falscher FTP-Pfad

**Was passierte:** Stundenlang wurden Spitex-Dateien nach `/public_html/itjob/` deployt statt `/public_html/spitex/`. Ursache: die itjob-CLAUDE.md war im Kontext geladen und enthielt den itjob-Pfad.

**Massnahmen nach Korrektur:**
1. Alle Dateien nochmals korrekt nach `/public_html/spitex/` deployt
2. `composer dump-autoload` via `ca.php` (HOME=/tmp nötig da kein Superuser)
3. `route:clear` + `view:clear` + `config:clear` via `cc.php`
4. Fehlende Migrationen identifiziert und nachgeholt

**Fehlende Migrationen auf Demo (wurden nachgeholt):**
- `2026_02_25_300000_add_versandart_to_klienten`
- `2026_02_26_100000_add_email_versand_to_rechnungen`
- `2026_02_26_110000_add_email_fehler_to_rechnungen`

**Für künftige Deploys: immer ALLE Migrations-Dateien deployen** — `artisan migrate` läuft nur die fehlenden, schadet nicht.

### Testdaten-Sync Demo ↔ Lokal

Vollständige Synchronisation der Demo-DB mit lokalen Testdaten via PHP-Export/Import-Script:
- Export: `PDO::fetchAll()` mit Boolean-Handling (INFO-Schema) + FK-Reihenfolge
- Lernpunkte: `session_replication_role` braucht Superuser; TRUNCATE-Reihenfolge muss FK-Abhängigkeiten respektieren; benutzer vor touren
- Ergebnis: 1938 Rows, 0 Fehler

### itjob-Aufräumen

Falsch deployten Spitex-Dateien in `/public_html/itjob/` haben itjob **nicht beschädigt** (keine Pfad-Überschneidungen). Diagnostic-Scripts in `/public_html/itjob/public/` waren nicht vorhanden (FTP 550).

---

## Neu in Session 15 — Nachmittag (2026-02-26)

### PDF-Rechnung: Kompakteres Layout (2 Seiten)
- Schrift 9pt → 8pt, Abstände/Padding überall reduziert
- Anschrift: 8.5pt → 7.5pt, margin-top 10mm → 7mm, margin-bottom 14mm → 8mm
- Positionen: 8pt → 7pt, Padding 1.5mm → 1mm
- Ziel: Seite 1 = Rechnungsinhalt kompakt, Seite 2 = QR-Zahlteil (immer 2 Seiten)

### Rechnungslauf: Zukunftsdaten blockiert
- Validierung `before_or_equal:today` auf `periode_von` und `periode_bis` in `store()`
- `max="{{ today()->format('Y-m-d') }}"` auf Date-Inputs in `create.blade.php`
- Roter Warn-Banner im View wenn Zukunftsdatum für Vorschau eingegeben

### Regionen: Standard-Tarife auto-initialisieren
- `RegionenController::initialisieren()` — kopiert Default-Ansätze aus Leistungsarten für fehlende Einträge
- Route: `POST /regionen/{region}/initialisieren` → `regionen.initialisieren`
- View `stammdaten/regionen/show.blade.php`: gelber Warn-Banner wenn Leistungsart ohne Tarif + Button «Standard-Tarife anlegen»
- Nützlich wenn Region vor Auto-Copy-Feature angelegt wurde oder auf Demo-Server fehlt

### Rechnung Model: email_versand_datum Cast
- `'email_versand_datum' => 'datetime'` in `$casts` hinzugefügt
- Fix: `->format('d.m.Y H:i')` in `rechnungen/lauf/show.blade.php` warf 500er (Call on string)

### Navigation
- "Rechnungsläufe" als eigener Nav-Link unter "Abrechnung" (getrennt von "Rechnungen")

---

## Neu in Session 15 (2026-02-26)

### Tagespauschalen — Neues Abrechnungsmodul

**Konzept:** 1 Einsatz pro Tag wird sofort generiert wenn eine Tagespauschale angelegt wird. Rechnungslauf verrechnet diese Einsätze wie normale Einsätze — kein Spezialcode nötig.

**Neue Dateien:**
- `app/Models/Tagespauschale.php` — `generiereEinsaetze()`, `loescheZukuenftigeEinsaetze()`, `hatUeberlappung()`, `anzahlTage()`, `anzahlVerrechnet()`
- `app/Http/Controllers/TagespauschaleController.php` — index, create, store, show, update (kein separates mutieren)
- `resources/views/tagespauschalen/` — index, create, show (show = Detail + Inline-Edit + Monatsübersicht)

**Geänderte Dateien:**
- `app/Models/Einsatz.php` — `tagespauschale_id` in fillable + `tagespauschale()` Beziehung
- `app/Models/RechnungsPosition.php` — `beschreibung` in fillable
- `app/Http/Controllers/RechnungslaufController.php`:
  - Filter: `orWhereNotNull('tagespauschale_id')` statt Leistungsart-Einheit-Check
  - Tarif für Tagespauschale: direkt aus `tagespauschale.ansatz`, rechnungstyp bestimmt Patient/KK-Aufteilung
  - Validation: `periode_von` + `periode_bis` müssen `before_or_equal:today` sein
- `resources/views/rechnungen/lauf/create.blade.php` — `max="{{ today()->format('Y-m-d') }}"` auf Date-Inputs + rote Warnung bei Zukunftsdatum

**UX-Regeln Tagespauschalen:**
- Nur vom Klienten-Detail aus erreichbar (kein eigener Hauptnav-Eintrag, ausser admin)
- Klienten-Detail: `<details>`-Sektion "Tagespauschalen" ganz unten, zeigt aktive TP als grünes Info-Badge
- Edit = direkt auf show-Seite (kein separates "mutieren")
- Speichern bleibt auf show, Zurück geht zu Klient
- Überlappungsschutz: kann nicht zwei TPs mit gleicher Periode für denselben Klienten geben
- Update-Logik: periode_von/bis Änderungen generieren neue Einsätze (Anfang/Ende) oder löschen unverrechnete

### Navigation: Rechnungsläufe eigenständig
- "Rechnungsläufe" neu als eigener Nav-Link unter "Abrechnung"
- Aktiv-State: `rechnungslauf.*` (getrennt von `rechnungen.*`)
- Redundanter "Rechnungslaeufe"-Button aus `rechnungen/index.blade.php` entfernt

### Klienten-Detail: Rechnungen-Sektion
- Letzte 15 Rechnungen (statt limit(20)), aktuellste zuerst
- Separater COUNT für Total → "→ Alle X Rechnungen" Link wenn >15

---

## Neu in Session 14 (2026-02-25)

### Swiss QR-Rechnung (Seite 2) im PDF
- `sprain/swiss-qr-bill` v5.3 installiert
- `PdfExportService` generiert QR-Code als PNG (base64) wenn IBAN in Regionsdaten vorhanden
- `pdfs/rechnung.blade.php` Seite 2: vollständiger Zahlteil + Empfangsschein (Swiss QR Standard)
  - 62mm Empfangsschein links (Trennlinie), 148mm Zahlteil rechts mit QR-Code
  - Wenn kein QR: Zahlungsinfo-Block mit IBAN-Text (wie bisher)
- `logo_ausrichtung` aus Org-Settings wird respektiert (3 Varianten):
  - `links_anschrift_rechts` (Standard): Logo links, Org-Details rechts
  - `rechts_anschrift_links`: Org-Details links, Logo rechts
  - `mitte_anschrift_fusszeile`: Logo zentriert
- Spalten der Positionstabelle je Rechnungstyp: nur KK / nur Patient / beide
- Deploy: `git pull && composer install --no-dev` auf Demo-Server erforderlich (neues Paket)

---

## Neu in Session 13 (2026-02-25)

### PDF-Export für Rechnungen
- `barryvdh/laravel-dompdf` (v3.1.1) installiert
- `app/Services/PdfExportService.php` — generiert A4-PDF aus Rechnung-Model
- `resources/views/pdfs/rechnung.blade.php` — professionelles Layout: Org-Kopf, Klient-Adresse, Positionstabelle, Totals, IBAN, Fusszeile
- Logo wird als Base64 eingebettet (DomPDF lädt keine externen URLs)
- Region-spezifische Bankdaten via `datenFuerRegion()`
- Route: `GET /rechnungen/{rechnung}/pdf` → `rechnungen.pdf`
- PDF-Button in `rechnungen/show.blade.php` aktiviert (war disabled "Folgt bald")
- Tarife sind in `rechnungs_positionen` eingefroren → PDF jederzeit korrekt regenerierbar

### Deploy-Workflow für Composer-Pakete (erkannt)
- `vendor/` ist in `.gitignore` — wird nie per FTP/Git deployed
- Korrekte Reihenfolge: lokal `composer require` → commit+push → Demo: `git pull && composer install --no-dev`
- FTP-Deploy nur für einzelne PHP/Blade-Dateien ohne Pakete

### Rapport: KI-Button-Text
- "KI Rapport schreiben" → "KI Bericht schreiben" (klarer: KI schreibt den Bericht-Text)

---

## Neu in Session 12 (2026-02-25)

### Architektur-Entscheid: Multi-Tenant via Subdomain + separate DB

- **Entscheid getroffen:** `kundenname.curasoft.ch` + eigene PostgreSQL-DB pro Organisation
- Wildcard DNS `*.curasoft.ch` beim Provider konfigurieren (max. 50 Subdomains — ausreichend)
- Keine Shared-DB mit `org_id` (Datenleck-Risiko für Gesundheitsdaten unakzeptabel)
- Demo unter `www.curasoft.ch` bleibt vorerst single-tenant, wird später `demo.curasoft.ch`
- Vollständiges Konzept siehe Abschnitt **"Multi-Tenant Architektur"** weiter oben

---

## Neu in Session 11 (2026-02-24)

### Nachrichten: Threading (parent_id)
- Migration: `parent_id` nullable FK auf `nachrichten` (Self-Reference), `nullOnDelete`
- `Nachricht` Model: `parent_id` in fillable, neue Beziehungen:
  - `antworten()` → hasMany Nachricht (parent_id), geordnet nach `created_at`
  - `parent()` → belongsTo Nachricht
- `NachrichtenController::antworten()`:
  - Setzt `parent_id = root.id` auf neue Antwort (immer zur Root-Nachricht verlinkt)
  - Empfänger-Logik: Absender antwortet → alle ursprünglichen Empfänger; Empfänger antwortet → Absender
  - Redirect immer zur Root-Nachricht (`nachrichten.show $root->id`)
- `NachrichtenController::show()`:
  - Wenn `parent_id` gesetzt → Redirect zur Root-Nachricht
  - Lädt vollständigen Thread: Root + alle Antworten (eager load `antworten.absender`)
  - Markiert alle Nachrichten im Thread als gelesen (Root + alle Antworten)
- `nachrichten/show.blade.php` — Thread-Ansicht:
  - Originalnachricht als Karte
  - Antworten als blau-linierte Karten (`border-left: 3px solid var(--cs-primaer)`)
  - Gemeinsames Antwort-Formular am Ende für alle Thread-Teilnehmer

### Nachrichten: Auto-Archivierung nach 90 Tagen
- In `index()`: einmal täglich (Cache-Throttle per `auth()->id()`, 24h TTL)
- Archiviert alle `nachricht_empfaenger`-Einträge älter als 90 Tage für den aktuellen Benutzer
- Kein Cronjob nötig — lazy cleanup beim ersten Posteingang-Aufruf des Tages

### Nachrichten: Archiv-Tab
- Dritter Tab "Archiv" in `nachrichten/index.blade.php`
- Zeigt alle archivierten Root-Nachrichten des Benutzers (manuell ✕ oder Auto-90-Tage)
- Archivierte Nachrichten bleiben lesbar (Thread-Ansicht weiterhin erreichbar)
- Posteingang und Gesendet filtern nun auf `whereNull('parent_id')` — nur Root-Nachrichten, keine einzelnen Antworten als separate Einträge

---

## Neu in Session 10 (2026-02-24)

### Vor-Ort-Seite: Komplettes Redesign
- **Header-Kachel (blau)** enthält jetzt alle Klienteninfos kompakt:
  - Name, Datum, Leistungsart, Zeit, Alter, Krankenkasse
  - Adresse als Text + `📍 Maps`-Button (anklickbar → Google Maps)
  - Telefon anklickbar (tel:)
  - Notfall in rot anklickbar
  - Diagnosen klein darunter
  - Verordnung abgelaufen → Warnung in rot
- Separate Adresse/Telefon/Patient/Diagnosen-Karten entfernt → alles im Header
- Hinweis (⚠) bleibt als eigene gelbe Karte direkt darunter

### Vor-Ort-Seite: Rapporte zum Einsatz
- `Einsatz::rapporte()` hasMany Relationship hinzugefügt
- `vorOrt()` lädt rapporte eager (`orderByDesc('datum')`)
- **Rapporte-Sektion** direkt nach Hinweis (vor Leistungserfassung)
- Klick auf Rapport → **Popup/Modal** von unten (kein Seitenwechsel)
- Modal zeigt: Datum + vollständiger Rapport-Text, `×` schliesst

### Rapport bearbeiten — NEU
- `RapporteController::edit()` + `update()` hinzugefügt
- Route: `GET /rapporte/{id}/edit` + `PUT /rapporte/{id}`
- `create.blade.php` dient als gemeinsame Create+Edit-View:
  - Titel, Form-Action, `@method('PUT')` je nach `$rapport` (null = neu)
  - Alle Felder vorausgefüllt mit bestehenden Werten (`$rapport?->feld`)
- **Vor-Ort-Button smart:**
  - Kein Rapport vorhanden → `+ Rapport schreiben`
  - Rapport vorhanden → `✏ Rapport bearbeiten`
  - Gilt für Button oben UND unten (Nav)
- **Store/Update Redirect:** wenn `einsatz_id` vorhanden → `einsaetze.vor-ort`, sonst `klienten.show`

### Security-Audit (extern)
- **SSL Labs:** A+ — TLS 1.3, HSTS, Forward Secrecy, alle bekannten Angriffe abgewehrt
- **Mozilla Observatory:** B+ (80/100) — nur CSP `unsafe-inline` als Abzug (-20)
  - `unsafe-inline` ist nötig für Blade-Inline-Styles/JS → bewusstes Tradeoff
  - Alle anderen Tests grün: Cookies, CORS, X-Frame, X-Content-Type, Referrer-Policy
- **Passwort-Sicherheit:** bcrypt, Rate Limiter, Magic Link, Face ID/Passkeys, CSRF-Schutz
- **Fazit:** Für Spitex-Pflegesoftware sehr solides Sicherheitsniveau — kein Handlungsbedarf

### Demo-Server: Stale Cache Fix
- **Problem:** Nach `git pull` auf Demo-Server crashte Dashboard mit `Undefined variable $einsaetzeDatumLabel`
- **Ursache:** Alter Route- und View-Cache wurde nicht automatisch invalidiert
- **Fix:** `php artisan optimize:clear` — clearrt config, cache, compiled, events, routes, views auf einmal
- **Merk-Regel:** Nach jedem `git pull` auf Demo: `php artisan optimize:clear` (nicht nur `view:clear`)

### Demo-Server: CLAUDE_API_KEY gesetzt
- `CLAUDE_API_KEY` fehlte in `/home/devitjob/public_html/spitex/.env`
- Manuell per `echo "CLAUDE_API_KEY=..." >> .env && php artisan config:clear` nachgetragen
- KI-Rapport funktioniert jetzt auf Demo-Server

### Rollenbasierte Back-Links — alle Pfade repariert
**Problem:** Pflege-Benutzer (Sandra) erhielten 403 beim Navigieren zurück, weil mehrere Links auf `einsaetze.show` zeigten, das nur für Admin zugänglich ist.

**Gefixt (3 Stellen):**
| Datei | War | Jetzt |
|-------|-----|-------|
| `rapporte/create.blade.php` "Abbrechen"-Button | `einsaetze.show` | `einsaetze.vor-ort` |
| `rapporte/show.blade.php` Einsatz-Datum-Link | `einsaetze.show` (immer) | admin→`show`, pflege→`vor-ort` |
| `einsaetze/vor-ort.blade.php` Header "← Zurück" | `einsaetze.show` (immer) | admin→`show`, pflege→`dashboard` |

**Noch vorhanden** (nur für Admin/Pflege mit Zugriff):
- `einsaetze/index.blade.php` → `einsaetze.show` (ok, pflege hat Zugriff auf Index)
- `klienten/show.blade.php` → `einsaetze.show` "Detail →" (nur Admin sieht das)

---

## Neu in Session 9 (2026-02-24)

### Vor-Ort-Workflow — Vollständig repariert und ausgebaut

#### Check-in/out auf Vor-Ort-Seite repariert
- `vor-ort.blade.php` verwendete `route('checkin.in', $einsatz->checkin_token)` — Route und Feld existierten nicht → 500er
- Neue Routen: `POST /checkin/{einsatz}/in` → `checkin.in`, `POST /checkout/{einsatz}/out` → `checkin.out`
- Neue Controller-Methoden `CheckInController::checkinVorOrt()` + `checkoutVorOrt()` — nutzen `now()` direkt, kein Token nötig
- Nach GPS/manuell Checkout: Redirect zu `einsaetze.vor-ort` statt `einsaetze.show` → Pflegerin sieht sofort Rapport-Button

#### Dashboard: "Vor Ort →" Link
- Jede Einsatz-Zeile auf Dashboard hat rechts Badge-Link `Vor Ort →` → direkt zur Vor-Ort-Seite
- Rapport-Back-Link: `← Zurück` geht zu `einsaetze.vor-ort` statt `einsaetze.show` (kein Zugriffsproblem mehr)

#### Rapport-Buttons: oben UND unten
- Vor-Ort-Seite: `+ Rapport schreiben` Button sowohl oben (nach Header) als auch unten (nach Leistungserfassung)
- Bottom Nav reduziert auf nur diesen einen Button — volle Breite, blau

### Leistungserfassung — NEU
- Neue Tabelle `einsatz_aktivitaeten` (migration `2026_02_24_000001`)
- Model `EinsatzAktivitaet` mit 25 vordefinierten Tätigkeiten in 5 Kategorien:
  - **Grundpflege**: Körperwäsche, Intimpflege, Ankleiden, Mund-/Zahnpflege, Rasur, Haarpflege, Nagelpflege
  - **Untersuchung/Behandlung**: Medikamentengabe, Verbandswechsel, Blutdruck/Vitalzeichen, Injektion/Insulin, Augentropfen, Sondenpflege/PEG
  - **Mobilisation**: Aufstehen/Hinlegen, Transfer, Gehübungen, Lagerung
  - **Hauswirtschaft**: Zimmer, Wäsche, Einkaufen, Kochen, Abwaschen
  - **Abklärung/Beratung**: Erstassessment, Beratungsgespräch, Angehörige informieren, Arztgespräch
- `Einsatz::aktivitaeten()` hasMany Relationship
- `EinsaetzeController::aktivitaetenSpeichern()` — delete + recreate Strategie
- Route: `POST /einsaetze/{einsatz}/aktivitaeten` → `einsaetze.aktivitaeten.speichern`
- **Vor-Ort-UI**: Checkliste mit Kategorien, Checkbox anklicken → Zeile grün, Standard 5 Min, `[−]` / `[+]` in 5er-Schritten, Gesamt-Minuten-Anzeige, gespeicherte Tätigkeiten vorausgefüllt

### KI-Assistent — Mikrofon-Buttons überarbeitet
- Rapport-Seite: Mikrofon-Button war winziges Icon-in-Textarea → jetzt volle Buttons
- **Stichworte-Bereich**: `[🎙 Diktieren]` und `[✨ KI Rapport schreiben]` nebeneinander, gleich gross
- **Bericht-Feld**: `[🎙 Direkt in Bericht diktieren]` volle Breite unterhalb Textarea
- Button wechselt zu `🔴 Stoppen` (roter Hintergrund) wenn Diktat läuft

### Sandra-Passwort zurückgesetzt
- Lokal: `Sandra2026!` (Spalte heisst `password` nicht `passwort`)

---

## Neu in Session 8 (2026-02-24)

### Dashboard — komplett überarbeitet
- **Stat-Chips** statt grosse Kacheln: `[Label  Zahl]` in einer Zeile, anklickbar, kaum Platzbedarf
- **Einsätze-Liste** direkt auf Dashboard: Zeit, Patient, Leistungsart, Status, Mitarbeiter (max. 10)
  - Ersetzt die "Touren heute"-Karte — kein doppelter Begriff mehr
  - Falls heute keine Einsätze: automatisch nächsten Tag mit Einsätzen anzeigen
- **Rapporte-Liste**: Klick auf Rapport → Rapport-Detail (nicht mehr Klient-Seite)
- **Logo/Firmenname**: Klick → Dashboard
- **Mobile Fix**: Listenzeilen umbrechen statt überlaufen; Firmenname im Header sichtbar wenn Sidebar versteckt

### Navigation — Topnav Dropdown
- "Verwaltung"-Menü in horizontaler Nav als **Dropdown** ausgebaut
- Enthält: Mitarbeitende, Firma, Leistungsarten, Einsatzarten, Regionen, Ärzte, Krankenkassen, Audit-Log
- **Layout-Toggle-Button** im Header (Admin): wechselt Sidebar ↔ Topnav per Klick

### Firma / Design-Einstellungen
- Neuer Abschnitt **"Design & Logo"** in `/firma`:
  - Logo hochladen (PNG/SVG/JPG, max. 2 MB) — wird in `public/uploads/` gespeichert
  - Primärfarbe mit Farbwähler + 7 Schnellfarben + Hex-Eingabe
  - Navigation (Sidebar / Top) umschalten
- Layout + Farbe + Logo werden **aus DB gelesen** (nicht mehr nur aus `.env`) → sofort aktiv ohne Restart
- Abgeleitete Farben (hell/dunkel) werden automatisch aus Primärfarbe berechnet
- App-Name im Titel kommt aus `organisation.name` (DB)

### Deploy-Workflow — AKTUELL (ab Session 16, 2026-02-27)

#### Ein Befehl für alles:
```bash
./deploy.sh        # Code + CSS/JS + Server-Deploy
./deploy.sh db     # Wie oben + DB-Daten syncen (Testdaten + Organisation)
```

#### Was deploy.sh macht:
| Schritt | Was | Wie |
|---------|-----|-----|
| 1 | `npm run build` | Vite Assets lokal bauen |
| 2 | `git push` | Code auf GitHub |
| 3 | FTP | CSS + JS nach `public/build/` |
| 4 | FTP + HTTP | `deploy/server.php` → `public/srv_XYZ.php` → aufrufen → leeren |
| 5 | (nur `db`) | `deploy/db_sync.php` → `db_import.php` → FTP → aufrufen → löschen |

#### Server-Deploy macht intern:
```
git reset --hard origin/master   ← IMMER reset, nie pull (vermeidet Konflikte)
composer install --no-dev        ← Alle PHP-Pakete
php artisan migrate --force      ← Neue Migrationen
php artisan optimize:clear       ← Cache flush
```

#### DB Sync (wann nötig):
- `./deploy.sh db` — bei Testdaten-Änderungen oder Firma-Daten-Änderungen
- **NICHT** bei reinen Code-Änderungen
- Synct: alle Tabellen + `organisationen` (Firma-Name, Adresse, IBAN usw.)
- Überschreibt Demo-DB vollständig mit lokalen Daten

#### Dateien im deploy/-Verzeichnis:
| Datei | Zweck | In git? |
|-------|-------|---------|
| `deploy/server.php` | Server-seitiges Deploy-Script | ✅ ja |
| `deploy/db_sync.php` | DB-Export-Generator | ✅ ja |
| `deploy/db_import.php` | Generiert, temporär | ❌ gitignored |

#### Produktiv vs. Demo:
| Befehl | Demo | Produktiv |
|--------|------|-----------|
| `./deploy.sh` | ✅ | ✅ sicher — nur Code + Migrationen |
| `./deploy.sh db` | ✅ | ❌ NIE — überschreibt alle Produktivdaten |

#### NIEMALS mehr manuell per FTP einzelne PHP-Dateien hochladen!
Manuelles FTP = Tod für die Entwicklung. Alles über git → deploy.sh. Keine Ausnahmen, nie wieder.

---

## Neu in Session 7 (2026-02-24)

### Demo-Server aufgesetzt (www.curasoft.ch)
- **Host:** devitjob.ch (cPanel Shared Hosting)
- **Domain:** `www.curasoft.ch` → Document Root: `/home/devitjob/public_html/spitex/public`
- **DB:** `devitjob_curasoft`, User: `devitjob_csapp`
- **PHP:** 8.2.29, Git 2.48.2, Composer 2.8.11, PostgreSQL 13.23
- **Repo:** Public GitHub `mhnilicka1962-boop/curasoft` — via `git clone` auf Server
- **Vite Assets:** Lokal gebaut (`npm run build`), per FTP hochgeladen nach `public/build/`
- **Alle Seeders eingespielt:** LeistungsartenSeeder, EinsatzartenSeeder, KrankenkassenSeeder, QualifikationenSeeder, TestdatenSeeder
- **Cache-Tabelle** nachträglich angelegt: `php artisan cache:table && php artisan migrate --force`

### TestdatenSeeder — Vollständig ausgebaut
- 10 Pflegefachpersonen (Sandra Huber, Peter Keller, Monika Leuthold, Beat Zimmermann, Claudia Roth, Thomas Brunner, Ursula Streit, Marco Steiner, Andrea Maurer, Daniel Fehr)
- 3 pflegende Angehörige (Ruth Gerber, Franziska Käser, Stefan Schneider) — `anstellungsart='angehoerig'`, nicht in Touren
- 1 Buchhaltung (Lisa Bauer)
- 5 Ärzte (Müller/Allgemein, Weber/Neurologie, Fischer/Kardiologie, Huber/Geriatrie, Meier/Onkologie)
- 20 Klienten mit vollen Details
- 383 Einsätze, 88 Rapporte, 6 Touren, 5 Rechnungen, 8 Verordnungen
- Alle Passwörter: `test1234`

### AuthController — Email trim()
- `Auth::attempt()` ruft jetzt `trim($request->email)` auf → verhindert Login-Fehler bei versehentlichen Leerzeichen

### Passkeys / Face ID — Testworkflow & Erkenntnisse
- Lokal (`http://spitex.test`) **nicht testbar** — kein HTTPS, Browser blockiert WebAuthn
- **Demo-Server** (`https://www.curasoft.ch`) hat HTTPS → Passkeys dort testen
- Workflow: lokal entwickeln → auf Demo deployen → Passkeys auf Demo testen
- **Fix `authenticatorAttachment: 'platform'`** in `WebAuthnController::registerOptions()` — erzwingt Gerät-Authenticator (Face ID) statt externe Geräte
- **Microsoft Authenticator Problem:** Wenn installiert, fängt er Passkeys ab. Fix: iOS Einstellungen → Passwörter → AutoFill → "Passwörter (Passkeys)" aktivieren, dann "In Passwörter sichern" wählen
- **PWA installierbar:** Safari → Teilen → "Zum Home-Bildschirm" → App-Icon → Face ID → drin
- Betriebsanweisung: `docs/ANLEITUNG_EINLOGGEN.md`

---

## Neu in Session 6 (2026-02-23)

### Apache als Windows-Dienst
- Apache läuft jetzt als Windows-Dienst `Apache2.4` (auto-start)
- Laragon GUI nicht mehr nötig für Entwicklung
- PostgreSQL war bereits Dienst

### Login-Verbesserungen
- Magic Link als Standard-Tab auf Login-Seite (Passwort an zweiter Stelle)
- Rate Limiter fix: `RateLimiter::hit($key, 900)` statt named argument `decay:`
- Nach Login: `pflege`-Rolle landet direkt auf Tourenplan (heute + benutzer_id)

### Leistungsarten-Freigabe pro Mitarbeiter
- Migration `2026_02_23_125201`: Pivot `benutzer_leistungsarten`
- `Benutzer::erlaubteLeistungsarten()` + `darfLeistungsart()` — leer = alle erlaubt
- Mitarbeiter-Detail: Checkbox-Sektion "Erlaubte Leistungsarten"
- EinsaetzeController store + update: Warnung wenn Pflegeperson nicht freigegeben

### Vor-Ort-Ansicht (`/einsaetze/{id}/vor-ort`)
- Eigene mobile HTML-Seite ohne Sidebar-Layout
- Check-in/out direkt (grosser Button)
- Adresse mit Google Maps Link, Telefon anklickbar (tel:)
- Notfallkontakte rot hervorgehoben
- Hinweis/Bemerkung gelb
- Klient-Basisdaten, Diagnosen, Ärztliche Verordnung mit Ablaufwarnung
- Navigation unten: + Rapport / Klient-Detail / Einsatz
- Tour-Detail: Klientenname verlinkt auf Vor-Ort-Ansicht

### Tourenplan — Pflege-Optimierung
- Titel "Deine Tour heute" für pflege-Rolle
- "+ Neue Tour" und "⚠ Nicht eingeplante Einsätze" für pflege ausgeblendet
- Einsätze in Tour-Liste direkt auf Vor-Ort-Ansicht verlinkt
- Bei keiner Tour: eigene Einsätze als anklickbare Fallback-Liste
- Rote Karte "⚠ Offene Einsätze — bitte nachbearbeiten" für vergangene offene Einsätze

### Diverses
- Nav: Rechnungen-Link für pflege-Rolle ausgeblendet (Route ist admin/buchhaltung)

---

## Neu in Session 5 (2026-02-23)

### KLV-Compliance
- **5-Minuten-Takt**: Validierung in EinsaetzeController (store + update) — Startzeit und Endzeit müssen Vielfache von 5 min sein; Mindestdauer 10 Minuten
- **Tiers payant / Tiers garant**: Boolean-Feld auf `klient_krankenkassen` — steuert XML-Struktur und Betrag-Aufteilung
- **Ärztliche Verordnungen** (`klient_verordnungen`): Neue Tabelle, Model, Routes, Controller-Methoden, Blade-Sektion im Klienten-Detail
  - Verknüpfung auf Einsatz-Ebene: `verordnung_id` FK auf `einsaetze`
  - Einsatz-Formular zeigt aktive Verordnungen des gewählten Klienten

### XML 450.100 — Vollständige Neuimplementierung
- Root-Element: `generalInvoiceRequest` (war falsch: `medicalInvoice`)
- Korrekte Struktur: `payload > invoice + body > tiers_payant|tiers_garant > biller/provider/insurance/patient/kvg`
- Biller + Provider: verschachtelte `company > postal > street/zip/city` Elemente
- Patient: `person (familyname/givenname)` + `postal`
- `kvg > treatment`: Periode, Kanton aus `region.kuerzel`, ICD-10-Diagnosen (main/secondary)
- Services: `tariff_type=311`, `unit=min`, Minuten als Quantität, CHF/min Preis, per-Service-Datum
- Tiers payant/garant dynamisch aus KK-Zuweisung — `amount_due` / `amount_prepaid` korrekt aufgeteilt
- Verordnungs-Nr als `obligation`-Attribut auf Service-Ebene

### tarmed_code auf leistungsarten
- Migration `2026_02_23_140000`: `tarmed_code varchar(20) nullable`
- Edit-Formular + Show-Ansicht ergänzt
- XmlExportService nutzt `$la->tarmed_code ?? '00.0010'`

### Bexio UI-Buttons
- `POST /klienten/{klient}/bexio/sync` → `KlientenController@bexioSync`
- `POST /rechnungen/{rechnung}/bexio/sync` → `RechnungenController@bexioSync`
- Button `→ Bexio` (erster Sync) / `↻ Bexio` (Update) — nur sichtbar wenn `bexio_api_key` konfiguriert
- Tooltip zeigt vorhandene Bexio-ID
- `Benutzer::organisation()` Relationship ergänzt

### Security Paket A (nDSG/VDSG-Konformität)
- **Rate Limiter** wieder aktiv in `AuthController`: `login()` + `sendMagicLink()` — max. 5 Versuche / 15 min pro IP (`RateLimiter::tooManyAttempts`), bei Erfolg automatisch gelöscht
- **Content-Security-Policy** in `SecurityHeaders`-Middleware: `default-src 'self'`, `script-src 'unsafe-inline'`, `connect-src https://api.bexio.com`, `frame-ancestors 'none'`; HSTS mit `preload`
- **Session-Sicherheit** in `.env.example`: `SESSION_LIFETIME=60`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`
- **bexio_api_key verschlüsselt**: `Organisation::$casts['bexio_api_key'] = 'encrypted'` — Laravel verschlüsselt transparenter mit APP_KEY

### Angehörigenpflege (CH-Regelung ab 1.5.2023)
- Migration `2026_02_23_150000`: 4 neue Felder
  - `klienten.klient_typ`: `patient` | `pflegebeduerftig` | `angehoerig` (default `patient`)
  - `klient_benutzer.beziehungstyp`: `fachperson` | `angehoerig_pflegend` | `freiwillig` (nullable)
  - `benutzer.anstellungsart`: `fachperson` | `angehoerig` | `freiwillig` | `praktikum` (default `fachperson`)
  - `einsaetze.leistungserbringer_typ`: `fachperson` | `angehoerig` (default `fachperson`)
- `Klient`: +`klientTypBadge()` (Badge im Header), +`klientTypLabel()`
- `Einsatz`: +`leistungserbringer_typ` in `$fillable`
- `KlientBenutzer`: +`beziehungstyp` in `$fillable`
- `Benutzer`: +`anstellungsart` in `$fillable`
- `XmlExportService`: `specialty` jetzt dynamisch — `39` wenn mind. 1 Einsatz `leistungserbringer_typ=angehoerig`, sonst `37`
- Views: Klient-Formular (+klient_typ), Einsatz create/edit (+leistungserbringer_typ), Mitarbeiter-Detail (+anstellungsart + beziehungstyp in Klient-Zuweisung)

### Swiss Krankenkassen Seeder
- `KrankenkassenSeeder`: 39 KVG-Krankenkassen mit BAG-Nr und EAN (CSS, Helsana, SWICA, Concordia, Sanitas, KPT, Visana, Sympany, Assura, Atupri, Groupe Mutuel, EGK, ÖKK u.a.)

### Landing Page — Neugestaltung
- Zielt auf **alle** Schweizer Spitex-Dienste (kantonal + kantonsübergreifend)
- 26 Kantone als Pills, Kantonsübergreifend als zentrales USP
- Tarif-Beispieltabelle (AG/ZH/BE/ZG), 3 Zielgruppen-Cards
- Schnittstellen: XML 450.100 ✅, MediData (in Entwicklung), Bexio ✅, QR/GPS ✅

---

## Neu in Session 4 (2026-02-22)

### WebAuthn / Passkeys (Face ID Login)
- `WebAuthnController.php` — komplett neu (CBOR-Decoder, COSE→SPKI, DER-Encoding, OpenSSL-Verify)
- `ProfilController.php` — neu, zeigt Passkeys, Registrierung/Löschung
- `resources/views/profil/index.blade.php` — neu
- `resources/views/auth/login.blade.php` — Face-ID Tab, PWA-Metatags, Install-Banner
- Migration `webauthn_credentials` bereits vorhanden
- Routen: `webauthn.authenticate.options`, `webauthn.authenticate`, `webauthn.register.options`, `webauthn.register`, `webauthn.delete`, `profil.index`

### Tourenplanung — Vollausbau
- **Tour erstellen** (`/touren/create`): MA+Datum → Seite lädt, zeigt offene Einsätze als Checkboxen, Bezeichnung auto-generiert
- **Tour-Detail** (`/touren/{id}`): Check-in/out-Zeiten mit Abweichung, Rapport-Badge, Zeilen-Farbkodierung (grün/orange), Mehrfach-Zuweisung per Checkboxen, Fortschrittsanzeige
- **Touren-Index** (`/touren`): ⚠ Lücken-Warnung — zeigt Einsätze ohne Tour, gruppiert nach MA, "Tour erstellen"-Button
- **Einsatz anlegen aus Tour**: Button "+ Einsatz anlegen" wenn keine Einsätze für MA+Datum, nach Speichern zurück zur Tour-Erstellung

### Pflegeplan im Klienten-Detail
- Abschnitt "Pflegeplan — Nächste 14 Tage" ganz oben in `klienten/show.blade.php`
- Zeigt tageweise: Mitarbeiter, Leistungsart, Uhrzeit, Status
- Grau bei fehlendem Einsatz ("Kein Einsatz geplant")
- Serie-Badge + "× Serie löschen" Button für wiederkehrende Serien

### Wiederkehrende Einsätze
- Formular `/einsaetze/create`: Wiederholung (Wöchentlich / Täglich), Wochentage-Auswahl (farbige Pills), Enddatum, Live-Preview ("13 Einsätze werden erstellt")
- Controller: Loop von Startdatum bis Enddatum, max 365 Iterationen, `serie_id` UUID als Gruppenkennung
- Migration `2026_02_22_220913`: `serie_id UUID nullable` auf `einsaetze`
- Serie löschen: `DELETE /einsaetze/serie/{serieId}` — löscht nur zukünftige, nicht abgeschlossene, nicht in Tour eingeplante Einsätze

### Migration (neu)
| Migration | Inhalt |
|-----------|--------|
| `2026_02_22_220913` | `einsaetze.serie_id` UUID nullable — Serien-Gruppierung |

---

## Bekannte offene Punkte

- **Tourenplanung**: Reihenfolge per Nummer setzbar, kein Drag-and-Drop.
- **Wiederkehrende Einsätze**: Serie bearbeiten (alle verschieben) noch nicht gebaut — nur Löschen möglich.
- **Profil-Seite**: Link im Header-User-Menu → `profil.index`.
- **Dokumente**: Speicher unter `storage/app/dokumente/{org_id}/` — kein public Zugriff, nur Download.
- **Klienten-Index**: Default zeigt nur aktive Klienten (Filter "Aktiv" vorausgewählt).
- **PDF-Druck**: Button auf Rechnungs-Detail vorhanden aber `disabled` ("Folgt bald").
- **MediData-Schnittstelle**: Auf Landing Page als "in Entwicklung" markiert — noch nicht gebaut.
- **EPD** (Elektronisches Patientendossier): Pflicht ab 2026 — noch nicht geplant.
- **Bexio**: Buttons gebaut. `bexio_api_key` muss in Firma → Bexio konfiguriert sein, sonst unsichtbar.
- **Security Paket B**: Audit-Log ✅ — vollständig implementiert und getestet (Login/Logout/erstellt/geändert, Filter nach Benutzer/Aktion/Modell/Datum).
- **Multi-Tenant Basis**: ✅ TenantMiddleware, master:init, tenant:create, Login-Seite pro Tenant — produktiv live (curapflege.curasoft.ch).
- **Security Paket C**: 2FA (TOTP) als zweiter Faktor — noch nicht gebaut. Passkey (WebAuthn) vorhanden als Alternative.
- **Vor-Ort-Ansicht**: Check-in/out vollständig repariert — `checkin.in` / `checkin.out` Routen vorhanden.
- **Leistungserfassung**: Checkliste auf Vor-Ort-Seite vorhanden. Noch nicht: Anbindung an Abrechnung (welche Minuten → welche Leistungsart → Rechnung).
- **Apache Dienst**: Läuft als `Apache2.4` Windows-Dienst. Laragon GUI nicht mehr nötig.

---

## Projektstruktur

```
app/
  Http/Controllers/
    AerzteController.php
    AuthController.php           ← Rate Limiter: max 5/15min; pflege → redirect Tourenplan
    CheckInController.php
    DokumenteController.php
    EinsatzartenController.php
    EinsaetzeController.php      ← +5-min Validierung, +verordnung_id, +leistungserbringer_typ, +vorOrt()
    FirmaController.php          ← +bexioSpeichern() +bexioTesten()
    KlientenController.php       ← +bexioSync(), +verordnungSpeichern/Entfernen(), +tiers_payant, +klient_typ
    KrankenkassenController.php
    LeistungsartenController.php ← +tarmed_code Validierung
    NachrichtenController.php
    RapporteController.php
    RechnungenController.php     ← +xmlExport() +bexioSync()
    RegionenController.php
    TourenController.php
  Middleware/
    SecurityHeaders.php          ← CSP, HSTS+preload, X-Frame, X-Content-Type
  Models/
    Arzt.php, KlientArzt.php
    Benutzer.php                 ← +organisation(), +anstellungsart, +erlaubteLeistungsarten(), +darfLeistungsart()
    BexioSync.php
    Dokument.php
    Einsatz.php                  ← +verordnung_id, +verordnung() Relationship, +leistungserbringer_typ
    KlientBenutzer.php           ← +beziehungstyp
    Klient.php                   ← +verordnungen() Relationship, +klient_typ, +klientTypBadge()
    KlientAdresse.php
    KlientBeitrag.php
    KlientDiagnose.php
    KlientKontakt.php
    KlientKrankenkasse.php       ← +tiers_payant
    KlientPflegestufe.php
    KlientVerordnung.php         ← NEU: Ärztliche Verordnungen
    Krankenkasse.php
    Leistungsart.php             ← +tarmed_code
    Leistungsregion.php
    Leistungstyp.php
    Organisation.php             ← +bexio_api_key encrypted cast
    Rapport.php
    RechnungsPosition.php        ← +leistungstyp() Relationship
    Region.php
    Tour.php
  Services/
    BexioService.php             ← verbindungTesten(), kontaktSynchronisieren(), rechnungSynchronisieren()
    XmlExportService.php         ← Vollständige Neuimplementierung 450.100; specialty 37/39 dynamisch

resources/views/
  landing.blade.php              ← Neugestaltung: alle 26 Kantone, kantonsübergreifend
  dashboard.blade.php
  klienten/
    index.blade.php              ← Default: nur aktive Klienten
    show.blade.php               ← +Bexio-Sync Button, +Tiers payant Badge, +Ärztliche Verordnungen, +klientTypBadge
    _formular.blade.php          ← +klient_typ Dropdown
  einsaetze/
    create.blade.php             ← +Verordnung-Dropdown, +leistungserbringer_typ
    edit.blade.php               ← +leistungserbringer_typ
    vor-ort.blade.php            ← NEU: mobile Vor-Ort-Ansicht
  rechnungen/
    show.blade.php               ← +XML-Button, +Bexio-Sync Button
  rapporte/
    index.blade.php, create.blade.php, show.blade.php
  touren/
    index.blade.php              ← +pflege-Optimierung (Titel, Links, Fallback, offene Vergangen.)
    create.blade.php, show.blade.php
  stammdaten/
    leistungsarten/
      index.blade.php
      edit.blade.php             ← +tarmed_code Feld
      show.blade.php             ← +tarmed_code im Header
      tarif_edit.blade.php
    einsatzarten/
      index.blade.php, edit.blade.php
    regionen/
      index.blade.php, show.blade.php
    aerzte/    (index, create, edit, _formular)
    krankenkassen/ (index, create, edit, _formular)
    firma/     (index + Bexio-Sektion)
    mitarbeiter/
      show.blade.php             ← +anstellungsart, +beziehungstyp in Klient-Zuweisung
```

---

## Session-Start — IMMER AUSFÜHREN

Laragon GUI startet nicht mehr (Lizenzkey-Pflicht). Apache und PostgreSQL müssen manuell geprüft und ggf. gestartet werden.

### 1. Prüfen ob Apache und PostgreSQL laufen

```bash
tasklist | grep -i httpd
tasklist | grep -i postgres
```

### 2. Falls Apache nicht läuft — direkt starten

```bash
# Apache starten
start "" "C:/laragon/bin/apache/httpd-2.4.66-260107-Win64-VS18/bin/httpd.exe"
```

### 3. Falls Apache neu geladen werden muss (z.B. neue VHost-Config)

```bash
# Erst beenden, dann neu starten
taskkill //IM httpd.exe //F
sleep 2
start "" "C:/laragon/bin/apache/httpd-2.4.66-260107-Win64-VS18/bin/httpd.exe"
```

### 4. Falls PostgreSQL nicht läuft

```bash
start "" "C:/laragon/bin/postgresql/postgresql/bin/pg_ctl.exe" start -D "C:/laragon/data/postgresql"
```

### 5. Danach prüfen

```bash
tasklist | grep -i httpd    # httpd.exe muss erscheinen
tasklist | grep -i postgres # postgres.exe muss erscheinen
```

→ Dann `http://spitex.test` im Browser aufrufen.

---

## Laptop-Setup (neues Gerät)

```bash
# 1. Laragon installieren (https://laragon.org)
#    → PHP 8.3, PostgreSQL, Apache aktivieren

# 2. Projekt klonen
cd C:\laragon\www
git clone <repo-url> spitex

# 3. Dependencies
cd spitex
composer install
npm install && npm run build

# 4. .env anlegen
cp .env.example .env
php artisan key:generate

# .env anpassen:
# APP_URL=http://spitex.test
# DB_CONNECTION=pgsql
# DB_HOST=localhost
# DB_PORT=5432
# DB_DATABASE=spitex
# DB_USERNAME=postgres
# DB_PASSWORD=

# 5. Datenbank anlegen (pgAdmin oder psql)
# CREATE DATABASE spitex;

# 6. Migrationen + Seeders
php artisan migrate
php artisan db:seed --class=LeistungsartenSeeder
php artisan db:seed --class=EinsatzartenSeeder

# 7. Storage verlinken
php artisan storage:link

# 8. Laragon: Virtual Host spitex.test → C:\laragon\www\spitex\public

# 9. Ersten Admin-User anlegen via Setup-Wizard
# http://spitex.test/setup
```

---

## Arbeitsregeln — IMMER EINHALTEN

### Keine Software ohne Rückfrage installieren
Für dieses Projekt wird **keine zusätzliche Software** benötigt:
- Laragon (Apache, PHP, PostgreSQL) ✓
- Composer ✓
- Node/NPM ✓
- Laravel 12 ✓

### Arbeitsablauf — ABSOLUT VERBINDLICH

Auftrag kommt → kurze Zusammenfassung → Mathias sagt ja → fertig bauen. Das war es.

**VERBOTEN — ausnahmslos:**
- Nummerierte Listen mit "Stimmt das so?" am Ende
- "Soll ich...?" / "Darf ich...?" / "Freigabe?"
- Bestätigungen einholen nach jedem Schritt
- Zusammenfassungen mit Fragezeichen
- "Nächster Schritt wenn du bereit bist"
- Jede Form von Rückfrage während der Arbeit

**Nach dem Bauen:** Ergebnis kurz zeigen. Fertig.

Wenn etwas technisch unklar ist → einmal direkt fragen, dann sofort ausführen.

### Lokale Entwicklungsumgebung
- App läuft auf `http://spitex.test` (Laragon)
- **Kein ngrok** — CSRF/Session-Probleme, nicht zuverlässig
- Für Handy-Tests: gleiches WLAN, direkte IP des PCs

---

## CSS-Architektur — ZWINGEND EINHALTEN

### Grundregel
**Alle wiederholten Darstellungsmuster gehören ins CSS — niemals als `style=""`-Attribut im Blade.**

Warum: Inline-Styles können nicht durch `@media`-Queries überschrieben werden → Mobile-Darstellung kaputt.

### Einzige CSS-Datei
`resources/css/app.css` → kompiliert via Vite nach `public/build/assets/app-*.css`

**Nach jeder CSS-Änderung**: `npm run build`

### Was erlaubt ist als Inline-Style
Nur dynamisch berechnete Werte die nicht im CSS stehen können:
- `style="{{ $klient->aktiv ? '' : 'opacity: 0.55;' }}"` — PHP-Bedingungen
- `style="max-width: 600px;"` — einmalige Seitenbreiten-Einschränkungen
- `style="color: {{ $istHeute ? 'var(--cs-primaer)' : 'var(--cs-text)' }}"` — dynamische Variablen

### Was NICHT als Inline-Style erlaubt ist
Alles was sich wiederholt oder auf Mobile anders aussehen soll:
- Farben (`color: var(--cs-text-hell)` → Klasse `.text-hell`)
- Schriftgrössen (`font-size: 0.875rem` → Klasse `.text-klein`)
- Flex-Layouts für Seitenköpfe → Klasse `.seiten-kopf`
- Grid-Layouts → Klassen `.form-grid`, `.form-grid-2`, `.form-grid-3`
- Text-Ausrichtung → `.text-rechts`, `.text-mitte`

### CSS-Klassen-Katalog

#### Layout & Struktur
| Klasse | Verwendung |
|--------|-----------|
| `.seiten-kopf` | Flex-Header Titel + Aktion (space-between, wrappend) |
| `.abschnitt-label` | Grauer Uppercase-Label für Karten-Überschriften |
| `.abschnitt-trenn` | Horizontaler Trenner mit Abstand (border-top) |
| `.karte` | Weisse Box mit Border, Shadow, 1.25rem Padding |
| `.karte-null` | Wie `.karte` aber padding 0 — für eingebettete Tabellen |

#### Typografie
| Klasse | Verwendung |
|--------|-----------|
| `.text-hell` | Gedämpfte Farbe (`--cs-text-hell`, grau) |
| `.text-primaer` | Primärfarbe Blau |
| `.text-klein` | `font-size: 0.875rem` |
| `.text-mini` | `font-size: 0.75rem` |
| `.text-fett` | `font-weight: 600` |
| `.text-mittel` | `font-weight: 500` |
| `.text-rechts` | `text-align: right` |
| `.text-mitte` | `text-align: center` |
| `.link-primaer` | Blauer Link ohne Unterstrich |
| `.link-gedaempt` | Grauer kleiner Link |

#### Formulare
| Klasse | Verwendung |
|--------|-----------|
| `.feld` | Input/Select/Textarea — volle Breite, Fokus-Outline |
| `.feld-label` | Label über Formularfeld |
| `.form-grid` | Auto-Grid `repeat(auto-fill, minmax(180px, 1fr))` |
| `.form-grid-2` | Festes 2-Spalten-Grid |
| `.form-grid-3` | Festes 3-Spalten-Grid |

#### Tabellen
| Klasse | Verwendung |
|--------|-----------|
| `.tabelle` | Standard-Tabelle mit Hover-Effekt |
| `.tabelle-wrapper` | Wrapper der auf Mobile `overflow-x: auto` aktiviert |
| `.col-desktop` | Spalte/Element nur auf Desktop sichtbar (`display: none` auf Mobile) |
| `.mobile-meta` | Zusatzinfo nur auf Mobile sichtbar (in Name-Zelle) |

#### Detail-Ansichten
| Klasse | Verwendung |
|--------|-----------|
| `.detail-raster` | 2-Spalten-Grid für Label+Wert-Paare |
| `.detail-label` | Kleines graues Label |
| `.detail-wert` | Wert mit `font-weight: 500` |

#### Info-Boxen
| Klasse | Verwendung |
|--------|-----------|
| `.info-box` | Blauer Hinweis-Bereich |
| `.warn-box` | Roter Warn-Bereich (kleiner Text) |
| `.erfolg-box` | Grüner Erfolgs-Bereich (zentriert) |

#### Badges
| Klasse | Verwendung |
|--------|-----------|
| `.badge` | Basis-Badge (pill, klein) |
| `.badge-primaer` | Blau — für Typen/Standard |
| `.badge-erfolg` | Grün — Aktiv, OK |
| `.badge-warnung` | Gelb — Achtung |
| `.badge-fehler` | Rot — Fehler, Zwischenfall |
| `.badge-grau` | Grau — Inaktiv, Standard |
| `.badge-info` | Hellblau — Info |

#### Buttons
| Klasse | Verwendung |
|--------|-----------|
| `.btn` | Basis-Button |
| `.btn-primaer` | Blauer Haupt-Button |
| `.btn-sekundaer` | Grauer Neben-Button |
| `.btn-gefahr` | Roter Löschen-Button |

#### Navigation
| Klasse | Verwendung |
|--------|-----------|
| `.nav-link` | Sidebar-Navigationslink |
| `.nav-link.aktiv` | Aktiver Sidebar-Link (blau, Border rechts) |
| `.nav-abschnitt` | Grauer Abschnitts-Header in Sidebar |
| `.topnav-link` | Top-Navigation-Link |
| `.topnav-link.aktiv` | Aktiver Top-Nav-Link |

### Responsivität
- `@media (max-width: 768px)` in `app.css`:
  - Alle Grids (`.form-grid`, `.form-grid-2`, `.form-grid-3`, `.detail-raster`) → 1-Spaltig
  - `.col-desktop` → `display: none !important`
  - `.mobile-meta` → `display: block !important`
  - `.tabelle-wrapper` → `overflow-x: auto`
  - Sidebar fährt aus (Transform)

---

## Konventionen

- **Sprache**: Laravel 12, PHP 8.3, PostgreSQL, Blade
- **Multi-Tenant**: `where('organisation_id', $this->orgId())` — nur 1 Org vorhanden
- **Rollen**: `admin` | `pflege` | `buchhaltung` — Middleware `rolle:admin,pflege`
- **Auth-Model**: `App\Models\Benutzer`, Tabelle `benutzer`
- **Rate Limiter**: aktiv in AuthController — max 5/15 min auf `login` + `magic-link` pro IP
- **CSS-Klassen**: siehe CSS-Architektur-Sektion oben
- **Formulare**: `@csrf`, `@method('PUT'/'DELETE')`, Fehler mit `@error('feld')`
- **Suche**: PostgreSQL `ilike` für case-insensitive
- **Pagination**: `->paginate(25)->withQueryString()`
- **Route Model Binding**: Bei `Route::resource()` IMMER `.parameters(['plural' => 'singular'])` — sonst 403
- **Neue Klient-Unterbeziehung**: Migration + Model + 2 Controller-Methoden + 2 Routen + Blade-Sektion
- **Historisierung Tarife**: kein `updateOrCreate` — immer `create()` → neuer Eintrag, alter bleibt

---

## Behobene Fehler (Lernprotokoll)

### 403 auf /klienten/{id}
Route::resource ohne `.parameters()` → `{klienten}` statt `{klient}` → null → abort(403).
Fix: `.parameters(['klienten' => 'klient'])`.

### Rate Limiter nach 4 Versuchen
Laravel-Standard-Throttle war aktiv → zu aggressiv. Fix: Throttle-Middleware entfernt, stattdessen eigene Logik mit `RateLimiter`-Facade (max 5/15min) in AuthController.

### 2 Organisationen in DB
Beim Setup versehentlich zweite Org erstellt. Fix: Org 2 gelöscht. Regel: max. 1 Org.

### ngrok
CSRF 419, Session-Probleme, APP_URL-Konflikte. Nie verwenden.
