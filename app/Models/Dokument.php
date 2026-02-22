<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Dokument extends Model
{
    protected $table = 'dokumente';
    protected $fillable = [
        'organisation_id', 'klient_id', 'hochgeladen_von', 'dokument_typ',
        'bezeichnung', 'dateiname', 'dateipfad', 'mime_type', 'groesse_bytes', 'vertraulich',
    ];
    protected $casts = ['vertraulich' => 'boolean'];

    public function klient()        { return $this->belongsTo(Klient::class); }
    public function hochgeladenVon(){ return $this->belongsTo(Benutzer::class, 'hochgeladen_von'); }

    public static array $typen = [
        'pflegeplanung'  => 'Pflegeplanung',
        'vertrag'        => 'Vertrag',
        'vollmacht'      => 'Vollmacht / Beistandschaft',
        'arztzeugnis'    => 'Arztzeugnis',
        'bericht'        => 'Bericht',
        'rechnung_kopie' => 'Rechnungskopie',
        'sonstiges'      => 'Sonstiges',
    ];

    public function groesseFormatiert(): string
    {
        if (!$this->groesse_bytes) return '—';
        if ($this->groesse_bytes < 1024) return $this->groesse_bytes . ' B';
        if ($this->groesse_bytes < 1048576) return round($this->groesse_bytes / 1024, 1) . ' KB';
        return round($this->groesse_bytes / 1048576, 1) . ' MB';
    }
}
