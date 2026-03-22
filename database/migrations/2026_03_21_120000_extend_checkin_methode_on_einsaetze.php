<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE einsaetze ALTER COLUMN checkin_methode TYPE varchar(30)');
        DB::statement('ALTER TABLE einsaetze ALTER COLUMN checkout_methode TYPE varchar(30)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE einsaetze ALTER COLUMN checkin_methode TYPE varchar(10)');
        DB::statement('ALTER TABLE einsaetze ALTER COLUMN checkout_methode TYPE varchar(10)');
    }
};
