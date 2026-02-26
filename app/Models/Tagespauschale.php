<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Tagespauschale extends Model
{
    protected $table = 'tagespauschalen';

    protected $fillable = [
        'organisation_id', 'klient_id', 'rechnungstyp',
        'datum_von', 'datum_bis', 'ansatz', 'text', 'erstellt_von',
    ];

    protected $casts = [
        'datum_von' => 'date',
        'datum_bis' => 'date',
        'ansatz'    => 'decimal:4',
    ];

    public function klient()      { return $this->belongsTo(Klient::class); }
    public function organisation(){ return $this->belongsTo(Organisation::class); }
    public function ersteller()   { return $this->belongsTo(Benutzer::class, 'erstellt_von'); }
    public function einsaetze()   { return $this->hasMany(Einsatz::class); }

    /**
     * Generiert 1 Einsatz pro Tag für den definierten Zeitraum.
     */
    public function generiereEinsaetze(): int
    {
        $current = $this->datum_von->copy();
        $bis     = $this->datum_bis->copy();
        $anzahl  = 0;

        while ($current <= $bis) {
            Einsatz::create([
                'organisation_id'   => $this->organisation_id,
                'klient_id'         => $this->klient_id,
                'benutzer_id'       => $this->erstellt_von,
                'tagespauschale_id' => $this->id,
                'datum'             => $current->copy(),
                'datum_bis'         => $current->copy(),
                'verrechnet'        => false,
                'status'            => 'abgeschlossen',
            ]);
            $current->addDay();
            $anzahl++;
        }

        return $anzahl;
    }

    /**
     * Löscht noch nicht verrechnete Einsätze ab einem bestimmten Datum.
     */
    public function loescheZukuenftigeEinsaetze(Carbon $ab): int
    {
        return $this->einsaetze()
            ->where('datum', '>=', $ab)
            ->where('verrechnet', false)
            ->delete();
    }

    /**
     * Anzahl bereits verrechneter Einsätze — zeigt ob Tagespauschale aktiv genutzt wurde.
     */
    public function anzahlVerrechnet(): int
    {
        return $this->einsaetze()->where('verrechnet', true)->count();
    }

    /**
     * Prüft ob die Tagespauschale bereits (teilweise) verrechnet wurde.
     */
    public function istVerrechnet(): bool
    {
        return $this->einsaetze()->where('verrechnet', true)->exists();
    }

    /**
     * Prüft ob ein gegebener Zeitraum mit einer anderen Tagespauschale
     * desselben Klienten überlappt (exkl. dieser Instanz).
     */
    public static function hatUeberlappung(
        int $klientId,
        int $organisationId,
        string $datumVon,
        string $datumBis,
        ?int $excludeId = null
    ): bool {
        return static::where('klient_id', $klientId)
            ->where('organisation_id', $organisationId)
            ->where('datum_von', '<=', $datumBis)
            ->where('datum_bis', '>=', $datumVon)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function rechnungstypLabel(): string
    {
        return match($this->rechnungstyp) {
            'kvg'     => 'KVG',
            'klient'  => 'Klient',
            'gemeinde'=> 'Gemeinde',
            default   => $this->rechnungstyp,
        };
    }

    public function anzahlTage(): int
    {
        return $this->datum_von->diffInDays($this->datum_bis) + 1;
    }
}
