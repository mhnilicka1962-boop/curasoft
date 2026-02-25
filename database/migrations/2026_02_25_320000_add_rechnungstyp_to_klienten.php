<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klienten', function (Blueprint $table) {
            $table->string('rechnungstyp', 20)->default('kombiniert')->after('versandart_kvg');
        });

        // Bestehende rechnungslaeufe-Felder nullable machen (Tarife kommen jetzt aus Leistungsregionen)
        Schema::table('rechnungslaeufe', function (Blueprint $table) {
            $table->string('rechnungstyp', 20)->nullable()->default(null)->change();
            $table->decimal('tarif_patient', 8, 4)->nullable()->default(null)->change();
            $table->decimal('tarif_kk', 8, 4)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('klienten', function (Blueprint $table) {
            $table->dropColumn('rechnungstyp');
        });
        Schema::table('rechnungslaeufe', function (Blueprint $table) {
            $table->string('rechnungstyp', 20)->nullable(false)->default('kombiniert')->change();
            $table->decimal('tarif_patient', 8, 4)->nullable(false)->default(0)->change();
            $table->decimal('tarif_kk', 8, 4)->nullable(false)->default(0)->change();
        });
    }
};
