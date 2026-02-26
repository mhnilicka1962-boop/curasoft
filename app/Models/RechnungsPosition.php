<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RechnungsPosition extends Model
{
    protected $table = 'rechnungs_positionen';

    protected $fillable = [
        'rechnung_id', 'einsatz_id', 'leistungstyp_id',
        'datum', 'menge', 'einheit', 'beschreibung',
        'tarif_patient', 'tarif_kk',
        'betrag_patient', 'betrag_kk',
    ];

    protected $casts = [
        'datum'         => 'date',
        'tarif_patient' => 'decimal:2',
        'tarif_kk'      => 'decimal:2',
        'betrag_patient'=> 'decimal:2',
        'betrag_kk'     => 'decimal:2',
    ];

    public function rechnung()     { return $this->belongsTo(Rechnung::class); }
    public function einsatz()      { return $this->belongsTo(Einsatz::class); }
    public function leistungstyp() { return $this->belongsTo(Leistungstyp::class); }
}
