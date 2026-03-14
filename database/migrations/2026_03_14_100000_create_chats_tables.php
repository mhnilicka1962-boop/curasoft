<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->enum('typ', ['team', 'direkt'])->default('team');
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('chat_teilnehmer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->cascadeOnDelete();
            $table->foreignId('benutzer_id')->constrained('benutzer')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['chat_id', 'benutzer_id']);
        });

        Schema::create('chat_nachrichten', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->cascadeOnDelete();
            $table->foreignId('absender_id')->constrained('benutzer')->cascadeOnDelete();
            $table->text('inhalt');
            $table->timestamp('geloescht_am')->nullable();
            $table->timestamps();
            $table->index(['chat_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_nachrichten');
        Schema::dropIfExists('chat_teilnehmer');
        Schema::dropIfExists('chats');
    }
};
