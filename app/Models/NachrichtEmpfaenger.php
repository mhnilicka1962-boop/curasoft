<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NachrichtEmpfaenger extends Model
{
    protected $table = 'nachricht_empfaenger';

    protected $fillable = [
        'nachricht_id', 'empfaenger_id', 'gelesen_am', 'archiviert',
    ];

    protected $casts = [
        'gelesen_am' => 'datetime',
        'archiviert' => 'boolean',
    ];

    public function nachricht()
    {
        return $this->belongsTo(Nachricht::class, 'nachricht_id');
    }

    public function empfaenger()
    {
        return $this->belongsTo(Benutzer::class, 'empfaenger_id');
    }

    public function istUngelesen(): bool
    {
        return $this->gelesen_am === null;
    }
}
