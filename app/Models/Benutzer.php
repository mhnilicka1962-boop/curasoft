<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Benutzer extends Authenticatable
{
    use Notifiable;

    protected $table = 'benutzer';

    protected $fillable = [
        'organisation_id',
        'anrede', 'geschlecht', 'geburtsdatum', 'nationalitaet', 'zivilstand',
        'vorname', 'nachname',
        'strasse', 'plz', 'ort',
        'telefon', 'telefax',
        'email', 'email_privat',
        'password',
        'ahv_nr', 'iban', 'bank',
        'pensum', 'eintrittsdatum', 'austrittsdatum',
        'rolle', 'aktiv',
        'notizen',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'       => 'hashed',
            'aktiv'          => 'boolean',
            'geburtsdatum'   => 'date',
            'eintrittsdatum' => 'date',
            'austrittsdatum' => 'date',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->vorname . ' ' . $this->nachname;
    }

    public function qualifikationen()
    {
        return $this->belongsToMany(Qualifikation::class, 'benutzer_qualifikation')
            ->orderBy('sort_order');
    }

    public function klientZuweisungen()
    {
        return $this->hasMany(KlientBenutzer::class)->with('klient');
    }

    public function alterInJahren(): ?int
    {
        return $this->geburtsdatum?->age;
    }
}
