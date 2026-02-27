<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * tenant:migrate — Migrationen auf allen aktiven Tenant-DBs ausführen
 *
 * Wird im Deploy-Script (deploy/server.php) aufgerufen statt `artisan migrate`.
 * Voraussetzung: Master-DB `curasoft_master` mit Tabelle `tenants` ist vorhanden.
 *
 * Noch nicht aktiv — wird aktiviert wenn Multi-Tenant live geht.
 * Dann in deploy/server.php ersetzen:
 *   php artisan migrate --force
 *   → php artisan tenant:migrate --force
 */
class TenantMigrate extends Command
{
    protected $signature = 'tenant:migrate {--force : Migrationen ohne Bestätigung ausführen}';
    protected $description = 'Migrationen auf allen aktiven Tenant-Datenbanken ausführen';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Migrationen auf ALLEN Tenant-DBs ausführen?')) {
            $this->info('Abgebrochen.');
            return self::SUCCESS;
        }

        // Master-DB verbinden und alle aktiven Tenants laden
        $tenants = DB::connection('master')->table('tenants')
            ->where('aktiv', true)
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('Keine aktiven Tenants gefunden.');
            return self::SUCCESS;
        }

        $this->info("Gefunden: {$tenants->count()} aktive Tenants");
        $this->newLine();

        $fehler = 0;

        foreach ($tenants as $tenant) {
            $this->line("── {$tenant->subdomain} ({$tenant->db_name})");

            // Dynamische DB-Connection für diesen Tenant setzen
            Config::set("database.connections.tenant", [
                'driver'   => 'pgsql',
                'host'     => env('DB_HOST', 'localhost'),
                'port'     => env('DB_PORT', '5432'),
                'database' => $tenant->db_name,
                'username' => $tenant->db_user,
                'password' => $tenant->db_password,
                'charset'  => 'utf8',
                'schema'   => 'public',
            ]);

            DB::purge('tenant');

            try {
                $this->call('migrate', [
                    '--database' => 'tenant',
                    '--force'    => true,
                    '--path'     => 'database/migrations',
                ]);
                $this->line("   ✓ {$tenant->subdomain} OK");
            } catch (\Exception $e) {
                $this->error("   ✗ {$tenant->subdomain}: " . $e->getMessage());
                $fehler++;
            }

            $this->newLine();
        }

        if ($fehler > 0) {
            $this->error("Fertig mit $fehler Fehler(n).");
            return self::FAILURE;
        }

        $this->info('Alle Tenants migriert.');
        return self::SUCCESS;
    }
}
