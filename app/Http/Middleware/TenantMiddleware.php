<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Liest die Subdomain aus dem Request-Host und schaltet auf die
     * entsprechende Tenant-Datenbank um.
     *
     * Beispiele:
     *   curasoft.ch              → Default-DB (Demo)
     *   www.curasoft.ch          → Default-DB (Demo)
     *   curapflege.curasoft.ch   → Tenant-DB aus tenants-Tabelle
     *   spitex.test              → Default-DB (lokal)
     *   curapflege.spitex.test   → Tenant-DB aus tenants-Tabelle (lokal)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $this->extractSubdomain($request->getHost());

        if ($subdomain === null) {
            return $next($request);
        }

        // Tenant in Master-DB suchen
        try {
            $tenant = DB::connection('master')
                ->table('tenants')
                ->where('subdomain', $subdomain)
                ->where('aktiv', true)
                ->first();
        } catch (\Exception $e) {
            abort(503, 'Tenant-Datenbank nicht erreichbar. Bitte Administrator kontaktieren.');
        }

        if (! $tenant) {
            abort(503, "Konfigurationsfehler: Subdomain '{$subdomain}' nicht eingerichtet. Bitte Administrator kontaktieren.");
        }

        // Tenant-DB-Connection dynamisch setzen
        Config::set('database.connections.tenant', [
            'driver'      => 'pgsql',
            'host'        => $tenant->db_host ?: env('DB_HOST', '127.0.0.1'),
            'port'        => $tenant->db_port ?: env('DB_PORT', '5432'),
            'database'    => $tenant->db_name,
            'username'    => $tenant->db_user,
            'password'    => $tenant->db_password,
            'charset'     => 'utf8',
            'prefix'      => '',
            'search_path' => 'public',
            'sslmode'     => env('DB_SSLMODE', 'prefer'),
        ]);

        DB::purge('tenant');
        DB::setDefaultConnection('tenant');

        // Aktuellen Tenant für späteren Zugriff im Request verfügbar machen
        $request->attributes->set('tenant', $tenant);
        app()->instance('tenant', $tenant);

        // Organisation aus Tenant-DB laden → Theme-Config setzen (für Login-Seite)
        try {
            $org = DB::connection('tenant')->table('organisationen')->first();
            if ($org) {
                Config::set('theme.app_name', $org->name);
                if (! empty($org->theme_farbe_primaer)) {
                    Config::set('theme.farbe_primaer', $org->theme_farbe_primaer);
                }
                if (! empty($org->logo_pfad)) {
                    Config::set('theme.logo', $org->logo_pfad);
                }
                if (! empty($org->theme_layout)) {
                    Config::set('theme.layout', $org->theme_layout);
                }
            }
        } catch (\Exception) {
            // Kein Org-Eintrag → Default-Theme behalten
        }

        return $next($request);
    }

    /**
     * Extrahiert die Subdomain aus dem Hostnamen.
     * Gibt null zurück wenn kein Tenant-Subdomain erkennbar (Root-Domain oder www).
     */
    private function extractSubdomain(string $host): ?string
    {
        // Bekannte Root-Domains (ohne Subdomain = Default-DB)
        $rootDomains = [
            'curasoft.ch',
            'www.curasoft.ch',
            'spitex.test',
            'localhost',
        ];

        if (in_array(strtolower($host), $rootDomains)) {
            return null;
        }

        // IP-Adressen ignorieren
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        // Subdomain extrahieren: erstes Segment vor dem ersten Punkt
        $parts = explode('.', $host);

        if (count($parts) < 2) {
            return null;
        }

        $subdomain = strtolower($parts[0]);

        // www.tenant.domain.tld → tenant extrahieren
        if ($subdomain === 'www' && count($parts) >= 4) {
            return strtolower($parts[1]);
        }

        // Bekannte Nicht-Tenant-Subdomains ignorieren
        if (in_array($subdomain, ['www', 'mail', 'ftp', 'smtp', 'pop', 'imap'])) {
            return null;
        }

        return $subdomain;
    }
}
