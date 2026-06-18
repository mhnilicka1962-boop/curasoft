<?php

namespace App\Http\Controllers;

use App\Models\Bedarfsanalyse;
use App\Models\Klient;
use App\Models\Krankenkasse;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BedarfsanalyseController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    public function index()
    {
        $entwuerfe = Bedarfsanalyse::where('organisation_id', $this->orgId())
            ->where('status', 'entwurf')
            ->with('ersteller')
            ->orderByDesc('updated_at')
            ->get();

        $abgeschlossene = Bedarfsanalyse::where('organisation_id', $this->orgId())
            ->where('status', 'abgeschlossen')
            ->with(['ersteller', 'klient'])
            ->orderByDesc('abgeschlossen_am')
            ->limit(30)
            ->get();

        return view('bedarfsanalysen.index', compact('entwuerfe', 'abgeschlossene'));
    }

    public function store(Request $request)
    {
        $analyse = Bedarfsanalyse::create([
            'organisation_id'   => $this->orgId(),
            'erstellt_von'      => auth()->id(),
            'status'            => 'entwurf',
            'aktueller_schritt' => 1,
            'datum_analyse'     => now()->toDateString(),
        ]);

        return redirect()->route('bedarfsanalysen.schritt', ['analyse' => $analyse->id, 'schritt' => 1]);
    }

    public function schritt(Bedarfsanalyse $analyse, int $schritt)
    {
        abort_if($analyse->organisation_id !== $this->orgId(), 403);
        abort_if($schritt < 1 || $schritt > 5, 404);

        $regionen     = Region::orderBy('bezeichnung')->get();
        $krankenkassen = Krankenkasse::where('organisation_id', $this->orgId())
            ->where('aktiv', true)->orderBy('name')->get();

        return view('bedarfsanalysen.wizard', compact('analyse', 'schritt', 'regionen', 'krankenkassen'));
    }

    public function schrittSpeichern(Request $request, Bedarfsanalyse $analyse, int $schritt)
    {
        abort_if($analyse->organisation_id !== $this->orgId(), 403);

        $felder     = $this->felderFuerSchritt($schritt);
        $daten      = $request->only($felder);
        $checkboxen = $this->checkboxenFuerSchritt($schritt);

        foreach ($checkboxen as $cb) {
            $daten[$cb] = $request->boolean($cb);
        }

        $neuerSchritt = max($analyse->aktueller_schritt, $schritt < 5 ? $schritt + 1 : 5);
        $analyse->update(array_merge($daten, ['aktueller_schritt' => $neuerSchritt]));

        if ($request->ajax()) {
            return response()->json(['ok' => true]);
        }

        if ($schritt < 5) {
            return redirect()->route('bedarfsanalysen.schritt', ['analyse' => $analyse->id, 'schritt' => $schritt + 1]);
        }

        return redirect()->route('bedarfsanalysen.abschliessen.form', $analyse);
    }

    public function abschliessenForm(Bedarfsanalyse $analyse)
    {
        abort_if($analyse->organisation_id !== $this->orgId(), 403);

        if ($analyse->status === 'abgeschlossen' && $analyse->klient_id) {
            return redirect()->route('klienten.show', $analyse->klient_id);
        }

        $regionen = Region::orderBy('bezeichnung')->get();
        return view('bedarfsanalysen.abschliessen', compact('analyse', 'regionen'));
    }

    public function abschliessen(Request $request, Bedarfsanalyse $analyse)
    {
        abort_if($analyse->organisation_id !== $this->orgId(), 403);

        if ($analyse->status === 'abgeschlossen') {
            return redirect()->route('klienten.show', $analyse->klient_id);
        }

        $request->validate([
            'region_id'    => 'required|exists:regionen,id',
            'rechnungstyp' => 'required|in:kombiniert,tiers_garant,tiers_payant',
        ]);

        DB::transaction(function () use ($analyse, $request) {
            $klient = Klient::create([
                'organisation_id'          => $this->orgId(),
                'anrede'                   => $analyse->anrede,
                'vorname'                  => $analyse->vorname ?? '—',
                'nachname'                 => $analyse->nachname ?? '—',
                'geburtsdatum'             => $analyse->geburtsdatum,
                'adresse'                  => $analyse->strasse,
                'plz'                      => $analyse->plz,
                'ort'                      => $analyse->ort,
                'telefon'                  => $analyse->telefon,
                'ahv_nr'                   => $analyse->ahv_nr,
                'zivilstand'               => $analyse->zivilstand,
                'notfallnummer'            => $analyse->ap1_telefon,
                'region_id'                => $request->region_id,
                'rechnungstyp'             => $request->rechnungstyp,
                'aktiv'                    => true,
                'klient_typ'               => 'patient',
                'datum_erstkontakt'        => $analyse->datum_analyse,
                'einsatz_geplant_von'      => $analyse->eintrittstermin,
                // neue Felder
                'mobile'                   => $analyse->mobile,
                'heimatort'                => $analyse->heimatort,
                'konfession'               => $analyse->konfession,
                'nationalitaet'            => $analyse->nationalitaet,
                'gewicht_kg'               => $analyse->gewicht_kg,
                'mobilitaet'               => $analyse->mobilitaet,
                'hilfsmittel'              => $analyse->hilfsmittel,
                'hobbies'                  => $analyse->hobbies,
                'aufnahmegrund'            => $analyse->aufnahmegrund,
                'hilflosenentschaedigung'  => $analyse->hilflosenentschaedigung,
                'pflegeversicherung'       => $analyse->pflegeversicherung,
                'pflegeversicherung_name'  => $analyse->pflegeversicherung_name,
                'vorauszahlung'            => $analyse->vorauszahlung,
                'personen_haushalt'        => $analyse->personen_haushalt,
                'personen_betreuungsbed'   => $analyse->personen_betreuungsbed,
                'wunschkost'               => $analyse->wunschkost,
                'wunschkost_details'       => $analyse->wunschkost_details,
                'pflegedienst_aktuell'     => $analyse->pflegedienst_aktuell,
                'pflegedienst_name'        => $analyse->pflegedienst_name,
                'pflegedienst_aufgaben'    => $analyse->pflegedienst_aufgaben,
                'pflegedienst_frequenz'    => $analyse->pflegedienst_frequenz,
                'pflegedienst_abbestellen' => $analyse->pflegedienst_abbestellen,
                'raucher'                  => $analyse->raucher,
                'wohntyp'                  => $analyse->wohntyp,
                'anzahl_zimmer'            => $analyse->anzahl_zimmer,
                'lift'                     => $analyse->lift,
                'treppe'                   => $analyse->treppe,
                'treppe_stufen'            => $analyse->treppe_stufen,
                'klinik'                   => $analyse->klinik,
                'patientenverfuegung'      => $analyse->patientenverfuegung,
                'haustiere'                => $analyse->haustiere,
                'haustiere_details'        => $analyse->haustiere_details,
                'pflegestufe_curapflege'   => $analyse->pflegestufe,
            ]);

            if ($analyse->ap1_name || $analyse->ap1_vorname) {
                DB::table('klient_kontakte')->insert([
                    'klient_id'          => $klient->id,
                    'name'               => trim(($analyse->ap1_vorname ?? '') . ' ' . ($analyse->ap1_name ?? '')),
                    'beziehung'          => $analyse->ap1_beziehung,
                    'telefon'            => $analyse->ap1_telefon,
                    'mobile'             => $analyse->ap1_mobile,
                    'adresse'            => trim(implode(', ', array_filter([
                        $analyse->ap1_strasse,
                        trim(($analyse->ap1_plz ?? '') . ' ' . ($analyse->ap1_ort ?? '')),
                    ]))),
                    'bemerkung'          => $analyse->ap1_bemerkung,
                    'bevollmaechtigt'    => (bool) $analyse->ap1_vormund,
                    'vormund'            => (bool) $analyse->ap1_vormund,
                    'erreichbarkeit'     => $analyse->ap1_erreichbarkeit,
                    'erreichbarkeit_von' => $analyse->ap1_erreichbarkeit_von,
                    'erreichbarkeit_bis' => $analyse->ap1_erreichbarkeit_bis,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            if ($analyse->ap2_name || $analyse->ap2_vorname) {
                DB::table('klient_kontakte')->insert([
                    'klient_id'          => $klient->id,
                    'name'               => trim(($analyse->ap2_vorname ?? '') . ' ' . ($analyse->ap2_name ?? '')),
                    'beziehung'          => $analyse->ap2_beziehung,
                    'telefon'            => $analyse->ap2_telefon,
                    'mobile'             => $analyse->ap2_mobile,
                    'adresse'            => trim(implode(', ', array_filter([
                        $analyse->ap2_strasse,
                        trim(($analyse->ap2_plz ?? '') . ' ' . ($analyse->ap2_ort ?? '')),
                    ]))),
                    'bemerkung'          => $analyse->ap2_bemerkung,
                    'bevollmaechtigt'    => (bool) $analyse->ap2_vormund,
                    'vormund'            => (bool) $analyse->ap2_vormund,
                    'erreichbarkeit'     => $analyse->ap2_erreichbarkeit,
                    'erreichbarkeit_von' => $analyse->ap2_erreichbarkeit_von,
                    'erreichbarkeit_bis' => $analyse->ap2_erreichbarkeit_bis,
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            // KVG-Krankenkasse → klient_krankenkassen
            if ($analyse->kvg_krankenkasse_id) {
                DB::table('klient_krankenkassen')->insert([
                    'klient_id'      => $klient->id,
                    'krankenkasse_id'=> $analyse->kvg_krankenkasse_id,
                    'versicherungs_typ' => 'kvg',
                    'deckungstyp'    => 'allgemein',
                    'gueltig_ab'     => now()->startOfYear()->toDateString(),
                    'aktiv'          => true,
                    'tiers_payant'   => false,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            // Zweite KK (VVG) → klient_krankenkassen
            if ($analyse->zweite_krankenkasse_id) {
                DB::table('klient_krankenkassen')->insert([
                    'klient_id'      => $klient->id,
                    'krankenkasse_id'=> $analyse->zweite_krankenkasse_id,
                    'versicherungs_typ' => 'vvg',
                    'deckungstyp'    => $analyse->vvg_deckungstyp ?? 'allgemein',
                    'gueltig_ab'     => now()->startOfYear()->toDateString(),
                    'aktiv'          => true,
                    'tiers_payant'   => false,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            $analyse->update([
                'klient_id'        => $klient->id,
                'status'           => 'abgeschlossen',
                'abgeschlossen_am' => now(),
            ]);
        });

        return redirect()->route('klienten.show', $analyse->fresh()->klient_id)
            ->with('success', 'Bedarfsanalyse abgeschlossen — Klient wurde angelegt.');
    }

    public function show(Klient $klient)
    {
        abort_if($klient->organisation_id !== $this->orgId(), 403);
        $analyse = Bedarfsanalyse::where('klient_id', $klient->id)->latest()->first();
        abort_if(!$analyse, 404);
        return view('bedarfsanalysen.show', compact('klient', 'analyse'));
    }

    public function sektionEditieren(Request $request, Bedarfsanalyse $analyse, int $schritt)
    {
        abort_if($analyse->organisation_id !== $this->orgId(), 403);
        $felder     = $this->felderFuerSchritt($schritt);
        $daten      = $request->only($felder);
        $checkboxen = $this->checkboxenFuerSchritt($schritt);
        foreach ($checkboxen as $cb) {
            $daten[$cb] = $request->boolean($cb);
        }
        $analyse->update($daten);
        return back()->with('success', 'Gespeichert.');
    }

    private function felderFuerSchritt(int $schritt): array
    {
        return match ($schritt) {
            1 => [
                'datum_analyse','ort_analyse','anrede','vorname','nachname','strasse','plz','ort',
                'telefon','mobile','geburtsdatum','heimatort','konfession','zivilstand','nationalitaet','ahv_nr',
                'ap1_name','ap1_vorname','ap1_strasse','ap1_plz','ap1_ort','ap1_beziehung','ap1_bemerkung',
                'ap1_telefon','ap1_mobile','ap1_vormund','ap1_erreichbarkeit','ap1_erreichbarkeit_von','ap1_erreichbarkeit_bis',
                'ap2_name','ap2_vorname','ap2_strasse','ap2_plz','ap2_ort','ap2_beziehung','ap2_bemerkung',
                'ap2_telefon','ap2_mobile','ap2_vormund','ap2_erreichbarkeit','ap2_erreichbarkeit_von','ap2_erreichbarkeit_bis',
            ],
            2 => [
                'kvg_krankenkasse_id','kvg_anschrift','vvg_vorhanden','vvg_deckungstyp',
                'pflegeversicherung','pflegeversicherung_name','zweite_krankenkasse_id',
                'zweite_krankenkasse_anschrift','haushaltshilfe','versicherung_bemerkungen',
                'aufnahmegrund','hilflosenentschaedigung','rechnungsadresse','vorauszahlung',
                'zustaendiger_arzt','personen_haushalt','personen_betreuungsbed','gewicht_kg',
            ],
            3 => ['diagnosen_text','medikamente_liste','mobilitaet','hilfsmittel','hobbies','pflegestufe'],
            4 => [
                'wunschkost','wunschkost_details','pflegedienst_aktuell','pflegedienst_name',
                'pflegedienst_aufgaben','pflegedienst_frequenz','pflegedienst_abbestellen','raucher',
            ],
            5 => [
                'wohntyp','anzahl_zimmer','lift','treppe','treppe_stufen',
                'klinik','patientenverfuegung','haustiere','haustiere_details','eintrittstermin',
            ],
            default => [],
        };
    }

    private function checkboxenFuerSchritt(int $schritt): array
    {
        return match ($schritt) {
            1 => ['ap1_vormund','ap2_vormund'],
            2 => ['vvg_vorhanden','pflegeversicherung','vorauszahlung'],
            3 => ['medikamente_liste'],
            4 => ['wunschkost','pflegedienst_aktuell','pflegedienst_abbestellen'],
            5 => ['lift','treppe','patientenverfuegung','haustiere'],
            default => [],
        };
    }
}
