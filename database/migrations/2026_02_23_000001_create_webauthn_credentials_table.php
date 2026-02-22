<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webauthn_credentials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('benutzer_id');
            $table->text('credential_id');        // base64url-encoded credential ID
            $table->text('public_key_spki');      // base64-encoded DER SPKI public key
            $table->unsignedBigInteger('counter')->default(0);
            $table->string('geraet_name', 100)->nullable(); // z.B. "iPhone von Sandra"
            $table->timestamps();

            $table->unique('credential_id');
            $table->foreign('benutzer_id')
                  ->references('id')->on('benutzer')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webauthn_credentials');
    }
};
