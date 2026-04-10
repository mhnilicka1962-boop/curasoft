<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DbBackup extends Command
{
    protected $signature = 'db:backup {--days=30 : Backups älter als X Tage löschen}';
    protected $description = 'PostgreSQL Backup aller aktiven Tenant-DBs';

    public function handle(): int
    {
        $backupDir = env('BACKUP_PATH', storage_path('app/backups'));

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Tenants aus Master-DB lesen
        try {
            $tenants = DB::connection('master')->table('tenants')
                ->where('aktiv', true)
                ->get(['subdomain', 'db_name', 'db_user', 'db_password']);
        } catch (\Exception $e) {
            $this->error('Master-DB nicht erreichbar: ' . $e->getMessage());
            return self::FAILURE;
        }

        if ($tenants->isEmpty()) {
            $this->warn('Keine aktiven Tenants gefunden.');
            return self::SUCCESS;
        }

        $this->info("Backup von {$tenants->count()} Tenant(s) nach: {$backupDir}");
        $this->newLine();

        $datum  = date('Y-m-d_His');
        $host   = env('DB_HOST', '127.0.0.1');
        $fehler = 0;

        foreach ($tenants as $tenant) {
            $datei = "{$backupDir}/{$datum}_{$tenant->subdomain}.sql";
            $this->line("── {$tenant->subdomain} ({$tenant->db_name})");

            putenv('PGPASSWORD=' . $tenant->db_password);

            $pgDump = PHP_OS_FAMILY === 'Windows'
                ? '"C:/laragon/bin/postgresql/postgresql/bin/pg_dump.exe"'
                : 'pg_dump';

            $cmd = sprintf(
                '%s -U %s -h %s %s > %s 2>&1',
                $pgDump,
                escapeshellarg($tenant->db_user),
                escapeshellarg($host),
                escapeshellarg($tenant->db_name),
                escapeshellarg($datei)
            );

            exec($cmd, $output, $exitCode);

            if ($exitCode === 0 && file_exists($datei) && filesize($datei) > 0) {
                $kb = round(filesize($datei) / 1024, 1);
                $this->line("   ✓ {$kb} KB → {$datei}");
            } else {
                $this->error("   ✗ Fehler bei {$tenant->subdomain}");
                if (! empty($output)) {
                    $this->line('   ' . implode("\n   ", $output));
                }
                $fehler++;
            }

            $output = [];
        }

        // Alte Backups aufräumen
        $days = (int) $this->option('days');
        $this->newLine();
        $this->line("Aufräumen: Backups älter als {$days} Tage löschen...");

        $geloescht = 0;
        foreach (glob("{$backupDir}/*.sql") as $datei) {
            if (filemtime($datei) < strtotime("-{$days} days")) {
                unlink($datei);
                $geloescht++;
            }
        }

        $this->line("   {$geloescht} Datei(en) gelöscht.");
        $this->newLine();

        if ($fehler > 0) {
            $this->error("Fertig mit {$fehler} Fehler(n).");
            return self::FAILURE;
        }

        $this->info('Backup abgeschlossen.');
        return self::SUCCESS;
    }
}
