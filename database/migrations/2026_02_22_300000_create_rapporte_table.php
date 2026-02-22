<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rapporte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisationen')->cascadeOnDelete();
            $table->foreignId('klient_id')->constrained('klienten')->cascadeOnDelete();
            $table->foreignId('benutzer_id')->nullable()->constrained('benutzer')->nullOnDelete();
            $table->foreignId('einsatz_id')->nullable()->constrained('einsaetze')->nullOnDelete();
            $table->date('datum');
            $table->time('zeit_von')->nullable();
            $table->time('zeit_bis')->nullable();
            $table->text('inhalt');
            $table->enum('rapport_typ', ['pflege', 'verlauf', 'information', 'zwischenfall', 'medikament'])->default('pflege');
            $table->boolean('vertraulich')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rapporte');
    }
};
