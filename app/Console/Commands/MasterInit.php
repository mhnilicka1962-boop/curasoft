<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * master:init — tenants-Tabelle in der Master-DB einmalig anlegen
 *
 * Ausführen nach erstem Setup:
 *   php artisan master:init
 *
 * Lokal:     tenants-Tabelle in 'curasoft' DB
 * Demo:      tenants-Tabelle in 'devitjob_curasoft' DB
 */
class MasterInit extends Command
{
    protected $signature = 'master:init';
    protected $description = 'tenants-Tabelle in Master-DB anlegen (einmalig)';

    public function handle(): int
    {
        $connection = DB::connection('master');
        $dbName = config('database.connections.master.database');

        $this->info("Master-DB: $dbName");

        if ($connection->getSchemaBuilder()->hasTable('tenants')) {
            $this->warn('tenants-Tabelle existiert bereits — übersprungen.');

            $count = $connection->table('tenants')->count();
            $this->line("Vorhandene Tenants: $count");
            $connection->table('tenants')->get()->each(function ($t) {
                $status = $t->aktiv ? '✓ aktiv' : '○ inaktiv';
                $this->line("  {$status}  {$t->subdomain} → {$t->db_name}");
            });

            return self::SUCCESS;
        }

        $connection->getSchemaBuilder()->create('tenants', function ($table) {
            $table->id();
            $table->string('subdomain')->unique();
            $table->string('db_name');
            $table->string('db_user');
            $table->string('db_password')->default('');
            $table->string('db_host')->default('');
            $table->string('db_port')->default('');
            $table->boolean('aktiv')->default(true);
            $table->timestamp('erstellt_am')->useCurrent();
        });

        $this->info('✅ tenants-Tabelle angelegt.');
        $this->line('');
        $this->line('Nächster Schritt:');
        $this->line('  php artisan tenant:create curapflege "CuraPflege" admin@example.com');

        return self::SUCCESS;
    }
}
