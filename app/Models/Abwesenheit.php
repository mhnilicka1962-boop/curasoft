<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abwesenheit extends Model
{
    protected $table = 'abwesenheiten';

    protected $fillable = ['organisation_id', 'benutzer_id', 'vertretung_id', 'datum_von', 'datum_bis'];

    protected $casts = ['datum_von' => 'date', 'datum_bis' => 'date'];

    public function benutzer()
    {
        return $this->belongsTo(Benutzer::class);
    }

    public function vertretung()
    {
        return $this->belongsTo(Benutzer::class, 'vertretung_id');
    }

    public function offeneEinsaetze(): int
    {
        return Einsatz::where('organisation_id', $this->organisation_id)
            ->where('benutzer_id', $this->benutzer_id)
            ->whereBetween('datum', [$this->datum_von, $this->datum_bis])
            ->where('status', 'geplant')
            ->count();
    }
}
