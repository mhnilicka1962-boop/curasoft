<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    protected $table = 'rapporte';
    protected $fillable = ['organisation_id', 'klient_id', 'benutzer_id', 'einsatz_id', 'datum', 'zeit_von', 'zeit_bis', 'inhalt', 'rapport_typ', 'vertraulich'];
    protected $casts = ['datum' => 'date', 'vertraulich' => 'boolean'];

    public function klient()  { return $this->belongsTo(Klient::class); }
    public function benutzer(){ return $this->belongsTo(Benutzer::class); }
    public function einsatz() { return $this->belongsTo(Einsatz::class); }

    public static array $typen = [
        'pflege'      => 'Pflegerapport',
        'verlauf'     => 'Verlaufsbericht',
        'information' => 'Information',
        'zwischenfall'=> 'Zwischenfall',
        'medikament'  => 'Medikament',
    ];
}
