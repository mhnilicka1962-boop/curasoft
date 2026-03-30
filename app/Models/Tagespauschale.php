<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Tagespauschale extends Model
{
    protected $table = 'tagespauschalen';

    protected $fillable = [
        'organisation_id', 'klient_id', 'rechnungstyp',
        'datum_von', 'datum_bis', 'auto_verlaengern', 'ansatz', 'text', 'erstellt_von',
    ];

    protected $casts = [
        'datum_von'        => 'date',
        'datum_bis'        => 'date',
        'ansatz'           => 'decimal:4',
        'auto_verlaengern' => 'boolean',
    ];

    public function klient()      { return $this->belongsTo(Klient::class); }
    public function organisation(){ return $this->belongsTo(Organisation::class); }
    public function ersteller()   { return $this->belongsTo(Benutzer::class, 'erstellt_von'); }
    public function einsaetze()   { return $this->hasMany(Einsatz::class); }

    public function istAktiv(): bool
    {
        return $this->datum_von->lte(today())
            && (!$this->datum_bis || $this->datum_bis->gt(today()));
    }

    public function istGeplant(): bool
    {
        return $this->datum_von->gt(today());
    }

    public function istBeendet(): bool
    {
        return $this->datum_bis !== null && $this->datum_bis->lt(today());
    }

    /**
     * Generiert fehlende Einsätze vom letzten vorhandenen bis zum Horizont.
     * Startet bei datum_von wenn noch keine Einsätze existieren (inklusive Vergangenheit).
     */
    public function generiereFehlende(Carbon $horizon): int
    {
        $zustaendigId = $this->klient?->zustaendig_id ?? $this->erstellt_von;

        // Effective end: min(datum_bis, horizon)
        $bis = $this->datum_bis
            ? ($this->datum_bis->lt($horizon) ? $this->datum_bis : $horizon)
            : $horizon;

        // Letzten vorhandenen Einsatz finden
        $letzter = $this->einsaetze()->orderByDesc('datum')->value('datum');
        $ab = $letzter
            ? Carbon::parse($letzter)->addDay()
            : $this->datum_von->copy();

        if ($ab->gt($bis)) return 0;

        $anzahl  = 0;
        $current = $ab->copy()->startOfDay();

        while ($current->lte($bis)) {
            Einsatz::create([
                'organisation_id'   => $this->organisation_id,
                'klient_id'         => $this->klient_id,
                'benutzer_id'       => $zustaendigId,
                'tagespauschale_id' => $this->id,
                'datum'             => $current->format('Y-m-d'),
                'datum_bis'         => $current->format('Y-m-d'),
                'verrechnet'        => false,
                'status'            => $current->lt(today()) ? 'abgeschlossen' : 'geplant',
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
     * Anzahl bereits verrechneter Einsätze.
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
     * Prüft ob ein Zeitraum mit einer anderen Tagespauschale desselben Klienten überlappt.
     * Unterstützt null datum_bis (= kein Enddatum, läuft unbegrenzt).
     */
    public static function hatUeberlappung(
        int $klientId,
        int $organisationId,
        string $datumVon,
        ?string $datumBis,
        ?int $excludeId = null
    ): bool {
        return static::where('klient_id', $klientId)
            ->where('organisation_id', $organisationId)
            // Bestehende muss vor dem neuen Enddatum beginnen (null = unbegrenzt → überlappend mit allem)
            ->when($datumBis !== null, fn($q) => $q->where('datum_von', '<=', $datumBis))
            // Bestehende muss nach dem neuen Startdatum enden (null = unbegrenzt → immer überlappend)
            ->where(fn($q) => $q->whereNull('datum_bis')->orWhere('datum_bis', '>=', $datumVon))
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
        if (!$this->datum_bis) return 0;
        return $this->datum_von->diffInDays($this->datum_bis) + 1;
    }
}
