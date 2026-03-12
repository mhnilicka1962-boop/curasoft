#!/bin/bash
# DB-Sync: Lokale Testdaten auf Demo-Server syncen
# Verwendung: ./db_sync.sh
# NUR für Testdaten-Änderungen — NIEMALS auf Produktiv-Instanzen!

set -e

FTP_USER="vscode@devitjob.ch:VsCode2026!Ftp"
FTP_BASE="ftp://ftp.devitjob.ch/public_html/spitex"
DEMO_URL="https://www.curasoft.ch"

echo "▶ DB Sync starten (Demo: $DEMO_URL)..."
php deploy/db_sync.php

curl -s -T "deploy/db_import.php" "$FTP_BASE/public/db_import.php" --user "$FTP_USER"
DB_RESULT=$(curl -s "$DEMO_URL/db_import.php")
echo "$DB_RESULT" | grep -E "(FERTIG|Fehler|ERROR|Rows)" || echo "$DB_RESULT" | tail -3
echo "" | curl -s -T - "$FTP_BASE/public/db_import.php" --user "$FTP_USER"
rm -f deploy/db_import.php

echo "✅ DB Sync fertig"
