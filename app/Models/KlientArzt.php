<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KlientArzt extends Model
{
    protected $table = 'klient_aerzte';
    protected $fillable = ['klient_id', 'arzt_id', 'rolle', 'hauptarzt', 'gueltig_ab', 'gueltig_bis', 'bemerkung'];
    protected $casts = ['hauptarzt' => 'boolean', 'gueltig_ab' => 'date', 'gueltig_bis' => 'date'];

    public function klient() { return $this->belongsTo(Klient::class); }
    public function arzt()   { return $this->belongsTo(Arzt::class); }

    public static array $rollen = ['behandelnder' => 'Behandelnder Arzt', 'einweisender' => 'Einweisender Arzt', 'konsultierender' => 'Konsultierender Arzt'];
}
