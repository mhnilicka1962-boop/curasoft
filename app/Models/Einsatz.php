<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Einsatz extends Model
{
    protected $table = 'einsaetze';

    protected $fillable = [
        'organisation_id', 'klient_id', 'benutzer_id',
        'leistungsart_id', 'verordnung_id', 'region_id', 'status',
        'datum', 'datum_bis', 'zeit_von', 'zeit_bis', 'minuten', 'bemerkung', 'verrechnet',
        'checkin_zeit', 'checkin_lat', 'checkin_lng', 'checkin_methode', 'checkin_distanz_meter',
        'checkout_zeit', 'checkout_lat', 'checkout_lng', 'checkout_methode', 'checkout_distanz_meter',
    ];

    protected $casts = [
        'datum'        => 'date',
        'datum_bis'    => 'date',
        'checkin_zeit' => 'datetime',
        'checkout_zeit'=> 'datetime',
        'verrechnet'   => 'boolean',
    ];

    public function klient()       { return $this->belongsTo(Klient::class); }
    public function benutzer()     { return $this->belongsTo(Benutzer::class); }
    public function leistungsart() { return $this->belongsTo(Leistungsart::class); }
    public function verordnung()   { return $this->belongsTo(KlientVerordnung::class, 'verordnung_id'); }
    public function region()       { return $this->belongsTo(Region::class); }
    public function tour()         { return $this->belongsTo(Tour::class); }

    public function isEingecheckt(): bool  { return !is_null($this->checkin_zeit); }
    public function isAusgecheckt(): bool  { return !is_null($this->checkout_zeit); }

    public function dauerMinuten(): ?int
    {
        if (!$this->checkin_zeit || !$this->checkout_zeit) return null;
        return (int) $this->checkin_zeit->diffInMinutes($this->checkout_zeit);
    }

    public function istPauschale(): bool
    {
        return $this->leistungsart?->einheit === 'tage';
    }

    public function anzahlTage(): ?int
    {
        if (!$this->datum || !$this->datum_bis) return null;
        return $this->datum->diffInDays($this->datum_bis) + 1;
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'aktiv'         => 'Läuft',
            'abgeschlossen' => 'Abgeschlossen',
            'storniert'     => 'Storniert',
            default         => 'Geplant',
        };
    }

    public function statusBadgeKlasse(): string
    {
        return match($this->status) {
            'aktiv'         => 'badge-warnung',
            'abgeschlossen' => 'badge-erfolg',
            'storniert'     => 'badge-fehler',
            default         => 'badge-grau',
        };
    }
}
