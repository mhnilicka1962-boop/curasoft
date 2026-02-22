<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    protected $table = 'touren';
    protected $fillable = ['organisation_id', 'benutzer_id', 'datum', 'bezeichnung', 'status', 'start_zeit', 'end_zeit', 'bemerkung'];
    protected $casts = ['datum' => 'date'];

    public function benutzer()  { return $this->belongsTo(Benutzer::class); }
    public function einsaetze() { return $this->hasMany(Einsatz::class)->orderBy('tour_reihenfolge'); }
}
