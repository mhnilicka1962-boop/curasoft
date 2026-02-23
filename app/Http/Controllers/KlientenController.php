<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Arzt;
use App\Models\Benutzer;
use App\Models\Klient;
use App\Models\KlientAdresse;
use App\Models\KlientArzt;
use App\Models\KlientDiagnose;
use App\Models\KlientKontakt;
use App\Models\KlientKrankenkasse;
use App\Models\KlientBeitrag;
use App\Models\KlientPflegestufe;
use App\Models\Krankenkasse;
use App\Models\Leistungsart;
use App\Models\Region;
use Illuminate\Http\Request;

class KlientenController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    public function index(Request $request)
    {
        $query = Klient::where('organisation_id', $this->orgId())
            ->orderBy('nachname')->orderBy('vorname');

        if ($request->filled('suche')) {
            $s = $request->suche;
            $query->where(fn($q) => $q
                ->where('vorname', 'ilike', "%{$s}%")
                ->orWhere('nachname', 'ilike', "%{$s}%")
                ->orWhere('ort', 'ilike', "%{$s}%")
            );
        }

        $status = $request->input('status', 'aktiv');
        if ($status !== 'alle') {
            $query->where('aktiv', $status === 'aktiv');
        }

        $klienten = $query->paginate(25)->withQueryString();

        return view('klienten.index', compact('klienten'));
    }

    public function create()
    {
        $regionen   = Region::orderBy('kuerzel')->get();
        $mitarbeiter = Benutzer::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();
        return view('klienten.create', compact('regionen', 'mitarbeiter'));
    }

    public function store(Request $request)
    {
        $daten = $request->validate([
            'anrede'              => ['nullable', 'string', 'max:20'],
            'vorname'             => ['required', 'string', 'max:100'],
            'nachname'            => ['required', 'string', 'max:100'],
            'geburtsdatum'        => ['nullable', 'date'],
            'geschlecht'          => ['nullable', 'in:m,w,x'],
            'zivilstand'          => ['nullable', 'string', 'max:50'],
            'anzahl_kinder'       => ['nullable', 'integer', 'min:0'],
            'adresse'             => ['nullable', 'string', 'max:255'],
            'plz'                 => ['nullable', 'string', 'max:10'],
            'ort'                 => ['nullable', 'string', 'max:100'],
            'region_id'           => ['nullable', 'exists:regionen,id'],
            'zustaendig_id'       => ['nullable', 'exists:benutzer,id'],
            'datum_erstkontakt'   => ['nullable', 'date'],
            'einsatz_geplant_von' => ['nullable', 'date'],
            'einsatz_geplant_bis' => ['nullable', 'date'],
            'telefon'             => ['nullable', 'string', 'max:50'],
            'notfallnummer'       => ['nullable', 'string', 'max:50'],
            'email'               => ['nullable', 'email', 'max:255'],
            'ahv_nr'              => ['nullable', 'string', 'max:20'],
            'krankenkasse_name'   => ['nullable', 'string', 'max:255'],
            'krankenkasse_nr'     => ['nullable', 'string', 'max:50'],
            'zahlbar_tage'        => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $klient = Klient::create(array_merge($daten, [
            'organisation_id' => $this->orgId(),
            'aktiv'           => true,
        ]));

        return redirect()->route('klienten.show', $klient)
            ->with('erfolg', 'Klient wurde erfolgreich angelegt.');
    }

    public function schnellerfassung()
    {
        $mitarbeiter   = Benutzer::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();
        $regionen      = Region::orderBy('kuerzel')->get();
        $leistungsarten = \App\Models\Leistungsart::where('aktiv', true)->orderBy('bezeichnung')->get();
        return view('klienten.schnellerfassung', compact('mitarbeiter', 'regionen', 'leistungsarten'));
    }

    public function schnellSpeichern(Request $request)
    {
        $daten = $request->validate([
            'vorname'         => ['required', 'string', 'max:100'],
            'nachname'        => ['required', 'string', 'max:100'],
            'telefon'         => ['nullable', 'string', 'max:50'],
            'adresse'         => ['nullable', 'string', 'max:200'],
            'plz'             => ['nullable', 'string', 'max:20'],
            'ort'             => ['nullable', 'string', 'max:100'],
            'region_id'       => ['nullable', 'exists:regionen,id'],
            'benutzer_id'     => ['nullable', 'exists:benutzer,id'],
            'leistungsart_id' => ['nullable', 'exists:leistungsarten,id'],
            'zeit_von'        => ['nullable', 'date_format:H:i'],
            'zeit_bis'        => ['nullable', 'date_format:H:i'],
            'datum_start'     => ['nullable', 'date'],
            'datum_ende'      => ['nullable', 'date', 'after_or_equal:datum_start'],
            'wochentage'      => ['nullable', 'array'],
            'wochentage.*'    => ['integer', 'between:0,6'],
        ]);

        // Klient anlegen
        $klient = Klient::create([
            'organisation_id' => $this->orgId(),
            'vorname'         => $daten['vorname'],
            'nachname'        => $daten['nachname'],
            'telefon'         => $daten['telefon'] ?? null,
            'adresse'         => $daten['adresse'] ?? null,
            'plz'             => $daten['plz'] ?? null,
            'ort'             => $daten['ort'] ?? null,
            'region_id'       => $daten['region_id'] ?? null,
            'aktiv'           => true,
        ]);

        // Einsätze anlegen wenn Einsatzplan ausgefüllt
        $anzahl = 0;
        if (!empty($daten['benutzer_id']) && !empty($daten['leistungsart_id']) && !empty($daten['datum_start'])) {
            $wochentage = array_map('intval', $daten['wochentage'] ?? []);
            $serieId    = (string) \Illuminate\Support\Str::uuid();
            $current    = \Carbon\Carbon::parse($daten['datum_start']);
            $ende       = isset($daten['datum_ende']) ? \Carbon\Carbon::parse($daten['datum_ende']) : $current->copy()->addMonths(3);

            while ($current->lte($ende) && $anzahl < 365) {
                if (empty($wochentage) || in_array($current->dayOfWeek, $wochentage)) {
                    \App\Models\Einsatz::create([
                        'organisation_id' => $this->orgId(),
                        'klient_id'       => $klient->id,
                        'benutzer_id'     => $daten['benutzer_id'],
                        'leistungsart_id' => $daten['leistungsart_id'],
                        'region_id'       => $klient->region_id,
                        'datum'           => $current->format('Y-m-d'),
                        'zeit_von'        => $daten['zeit_von'] ?? null,
                        'zeit_bis'        => $daten['zeit_bis'] ?? null,
                        'status'          => 'geplant',
                        'serie_id'        => count($wochentage) ? $serieId : null,
                    ]);
                    $anzahl++;
                }
                $current->addDay();
            }
        }

        $meldung = "Klient {$klient->vorname} {$klient->nachname} angelegt" .
            ($anzahl ? " · {$anzahl} Einsatz" . ($anzahl !== 1 ? 'ätze' : '') . ' geplant' : '') . '.';

        return redirect()->route('klienten.show', $klient)->with('erfolg', $meldung);
    }

    public function show(Klient $klient)
    {
        $this->autorisiereZugriff($klient);

        AuditLog::schreiben(
            aktion:       'angezeigt',
            modellTyp:    'Klient',
            modellId:     $klient->id,
            beschreibung: "Klient \"{$klient->vorname} {$klient->nachname}\" angezeigt",
        );

        $leistungsarten = Leistungsart::where('aktiv', true)->orderBy('bezeichnung')->get();

        $mitarbeiter = (auth()->user()->rolle === 'admin')
            ? Benutzer::where('organisation_id', $this->orgId())
                ->where('aktiv', true)
                ->orderBy('nachname')
                ->get()
            : collect();

        return view('klienten.show', compact('klient', 'leistungsarten', 'mitarbeiter'));
    }

    public function edit(Klient $klient)
    {
        $this->autorisiereZugriff($klient);
        $regionen    = Region::orderBy('kuerzel')->get();
        $mitarbeiter = Benutzer::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();
        return view('klienten.edit', compact('klient', 'regionen', 'mitarbeiter'));
    }

    public function update(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);

        $daten = $request->validate([
            'anrede'              => ['nullable', 'string', 'max:20'],
            'vorname'             => ['required', 'string', 'max:100'],
            'nachname'            => ['required', 'string', 'max:100'],
            'geburtsdatum'        => ['nullable', 'date'],
            'geschlecht'          => ['nullable', 'in:m,w,x'],
            'zivilstand'          => ['nullable', 'string', 'max:50'],
            'anzahl_kinder'       => ['nullable', 'integer', 'min:0'],
            'adresse'             => ['nullable', 'string', 'max:255'],
            'plz'                 => ['nullable', 'string', 'max:10'],
            'ort'                 => ['nullable', 'string', 'max:100'],
            'region_id'           => ['nullable', 'exists:regionen,id'],
            'zustaendig_id'       => ['nullable', 'exists:benutzer,id'],
            'datum_erstkontakt'   => ['nullable', 'date'],
            'einsatz_geplant_von' => ['nullable', 'date'],
            'einsatz_geplant_bis' => ['nullable', 'date'],
            'telefon'             => ['nullable', 'string', 'max:50'],
            'notfallnummer'       => ['nullable', 'string', 'max:50'],
            'email'               => ['nullable', 'email', 'max:255'],
            'ahv_nr'              => ['nullable', 'string', 'max:20'],
            'krankenkasse_name'   => ['nullable', 'string', 'max:255'],
            'krankenkasse_nr'     => ['nullable', 'string', 'max:50'],
            'zahlbar_tage'        => ['nullable', 'integer', 'min:1', 'max:365'],
            'aktiv'               => ['boolean'],
        ]);

        $klient->update($daten);

        return redirect()->route('klienten.show', $klient)
            ->with('erfolg', 'Klient wurde gespeichert.');
    }

    public function destroy(Klient $klient)
    {
        $this->autorisiereZugriff($klient);

        // Nicht löschen — nur inaktivieren (medizinische Daten)
        $klient->update(['aktiv' => false]);

        return redirect()->route('klienten.index')
            ->with('erfolg', 'Klient wurde deaktiviert.');
    }

    public function qr(Klient $klient)
    {
        $this->autorisiereZugriff($klient);
        return view('klienten.qr', compact('klient'));
    }

    public function adresseSpeichern(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);

        $daten = $request->validate([
            'adressart'   => ['required', 'in:einsatzort,rechnung,notfall,korrespondenz'],
            'gueltig_ab'  => ['nullable', 'date'],
            'gueltig_bis' => ['nullable', 'date'],
            'firma'       => ['nullable', 'string', 'max:100'],
            'anrede'      => ['nullable', 'string', 'max:20'],
            'vorname'     => ['nullable', 'string', 'max:100'],
            'nachname'    => ['nullable', 'string', 'max:100'],
            'strasse'     => ['nullable', 'string', 'max:255'],
            'postfach'    => ['nullable', 'string', 'max:50'],
            'plz'         => ['nullable', 'string', 'max:10'],
            'ort'         => ['nullable', 'string', 'max:100'],
            'region_id'   => ['nullable', 'exists:regionen,id'],
            'telefon'     => ['nullable', 'string', 'max:50'],
            'telefax'     => ['nullable', 'string', 'max:50'],
            'email'       => ['nullable', 'email', 'max:255'],
        ]);

        $klient->adressen()->create($daten);

        return redirect()->route('klienten.show', $klient)
            ->with('erfolg', 'Adresse wurde hinzugefügt.');
    }

    public function adresseLoeschen(Klient $klient, KlientAdresse $adresse)
    {
        $this->autorisiereZugriff($klient);
        if ($adresse->klient_id !== $klient->id) abort(403);

        $adresse->delete();

        return redirect()->route('klienten.show', $klient)
            ->with('erfolg', 'Adresse wurde entfernt.');
    }

    public function arztSpeichern(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);
        $daten = $request->validate([
            'arzt_id'    => ['required', 'exists:aerzte,id'],
            'rolle'      => ['required', 'in:behandelnder,einweisender,konsultierender'],
            'hauptarzt'  => ['boolean'],
            'gueltig_ab' => ['nullable', 'date'],
            'bemerkung'  => ['nullable', 'string', 'max:500'],
        ]);
        if ($request->boolean('hauptarzt')) {
            KlientArzt::where('klient_id', $klient->id)->update(['hauptarzt' => false]);
        }
        KlientArzt::create([
            'klient_id'  => $klient->id,
            'arzt_id'    => $daten['arzt_id'],
            'rolle'      => $daten['rolle'],
            'hauptarzt'  => $request->boolean('hauptarzt'),
            'gueltig_ab' => $daten['gueltig_ab'] ?? null,
            'bemerkung'  => $daten['bemerkung'] ?? null,
        ]);
        return back()->with('erfolg', 'Arzt wurde hinzugefügt.');
    }

    public function arztEntfernen(Klient $klient, KlientArzt $klientArzt)
    {
        $this->autorisiereZugriff($klient);
        if ($klientArzt->klient_id !== $klient->id) abort(403);
        $klientArzt->delete();
        return back()->with('erfolg', 'Arzt wurde entfernt.');
    }

    public function krankenkasseSpeichern(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);
        $daten = $request->validate([
            'krankenkasse_id'    => ['required', 'exists:krankenkassen,id'],
            'versicherungs_typ'  => ['required', 'in:kvg,vvg'],
            'deckungstyp'        => ['nullable', 'in:allgemein,halbprivat,privat'],
            'versichertennummer' => ['nullable', 'string', 'max:50'],
            'kartennummer'       => ['nullable', 'string', 'max:50'],
            'gueltig_ab'         => ['nullable', 'date'],
            'gueltig_bis'        => ['nullable', 'date'],
            'bemerkung'          => ['nullable', 'string', 'max:500'],
        ]);
        $klient->krankenkassen()->create(array_merge($daten, ['aktiv' => true]));
        return back()->with('erfolg', 'Krankenkasse wurde hinzugefügt.');
    }

    public function krankenkasseEntfernen(Klient $klient, KlientKrankenkasse $klientKk)
    {
        $this->autorisiereZugriff($klient);
        if ($klientKk->klient_id !== $klient->id) abort(403);
        $klientKk->delete();
        return back()->with('erfolg', 'Krankenkasse wurde entfernt.');
    }

    public function kontaktSpeichern(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);
        $daten = $request->validate([
            'rolle'               => ['required', 'in:angehoerig,gesetzlicher_vertreter,rechnungsempfaenger,notfallkontakt,sonstige'],
            'anrede'              => ['nullable', 'string', 'max:20'],
            'vorname'             => ['nullable', 'string', 'max:100'],
            'nachname'            => ['required', 'string', 'max:100'],
            'firma'               => ['nullable', 'string', 'max:100'],
            'beziehung'           => ['nullable', 'string', 'max:100'],
            'adresse'             => ['nullable', 'string', 'max:255'],
            'plz'                 => ['nullable', 'string', 'max:10'],
            'ort'                 => ['nullable', 'string', 'max:100'],
            'telefon'             => ['nullable', 'string', 'max:50'],
            'telefon_mobil'       => ['nullable', 'string', 'max:50'],
            'email'               => ['nullable', 'email', 'max:255'],
            'bevollmaechtigt'     => ['boolean'],
            'rechnungen_erhalten' => ['boolean'],
            'bemerkung'           => ['nullable', 'string', 'max:500'],
        ]);
        $klient->kontakte()->create(array_merge($daten, ['aktiv' => true]));
        return back()->with('erfolg', 'Kontakt wurde hinzugefügt.');
    }

    public function kontaktEntfernen(Klient $klient, KlientKontakt $kontakt)
    {
        $this->autorisiereZugriff($klient);
        if ($kontakt->klient_id !== $klient->id) abort(403);
        $kontakt->delete();
        return back()->with('erfolg', 'Kontakt wurde entfernt.');
    }

    public function beitragSpeichern(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);
        $daten = $request->validate([
            'gueltig_ab'               => ['required', 'date'],
            'ansatz_kunde'             => ['required', 'numeric', 'min:0'],
            'limit_restbetrag_prozent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ansatz_spitex'            => ['nullable', 'numeric', 'min:0'],
            'kanton_abrechnung'        => ['nullable', 'numeric', 'min:0'],
        ]);
        $klient->beitraege()->create(array_merge($daten, ['erfasst_von' => auth()->id()]));
        return back()->with('erfolg', 'Beitrag wurde erfasst.');
    }

    public function beitragLoeschen(Klient $klient, KlientBeitrag $beitrag)
    {
        $this->autorisiereZugriff($klient);
        if ($beitrag->klient_id !== $klient->id) abort(403);
        $beitrag->delete();
        return back()->with('erfolg', 'Beitrag wurde entfernt.');
    }

    public function pflegestufeSpeichern(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);
        $daten = $request->validate([
            'instrument'        => ['required', 'in:besa,rai_hc,ibm,manuell'],
            'stufe'             => ['required', 'integer', 'min:0', 'max:12'],
            'punkte'            => ['nullable', 'numeric', 'min:0'],
            'einstufung_datum'  => ['required', 'date'],
            'naechste_pruefung' => ['nullable', 'date'],
            'bemerkung'         => ['nullable', 'string', 'max:500'],
        ]);
        $klient->pflegestufen()->create(array_merge($daten, ['erfasst_von' => auth()->id()]));
        return back()->with('erfolg', 'Pflegestufe wurde erfasst.');
    }

    public function diagnoseSpeichern(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);
        $daten = $request->validate([
            'icd10_code'        => ['required', 'string', 'max:20'],
            'icd10_bezeichnung' => ['required', 'string', 'max:255'],
            'diagnose_typ'      => ['required', 'in:haupt,neben,einweisung'],
            'arzt_id'           => ['nullable', 'exists:aerzte,id'],
            'datum_gestellt'    => ['nullable', 'date'],
            'datum_bis'         => ['nullable', 'date'],
            'bemerkung'         => ['nullable', 'string', 'max:500'],
        ]);
        $klient->diagnosen()->create(array_merge($daten, ['erfasst_von' => auth()->id(), 'aktiv' => true]));
        return back()->with('erfolg', 'Diagnose wurde hinzugefügt.');
    }

    public function diagnoseEntfernen(Klient $klient, KlientDiagnose $diagnose)
    {
        $this->autorisiereZugriff($klient);
        if ($diagnose->klient_id !== $klient->id) abort(403);
        $diagnose->update(['aktiv' => false]);
        return back()->with('erfolg', 'Diagnose wurde deaktiviert.');
    }

    private function autorisiereZugriff(Klient $klient): void
    {
        if ($klient->organisation_id !== $this->orgId()) {
            abort(403);
        }
    }
}
