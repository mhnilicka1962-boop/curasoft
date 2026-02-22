<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KlientBeitrag extends Model
{
    protected $table = 'klient_beitraege';

    protected $fillable = [
        'klient_id',
        'gueltig_ab',
        'ansatz_kunde',
        'limit_restbetrag_prozent',
        'ansatz_spitex',
        'kanton_abrechnung',
        'erfasst_von',
    ];

    protected $casts = [
        'gueltig_ab'               => 'date',
        'ansatz_kunde'             => 'decimal:2',
        'limit_restbetrag_prozent' => 'decimal:2',
        'ansatz_spitex'            => 'decimal:2',
        'kanton_abrechnung'        => 'decimal:2',
    ];

    public function klient()     { return $this->belongsTo(Klient::class); }
    public function erfasstVon() { return $this->belongsTo(Benutzer::class, 'erfasst_von'); }
}
