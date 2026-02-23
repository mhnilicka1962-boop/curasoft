<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leistungsart extends Model
{
    protected $table = 'leistungsarten';

    protected $fillable = [
        'bezeichnung', 'einheit', 'kassenpflichtig', 'aktiv',
        'gueltig_ab', 'gueltig_bis',
        'ansatz_default', 'kvg_default', 'ansatz_akut_default', 'kvg_akut_default',
        'tarmed_code',
    ];

    protected $casts = [
        'kassenpflichtig'     => 'boolean',
        'aktiv'               => 'boolean',
        'gueltig_ab'          => 'date',
        'gueltig_bis'         => 'date',
        'ansatz_default'      => 'decimal:2',
        'kvg_default'         => 'decimal:2',
        'ansatz_akut_default' => 'decimal:2',
        'kvg_akut_default'    => 'decimal:2',
    ];

    public function tarife()
    {
        return $this->hasMany(Leistungsregion::class, 'leistungsart_id');
    }

    public function leistungstypen()
    {
        return $this->hasMany(Leistungstyp::class, 'leistungsart_id');
    }

    public function benutzer()
    {
        return $this->belongsToMany(Benutzer::class, 'benutzer_leistungsarten');
    }

    public function einheitLabel(): string
    {
        return match($this->einheit ?? 'minuten') {
            'stunden' => 'pro Stunde',
            'tage'    => 'pro Tag',
            default   => 'pro Minute',
        };
    }
}
