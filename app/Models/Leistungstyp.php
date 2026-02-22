<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leistungstyp extends Model
{
    protected $table = 'leistungstypen';

    protected $fillable = [
        'leistungsart_id', 'bezeichnung',
        'einheit', 'gueltig_ab', 'gueltig_bis', 'aktiv',
    ];

    protected $casts = [
        'aktiv'      => 'boolean',
        'gueltig_ab' => 'date',
        'gueltig_bis' => 'date',
    ];

    public function leistungsart()
    {
        return $this->belongsTo(Leistungsart::class, 'leistungsart_id');
    }
}
