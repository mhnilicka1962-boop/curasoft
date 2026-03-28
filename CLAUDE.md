# CLAUDE.md — CURASOFT / SPITEX (Laravel 12, PostgreSQL)
# Lokales Verzeichnis: C:\laragon\www\spitex
# DIESE DATEI BLEIBT KOMPAKT — max. ~200 Zeilen. Kein Session-Log, kein Changelog.
# Neues kommt in docs/ oder Memory — NIEMALS hier anfügen.

## DEPLOY: git push → GitHub Actions deployt automatisch (~30 Sek.)
## KEIN FTP. NIEMALS. Nicht für PHP, nicht für Assets, nicht für irgendetwas.

---

## ZWINGEND: Vor jeder Code-Änderung

1. Kurz beschreiben was geplant ist (1-3 Sätze)
2. Warten bis Mathias ja sagt
3. Erst dann bauen

NIEMALS eigenständig verbessern, aufräumen oder optimieren.
NIEMALS direkt pushen ohne dass Mathias die Änderung gesehen hat.

---

## TESTDATEN — Absolut verbindlich

| Umgebung | DB | Testdaten? |
|---|---|---|
| Lokal (`spitex.test`) | `spitex` | JA |
| Demo (`www.curasoft.ch`) | `devitjob_curasoft` | JA — nur CurasoftDemoSeeder |
| Produktiv (`curapflege.curasoft.ch`) | `devitjob_curapflege` | NIEMALS |

Auf Produktiv-DBs: kein Seeder, kein db_sync.sh, keine Testdaten.

---

## Deploy-Workflow

### Code deployen:
```
git add . && git commit -m "..." && git push
```
GitHub Actions: npm build → SCP Assets → git reset --hard → composer → migrate → tenant:migrate → optimize:clear

### DB / Testdaten deployen:
```
php artisan db:seed --class=XyzSeeder   # lokal ausführen + prüfen
./db_sync.sh                            # dann auf Demo syncen — NIEMALS auf Produktiv
```

---

## Login-Daten

### Lokal (http://spitex.test)
- Admin: `mhn@itjob.ch` / `Admin2026!`
- Pflege: `1234@itjob.ch` / `Sandra2026!`
- CuraPflege lokal: `http://curapflege.spitex.test/login`

### Server
| Instanz | URL | Email | Passwort |
|---|---|---|---|
| Demo | `https://curasoft.ch/login` | `mhn@itjob.ch` | `Admin2026!` |
| CuraPflege (prod) | `https://curapflege.curasoft.ch/login` | `mhn@itjob.ch` | `Admin2026!` |

---

## Stack & Konventionen

- Laravel 12, PHP 8.3, PostgreSQL, Blade, DomPDF, Vite
- Auth-Model: `App\Models\Benutzer` (NICHT User), Tabelle `benutzer`
- Rollen: `admin` | `pflege` | `buchhaltung`
- Multi-Tenant: TenantMiddleware liest Subdomain → wählt DB-Connection
- Master-DB: `devitjob_curasoft` enthält `tenants`-Tabelle
- Telescope: http://spitex.test/telescope (nur lokal, nur admin)

### Route Model Binding — IMMER
`Route::resource('/klienten', ...)` braucht `.parameters(['klienten' => 'klient'])`.
Ohne das: null-Objekt → 403. Gilt für alle deutschen Pluralformen.

### Historisierung Tarife
Kein `updateOrCreate` — immer `create()` → neuer Eintrag, alter bleibt als Historie.

### CSS
Einzige Datei: `resources/css/app.css`. Nach Änderungen: `npm run build`.
Kein `style=""` für wiederholte Muster — immer CSS-Klassen.
Klassen: `.karte`, `.btn`, `.btn-primaer`, `.btn-sekundaer`, `.btn-gefahr`, `.badge`, `.badge-*`, `.tabelle`, `.form-grid`, `.seiten-kopf`, `.feld`, `.detail-raster`

---

## Aktuelle DB (lokal + Demo identisch, Stand 2026-03-28)

| Tabelle | Anzahl |
|---|---|
| klienten | 5 |
| einsaetze | 965 |
| einsatz_leistungsarten | 965 |
| touren | 338 |
| rechnungslaeufe | 4 |
| rechnungen | 16 |
| benutzer | 6 |

Seeder: `php artisan db:seed --class=CurasoftDemoSeeder` — einziger Demo/Test-Seeder.

---

## Module / URLs

| Modul | URL | Rollen |
|---|---|---|
| Dashboard | `/dashboard` | alle |
| Klienten | `/klienten` | admin, pflege |
| Einsätze | `/einsaetze` | admin, pflege |
| Vor-Ort | `/einsaetze/{id}/vor-ort` | admin, pflege |
| Rapportierung | `/klienten/{id}/rapportierung/{jahr}/{monat}` | admin |
| Kalender | `/kalender` | admin |
| Touren | `/touren` | admin, pflege |
| Rechnungen | `/rechnungen` | admin, buchhaltung |
| Rechnungsläufe | `/rechnungen/lauf` | admin, buchhaltung |
| Tagespauschalen | `/tagespauschalen` | admin, buchhaltung |
| Chat | `/chat` | alle |
| Firma | `/firma` | admin |
| Leistungsarten | `/leistungsarten` | admin |
| Regionen | `/regionen` | admin |

---

## Multi-Tenant — Neuen Kunden einrichten (Server)

```bash
# cPanel: Subdomain + DB anlegen, dann:
rm -rf ~/X.curasoft.ch && ln -s ~/public_html/spitex/public ~/X.curasoft.ch
php artisan tenant:create X "Name GmbH" admin@x.ch --skip-create-db --db=devitjob_X
```

---

## Session-Start — Apache/PostgreSQL prüfen

```bash
tasklist | grep -i httpd
tasklist | grep -i postgres
# Falls Apache fehlt:
start "" "C:/laragon/bin/apache/httpd-2.4.66-260107-Win64-Win64-VS18/bin/httpd.exe"
```

---

## Wichtige Dateien

| Datei | Zweck |
|---|---|
| `app/Http/Middleware/TenantMiddleware.php` | Subdomain → DB-Switch |
| `app/Services/PdfExportService.php` | PDF + QR-Rechnung + Rapportblatt |
| `app/Services/XmlExportService.php` | XML 450.100 KK-Abrechnung |
| `app/Services/BexioService.php` | Bexio API |
| `app/Services/GeocodingService.php` | Nominatim + Route-Optimierung |
| `app/Http/Controllers/RechnungslaufController.php` | Batch-Abrechnung |
| `app/Http/Controllers/RapportierungController.php` | Monatsraster-Erfassung |
| `database/seeders/CurasoftDemoSeeder.php` | Einziger Demo-Seeder |
| `.github/workflows/deploy.yml` | Auto-Deploy |
| `docs/ABRECHNUNG_LOGIK.md` | Tarif-Berechnung, Tiers garant/payant |
| `docs/BETRIEBSANWEISUNG.md` | Betrieb + Rechnungsläufe |
