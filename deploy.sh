#!/bin/bash
# =============================================================================
# SPITEX DEPLOY — Ein Befehl für alles
# =============================================================================
# Verwendung:
#   ./deploy.sh          → Code + Assets deployen
#   ./deploy.sh db       → Zusätzlich DB-Daten syncen (Testdaten + Organisation)
#   ./deploy.sh code     → Nur Code + Assets (kein DB-Sync)
# =============================================================================

set -e

FTP_USER="vscode@devitjob.ch:VsCode2026!Ftp"
FTP_BASE="ftp://ftp.devitjob.ch/public_html/spitex"
DEMO_URL="https://www.curasoft.ch"
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

DB_SYNC=${1:-"code"}

echo ""
echo "╔══════════════════════════════════╗"
echo "║  SPITEX DEPLOY → curasoft.ch     ║"
echo "╚══════════════════════════════════╝"
echo ""

# --- 1. Vite Assets bauen ---
echo "▶ 1/5  Vite Assets bauen..."
cd "$PROJECT_DIR"
npm run build --silent
CSS_FILE=$(ls public/build/assets/*.css 2>/dev/null | head -1)
JS_FILE=$(ls public/build/assets/*.js 2>/dev/null | head -1)
echo "    CSS: $(basename $CSS_FILE)"
echo "    JS:  $(basename $JS_FILE)"

# --- 2. Git Push ---
echo ""
echo "▶ 2/5  Git Push..."
git push
echo "    Gepusht: $(git log --oneline -1)"

# --- 3. Vite Assets via FTP ---
echo ""
echo "▶ 3/5  Vite Assets hochladen..."
curl -s -T "public/build/manifest.json" "$FTP_BASE/public/build/manifest.json" --user "$FTP_USER"
curl -s -T "$CSS_FILE" "$FTP_BASE/public/build/assets/$(basename $CSS_FILE)" --user "$FTP_USER" --ftp-create-dirs
curl -s -T "$JS_FILE" "$FTP_BASE/public/build/assets/$(basename $JS_FILE)" --user "$FTP_USER"
echo "    Hochgeladen ✓"

# --- 4. Server Deploy (git reset + composer + migrate + cache) ---
echo ""
echo "▶ 4/5  Server deployen (git + composer + migrate + cache)..."
DEPLOY_NAME="srv_$(date +%s).php"
curl -s -T "deploy/server.php" "$FTP_BASE/public/$DEPLOY_NAME" --user "$FTP_USER"
RESULT=$(curl -s "$DEMO_URL/$DEPLOY_NAME")
echo "$RESULT" | grep -E "(HEAD|migrate|FERTIG|ERROR|Fehler)" || echo "$RESULT" | head -5
# Script leeren
echo "" | curl -s -T - "$FTP_BASE/public/$DEPLOY_NAME" --user "$FTP_USER"
echo "    Server Deploy ✓"

# --- 5. DB Sync (optional) ---
if [ "$DB_SYNC" = "db" ]; then
    echo ""
    echo "▶ 5/5  DB Sync (Testdaten + Organisation)..."
    php deploy/db_sync.php
    curl -s -T "deploy/db_import.php" "$FTP_BASE/public/db_import.php" --user "$FTP_USER"
    DB_RESULT=$(curl -s "$DEMO_URL/db_import.php")
    echo "$DB_RESULT" | grep -E "(FERTIG|Fehler|ERROR)" || echo "$DB_RESULT" | tail -3
    echo "" | curl -s -T - "$FTP_BASE/public/db_import.php" --user "$FTP_USER"
    rm -f deploy/db_import.php
    echo "    DB Sync ✓"
else
    echo ""
    echo "  5/5  DB Sync übersprungen (./deploy.sh db für vollständigen Sync)"
fi

echo ""
echo "╔══════════════════════════════════╗"
echo "║  ✅ DEPLOY FERTIG                ║"
echo "║  $(date '+%Y-%m-%d %H:%M:%S')           ║"
echo "╚══════════════════════════════════╝"
echo ""
echo "  Demo: $DEMO_URL"
echo ""
