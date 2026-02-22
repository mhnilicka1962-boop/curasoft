# CLAUDE.md — CuraSoft Projektkontext

## Stand: 2026-02-22 (Session 3)

---

## Login-Daten (lokal)

| | |
|---|---|
| **URL** | `http://curasoft.test/login` |
| **E-Mail** | `admin@curasoft.ch` |
| **Passwort** | `Admin2026!` |
| **Rolle** | admin |
| **Organisation** | ID 1 (einzige — kein Multi-Tenant) |

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

### Seeders (bereits eingespielt)
- `LeistungsartenSeeder` — 5 Leistungsarten mit Default-Ansätzen
- `EinsatzartenSeeder` — 30 Einsatzarten, je einer Leistungsart zugeordnet

### DB-Inhalt (Testdaten)
- Region AG (Aargau) mit 5 Leistungsregionen (Auto-Copy beim Anlegen)

---

## Module und URLs

| Modul | URL | Controller | Rollen |
|-------|-----|------------|--------|
| Dashboard | `/dashboard` | Route-Closure | alle |
| Klienten | `/klienten` | KlientenController | admin, pflege |
| Einsätze | `/einsaetze` | EinsaetzeController | admin, pflege |
| Check-In/Out | `/checkin/{token}` | CheckInController | admin, pflege |
| Rapporte | `/rapporte` | RapporteController | admin, pflege |
| Tourenplanung | `/touren` | TourenController | admin, pflege |
| Rechnungen | `/rechnungen` | RechnungenController | admin, buchhaltung |
| XML-Export | `/rechnungen/{id}/xml` | RechnungenController | admin, buchhaltung |
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
6. **Krankenkassen** — KVG / VVG, Deckungstyp, Versichertennummer
7. **Beiträge** — Ansatz Kunde, Limit %, Ansatz SPITEX, Kanton, historisiert nach gültig_ab
8. **Kontakte & Angehörige** — Rolle, Bevollmächtigt, Erhält Rechnungen
9. **Pflegebedarf / Einstufungen** — BESA / RAI-HC / IBM / Manuell, Stufe, Punkte, Nächste Prüfung
10. **Diagnosen (ICD-10)** — Code, Bezeichnung, Typ (Haupt/Neben/Einweisung)
11. **Dokumente** — Upload (PDF/DOCX/XLSX/Bilder, max 20 MB), Download
12. **Rapporte** — letzte 5, Link zu neuen Rapport
13. **Letzte Einsätze** — letzte 5

---

## Services

| Datei | Klasse | Zweck |
|-------|--------|-------|
| `app/Services/BexioService.php` | `App\Services\BexioService` | Bexio API: Verbindungstest, Kontakt-Sync, Rechnungs-Sync |
| `app/Services/XmlExportService.php` | `App\Services\XmlExportService` | XML 450.100 für KK-Abrechnung (DOMDocument) |

---

## Bekannte offene Punkte

- **Bexio**: Kontakt-Sync und Rechnungs-Sync im Service vorhanden, aber kein UI-Button. Nächster Schritt: Button auf Klient-Detail und Rechnungs-Detail.
- **XML-Export**: `tarmed_code`-Feld fehlt auf leistungsarten. Default `00.0010` verwenden oder Feld ergänzen.
- **Tourenplanung**: Reihenfolge per Nummer setzbar, kein Drag-and-Drop.
- **Dokumente**: Speicher unter `storage/app/dokumente/{org_id}/` — kein public Zugriff, nur Download.
- **Klienten-Index**: Default zeigt nur aktive Klienten (Filter "Aktiv" vorausgewählt).

---

## Projektstruktur

```
app/
  Http/Controllers/
    AerzteController.php
    AuthController.php           ← kein Rate Limiter
    CheckInController.php
    DokumenteController.php
    EinsatzartenController.php   ← NEU: /einsatzarten CRUD
    EinsaetzeController.php
    FirmaController.php
    KlientenController.php       ← +11 Unterbeziehungs-Methoden (inkl. Beiträge)
    KrankenkassenController.php
    LeistungsartenController.php ← +tarifeBearbeiten/tarifeAktualisieren
    NachrichtenController.php
    RapporteController.php
    RechnungenController.php     ← +xmlExport()
    RegionenController.php       ← +show() +tarifSpeichern() +Auto-Copy
    TourenController.php
  Models/
    Arzt.php, KlientArzt.php
    BexioSync.php
    Dokument.php
    Einsatz.php
    Klient.php
    KlientAdresse.php
    KlientBeitrag.php
    KlientDiagnose.php
    KlientKontakt.php
    KlientKrankenkasse.php
    KlientPflegestufe.php
    Krankenkasse.php
    Leistungsart.php             ← +gueltig_ab/bis, +Default-Ansätze
    Leistungsregion.php          ← +verrechnung/einsatz_*/mwst
    Leistungstyp.php             ← Einsatzarten
    Organisation.php
    Rapport.php
    Region.php
    Tour.php
  Services/
    BexioService.php
    XmlExportService.php

resources/views/
  dashboard.blade.php
  klienten/
    index.blade.php              ← Default: nur aktive Klienten
    show.blade.php               ← 13 Sektionen
    _formular.blade.php
  rapporte/
    index.blade.php, create.blade.php, show.blade.php
  touren/
    index.blade.php, create.blade.php, show.blade.php
  stammdaten/
    leistungsarten/
      index.blade.php            ← Grundset + Formular
      edit.blade.php             ← inkl. Default-Ansätze
      show.blade.php             ← Tarife pro Kanton, historisiert
      tarif_edit.blade.php       ← Einzel-Tarif bearbeiten
    einsatzarten/
      index.blade.php            ← Liste + Filter + Formular
      edit.blade.php
    regionen/
      index.blade.php            ← ✏ Leistungsarten Button pro Kanton
      show.blade.php             ← 5 Leistungsarten + Historisierung + Neuerfassung
    aerzte/    (index, create, edit, _formular)
    krankenkassen/ (index, create, edit, _formular)
    firma/     (index + Bexio-Sektion)
```

---

## Laptop-Setup (neues Gerät)

```bash
# 1. Laragon installieren (https://laragon.org)
#    → PHP 8.3, PostgreSQL, Apache aktivieren

# 2. Projekt klonen
cd C:\laragon\www
git clone <repo-url> curasoft

# 3. Dependencies
cd curasoft
composer install
npm install && npm run build

# 4. .env anlegen
cp .env.example .env
php artisan key:generate

# .env anpassen:
# APP_URL=http://curasoft.test
# DB_CONNECTION=pgsql
# DB_HOST=localhost
# DB_PORT=5432
# DB_DATABASE=curasoft
# DB_USERNAME=postgres
# DB_PASSWORD=

# 5. Datenbank anlegen (pgAdmin oder psql)
# CREATE DATABASE curasoft;

# 6. Migrationen + Seeders
php artisan migrate
php artisan db:seed --class=LeistungsartenSeeder
php artisan db:seed --class=EinsatzartenSeeder

# 7. Storage verlinken
php artisan storage:link

# 8. Laragon: Virtual Host curasoft.test → C:\laragon\www\curasoft\public

# 9. Ersten Admin-User anlegen via Setup-Wizard
# http://curasoft.test/setup
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
- App läuft auf `http://curasoft.test` (Laragon)
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
- **Rate Limiter**: entfernt aus AuthController
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
Laravel-Standard-Throttle war aktiv. Fix: komplett entfernt.

### 2 Organisationen in DB
Beim Setup versehentlich zweite Org erstellt. Fix: Org 2 gelöscht. Regel: max. 1 Org.

### ngrok
CSRF 419, Session-Probleme, APP_URL-Konflikte. Nie verwenden.
