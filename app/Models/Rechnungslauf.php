<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rechnungslauf extends Model
{
    protected $table = 'rechnungslaeufe';

    protected $fillable = [
        'organisation_id',
        'periode_von',
        'periode_bis',
        'rechnungstyp',
        'tarif_patient',
        'tarif_kk',
        'anzahl_erstellt',
        'anzahl_uebersprungen',
        'status',
        'erstellt_von',
    ];

    protected $casts = [
        'periode_von'   => 'date',
        'periode_bis'   => 'date',
        'tarif_patient' => 'decimal:4',
        'tarif_kk'      => 'decimal:4',
    ];

    public function rechnungen() { return $this->hasMany(Rechnung::class); }
    public function ersteller()  { return $this->belongsTo(Benutzer::class, 'erstellt_von'); }

    public function totalBetrag(): float
    {
        return (float) $this->rechnungen()->sum('betrag_total');
    }

    public function typLabel(): string
    {
        if (!$this->rechnungstyp) return 'Gemischt';
        return Rechnung::$typen[$this->rechnungstyp] ?? $this->rechnungstyp;
    }
}
