<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // kvg_angehoerig_default war noch auf 27.60 (CHF/Tag) — korrigiert auf 52.60 (CHF/h, KLV Art. 7)
        DB::table('leistungsarten')
            ->where('bezeichnung', 'Grundpflege')
            ->where('kvg_angehoerig_default', 27.60)
            ->update(['kvg_angehoerig_default' => 52.60]);
    }

    public function down(): void
    {
        DB::table('leistungsarten')
            ->where('bezeichnung', 'Grundpflege')
            ->where('kvg_angehoerig_default', 52.60)
            ->update(['kvg_angehoerig_default' => 27.60]);
    }
};
