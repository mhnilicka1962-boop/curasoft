<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * tenant:seed — Basis-Seeders für einen bestehenden Tenant einspielen
 *
 * Nützlich wenn:
 * - tenant:create gelaufen ist aber Seeder fehlgeschlagen
 * - Neue Seeder nachträglich einspielen
 *
 * Beispiel:
 *   php artisan tenant:seed curapflege
 *   php artisan tenant:seed curapflege --db=devitjob_curapflege
 */
class TenantSeed extends Command
{
    protected $signature = 'tenant:seed
                            {subdomain : Subdomain z.B. curapflege}
                            {--db= : DB-Name überschreiben (Standard: curasoft_<subdomain>)}';

    protected $description = 'Basis-Seeders in Tenant-DB einspielen (Leistungsarten, Einsatzarten, Krankenkassen)';

    public function handle(): int
    {
        $subdomain = strtolower($this->argument('subdomain'));
        $dbName    = $this->option('db') ?? ('curasoft_' . preg_replace('/[^a-z0-9]/', '_', $subdomain));
        $dbUser    = env('DB_USERNAME', 'postgres');
        $dbPass    = env('DB_PASSWORD', '');

        $this->info("Seeders für Tenant: $subdomain (DB: $dbName)");

        // Tenant-Connection konfigurieren
        Config::set('database.connections.tenant_seed_tmp', [
            'driver'      => 'pgsql',
            'host'        => env('DB_HOST', 'localhost'),
            'port'        => env('DB_PORT', '5432'),
            'database'    => $dbName,
            'username'    => $dbUser,
            'password'    => $dbPass,
            'charset'     => 'utf8',
            'search_path' => 'public',
        ]);
        DB::purge('tenant_seed_tmp');

        // Default-Connection auf Tenant setzen — damit Seeders in richtige DB schreiben
        $prevConnection = DB::getDefaultConnection();
        DB::setDefaultConnection('tenant_seed_tmp');

        $this->line('Starte Seeders...');

        foreach (['LeistungsartenSeeder', 'EinsatzartenSeeder', 'KrankenkassenSeeder'] as $seeder) {
            try {
                $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
                $this->line("  ✓ $seeder");
            } catch (\Exception $e) {
                $this->warn("  ⚠ $seeder: " . $e->getMessage());
            }
        }

        DB::setDefaultConnection($prevConnection);

        $count = DB::connection('tenant_seed_tmp')->table('krankenkassen')->count();
        $this->newLine();
        $this->info("✅ Fertig — $count Krankenkassen in $dbName");

        return self::SUCCESS;
    }
}
