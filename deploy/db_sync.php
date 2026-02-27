<?php
/**
 * DB-Sync: Lokal ausführen → generiert db_import.php → deploy.sh lädt es hoch + ruft es auf
 * Aufruf: php deploy/db_sync.php
 */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pdo = DB::connection()->getPdo();

// INSERT-Reihenfolge (Eltern vor Kindern)
$tables = [
    'benutzer', 'regionen', 'krankenkassen', 'aerzte',
    'leistungsarten', 'leistungstypen', 'leistungsregionen',
    'qualifikationen', 'benutzer_qualifikation', 'benutzer_leistungsarten',
    'klienten', 'touren', 'tagespauschalen',
    'klient_adressen', 'klient_aerzte', 'klient_krankenkassen', 'klient_verordnungen',
    'klient_kontakte', 'klient_pflegestufen', 'klient_diagnosen',
    'klient_beitraege', 'klient_benutzer',
    'einsaetze', 'einsatz_aktivitaeten', 'rapporte',
    'nachrichten', 'nachricht_empfaenger', 'dokumente',
    'rechnungslaeufe', 'rechnungen', 'rechnungs_positionen',
];

function getBoolColumns(PDO $pdo, string $table): array {
    $stmt = $pdo->query("SELECT column_name FROM information_schema.columns
        WHERE table_schema='public' AND table_name='$table' AND data_type='boolean'");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function escVal($val, bool $isBool = false): string {
    if ($val === null) return 'NULL';
    if ($isBool) return ($val === true || $val === 't' || $val === '1' || $val === 1) ? 'TRUE' : 'FALSE';
    if (is_int($val) || is_float($val)) return (string)$val;
    return "'" . str_replace("'", "''", (string)$val) . "'";
}

echo "Exportiere...\n";
$allData = [];
foreach ($tables as $table) {
    try {
        $rows = $pdo->query("SELECT * FROM $table ORDER BY 1")->fetchAll(PDO::FETCH_ASSOC);
        $allData[$table] = ['rows' => $rows, 'boolCols' => getBoolColumns($pdo, $table)];
        echo "  $table: " . count($rows) . "\n";
    } catch (Exception $e) {
        $allData[$table] = ['rows' => [], 'boolCols' => []];
    }
}

// Organisation separat
$org = $pdo->query("SELECT * FROM organisationen WHERE id=1")->fetch(PDO::FETCH_ASSOC);
$orgBoolCols = getBoolColumns($pdo, 'organisationen');

// Import-Script generieren
$out = '<?php' . "\n";
$out .= '// DB-Import — EINMAL AUFRUFEN DANN WIRD ES AUTOMATISCH GELEERT' . "\n";
$out .= '// Generiert: ' . date('Y-m-d H:i:s') . "\n";
$out .= 'set_time_limit(300); ini_set("memory_limit","512M");' . "\n";
$out .= 'require dirname(__DIR__)."/vendor/autoload.php";' . "\n";
$out .= '$app=require dirname(__DIR__)."/bootstrap/app.php";' . "\n";
$out .= '$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();' . "\n";
$out .= 'echo "<pre>\n"; $errors=0;' . "\n\n";

// Organisation updaten
$orgSets = [];
foreach ($org as $col => $val) {
    if ($col === 'id' || $col === 'created_at') continue;
    $isBool = in_array($col, $orgBoolCols);
    $orgSets[] = '"' . $col . '"=' . escVal($val, $isBool);
}
$orgSql = 'UPDATE "organisationen" SET ' . implode(',', $orgSets) . ' WHERE id=1';
$orgEsc = str_replace(['\\', "'"], ['\\\\', "\\'"], $orgSql);
$out .= 'echo "=== Organisation ===\n";' . "\n";
$out .= 'DB::statement(\'' . $orgEsc . '\');' . "\n";
$out .= 'echo "OK\n\n";' . "\n\n";

// DELETE (reverse order)
$out .= 'echo "=== Lösche alte Daten ===\n";' . "\n";
foreach (array_reverse($tables) as $t) {
    $out .= 'try{DB::statement("DELETE FROM \"' . $t . '\"");echo "' . $t . ' OK\n";}catch(Exception $e){echo "' . $t . ': ".$e->getMessage()."\n";$errors++;}' . "\n";
}

// Reset sequences
$out .= "\n" . 'foreach([' . implode(',', array_map(fn($t) => "'$t'", $tables)) . '] as $t){' . "\n";
$out .= '  try{DB::statement("SELECT setval(pg_get_serial_sequence(\'$t\',\'id\'),1,false)");}catch(Exception $e){}}' . "\n\n";

// INSERT
$out .= 'echo "\n=== Importiere Daten ===\n";' . "\n";
foreach ($tables as $table) {
    $rows = $allData[$table]['rows'];
    $boolCols = $allData[$table]['boolCols'];
    if (empty($rows)) { $out .= 'echo "' . $table . ': leer\n";' . "\n"; continue; }
    $cols = array_keys($rows[0]);
    $colList = implode(',', array_map(fn($c) => '"'.$c.'"', $cols));
    $out .= '$c=0;' . "\n";
    foreach (array_chunk($rows, 50) as $chunk) {
        $vals = [];
        foreach ($chunk as $row) {
            $v = [];
            foreach ($cols as $col) $v[] = escVal($row[$col], in_array($col, $boolCols));
            $vals[] = '(' . implode(',', $v) . ')';
        }
        $sql = 'INSERT INTO "' . $table . '" (' . $colList . ') VALUES ' . implode(',', $vals);
        $esc = str_replace(['\\', "'"], ['\\\\', "\\'"], $sql);
        $out .= 'try{DB::statement(\'' . $esc . '\');$c+=' . count($chunk) . ';}catch(Exception $e){echo "FEHLER ' . $table . ': ".$e->getMessage()."\n";$errors++;}' . "\n";
    }
    $out .= 'try{DB::statement("SELECT setval(pg_get_serial_sequence(\'' . $table . '\',\'id\'),(SELECT COALESCE(MAX(id),0) FROM \"' . $table . '\")+1,false)");}catch(Exception $e){}' . "\n";
    $out .= 'echo "' . $table . ': $c Zeilen\n";' . "\n";
}

$out .= "\n" . 'echo "\n=== FERTIG — Fehler: $errors ".date("Y-m-d H:i:s")." ===\n";' . "\n";
$out .= 'echo "</pre>\n";' . "\n";

$outFile = __DIR__ . '/db_import.php';
file_put_contents($outFile, $out);
echo "\ndeploy/db_import.php erstellt (" . round(filesize($outFile)/1024) . " KB)\n";
echo "Jetzt: php deploy.sh db  (oder manuell: deploy.sh db ausführen)\n";
