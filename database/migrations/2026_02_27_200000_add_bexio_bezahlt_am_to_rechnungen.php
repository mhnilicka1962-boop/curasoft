<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->timestamp('bexio_bezahlt_am')->nullable()->after('bexio_rechnung_id');
        });
    }

    public function down(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->dropColumn('bexio_bezahlt_am');
        });
    }
};
