<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tagespauschalen', function (Blueprint $table) {
            $table->boolean('auto_verlaengern')->default(false)->after('text');
            $table->date('datum_bis')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tagespauschalen', function (Blueprint $table) {
            $table->dropColumn('auto_verlaengern');
            $table->date('datum_bis')->nullable(false)->change();
        });
    }
};
