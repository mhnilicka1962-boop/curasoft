<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Leistungsregion extends Model
{
    protected $table = 'leistungsregionen';

    protected $fillable = [
        'leistungsart_id', 'region_id',
        'ansatz', 'kkasse', 'ansatz_akut', 'kkasse_akut',
        'kassenpflichtig', 'gueltig_ab', 'gueltig_bis',
        'verrechnung', 'einsatz_minuten', 'einsatz_stunden', 'einsatz_tage', 'mwst',
    ];

    protected $casts = [
        'ansatz'           => 'decimal:2',
        'kkasse'           => 'decimal:2',
        'ansatz_akut'      => 'decimal:2',
        'kkasse_akut'      => 'decimal:2',
        'kassenpflichtig'  => 'boolean',
        'gueltig_ab'       => 'date',
        'gueltig_bis'      => 'date',
        'verrechnung'      => 'boolean',
        'einsatz_minuten'  => 'boolean',
        'einsatz_stunden'  => 'boolean',
        'einsatz_tage'     => 'boolean',
        'mwst'             => 'boolean',
    ];

    public function leistungsart()
    {
        return $this->belongsTo(Leistungsart::class, 'leistungsart_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /** Patientenanteil = Gesamtansatz minus KK-Anteil */
    public function patientAnteil(): float
    {
        return max(0, (float)$this->ansatz - (float)$this->kkasse);
    }

    public function patientAnteilAkut(): float
    {
        return max(0, (float)$this->ansatz_akut - (float)$this->kkasse_akut);
    }
}
