<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EinsatzAktivitaet extends Model
{
    protected $table = 'einsatz_aktivitaeten';

    protected $fillable = [
        'einsatz_id', 'organisation_id', 'kategorie', 'aktivitaet', 'minuten',
    ];

    public static array $aktivitaeten = [
        'Grundpflege' => [
            'Körperwäsche',
            'Intimpflege',
            'Ankleiden / Auskleiden',
            'Mund- / Zahnpflege',
            'Rasur',
            'Haarpflege',
            'Nagelpflege',
        ],
        'Untersuchung / Behandlung' => [
            'Medikamentengabe',
            'Verbandswechsel',
            'Blutdruck / Vitalzeichen',
            'Injektion / Insulin',
            'Augentropfen',
            'Sondenpflege / PEG',
        ],
        'Mobilisation' => [
            'Aufstehen / Hinlegen',
            'Transfer (Bett → Stuhl)',
            'Gehübungen',
            'Lagerung / Positionswechsel',
        ],
        'Hauswirtschaft' => [
            'Zimmer aufräumen',
            'Wäsche',
            'Einkaufen',
            'Kochen / Mahlzeit',
            'Abwaschen',
        ],
        'Abklärung / Beratung' => [
            'Erstassessment',
            'Beratungsgespräch',
            'Angehörige informieren',
            'Arztgespräch / Koordination',
        ],
    ];

    public function einsatz()
    {
        return $this->belongsTo(Einsatz::class);
    }
}
