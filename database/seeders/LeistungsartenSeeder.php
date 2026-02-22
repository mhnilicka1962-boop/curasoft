<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeistungsartenSeeder extends Seeder
{
    public function run(): void
    {
        // Nur einfügen wenn Tabelle noch leer
        if (DB::table('leistungsarten')->count() > 0) {
            return;
        }

        $heute = now()->toDateString();

        DB::table('leistungsarten')->insert([
            [
                'bezeichnung'        => 'Pauschale',
                'einheit'            => 'tage',
                'kassenpflichtig'    => false,
                'aktiv'              => true,
                'gueltig_ab'         => $heute,
                'gueltig_bis'        => null,
                'ansatz_default'     => 0.00,
                'kvg_default'        => 0.00,
                'ansatz_akut_default'=> 0.00,
                'kvg_akut_default'   => 0.00,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'bezeichnung'        => 'Untersuchung Behandlung',
                'einheit'            => 'stunden',
                'kassenpflichtig'    => true,
                'aktiv'              => true,
                'gueltig_ab'         => $heute,
                'gueltig_bis'        => null,
                'ansatz_default'     => 92.00,
                'kvg_default'        => 65.40,
                'ansatz_akut_default'=> 92.00,
                'kvg_akut_default'   => 50.65,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'bezeichnung'        => 'Hauswirtschaft',
                'einheit'            => 'stunden',
                'kassenpflichtig'    => false,
                'aktiv'              => true,
                'gueltig_ab'         => $heute,
                'gueltig_bis'        => null,
                'ansatz_default'     => 44.00,
                'kvg_default'        => 0.00,
                'ansatz_akut_default'=> 44.00,
                'kvg_akut_default'   => 0.00,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'bezeichnung'        => 'Grundpflege',
                'einheit'            => 'stunden',
                'kassenpflichtig'    => true,
                'aktiv'              => true,
                'gueltig_ab'         => $heute,
                'gueltig_bis'        => null,
                'ansatz_default'     => 88.00,
                'kvg_default'        => 54.60,
                'ansatz_akut_default'=> 66.00,
                'kvg_akut_default'   => 41.70,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'bezeichnung'        => 'Abklärung/Beratung',
                'einheit'            => 'stunden',
                'kassenpflichtig'    => true,
                'aktiv'              => true,
                'gueltig_ab'         => $heute,
                'gueltig_bis'        => null,
                'ansatz_default'     => 96.00,
                'kvg_default'        => 79.80,
                'ansatz_akut_default'=> 66.00,
                'kvg_akut_default'   => 52.30,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ]);
    }
}
