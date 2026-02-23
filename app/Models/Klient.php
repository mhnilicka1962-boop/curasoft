<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Klient extends Model
{
    protected $table = 'klienten';

    protected static function booted(): void
    {
        static::creating(function (Klient $klient) {
            $klient->qr_token ??= Str::random(32);
        });
    }

    protected $fillable = [
        'organisation_id',
        'anrede',
        'vorname',
        'nachname',
        'geburtsdatum',
        'geschlecht',
        'zivilstand',
        'anzahl_kinder',
        'adresse',
        'plz',
        'ort',
        'region_id',
        'zustaendig_id',
        'datum_erstkontakt',
        'einsatz_geplant_von',
        'einsatz_geplant_bis',
        'telefon',
        'notfallnummer',
        'email',
        'ahv_nr',
        'krankenkasse_name',
        'krankenkasse_nr',
        'zahlbar_tage',
        'aktiv',
        'qr_token',
        'klient_lat',
        'klient_lng',
    ];

    protected $casts = [
        'geburtsdatum'       => 'date',
        'datum_erstkontakt'  => 'date',
        'einsatz_geplant_von'=> 'date',
        'einsatz_geplant_bis'=> 'date',
        'aktiv'              => 'boolean',
    ];

    public function region()     { return $this->belongsTo(Region::class); }
    public function zustaendig() { return $this->belongsTo(Benutzer::class, 'zustaendig_id'); }
    public function einsaetze()  { return $this->hasMany(Einsatz::class); }
    public function adressen()   { return $this->hasMany(KlientAdresse::class)->orderBy('adressart'); }

    public function adresseVom(string $art): ?KlientAdresse
    {
        return $this->adressen->where('adressart', $art)->where('aktiv', true)->first();
    }

    public function einsatzadresse(): ?KlientAdresse   { return $this->adresseVom('einsatzort'); }
    public function rechnungsadresse(): ?KlientAdresse { return $this->adresseVom('rechnung'); }
    public function notfalladresse(): ?KlientAdresse   { return $this->adresseVom('notfall'); }

    public function aerzte()         { return $this->hasMany(KlientArzt::class)->with('arzt'); }
    public function hauptarzt()      { return $this->hasOneThrough(Arzt::class, KlientArzt::class, 'klient_id', 'id', 'id', 'arzt_id')->where('klient_aerzte.hauptarzt', true); }
    public function krankenkassen()  { return $this->hasMany(KlientKrankenkasse::class)->with('krankenkasse'); }
    public function kontakte()       { return $this->hasMany(KlientKontakt::class)->orderBy('rolle'); }
    public function pflegestufen()   { return $this->hasMany(KlientPflegestufe::class)->orderByDesc('einstufung_datum'); }
    public function aktPflegestufe() { return $this->hasOne(KlientPflegestufe::class)->latestOfMany('einstufung_datum'); }
    public function diagnosen()      { return $this->hasMany(KlientDiagnose::class)->where('aktiv', true); }
    public function beitraege()      { return $this->hasMany(KlientBeitrag::class)->orderByDesc('gueltig_ab'); }
    public function aktBeitrag()     { return $this->hasOne(KlientBeitrag::class)->latestOfMany('gueltig_ab'); }
    public function verordnungen()   { return $this->hasMany(KlientVerordnung::class)->orderByDesc('ausgestellt_am'); }
    public function rapporte()         { return $this->hasMany(Rapport::class)->orderByDesc('datum'); }
    public function dokumente()        { return $this->hasMany(Dokument::class)->orderByDesc('created_at'); }
    public function betreuungspersonen() { return $this->hasMany(KlientBenutzer::class)->with('benutzer')->orderBy('rolle'); }

    public function vollname(): string
    {
        return trim(($this->anrede ? $this->anrede . ' ' : '') . $this->vorname . ' ' . $this->nachname);
    }
}
