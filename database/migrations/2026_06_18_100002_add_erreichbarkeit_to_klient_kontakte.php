<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('klient_kontakte', function (Blueprint $table) {
            $table->boolean('vormund')->default(false)->after('bevollmaechtigt');
            $table->string('erreichbarkeit', 20)->nullable()->after('vormund');
            $table->time('erreichbarkeit_von')->nullable()->after('erreichbarkeit');
            $table->time('erreichbarkeit_bis')->nullable()->after('erreichbarkeit_von');
        });
    }

    public function down(): void
    {
        Schema::table('klient_kontakte', function (Blueprint $table) {
            $table->dropColumn(['vormund','erreichbarkeit','erreichbarkeit_von','erreichbarkeit_bis']);
        });
    }
};
