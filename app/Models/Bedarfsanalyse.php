<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bedarfsanalyse extends Model
{
    protected $table = 'bedarfsanalysen';

    protected $fillable = [
        'organisation_id', 'klient_id', 'erstellt_von', 'status', 'aktueller_schritt', 'abgeschlossen_am',
        'datum_analyse', 'ort_analyse', 'anrede', 'vorname', 'nachname', 'strasse', 'plz', 'ort',
        'telefon', 'mobile', 'geburtsdatum', 'heimatort', 'konfession', 'zivilstand', 'nationalitaet', 'ahv_nr',
        'ap1_name', 'ap1_vorname', 'ap1_strasse', 'ap1_plz', 'ap1_ort', 'ap1_beziehung', 'ap1_bemerkung',
        'ap1_telefon', 'ap1_mobile', 'ap1_vormund', 'ap1_erreichbarkeit', 'ap1_erreichbarkeit_von', 'ap1_erreichbarkeit_bis',
        'ap2_name', 'ap2_vorname', 'ap2_strasse', 'ap2_plz', 'ap2_ort', 'ap2_beziehung', 'ap2_bemerkung',
        'ap2_telefon', 'ap2_mobile', 'ap2_vormund', 'ap2_erreichbarkeit', 'ap2_erreichbarkeit_von', 'ap2_erreichbarkeit_bis',
        'kvg_krankenkasse_id', 'kvg_anschrift', 'vvg_vorhanden', 'vvg_deckungstyp', 'pflegeversicherung',
        'pflegeversicherung_name', 'zweite_krankenkasse_id', 'zweite_krankenkasse_anschrift', 'haushaltshilfe',
        'versicherung_bemerkungen', 'aufnahmegrund', 'hilflosenentschaedigung', 'rechnungsadresse', 'vorauszahlung',
        'zustaendiger_arzt', 'personen_haushalt', 'personen_betreuungsbed', 'gewicht_kg',
        'diagnosen_text', 'medikamente_liste', 'mobilitaet', 'hilfsmittel', 'hobbies', 'pflegestufe',
        'wunschkost', 'wunschkost_details', 'pflegedienst_aktuell', 'pflegedienst_name', 'pflegedienst_aufgaben',
        'pflegedienst_frequenz', 'pflegedienst_abbestellen', 'raucher',
        'wohntyp', 'anzahl_zimmer', 'lift', 'treppe', 'treppe_stufen', 'klinik', 'patientenverfuegung',
        'haustiere', 'haustiere_details', 'eintrittstermin',
    ];

    protected $casts = [
        'datum_analyse'    => 'date',
        'geburtsdatum'     => 'date',
        'eintrittstermin'  => 'date',
        'abgeschlossen_am' => 'datetime',
        'ap1_vormund'      => 'boolean',
        'ap2_vormund'      => 'boolean',
        'vvg_vorhanden'    => 'boolean',
        'pflegeversicherung'        => 'boolean',
        'vorauszahlung'             => 'boolean',
        'medikamente_liste'         => 'boolean',
        'wunschkost'                => 'boolean',
        'pflegedienst_aktuell'      => 'boolean',
        'pflegedienst_abbestellen'  => 'boolean',
        'raucher'                   => 'boolean',
        'lift'                      => 'boolean',
        'treppe'                    => 'boolean',
        'patientenverfuegung'       => 'boolean',
        'haustiere'                 => 'boolean',
    ];

    public function klient()
    {
        return $this->belongsTo(Klient::class);
    }

    public function ersteller()
    {
        return $this->belongsTo(Benutzer::class, 'erstellt_von');
    }

    public function schrittTitel(int $n): string
    {
        return [
            1 => 'Personalien & Ansprechpersonen',
            2 => 'Versicherung & Details',
            3 => 'Medizin & Pflegestufe',
            4 => 'Verpflegung & Pflegedienst',
            5 => 'Wohnverhältnisse & Abschluss',
        ][$n] ?? '';
    }

    public function anzeigeName(): string
    {
        if ($this->vorname || $this->nachname) {
            return trim(($this->vorname ?? '') . ' ' . ($this->nachname ?? ''));
        }
        return 'Neue Aufnahme #' . $this->id;
    }
}
