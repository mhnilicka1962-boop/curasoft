<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KlientAdresse extends Model
{
    protected $table = 'klient_adressen';

    protected $fillable = [
        'klient_id', 'adressart',
        'gueltig_ab', 'gueltig_bis',
        'firma', 'anrede', 'vorname', 'nachname',
        'strasse', 'postfach', 'plz', 'ort', 'region_id',
        'telefon', 'telefax', 'email', 'aktiv',
    ];

    protected $casts = [
        'gueltig_ab'  => 'date',
        'gueltig_bis' => 'date',
        'aktiv'       => 'boolean',
    ];

    public function klient()  { return $this->belongsTo(Klient::class); }
    public function region()  { return $this->belongsTo(Region::class); }

    public static array $arten = [
        'einsatzort'    => 'Einsatzort',
        'rechnung'      => 'Rechnungsadresse',
        'notfall'       => 'Notfall',
        'korrespondenz' => 'Korrespondenz',
    ];

    public function artLabel(): string
    {
        return self::$arten[$this->adressart] ?? $this->adressart;
    }

    public function vollname(): ?string
    {
        $teile = array_filter([$this->firma, $this->anrede, $this->vorname, $this->nachname]);
        return $teile ? implode(' ', $teile) : null;
    }

    public function adresseZeile(): string
    {
        $teile = array_filter([$this->strasse, trim($this->plz . ' ' . $this->ort)]);
        return implode(', ', $teile) ?: '—';
    }
}
