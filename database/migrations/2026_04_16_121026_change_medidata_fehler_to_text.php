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
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->text('medidata_fehler')->nullable()->change();
            $table->text('gemeinde_fehler')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->string('medidata_fehler')->nullable()->change();
            $table->string('gemeinde_fehler')->nullable()->change();
        });
    }
};
