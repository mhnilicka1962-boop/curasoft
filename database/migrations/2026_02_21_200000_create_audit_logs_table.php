<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('benutzer_id')->nullable();
            $table->string('benutzer_name')->nullable();   // gespeichert für Historie
            $table->string('benutzer_email')->nullable();
            $table->string('aktion', 50);                 // login, logout, erstellt, geaendert, geloescht, angezeigt
            $table->string('modell_typ', 100)->nullable(); // Klient, Einsatz, Rechnung, ...
            $table->unsignedBigInteger('modell_id')->nullable();
            $table->string('beschreibung')->nullable();
            $table->jsonb('alte_werte')->nullable();
            $table->jsonb('neue_werte')->nullable();
            $table->string('ip_adresse', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
