<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE rechnungslaeufe rl
            SET abrechnungslogik = 'tiers_payant'
            WHERE rl.abrechnungslogik = 'tiers_garant'
              AND EXISTS (
                  SELECT 1 FROM organisationen o
                  WHERE o.id = rl.organisation_id
                    AND o.abrechnungslogik = 'tiers_payant'
              )
        ");
    }

    public function down(): void {}
};
