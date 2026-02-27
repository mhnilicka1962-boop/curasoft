<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rechnung extends Model
{
    protected $table = 'rechnungen';

    public static array $typen = [
        'kombiniert' => 'Kombiniert (KK + Patient)',
        'kvg'        => 'KVG → Krankenkasse',
        'klient'     => 'Klient (Selbstbehalt)',
        'gemeinde'   => 'Gemeinde / Kanton',
    ];

    protected $fillable = [
        'organisation_id', 'klient_id', 'rechnungsnummer',
        'periode_von', 'periode_bis', 'rechnungsdatum',
        'betrag_patient', 'betrag_kk', 'betrag_total',
        'status', 'rechnungstyp', 'rechnungslauf_id', 'pdf_pfad',
        'email_versand_datum', 'email_versand_an', 'email_fehler',
        'bexio_rechnung_id', 'bexio_bezahlt_am',
    ];

    protected $casts = [
        'periode_von'         => 'date',
        'periode_bis'         => 'date',
        'rechnungsdatum'      => 'date',
        'email_versand_datum' => 'datetime',
        'bexio_bezahlt_am'    => 'datetime',
        'betrag_patient'      => 'decimal:2',
        'betrag_kk'           => 'decimal:2',
        'betrag_total'        => 'decimal:2',
    ];

    public function klient()     { return $this->belongsTo(Klient::class); }
    public function positionen() { return $this->hasMany(RechnungsPosition::class); }
    public function lauf()       { return $this->belongsTo(Rechnungslauf::class, 'rechnungslauf_id'); }

    public function berechneTotale(): void
    {
        $this->betrag_patient = $this->positionen->sum('betrag_patient');
        $this->betrag_kk      = $this->positionen->sum('betrag_kk');
        $this->betrag_total   = $this->betrag_patient + $this->betrag_kk;
        $this->save();
    }

    public static function naechsteNummer(int $orgId): string
    {
        $jahr  = date('Y');
        $letzte = static::where('organisation_id', $orgId)
            ->whereYear('rechnungsdatum', $jahr)
            ->orderByDesc('id')
            ->value('rechnungsnummer');

        $naechste = $letzte
            ? ((int) substr($letzte, -4)) + 1
            : 1;

        return 'RE-' . $jahr . '-' . str_pad($naechste, 4, '0', STR_PAD_LEFT);
    }

    public function statusBadge(): string
    {
        return match($this->status) {
            'entwurf'   => '<span class="badge badge-grau">Entwurf</span>',
            'gesendet'  => '<span class="badge badge-info">Gesendet</span>',
            'bezahlt'   => '<span class="badge badge-erfolg">Bezahlt</span>',
            'storniert' => '<span class="badge badge-fehler">Storniert</span>',
            default     => '',
        };
    }

    public function typBadge(): string
    {
        return match($this->rechnungstyp ?? 'kombiniert') {
            'kvg'        => '<span class="badge badge-info">KVG → KK</span>',
            'klient'     => '<span class="badge badge-erfolg">Klient</span>',
            'gemeinde'   => '<span class="badge" style="background:#fff3cd;color:#856404;">Gemeinde</span>',
            default      => '<span class="badge badge-grau">Kombiniert</span>',
        };
    }
}
