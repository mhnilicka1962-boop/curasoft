<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Krankenkasse extends Model
{
    protected $table = 'krankenkassen';
    protected $fillable = ['organisation_id', 'name', 'kuerzel', 'ean_nr', 'bag_nr', 'adresse', 'plz', 'ort', 'telefon', 'email', 'aktiv'];
    protected $casts = ['aktiv' => 'boolean'];

    public function klientVerknuepfungen() { return $this->hasMany(KlientKrankenkasse::class); }
}
