<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebAuthnCredential extends Model
{
    protected $table = 'webauthn_credentials';

    protected $fillable = [
        'benutzer_id',
        'credential_id',
        'public_key_spki',
        'counter',
        'geraet_name',
    ];

    protected $casts = [
        'counter' => 'integer',
    ];

    public function benutzer(): BelongsTo
    {
        return $this->belongsTo(Benutzer::class, 'benutzer_id');
    }
}
