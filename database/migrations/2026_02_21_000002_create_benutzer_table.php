<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benutzer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->string('vorname');
            $table->string('nachname');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('rolle', ['admin', 'pflege', 'buchhaltung'])->default('pflege');
            $table->boolean('aktiv')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('benutzer');
    }
};
