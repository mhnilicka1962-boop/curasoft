<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->timestamp('email_versand_datum')->nullable()->after('pdf_pfad');
            $table->string('email_versand_an', 255)->nullable()->after('email_versand_datum');
        });
    }

    public function down(): void
    {
        Schema::table('rechnungen', function (Blueprint $table) {
            $table->dropColumn(['email_versand_datum', 'email_versand_an']);
        });
    }
};
