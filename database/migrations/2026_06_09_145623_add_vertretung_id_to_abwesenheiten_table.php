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
        Schema::table('abwesenheiten', function (Blueprint $table) {
            $table->unsignedBigInteger('vertretung_id')->nullable()->after('benutzer_id');
            $table->foreign('vertretung_id')->references('id')->on('benutzer')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('abwesenheiten', function (Blueprint $table) {
            $table->dropForeign(['vertretung_id']);
            $table->dropColumn('vertretung_id');
        });
    }
};
