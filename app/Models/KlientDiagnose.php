<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KlientDiagnose extends Model
{
    protected $table = 'klient_diagnosen';
    protected $fillable = [
        'klient_id', 'erfasst_von', 'arzt_id', 'icd10_code', 'icd10_bezeichnung',
        'diagnose_typ', 'datum_gestellt', 'datum_bis', 'aktiv', 'bemerkung',
    ];
    protected $casts = ['datum_gestellt' => 'date', 'datum_bis' => 'date', 'aktiv' => 'boolean'];

    public function klient()     { return $this->belongsTo(Klient::class); }
    public function erfasstVon() { return $this->belongsTo(Benutzer::class, 'erfasst_von'); }
    public function arzt()       { return $this->belongsTo(Arzt::class); }

    public static array $typen = ['haupt' => 'Hauptdiagnose', 'neben' => 'Nebendiagnose', 'einweisung' => 'Einweisungsdiagnose'];
}
