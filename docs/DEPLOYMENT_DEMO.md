# Deployment — Demo & Tenants

**Stand: 2026-03-15**

---

## Code deployen (täglich)

```bash
git add .
git commit -m "..."
git push
```

→ GitHub Actions deployt automatisch auf alle Instanzen (ca. 30 Sekunden).

### Was GitHub Actions macht (`.github/workflows/deploy.yml`)

| Schritt | Was |
|---------|-----|
| 1 | `npm ci && npm run build` — Vite Assets bauen |
| 2 | SCP — Assets nach `public/build/` |
| 3 | SSH — `git reset --hard origin/master` |
| 4 | SSH — `composer install --no-dev` |
| 5 | SSH — `php artisan migrate --force` |
| 6 | SSH — `php artisan tenant:migrate --force` |
| 7 | SSH — `php artisan optimize:clear` |

---

## DB / Testdaten deployen

```bash
# 1. Seeder lokal ausführen + prüfen
php artisan db:seed --class=CurasoftDemoSeeder

# 2. Auf Demo syncen (NIEMALS auf Produktiv!)
./db_sync.sh
```

⛔ `db_sync.sh` **niemals auf Produktiv-DBs** anwenden — nur auf Demo (`devitjob_curasoft`).

---

## Bestehende Instanzen (Server)

| Domain | DB | Typ |
|--------|-----|-----|
| `curasoft.ch` | `devitjob_curasoft` | Demo — CurasoftDemoSeeder |
| `curapflege.curasoft.ch` | `devitjob_curapflege` | Produktiv ⛔ Keine Testdaten |

---

## Neuen Tenant einrichten

```bash
# 1. cPanel: Subdomain X.curasoft.ch anlegen
#    Document Root: /home/devitjob/public_html/spitex/public
#    cPanel erstellt falschen Pfad → Symlink im Terminal:
rm -rf ~/X.curasoft.ch && ln -s ~/public_html/spitex/public ~/X.curasoft.ch

# 2. cPanel: PostgreSQL-DB devitjob_X anlegen
#    User devitjob_csapp berechtigen

# 3. Tenant anlegen
php artisan tenant:create X "Name GmbH" admin@x.ch --skip-create-db --db=devitjob_X
```

→ `tenant:create` führt automatisch alle Seeders aus (Leistungsarten, Einsatzarten, Krankenkassen, Qualifikationen) und legt Admin-Benutzer an. Passwort wird im Terminal angezeigt.

---

## Automatische DB-Backups

Täglich 03:00 Uhr via Cron — alle aktiven Tenants werden gesichert.

### Was gesichert wird
- Alle Tenants aus der `tenants`-Tabelle (`aktiv = true`)
- Struktur + Daten (`pg_dump` Standard)
- Aufbewahrung: 30 Tage, danach automatisch gelöscht

### Speicherort (Server)
```
/home/devitjob/public_html/spitex/storage/app/backups/
```
Dateiname: `YYYY-MM-DD_HHMMSS_subdomain.sql`

### Manuell ausführen
```bash
php /home/devitjob/public_html/spitex/artisan db:backup
```

### Neuen Tenant hinzufügen
Kein Anpassen nötig — neuer Eintrag in `tenants`-Tabelle wird automatisch mitgesichert.

### Cron (cPanel)
```
0 3 * * * /usr/local/bin/php /home/devitjob/public_html/spitex/artisan db:backup >> /home/devitjob/logs/db_backup.log 2>&1
```

### Lokal testen
```bash
php artisan db:backup
```
Backups landen in `storage/app/backups/`.

---

## Lokale Entwicklung

```bash
# Voraussetzungen: Laragon, PHP 8.3+, PostgreSQL, Composer, Node

cd C:\laragon\www
git clone https://github.com/mhnilicka1962-boop/curasoft spitex
cd spitex

composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
# .env: DB_DATABASE=spitex, DB_HOST=localhost, DB_PORT=5432

# DB anlegen in pgAdmin: CREATE DATABASE spitex;
php artisan migrate
php artisan db:seed --class=LeistungsartenSeeder
php artisan db:seed --class=EinsatzartenSeeder
php artisan db:seed --class=KrankenkassenSeeder
php artisan db:seed --class=TestdatenSeeder
# oder für Demo-Daten:
php artisan db:seed --class=CurasoftDemoSeeder

php artisan storage:link
```

Login: `http://spitex.test` → `mhn@itjob.ch` / `Admin2026!`
