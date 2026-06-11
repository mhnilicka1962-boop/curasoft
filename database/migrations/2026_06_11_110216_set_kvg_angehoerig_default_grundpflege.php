<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // kvg_angehoerig_default auf leistungsarten setzen
        DB::table('leistungsarten')
            ->where('bezeichnung', 'Grundpflege')
            ->update(['kvg_angehoerig_default' => 27.60]);

        // kkasse_angehoerig auf bestehenden leistungsregionen setzen
        $gpId = DB::table('leistungsarten')->where('bezeichnung', 'Grundpflege')->value('id');
        if ($gpId) {
            DB::table('leistungsregionen')
                ->where('leistungsart_id', $gpId)
                ->where('kkasse_angehoerig', 0)
                ->update(['kkasse_angehoerig' => 27.60]);
        }
    }

    public function down(): void
    {
        //
    }
};
