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
        Schema::table('serien', function (Blueprint $table) {
            $table->unsignedBigInteger('helfer_id')->nullable()->after('benutzer_id');
            $table->foreign('helfer_id')->references('id')->on('benutzer')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('serien', function (Blueprint $table) {
            $table->dropForeign(['helfer_id']);
            $table->dropColumn('helfer_id');
        });
    }
};
