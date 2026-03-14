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
            'Waschen im Bett',
            'Waschen am Lavabo',
            'Duschen',
            'Intimpflege',
            'An-/Auskleiden',
            'Mundpflege',
            'Rasur',
            'Nagelpflege',
            'Essen und Trinken',
            'Ausscheidung',
            'Beine einbinden',
            'Antithrombose Strümpfe',
            'Dekubitusprophylaxe',
            'Betten im Bett',
        ],
        'Mobilisation' => [
            'Mobilisation',
            'Lagern',
        ],
        'Untersuchung / Behandlung' => [
            'Vitalzeichen (Puls, BD, T, Gewicht)',
            'Blutzucker',
            'Medikamente',
            'Spritzen',
            'Verbandwechsel',
            'Inhalation',
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
