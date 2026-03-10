<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * tenant:create — Neuen Kunden anlegen (DB + Seeders + Master-Eintrag)
 *
 * Beispiel:
 *   php artisan tenant:create spitex-aarau "Spitex Aarau" admin@spitex-aarau.ch
 *
 * Was es macht:
 *   1. DB anlegen (curasoft_<subdomain_slug>)
 *   2. Migrations ausführen
 *   3. Basis-Seeders einspielen (Leistungsarten, Einsatzarten, Krankenkassen)
 *   4. Organisation + Admin-Benutzer anlegen
 *   5. Eintrag in Master-DB (tenants-Tabelle)
 *
 * Noch nicht aktiv — wird fertiggestellt wenn Multi-Tenant live geht.
 */
class TenantCreate extends Command
{
    protected $signature = 'tenant:create
                            {subdomain : Subdomain z.B. curapflege}
                            {name : Organisationsname z.B. "CuraPflege GmbH"}
                            {email : E-Mail des ersten Admin-Benutzers}
                            {--password= : Passwort (Standard: zufällig generiert)}
                            {--db= : DB-Name überschreiben (Standard: curasoft_<subdomain>)}
                            {--skip-create-db : DB-Erstellung überspringen (z.B. auf cPanel)}';

    protected $description = 'Neuen Tenant (Kunden) anlegen: DB + Seeders + Admin-User';

    public function handle(): int
    {
        $subdomain = strtolower($this->argument('subdomain'));
        $name      = $this->argument('name');
        $email     = $this->argument('email');
        $password  = $this->option('password') ?? $this->generatePassword();
        $dbName    = $this->option('db') ?? ('curasoft_' . preg_replace('/[^a-z0-9]/', '_', $subdomain));
        $dbUser    = env('DB_USERNAME', 'postgres');
        $dbPass    = env('DB_PASSWORD', '');

        $this->info("Neuer Tenant: $name ($subdomain.curasoft.ch)");
        $this->line("DB: $dbName");
        $this->newLine();

        // 1. DB anlegen (lokal) oder überspringen (cPanel: DB manuell anlegen)
        $this->line('1/5  Datenbank anlegen...');
        if ($this->option('skip-create-db')) {
            $this->warn("     --skip-create-db: DB $dbName muss bereits existieren");
        } else {
            try {
                DB::statement("CREATE DATABASE \"$dbName\"");
                $this->line("     ✓ $dbName erstellt");
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'already exists')) {
                    $this->warn("     DB $dbName existiert bereits — OK");
                } else {
                    $this->error('     Fehler: ' . $e->getMessage());
                    $this->error('     Tipp: DB manuell anlegen und --skip-create-db verwenden');
                    return self::FAILURE;
                }
            }
        }

        // 2. Tenant-Connection setzen
        Config::set("database.connections.tenant_new", [
            'driver'   => 'pgsql',
            'host'     => env('DB_HOST', 'localhost'),
            'port'     => env('DB_PORT', '5432'),
            'database' => $dbName,
            'username' => $dbUser,
            'password' => $dbPass,
            'charset'  => 'utf8',
            'schema'   => 'public',
        ]);
        DB::purge('tenant_new');

        // 3. Migrationen
        $this->line('2/5  Migrationen ausführen...');
        $this->call('migrate', ['--database' => 'tenant_new', '--force' => true]);

        // 4. Basis-Seeders — Default-Connection auf tenant_new setzen damit Seeders korrekt schreiben
        $this->line('3/5  Seeders einspielen...');
        $prevConnection = DB::getDefaultConnection();
        DB::setDefaultConnection('tenant_new');
        foreach (['LeistungsartenSeeder', 'EinsatzartenSeeder', 'KrankenkassenSeeder'] as $seeder) {
            try {
                $this->call('db:seed', ['--class' => $seeder, '--force' => true]);
                $this->line("     ✓ $seeder");
            } catch (\Exception $e) {
                $this->warn("     ⚠ $seeder: " . $e->getMessage());
            }
        }
        DB::setDefaultConnection($prevConnection);

        // 5. Organisation anlegen
        $this->line('4/5  Organisation + Admin anlegen...');
        $orgId = DB::connection('tenant_new')->table('organisationen')->insertGetId([
            'name'       => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::connection('tenant_new')->table('benutzer')->insert([
            'organisation_id' => $orgId,
            'vorname'         => 'Admin',
            'nachname'        => $name,
            'email'           => $email,
            'password'        => Hash::make($password),
            'rolle'           => 'admin',
            'aktiv'           => true,
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
        $this->line("     ✓ Admin: $email / $password");

        // 6. Master-DB Eintrag
        $this->line('5/5  Master-DB Eintrag...');
        try {
            DB::connection('master')->table('tenants')->insert([
                'subdomain'   => $subdomain,
                'db_name'     => $dbName,
                'db_user'     => $dbUser,
                'db_password' => $dbPass,
                'aktiv'       => true,
                'erstellt_am' => now(),
            ]);
            $this->line("     ✓ tenants-Eintrag gesetzt");
        } catch (\Exception $e) {
            $this->warn('     Master-DB nicht verfügbar: ' . $e->getMessage());
            $this->warn('     Manuell eintragen: INSERT INTO tenants ...');
        }

        $this->newLine();
        $this->info("✅ Tenant '$name' bereit:");
        $this->line("   URL:      https://{$subdomain}.curasoft.ch");
        $this->line("   Login:    $email");
        $this->line("   Passwort: $password");

        return self::SUCCESS;
    }

    private function generatePassword(): string
    {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#';
        return substr(str_shuffle(str_repeat($chars, 3)), 0, 12);
    }
}
