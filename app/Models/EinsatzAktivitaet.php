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
            'Grundpflege',
            'Waschen im Bett',
            'Waschen am Lavabo',
            'Duschen',
            'Intimpflege',
            'Mobilisation',
            'Lagern',
            'Ausscheidung',
            'Beine einbinden',
            'Antithrombose Strümpfe',
            'Betten im Bett',
            'Dekubitusprophylaxe',
            'An-/Auskleiden',
            'Essen und Trinken',
            'Mundpflege',
            'Rasur',
            'Nagelpflege',
        ],
        'Untersuchung / Behandlung' => [
            'Vitalzeichen (Puls, BD, T, Gewicht)',
            'Blutzucker',
            'Inhalation',
            'Medikamente',
            'Verbandwechsel',
        ],
        'Hauswirtschaft' => [
            'HWL-Leistungen',
            'Abklärung und Beratung HWL',
        ],
        'Abklärung / Beratung' => [
            'Bedarfsanalyse',
            'Beratungsgespräch',
            'Dokumentation',
            'Administration',
        ],
    ];

    public function einsatz()
    {
        return $this->belongsTo(Einsatz::class);
    }
}
