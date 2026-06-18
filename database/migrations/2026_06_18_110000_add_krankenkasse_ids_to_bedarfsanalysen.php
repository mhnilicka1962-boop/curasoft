<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('bedarfsanalysen', function (Blueprint $table) {
            $table->unsignedBigInteger('kvg_krankenkasse_id')->nullable()->after('gewicht_kg');
            $table->string('vvg_deckungstyp', 20)->nullable()->change();
            $table->unsignedBigInteger('zweite_krankenkasse_id')->nullable()->after('kvg_krankenkasse_id');
            $table->foreign('kvg_krankenkasse_id')->references('id')->on('krankenkassen')->nullOnDelete();
            $table->foreign('zweite_krankenkasse_id')->references('id')->on('krankenkassen')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('bedarfsanalysen', function (Blueprint $table) {
            $table->dropForeign(['kvg_krankenkasse_id']);
            $table->dropForeign(['zweite_krankenkasse_id']);
            $table->dropColumn(['kvg_krankenkasse_id', 'zweite_krankenkasse_id']);
        });
    }
};
