<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EinsatzLeistungsart extends Model
{
    protected $table = 'einsatz_leistungsarten';

    protected $fillable = [
        'einsatz_id',
        'leistungsart_id',
        'minuten',
    ];

    protected $casts = [
        'minuten' => 'integer',
    ];

    public function einsatz()
    {
        return $this->belongsTo(Einsatz::class);
    }

    public function leistungsart()
    {
        return $this->belongsTo(Leistungsart::class);
    }
}
