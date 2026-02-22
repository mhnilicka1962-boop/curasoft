<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KlientKontakt extends Model
{
    protected $table = 'klient_kontakte';
    protected $fillable = [
        'klient_id', 'rolle', 'anrede', 'vorname', 'nachname', 'firma', 'beziehung',
        'adresse', 'plz', 'ort', 'region_id', 'telefon', 'telefon_mobil', 'email',
        'bevollmaechtigt', 'rechnungen_erhalten', 'aktiv', 'bemerkung',
    ];
    protected $casts = ['bevollmaechtigt' => 'boolean', 'rechnungen_erhalten' => 'boolean', 'aktiv' => 'boolean'];

    public function klient() { return $this->belongsTo(Klient::class); }
    public function region() { return $this->belongsTo(Region::class); }

    public static array $rollen = [
        'angehoerig'             => 'Angehöriger',
        'gesetzlicher_vertreter' => 'Gesetzl. Vertreter',
        'rechnungsempfaenger'    => 'Rechnungsempfänger',
        'notfallkontakt'         => 'Notfallkontakt',
        'sonstige'               => 'Sonstige',
    ];

    public function vollname(): string
    {
        return trim(($this->anrede ? $this->anrede . ' ' : '') . ($this->vorname ? $this->vorname . ' ' : '') . $this->nachname);
    }
}
