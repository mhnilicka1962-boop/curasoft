# CLAUDE.md — Spitex Projektkontext

## Stand: 2026-02-24 (Session 7)

---

## Login-Daten (lokal)

| | |
|---|---|
| **URL** | `http://spitex.test/login` |
| **Admin E-Mail** | `mhn@itjob.ch` |
| **Admin Passwort** | `Admin2026!` |
| **Rolle** | admin |
| **Pflege (Test)** | `1234@itjob.ch` / `test1234` (Sandra Huber) |
| **Organisation** | ID 1 (einzige — kein Multi-Tenant) |

## Login-Daten (Demo-Server)

| | |
|---|---|
| **URL** | `https://www.curasoft.ch/login` |
| **Admin E-Mail** | `mhn@itjob.ch` |
| **Admin Passwort** | `Admin2026!` |
| **Pflege E-Mail** | `1234@itjob.ch` (Sandra Huber) |
| **Pflege Passwort** | `test1234` |
| **Weitere Pflege** | `peter.keller@test.spitex` / `test1234` etc. |
| **Buchhaltung** | `lisa.bauer@test.spitex` / `test1234` |

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

### Seeders (bereits eingespielt)
- `LeistungsartenSeeder` — 5 Leistungsarten mit Default-Ansätzen
- `EinsatzartenSeeder` — 30 Einsatzarten, je einer Leistungsart zugeordnet
- `KrankenkassenSeeder` — 39 Schweizer KVG-Krankenkassen (BAG-Nr + EAN) — per Tinker eingespielt

### DB-Inhalt (Testdaten)
- Region AG (Aargau) mit 5 Leistungsregionen (Auto-Copy beim Anlegen)

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
- **Security Paket B**: Audit-Log (wer hat was wann geändert) — noch nicht gebaut.
- **Security Paket C**: 2FA (TOTP) als zweiter Faktor — noch nicht gebaut. Passkey (WebAuthn) vorhanden als Alternative.
- **Vor-Ort-Ansicht**: Check-in/out funktioniert nur wenn `checkin_token` auf Einsatz gesetzt ist.
- **Tätigkeiten-Checkliste**: Beim Check-out auswählen was gemacht wurde (Ankleiden, Waschen…) — noch nicht gebaut.
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
