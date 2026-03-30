<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'organisation_id', 'klient_id', 'benutzer_id',
        'rhythmus', 'wochentage', 'leistungsarten',
        'gueltig_ab', 'gueltig_bis', 'auto_verlaengern',
        'zeit_von', 'zeit_bis',
        'leistungserbringer_typ', 'verordnung_id', 'bemerkung',
    ];

    protected $casts = [
        'wochentage'      => 'array',
        'leistungsarten'  => 'array',
        'gueltig_ab'      => 'date',
        'gueltig_bis'     => 'date',
        'auto_verlaengern' => 'boolean',
    ];

    protected $table = 'serien';

    public function klient()
    {
        return $this->belongsTo(Klient::class);
    }

    public function benutzer()
    {
        return $this->belongsTo(Benutzer::class);
    }

    public function einsaetze()
    {
        return $this->hasMany(Einsatz::class, 'serie_id', 'id');
    }

    public function rhythmusLabel(): string
    {
        if ($this->rhythmus === 'taeglich') return 'Täglich';

        $tage = ['0' => 'So', '1' => 'Mo', '2' => 'Di', '3' => 'Mi', '4' => 'Do', '5' => 'Fr', '6' => 'Sa'];
        $gewaehlte = collect($this->wochentage ?? [])
            ->map(fn($d) => $tage[(string)$d] ?? '?')
            ->implode('+');
        return 'Wöchentlich ' . ($gewaehlte ?: '—');
    }

    public function istAktiv(): bool
    {
        return $this->gueltig_ab->lte(today())
            && (!$this->gueltig_bis || $this->gueltig_bis->gt(today()));
    }

    public function istGeplant(): bool
    {
        return $this->gueltig_ab->gt(today());
    }
}
