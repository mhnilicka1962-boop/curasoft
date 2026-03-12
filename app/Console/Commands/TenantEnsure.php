<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * tenant:ensure — Stellt sicher dass alle bekannten Tenants in der tenants-Tabelle eingetragen sind.
 * Wird bei jedem Deploy via GitHub Actions ausgeführt.
 */
class TenantEnsure extends Command
{
    protected $signature   = 'tenant:ensure';
    protected $description = 'Tenant-Einträge in Master-DB sicherstellen (upsert)';

    // Alle bekannten Tenants — hier ergänzen wenn neuer Tenant angelegt wird
    private array $tenants = [
        [
            'subdomain' => 'curapflege',
            'db_name'   => 'devitjob_curapflege',
        ],
    ];

    public function handle(): int
    {
        $dbUser = env('DB_USERNAME', 'postgres');
        $dbPass = env('DB_PASSWORD', '');

        try {
            $master = DB::connection('master');
        } catch (\Exception $e) {
            $this->warn('tenant:ensure: Master-DB nicht erreichbar — ' . $e->getMessage());
            return self::SUCCESS;
        }

        foreach ($this->tenants as $t) {
            $master->table('tenants')->updateOrInsert(
                ['subdomain' => $t['subdomain']],
                [
                    'db_name'     => $t['db_name'],
                    'db_user'     => $dbUser,
                    'db_password' => $dbPass,
                    'aktiv'       => true,
                    'erstellt_am' => now(),
                ]
            );
            $this->line("✓ Tenant '{$t['subdomain']}' → {$t['db_name']}");
        }

        return self::SUCCESS;
    }
}
