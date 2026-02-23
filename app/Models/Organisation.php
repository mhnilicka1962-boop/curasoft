<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    protected $table = 'organisationen';

    protected $fillable = [
        'name', 'adresse', 'postfach', 'adresszusatz', 'plz', 'ort', 'kanton',
        'telefon', 'fax', 'email', 'website',
        'zsr_nr', 'mwst_nr', 'abrechnungsnummer',
        'bank', 'bankadresse', 'iban', 'postcheckkonto',
        'logo_pfad', 'theme_layout', 'theme_farbe_primaer',
        'druck_mit_firmendaten', 'rechnungsadresse_position', 'logo_ausrichtung',
        'aktiv',
        'bexio_api_key', 'bexio_mandant_id',
    ];

    protected $casts = [
        'aktiv'               => 'boolean',
        'druck_mit_firmendaten' => 'boolean',
        'bexio_api_key'       => 'encrypted', // nDSG: API-Schlüssel verschlüsselt in DB
    ];

    public function regionen()
    {
        return $this->hasMany(OrganisationRegion::class, 'organisation_id');
    }

    public function aktiveRegionen()
    {
        return $this->hasMany(OrganisationRegion::class, 'organisation_id')
            ->where('aktiv', true)
            ->with('region');
    }

    /** Abrechnungsdaten für einen bestimmten Kanton (mit Fallback auf Hauptdaten) */
    public function datenFuerRegion(int $regionId): array
    {
        $kantonal = $this->regionen()
            ->where('region_id', $regionId)
            ->where('aktiv', true)
            ->first();

        return [
            'zsr_nr'         => ($kantonal?->zsr_nr        ?: $this->zsr_nr) ?? '',
            'bank'           => ($kantonal?->bank          ?: $this->bank) ?? '',
            'bankadresse'    => ($kantonal?->bankadresse   ?: $this->bankadresse) ?? '',
            'iban'           => ($kantonal?->iban          ?: $this->iban) ?? '',
            'postcheckkonto' => ($kantonal?->postcheckkonto ?: $this->postcheckkonto) ?? '',
            'esr'            => $kantonal?->esr_teilnehmernr ?? '',
            'qr_iban'        => $kantonal?->qr_iban ?? '',
        ];
    }
}
