<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganisationRegion extends Model
{
    protected $table = 'organisations_regionen';

    protected $fillable = [
        'organisation_id', 'region_id', 'aktiv',
        'zsr_nr',
        'iban', 'postcheckkonto', 'bank', 'bankadresse',
        'esr_teilnehmernr', 'qr_iban', 'bemerkung',
    ];

    protected $casts = [
        'aktiv' => 'boolean',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
}
