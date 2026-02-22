<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KlientPflegestufe extends Model
{
    protected $table = 'klient_pflegestufen';
    protected $fillable = ['klient_id', 'erfasst_von', 'instrument', 'stufe', 'punkte', 'einstufung_datum', 'naechste_pruefung', 'bemerkung'];
    protected $casts = ['einstufung_datum' => 'date', 'naechste_pruefung' => 'date', 'punkte' => 'decimal:2'];

    public function klient()     { return $this->belongsTo(Klient::class); }
    public function erfasstVon() { return $this->belongsTo(Benutzer::class, 'erfasst_von'); }

    public static array $instrumente = ['besa' => 'BESA', 'rai_hc' => 'RAI-HC', 'ibm' => 'IBM', 'manuell' => 'Manuell'];
}
