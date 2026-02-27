<?php
/**
 * Server-seitiges Deploy-Script
 * Wird von deploy.sh temporär nach public/ kopiert, aufgerufen, dann geleert.
 * NICHT direkt in public/ committen.
 */
$base = dirname(__DIR__);
echo "<pre>\n";

echo "=== git reset --hard origin/master ===\n";
echo htmlspecialchars(shell_exec("cd $base && git fetch origin 2>&1 && git reset --hard origin/master 2>&1")) . "\n";

echo "=== composer install --no-dev ===\n";
echo htmlspecialchars(shell_exec("cd $base && HOME=/tmp composer install --no-dev --optimize-autoloader 2>&1")) . "\n";

echo "=== php artisan migrate --force ===\n";
echo htmlspecialchars(shell_exec("cd $base && php artisan migrate --force 2>&1")) . "\n";

echo "=== php artisan optimize:clear ===\n";
echo htmlspecialchars(shell_exec("cd $base && php artisan optimize:clear 2>&1")) . "\n";

echo "=== FERTIG " . date('Y-m-d H:i:s') . " ===\n";
echo "</pre>\n";
