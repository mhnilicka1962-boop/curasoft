<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KlientVerordnung extends Model
{
    protected $table = 'klient_verordnungen';
    protected $fillable = [
        'klient_id', 'arzt_id', 'leistungsart_id',
        'verordnungs_nr', 'ausgestellt_am', 'gueltig_ab', 'gueltig_bis',
        'bemerkung', 'aktiv',
    ];
    protected $casts = [
        'ausgestellt_am' => 'date',
        'gueltig_ab'     => 'date',
        'gueltig_bis'    => 'date',
        'aktiv'          => 'boolean',
    ];

    public function klient()       { return $this->belongsTo(Klient::class); }
    public function arzt()         { return $this->belongsTo(Arzt::class); }
    public function leistungsart() { return $this->belongsTo(Leistungsart::class); }
    public function einsaetze()    { return $this->hasMany(Einsatz::class, 'verordnung_id'); }

    public function istAktiv(): bool
    {
        if (!$this->aktiv) return false;
        $heute = today();
        if ($this->gueltig_ab && $this->gueltig_ab->gt($heute)) return false;
        if ($this->gueltig_bis && $this->gueltig_bis->lt($heute)) return false;
        return true;
    }

    public function statusLabel(): string
    {
        if (!$this->aktiv) return 'Inaktiv';
        if ($this->gueltig_bis && $this->gueltig_bis->lt(today())) return 'Abgelaufen';
        if ($this->gueltig_bis && $this->gueltig_bis->lte(today()->addDays(14))) return 'Läuft bald ab';
        return 'Aktiv';
    }

    public function statusBadge(): string
    {
        return match($this->statusLabel()) {
            'Aktiv'           => 'badge-erfolg',
            'Läuft bald ab'   => 'badge-warnung',
            'Abgelaufen'      => 'badge-fehler',
            default           => 'badge-grau',
        };
    }
}
