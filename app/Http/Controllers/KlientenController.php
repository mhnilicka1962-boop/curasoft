<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Arzt;
use App\Models\Benutzer;
use App\Models\Klient;
use App\Models\KlientAdresse;
use App\Models\KlientArzt;
use App\Models\KlientDiagnose;
use App\Models\KlientBenutzer;
use App\Models\KlientKontakt;
use App\Models\KlientKrankenkasse;
use App\Models\KlientBeitrag;
use App\Models\KlientPflegestufe;
use App\Models\Krankenkasse;
use App\Models\Leistungsart;
use App\Models\Organisation;
use App\Models\Region;
use App\Services\BexioService;
use App\Services\GeocodingService;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
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
                ->orWhereRaw("(nachname || ' ' || vorname) ilike ?", ["%{$s}%"])
                ->orWhereRaw("(vorname || ' ' || nachname) ilike ?", ["%{$s}%"])
                ->orWhere('ort', 'ilike', "%{$s}%")
                ->orWhere('id', is_numeric($s) ? (int)$s : 0)
            );
        }

        $status = $request->input('status', 'aktiv');
        if ($status !== 'alle') {
            $query->where('aktiv', $status === 'aktiv');
        }

        $klienten = $query
            ->withCount('beitraege')
            ->withExists(['einsaetze as ohne_tour' => fn($q) => $q
                ->where('status', 'geplant')
                ->where('datum', '>=', today()->toDateString())
                ->whereNull('tour_id')
            ])
            ->paginate(25)->withQueryString();

        return view('klienten.index', compact('klienten'));
    }

    public function create()
    {
        $regionen      = Region::orderBy('kuerzel')->get();
        $mitarbeiter   = Benutzer::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();
        $krankenkassen = Krankenkasse::where('aktiv', true)->orderBy('name')->get();
        return view('klienten.create', compact('regionen', 'mitarbeiter', 'krankenkassen'));
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
            'region_id'           => ['required', 'exists:regionen,id'],
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

        // Koordinaten automatisch holen
        if ($klient->adresse && $klient->plz && $klient->ort) {
            $coords = app(GeocodingService::class)->geocode($klient->adresse, $klient->plz, $klient->ort);
            if ($coords) $klient->update(['klient_lat' => $coords['lat'], 'klient_lng' => $coords['lng']]);
        }

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
            'geburtsdatum'    => ['nullable', 'date'],
            'zivilstand'      => ['nullable', 'in:ledig,verheiratet,geschieden,verwitwet,eingetragen'],
            'ahv_nr'          => ['nullable', 'string', 'max:20'],
            'zahlbar_tage'    => ['nullable', 'integer', 'min:1'],
            'telefon'         => ['nullable', 'string', 'max:50'],
            'adresse'         => ['nullable', 'string', 'max:200'],
            'plz'             => ['nullable', 'string', 'max:20'],
            'ort'             => ['nullable', 'string', 'max:100'],
            'region_id'       => ['required', 'exists:regionen,id'],
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
            'geburtsdatum'    => $daten['geburtsdatum'] ?? null,
            'zivilstand'      => $daten['zivilstand'] ?? null,
            'ahv_nr'          => $daten['ahv_nr'] ?? null,
            'zahlbar_tage'    => $daten['zahlbar_tage'] ?? 30,
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

        $pflegendeAngehoerige = KlientBenutzer::with('benutzer')
            ->where('klient_id', $klient->id)
            ->where('beziehungstyp', 'angehoerig_pflegend')
            ->where('aktiv', true)
            ->get();

        $regionen = Region::orderBy('kuerzel')->get();

        $einzelleistungen = \App\Models\Einsatz::where('klient_id', $klient->id)
            ->where('organisation_id', $this->orgId())
            ->whereNotNull('betrag_fix')
            ->orderByDesc('datum')
            ->get();

        $einsaetzeAnzahl = $klient->einsaetze()
            ->where('datum', '>=', today())
            ->whereNotIn('status', ['abgeschlossen', 'storniert'])
            ->count();

        return view('klienten.show', compact('klient', 'leistungsarten', 'mitarbeiter', 'pflegendeAngehoerige', 'regionen', 'einzelleistungen', 'einsaetzeAnzahl'));
    }

    public function einsaetzePopup(Klient $klient)
    {
        $this->autorisiereZugriff($klient);

        $heute = today();
        $alle  = $klient->einsaetze()
            ->with('einsatzLeistungsarten.leistungsart', 'benutzer', 'tour')
            ->orderBy('datum')
            ->orderBy('zeit_von')
            ->get();

        $anstehend = $alle->filter(fn($e) => $e->datum >= $heute && !in_array($e->status, ['abgeschlossen','storniert']))->values();
        $vergangen  = $alle->filter(fn($e) => $e->datum < $heute || in_array($e->status, ['abgeschlossen','storniert']))->sortByDesc('datum')->values();
        $monat      = $alle->filter(fn($e) => $e->datum->month === $heute->month && $e->datum->year === $heute->year)->values();

        $leistungsarten = Leistungsart::where('aktiv', true)->where('einheit', '!=', 'tage')->orderBy('bezeichnung')->get();
        $mitarbeiter    = auth()->user()->rolle === 'admin'
            ? Benutzer::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get()
            : collect();

        return view('klienten.einsaetze_popup', compact('klient', 'anstehend', 'vergangen', 'monat', 'leistungsarten', 'mitarbeiter', 'heute'));
    }

    public function edit(Klient $klient)
    {
        $this->autorisiereZugriff($klient);
        $regionen      = Region::orderBy('kuerzel')->get();
        $mitarbeiter   = Benutzer::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();
        $krankenkassen = Krankenkasse::where('aktiv', true)->orderBy('name')->get();
        return view('klienten.edit', compact('klient', 'regionen', 'mitarbeiter', 'krankenkassen'));
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
            'region_id'           => ['required', 'exists:regionen,id'],
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
            'versandart_patient'  => ['nullable', 'in:post,email,manuell'],
            'versandart_kvg'      => ['nullable', 'in:email,healthnet,manuell'],
            'rechnungstyp'        => ['nullable', 'in:kombiniert,kvg,klient,gemeinde'],
            'aktiv'               => ['boolean'],
        ]);

        $adresseGeaendert    = isset($daten['adresse']) && (
            $daten['adresse'] !== $klient->adresse ||
            ($daten['plz'] ?? null) !== $klient->plz ||
            ($daten['ort'] ?? null) !== $klient->ort
        );
        $zustaendigGeaendert = isset($daten['zustaendig_id']) &&
            (int) $daten['zustaendig_id'] !== (int) $klient->zustaendig_id;

        $klient->update($daten);

        // Zuständige Person geändert → zukünftige Tagespauschale-Einsätze umschreiben
        if ($zustaendigGeaendert && $daten['zustaendig_id']) {
            \App\Models\Einsatz::where('klient_id', $klient->id)
                ->whereNotNull('tagespauschale_id')
                ->where('verrechnet', false)
                ->whereDate('datum', '>=', today())
                ->update(['benutzer_id' => $daten['zustaendig_id']]);
        }

        // Koordinaten neu holen wenn Adresse geändert
        if ($adresseGeaendert && $klient->adresse && $klient->plz && $klient->ort) {
            $coords = app(GeocodingService::class)->geocode($klient->adresse, $klient->plz, $klient->ort);
            if ($coords) $klient->update(['klient_lat' => $coords['lat'], 'klient_lng' => $coords['lng']]);
        }

        return redirect()->route('klienten.show', $klient)
            ->with('erfolg', 'Klient wurde gespeichert.');
    }

    public function destroy(Klient $klient)
    {
        $this->autorisiereZugriff($klient);

        $blocker = [];
        if ($klient->einsaetze()->exists())    $blocker[] = $klient->einsaetze()->count() . ' Einsatz/Einsätze';
        if ($klient->rechnungen()->exists())   $blocker[] = $klient->rechnungen()->count() . ' Rechnung(en)';
        if ($klient->rapporte()->exists())     $blocker[] = $klient->rapporte()->count() . ' Rapport(e)';

        if (!empty($blocker)) {
            return back()->with('fehler',
                'Klient kann nicht gelöscht werden — verknüpfte Daten: ' . implode(', ', $blocker) . '.');
        }

        $name = $klient->vorname . ' ' . $klient->nachname;
        $klient->delete();

        return redirect()->route('klienten.index')
            ->with('erfolg', 'Klient «' . $name . '» wurde gelöscht.');
    }

    public function qr(Klient $klient)
    {
        $this->autorisiereZugriff($klient);
        if (!$klient->qr_token) {
            $klient->update(['qr_token' => \Illuminate\Support\Str::random(32)]);
        }
        $url    = route('checkin.scan', $klient->qr_token);
        $result = (new PngWriter())->write(new QrCode($url, size: 200, margin: 10));
        $qrDataUri = $result->getDataUri();
        return view('klienten.qr', compact('klient', 'qrDataUri'));
    }

    public function adresseSpeichern(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);

        $daten = $request->validate([
            'adressart' => ['required', 'in:rechnung,notfall'],
            'firma'     => ['nullable', 'string', 'max:200'],
            'name'      => ['nullable', 'string', 'max:200'],
            'strasse'   => ['nullable', 'string', 'max:255'],
            'plz'       => ['nullable', 'string', 'max:10'],
            'ort'       => ['nullable', 'string', 'max:100'],
            'telefon'   => ['nullable', 'string', 'max:50'],
            'email'     => ['nullable', 'email', 'max:255'],
        ]);

        $klient->adressen()->updateOrCreate(
            ['adressart' => $daten['adressart']],
            [
                'firma'    => $daten['firma'] ?? null,
                'nachname' => $daten['name'] ?? null,
                'strasse'  => $daten['strasse'] ?? null,
                'plz'      => $daten['plz'] ?? null,
                'ort'      => $daten['ort'] ?? null,
                'telefon'  => $daten['telefon'] ?? null,
                'email'    => $daten['email'] ?? null,
                'aktiv'    => true,
            ]
        );

        return redirect()->route('klienten.show', $klient)
            ->with('erfolg', 'Adresse gespeichert.');
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
            'tiers_payant'       => ['nullable', 'boolean'],
            'deckungstyp'        => ['nullable', 'in:allgemein,halbprivat,privat'],
            'versichertennummer' => ['nullable', 'string', 'max:50'],
            'kartennummer'       => ['nullable', 'string', 'max:50'],
            'gueltig_ab'         => ['nullable', 'date'],
            'gueltig_bis'        => ['nullable', 'date'],
            'bemerkung'          => ['nullable', 'string', 'max:500'],
        ]);
        $klient->krankenkassen()->create(array_merge($daten, [
            'aktiv'        => true,
            'tiers_payant' => (bool) ($daten['tiers_payant'] ?? true),
            'deckungstyp'  => $daten['deckungstyp'] ?? 'allgemein',
        ]));
        return back()->with('erfolg', 'Krankenkasse wurde hinzugefügt.');
    }

    public function krankenkasseEntfernen(Klient $klient, KlientKrankenkasse $klientKk)
    {
        $this->autorisiereZugriff($klient);
        if ($klientKk->klient_id !== $klient->id) abort(403);
        $klientKk->delete();
        return back()->with('erfolg', 'Krankenkasse wurde entfernt.');
    }

    public function verordnungSpeichern(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);
        $daten = $request->validate([
            'arzt_id'         => ['nullable', 'exists:aerzte,id'],
            'leistungsart_id' => ['nullable', 'exists:leistungsarten,id'],
            'verordnungs_nr'  => ['nullable', 'string', 'max:50'],
            'ausgestellt_am'  => ['nullable', 'date'],
            'gueltig_ab'      => ['required', 'date'],
            'gueltig_bis'     => ['nullable', 'date', 'after_or_equal:gueltig_ab'],
            'bemerkung'       => ['nullable', 'string', 'max:500'],
        ]);
        $klient->verordnungen()->create(array_merge($daten, ['aktiv' => true]));
        return back()->with('erfolg', 'Verordnung wurde gespeichert.');
    }

    public function verordnungEntfernen(Klient $klient, \App\Models\KlientVerordnung $verordnung)
    {
        $this->autorisiereZugriff($klient);
        if ($verordnung->klient_id !== $klient->id) abort(403);
        $verordnung->delete();
        return back()->with('erfolg', 'Verordnung wurde entfernt.');
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

    public function beitragAktualisieren(Request $request, Klient $klient, KlientBeitrag $beitrag)
    {
        $this->autorisiereZugriff($klient);
        if ($beitrag->klient_id !== $klient->id) abort(403);
        $daten = $request->validate([
            'gueltig_ab'               => ['required', 'date'],
            'ansatz_kunde'             => ['required', 'numeric', 'min:0'],
            'limit_restbetrag_prozent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ansatz_spitex'            => ['nullable', 'numeric', 'min:0'],
            'kanton_abrechnung'        => ['nullable', 'numeric', 'min:0'],
        ]);
        $beitrag->update($daten);
        return back()->with('erfolg', 'Beitrag wurde aktualisiert.');
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

    public function angehoerigZuweisen(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);

        $request->validate([
            'benutzer_id' => ['required', 'exists:benutzer,id'],
        ]);

        KlientBenutzer::updateOrCreate(
            ['klient_id' => $klient->id, 'benutzer_id' => $request->benutzer_id],
            ['rolle' => 'betreuer', 'beziehungstyp' => 'angehoerig_pflegend', 'aktiv' => true]
        );

        return back()->with('erfolg', 'Pflegender Angehöriger zugewiesen.');
    }

    public function angehoerigEntfernen(Klient $klient, KlientBenutzer $zuweisung)
    {
        $this->autorisiereZugriff($klient);
        $zuweisung->delete();
        return back()->with('erfolg', 'Zuweisung entfernt.');
    }

    public function bexioSync(Klient $klient)
    {
        $this->autorisiereZugriff($klient);

        $org = Organisation::findOrFail($this->orgId());
        if (empty($org->bexio_api_key)) {
            return back()->with('fehler', 'Kein Bexio API-Key konfiguriert. Bitte unter Firma → Bexio einrichten.');
        }

        $service = new BexioService($org);
        $ok = $service->kontaktSynchronisieren($klient);

        return back()->with(
            $ok ? 'erfolg' : 'fehler',
            $ok ? 'Kontakt wurde mit Bexio synchronisiert.' : 'Bexio-Sync fehlgeschlagen. Bitte Verbindung unter Firma prüfen.'
        );
    }

    public function monatsuebersicht(Request $request, Klient $klient)
    {
        $this->autorisiereZugriff($klient);

        $monat = max(1, min(12, (int) $request->input('monat', now()->month)));
        $jahr  = max(2020, min(2040, (int) $request->input('jahr', now()->year)));

        $von  = \Carbon\Carbon::create($jahr, $monat, 1)->startOfDay();
        $bis  = $von->copy()->endOfMonth();
        $tage = $von->daysInMonth;

        $einsaetze = $klient->einsaetze()
            ->with('aktivitaeten')
            ->whereNull('tagespauschale_id')
            ->whereBetween('datum', [$von->toDateString(), $bis->toDateString()])
            ->get();

        // Tag-Status ermitteln
        $heute = today();
        $tagStatus = [];
        foreach ($einsaetze as $e) {
            $tag = (int) $e->datum->day;
            if ($e->status === 'abgeschlossen') {
                if (!isset($tagStatus[$tag]) || $tagStatus[$tag] !== 'offen') {
                    $tagStatus[$tag] = 'abgeschlossen';
                }
            } elseif ($e->status !== 'storniert') {
                if ($e->datum->lte($heute)) {
                    $tagStatus[$tag] = 'offen';
                } elseif (!isset($tagStatus[$tag])) {
                    $tagStatus[$tag] = 'geplant_zukunft';
                }
            }
        }

        // Grid aufbauen: [kategorie][aktivitaet][day] = minutes
        $grid = [];
        foreach ($einsaetze as $e) {
            $tag = (int) $e->datum->day;
            foreach ($e->aktivitaeten as $akt) {
                $kat  = $akt->kategorie;
                $name = $akt->aktivitaet;
                $grid[$kat][$name][$tag] = ($grid[$kat][$name][$tag] ?? 0) + $akt->minuten;
            }
        }

        // Fallback: keine Aktivitäten → Einsätze direkt nach Leistungsart anzeigen
        $fallback = false;
        if (empty($grid) && $einsaetze->isNotEmpty()) {
            $fallback = true;
            foreach ($einsaetze as $e) {
                $tag = (int) $e->datum->day;
                $kat  = $e->einsatzLeistungsarten->first()?->leistungsart?->bezeichnung ?? 'Einsätze';
                $name = 'Minuten';
                // Minuten: aus einsatz.minuten, sonst aus zeit_von/bis, sonst dauerMinuten()
                $min = $e->minuten;
                if (!$min && $e->zeit_von && $e->zeit_bis) {
                    [$h1, $m1] = explode(':', substr($e->zeit_von, 0, 5));
                    [$h2, $m2] = explode(':', substr($e->zeit_bis, 0, 5));
                    $min = ($h2 * 60 + $m2) - ($h1 * 60 + $m1);
                }
                if (!$min) $min = $e->dauerMinuten() ?? 0;
                if ($min > 0) {
                    $grid[$kat][$name][$tag] = ($grid[$kat][$name][$tag] ?? 0) + $min;
                }
            }
        }

        // Master-Liste + zusätzliche Aktivitäten aus Daten
        $master = \App\Models\EinsatzAktivitaet::$aktivitaeten;
        foreach ($grid as $kat => $aktivitaeten) {
            if (!isset($master[$kat])) $master[$kat] = [];
            foreach (array_keys($aktivitaeten) as $name) {
                if (!in_array($name, $master[$kat])) $master[$kat][] = $name;
            }
        }

        // Nur Kategorien mit Daten anzeigen
        $masterGefiltert = array_filter($master, fn($kat) => isset($grid[$kat]), ARRAY_FILTER_USE_KEY);

        return view('klienten.monatsuebersicht', compact(
            'klient', 'monat', 'jahr', 'tage', 'tagStatus', 'grid', 'masterGefiltert', 'von', 'fallback'
        ));
    }

    private function autorisiereZugriff(Klient $klient): void
    {
        if ($klient->organisation_id !== $this->orgId()) {
            abort(403);
        }
    }
}
