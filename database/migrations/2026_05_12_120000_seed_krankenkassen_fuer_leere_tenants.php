<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Einmaliger Sofort-Fix: spitexzentrum.curasoft.ch (Aarau) hat keine Krankenkassen,
 * weil im alten tenant:create der KrankenkassenSeeder VOR der Organisation lief.
 *
 * Migration läuft pro Tenant via tenant:migrate:
 *   - Tenant hat schon KK (Demo, CuraPflege) → return, nichts passiert
 *   - Tenant hat KEINE KK + Org existiert (Aarau) → KrankenkassenSeeder einspielen
 *
 * Keine UPDATE/DELETE — nur INSERT auf leere Tabelle.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('krankenkassen')) return;
        if (!Schema::hasTable('organisationen')) return;

        if (DB::table('krankenkassen')->count() > 0) return;
        if (!DB::table('organisationen')->exists()) return;

        Artisan::call('db:seed', [
            '--class' => 'KrankenkassenSeeder',
            '--force' => true,
        ]);
    }

    public function down(): void {}
};
