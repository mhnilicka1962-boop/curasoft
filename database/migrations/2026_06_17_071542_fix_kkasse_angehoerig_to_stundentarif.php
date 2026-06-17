<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // kkasse_angehoerig war fälschlicherweise als CHF/Tag (27.60) gesetzt.
        // Korrekt ist CHF/h gemäss KLV Art. 7 — Grundpflege KK-Anteil 52.60/h.
        DB::table('leistungsregionen')
            ->where('kkasse_angehoerig', 27.60)
            ->update(['kkasse_angehoerig' => 52.60]);
    }

    public function down(): void
    {
        DB::table('leistungsregionen')
            ->where('kkasse_angehoerig', 52.60)
            ->update(['kkasse_angehoerig' => 27.60]);
    }
};
