<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KlientBenutzer extends Model
{
    protected $table = 'klient_benutzer';

    protected $fillable = ['klient_id', 'benutzer_id', 'rolle', 'aktiv', 'bemerkung'];

    protected $casts = ['aktiv' => 'boolean'];

    public static array $rollen = [
        'hauptbetreuer' => 'Hauptbetreuer',
        'betreuer'      => 'Betreuer',
        'vertretung'    => 'Vertretung',
    ];

    public function klient()   { return $this->belongsTo(Klient::class); }
    public function benutzer() { return $this->belongsTo(Benutzer::class); }
}
