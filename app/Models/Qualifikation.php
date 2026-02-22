<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qualifikation extends Model
{
    protected $table = 'qualifikationen';

    protected $fillable = ['bezeichnung', 'kuerzel', 'sort_order', 'aktiv'];

    protected $casts = ['aktiv' => 'boolean'];

    public function benutzer()
    {
        return $this->belongsToMany(Benutzer::class, 'benutzer_qualifikation');
    }
}
