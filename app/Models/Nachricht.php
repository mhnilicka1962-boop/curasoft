<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nachricht extends Model
{
    protected $table = 'nachrichten';

    protected $fillable = [
        'absender_id', 'betreff', 'inhalt', 'referenz_typ', 'referenz_id',
    ];

    public function absender()
    {
        return $this->belongsTo(Benutzer::class, 'absender_id');
    }

    public function empfaenger()
    {
        return $this->hasMany(NachrichtEmpfaenger::class, 'nachricht_id');
    }

    public function istGelesenVon(int $benutzerId): bool
    {
        return $this->empfaenger()
            ->where('empfaenger_id', $benutzerId)
            ->whereNotNull('gelesen_am')
            ->exists();
    }

    /** Posteingang-Eintrag für einen bestimmten Benutzer */
    public function empfaengerEintrag(int $benutzerId): ?NachrichtEmpfaenger
    {
        return $this->empfaenger()
            ->where('empfaenger_id', $benutzerId)
            ->first();
    }
}
