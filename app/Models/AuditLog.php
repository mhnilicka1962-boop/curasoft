<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'benutzer_id',
        'benutzer_name',
        'benutzer_email',
        'aktion',
        'modell_typ',
        'modell_id',
        'beschreibung',
        'alte_werte',
        'neue_werte',
        'ip_adresse',
        'user_agent',
    ];

    protected $casts = [
        'alte_werte' => 'array',
        'neue_werte' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Eintrag schreiben — zentrale Methode für alle Logging-Aufrufe.
     */
    public static function schreiben(
        string  $aktion,
        ?string $modellTyp    = null,
        ?int    $modellId     = null,
        ?string $beschreibung = null,
        ?array  $alteWerte    = null,
        ?array  $neueWerte    = null,
    ): void {
        try {
            $benutzer = Auth::user();

            static::create([
                'benutzer_id'    => $benutzer?->id,
                'benutzer_name'  => $benutzer ? ($benutzer->vorname . ' ' . $benutzer->nachname) : null,
                'benutzer_email' => $benutzer?->email,
                'aktion'         => $aktion,
                'modell_typ'     => $modellTyp,
                'modell_id'      => $modellId,
                'beschreibung'   => $beschreibung,
                'alte_werte'     => $alteWerte,
                'neue_werte'     => $neueWerte,
                'ip_adresse'     => Request::ip(),
                'user_agent'     => substr(Request::userAgent() ?? '', 0, 255),
            ]);
        } catch (\Throwable) {
            // Logging darf die Applikation nie zum Absturz bringen
        }
    }

    // Felder die NICHT geloggt werden sollen (Passwörter etc.)
    public static array $ausgeblendet = [
        'password', 'remember_token', 'updated_at', 'created_at',
    ];
}
