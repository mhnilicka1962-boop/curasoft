<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KlientKrankenkasse extends Model
{
    protected $table = 'klient_krankenkassen';
    protected $fillable = [
        'klient_id', 'krankenkasse_id', 'versicherungs_typ', 'tiers_payant', 'deckungstyp',
        'versichertennummer', 'kartennummer', 'gueltig_ab', 'gueltig_bis', 'aktiv', 'bemerkung',
    ];
    protected $casts = ['gueltig_ab' => 'date', 'gueltig_bis' => 'date', 'aktiv' => 'boolean', 'tiers_payant' => 'boolean'];

    public function klient()       { return $this->belongsTo(Klient::class); }
    public function krankenkasse() { return $this->belongsTo(Krankenkasse::class); }

    public static array $versicherungsTypen = ['kvg' => 'KVG (Obligatorisch)', 'vvg' => 'VVG (Zusatz)'];
    public static array $deckungstypen = ['allgemein' => 'Allgemein', 'halbprivat' => 'Halbprivat', 'privat' => 'Privat'];

    public function typLabel(): string { return self::$versicherungsTypen[$this->versicherungs_typ] ?? $this->versicherungs_typ; }
    public function deckungLabel(): string { return self::$deckungstypen[$this->deckungstyp] ?? $this->deckungstyp; }
}
