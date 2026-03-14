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
            'An-/Auskleiden',
            'Antithrombose Strümpfe',
            'Ausscheidung',
            'Beine einbinden',
            'Betten im Bett',
            'Dekubitusprophylaxe',
            'Duschen',
            'Essen und Trinken',
            'Grundpflege',
            'Intimpflege',
            'Lagern',
            'Medikamente abgeben',
            'Mobilisation',
            'Mundpflege',
            'Nagelpflege',
            'Rasur',
            'Waschen am Lavabo',
            'Waschen im Bett',
        ],
        'Untersuchung / Behandlung' => [
            'Blutzucker',
            'Inhalation',
            'Injektion subcutan',
            'Medikamente richten',
            'Verbandwechsel',
            'Vitalzeichen (Puls, BD, T, Gewicht)',
        ],
        'Hauswirtschaft' => [
            'Abklärung und Beratung HWL',
            'HWL-Leistungen',
        ],
        'Abklärung / Beratung' => [
            'Administration',
            'Bedarfsanalyse',
            'Beratungsgespräch',
            'Dokumentation',
        ],
    ];

    public function einsatz()
    {
        return $this->belongsTo(Einsatz::class);
    }
}
