<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Arzt extends Model
{
    protected $table = 'aerzte';
    protected $fillable = [
        'organisation_id', 'anrede', 'vorname', 'nachname', 'zsr_nr', 'gln_nr',
        'fachrichtung', 'praxis_name', 'adresse', 'plz', 'ort', 'region_id',
        'telefon', 'fax', 'email', 'aktiv',
    ];
    protected $casts = ['aktiv' => 'boolean'];

    public function region()  { return $this->belongsTo(Region::class); }
    public function klienten() { return $this->belongsToMany(Klient::class, 'klient_aerzte')->withPivot('rolle', 'hauptarzt', 'gueltig_ab', 'gueltig_bis', 'bemerkung'); }

    public function vollname(): string
    {
        return trim(($this->anrede ? $this->anrede . ' ' : '') . ($this->vorname ? $this->vorname . ' ' : '') . $this->nachname);
    }
}
