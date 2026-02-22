<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('benutzer', function (Blueprint $table) {
            $table->string('anrede', 20)->nullable()->after('nachname');
            $table->enum('geschlecht', ['m', 'f', 'd'])->nullable()->after('anrede');
            $table->date('geburtsdatum')->nullable()->after('geschlecht');
            $table->string('nationalitaet', 60)->nullable()->after('geburtsdatum');
            $table->string('zivilstand', 40)->nullable()->after('nationalitaet');
            $table->string('strasse', 150)->nullable()->after('zivilstand');
            $table->string('plz', 10)->nullable()->after('strasse');
            $table->string('ort', 100)->nullable()->after('plz');
            $table->string('telefon', 30)->nullable()->after('ort');
            $table->string('telefax', 30)->nullable()->after('telefon');
            $table->string('email_privat', 100)->nullable()->after('email');
            $table->string('ahv_nr', 20)->nullable()->after('email_privat');
            $table->string('iban', 25)->nullable()->after('ahv_nr');
            $table->string('bank', 100)->nullable()->after('iban');
            $table->smallInteger('pensum')->default(100)->after('bank');
            $table->date('eintrittsdatum')->nullable()->after('pensum');
            $table->date('austrittsdatum')->nullable()->after('eintrittsdatum');
            $table->text('notizen')->nullable()->after('austrittsdatum');
        });
    }

    public function down(): void
    {
        Schema::table('benutzer', function (Blueprint $table) {
            $table->dropColumn([
                'anrede', 'geschlecht', 'geburtsdatum', 'nationalitaet', 'zivilstand',
                'strasse', 'plz', 'ort', 'telefon', 'telefax', 'email_privat',
                'ahv_nr', 'iban', 'bank', 'pensum', 'eintrittsdatum', 'austrittsdatum', 'notizen',
            ]);
        });
    }
};
