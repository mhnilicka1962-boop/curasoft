# Deployment-Anleitung — Demo-Umgebung

**Zweck:** Erstmalige Migration der Curasoft-App auf den Demo-Server
**Wichtig:** Die produktive `spitex`-Datenbank wird zu keinem Zeitpunkt berührt.

---

## Schritt 1 — cPanel: Datenbank + User anlegen

1. cPanel öffnen → **MySQL Databases** (oder PostgreSQL Databases)
2. Neue Datenbank anlegen: `curasoft`
3. Neuen DB-User anlegen: `curasoft_user` + sicheres Passwort notieren
4. User der Datenbank zuweisen: `curasoft_user` → `curasoft` → **All Privileges**

> Dieser User hat **keinen Zugriff** auf die produktive `spitex`-DB.

---

## Schritt 2 — Code hochladen

### Option A: Git (empfohlen)
```bash
cd /pfad/zum/webroot
git clone <repo-url> curasoft
```

### Option B: FTP / Dateimanager
- Alle Projektdateien in den Zielordner hochladen
- `node_modules/` und `.env` **nicht** hochladen

---

## Schritt 3 — .env erstellen

Im Projektordner auf dem Server eine `.env`-Datei anlegen (Vorlage: `.env.example`):

```env
APP_NAME=Curasoft
APP_ENV=production
APP_KEY=                          # wird in Schritt 4 generiert
APP_DEBUG=false
APP_URL=https://demo.example.ch   # URL der Demo-Umgebung

APP_LOCALE=de
APP_FALLBACK_LOCALE=de

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=curasoft
DB_USERNAME=curasoft_user
DB_PASSWORD=<passwort aus Schritt 1>

SESSION_DRIVER=database
SESSION_LIFETIME=60
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=<smtp-host>
MAIL_PORT=587
MAIL_USERNAME=<absender@example.ch>
MAIL_PASSWORD=<mail-passwort>
MAIL_FROM_ADDRESS=demo@example.ch
MAIL_FROM_NAME="Curasoft Demo"
```

---

## Schritt 4 — Dependencies + App-Key

Über SSH im Projektordner:

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
```

> `key:generate` trägt den APP_KEY automatisch in die `.env` ein.

---

## Schritt 5 — Datenbank aufbauen

```bash
php artisan migrate
```

→ Alle Tabellen werden frisch angelegt. Kein SQL-Dump nötig.

---

## Schritt 6 — Grunddaten einspielen (Seeders)

```bash
php artisan db:seed --class=LeistungsartenSeeder
php artisan db:seed --class=EinsatzartenSeeder
php artisan db:seed --class=KrankenkassenSeeder
```

---

## Schritt 7 — Storage + Cache

```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Schritt 8 — Ersten Admin-User anlegen

Im Browser aufrufen:
```
https://demo.example.ch/setup
```

→ Organisationsname, Admin-E-Mail und Passwort eingeben.

---

## Schritt 9 — Smoke Test

| Was | URL | Erwartung |
|-----|-----|-----------|
| Login | `/login` | Formular erscheint |
| Dashboard | `/dashboard` | Lädt ohne Fehler |
| Klienten | `/klienten` | Leere Liste |
| Leistungsarten | `/leistungsarten` | 5 Einträge vorhanden |
| Einsatzarten | `/einsatzarten` | 30 Einträge vorhanden |
| Krankenkassen | `/krankenkassen` | 39 Einträge vorhanden |
| Region anlegen | `/regionen` | AG anlegen → 5 Leistungsregionen auto-erstellt |

---

## Rollback (falls nötig)

Wenn etwas schiefläuft:

```bash
php artisan migrate:rollback
```

Oder einfach die DB `curasoft` löschen und neu starten ab Schritt 5.
Die produktive `spitex`-DB ist zu keinem Zeitpunkt betroffen.

---

## Checkliste vor dem Start

- [ ] SSH-Zugang zum Server vorhanden
- [ ] cPanel-Zugang vorhanden
- [ ] DB-Name und Passwort notiert
- [ ] Demo-URL bekannt
- [ ] Mail-Zugangsdaten bereit
- [ ] PHP 8.3+ auf dem Server (`php -v`)
- [ ] Composer auf dem Server (`composer -V`)
- [ ] PostgreSQL auf dem Server verfügbar
