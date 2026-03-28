<?php

namespace App\Http\Controllers;

use App\Mail\EinladungMail;
use App\Models\Benutzer;
use App\Models\Klient;
use App\Models\KlientBenutzer;
use App\Models\Leistungsart;
use App\Models\Qualifikation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class MitarbeiterController extends Controller
{
    private function orgId(): int { return auth()->user()->organisation_id; }

    public function index(Request $request)
    {
        $query = Benutzer::where('organisation_id', $this->orgId())
            ->with('qualifikationen')
            ->orderBy('nachname')
            ->orderBy('vorname');

        if ($request->filled('rolle')) {
            $query->where('rolle', $request->rolle);
        }
        if ($request->aktiv === '0') {
            $query->where('aktiv', false);
        } elseif ($request->aktiv !== '') {
            $query->where('aktiv', true);
        }
        // aktiv='' → kein Filter → alle anzeigen
        if ($request->filled('suche')) {
            $query->where(function ($q) use ($request) {
                $q->where('vorname', 'ilike', '%' . $request->suche . '%')
                  ->orWhere('nachname', 'ilike', '%' . $request->suche . '%')
                  ->orWhere('email', 'ilike', '%' . $request->suche . '%');
            });
        }

        $mitarbeiter   = $query->paginate(25)->withQueryString();
        $qualifikationen = Qualifikation::where('aktiv', true)->orderBy('sort_order')->get();

        return view('stammdaten.mitarbeiter.index', compact('mitarbeiter', 'qualifikationen'));
    }

    public function show(Benutzer $mitarbeiter)
    {
        if ($mitarbeiter->organisation_id !== $this->orgId()) abort(403);
        $mitarbeiter->load('qualifikationen', 'klientZuweisungen.klient', 'erlaubteLeistungsarten');
        $qualifikationen = Qualifikation::where('aktiv', true)->orderBy('sort_order')->get();
        $leistungsarten  = Leistungsart::where('aktiv', true)->orderBy('id')->get();
        $klienten        = Klient::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();
        return view('stammdaten.mitarbeiter.show', compact('mitarbeiter', 'qualifikationen', 'leistungsarten', 'klienten'));
    }

    public function create()
    {
        $mitarbeiter     = new \App\Models\Benutzer(['aktiv' => true, 'pensum' => 100]);
        $qualifikationen = Qualifikation::where('aktiv', true)->orderBy('sort_order')->get();
        $leistungsarten  = Leistungsart::where('aktiv', true)->orderBy('id')->get();
        $klienten        = Klient::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();
        return view('stammdaten.mitarbeiter.show', compact('mitarbeiter', 'qualifikationen', 'leistungsarten', 'klienten'));
    }

    public function store(Request $request)
    {
        $daten = $request->validate([
            'anrede'          => ['nullable', 'string', 'max:20'],
            'geschlecht'      => ['nullable', 'in:m,f,d'],
            'vorname'         => ['required', 'string', 'max:100'],
            'nachname'        => ['required', 'string', 'max:100'],
            'geburtsdatum'    => ['nullable', 'date'],
            'email'           => ['required', 'email', 'unique:benutzer,email'],
            'telefon'         => ['nullable', 'string', 'max:30'],
            'strasse'         => ['nullable', 'string', 'max:150'],
            'plz'             => ['nullable', 'string', 'max:10'],
            'ort'             => ['nullable', 'string', 'max:100'],
            'rolle'           => ['required', 'in:admin,pflege,buchhaltung'],
            'anstellungsart'  => ['nullable', 'in:fachperson,angehoerig,freiwillig,praktikum'],
            'pensum'          => ['nullable', 'integer', 'min:0', 'max:100'],
            'eintrittsdatum'  => ['nullable', 'date'],
            'nationalitaet'   => ['nullable', 'string', 'max:60'],
            'zivilstand'      => ['nullable', 'string', 'max:40'],
            'telefax'         => ['nullable', 'string', 'max:30'],
            'email_privat'    => ['nullable', 'email', 'max:100'],
            'ahv_nr'          => ['nullable', 'string', 'max:20'],
            'gln'             => ['nullable', 'digits:13'],
            'nareg_nr'        => ['nullable', 'string', 'max:20'],
            'iban'            => ['nullable', 'string', 'max:25'],
            'bank'            => ['nullable', 'string', 'max:100'],
            'austrittsdatum'  => ['nullable', 'date'],
            'notizen'         => ['nullable', 'string', 'max:5000'],
        ]);

        $token = Str::random(48);

        $benutzer = Benutzer::create(array_merge($daten, [
            'organisation_id'         => $this->orgId(),
            'password'                => Hash::make(Str::random(32)),
            'aktiv'                   => true,
            'einladungs_token'        => $token,
            'einladungs_token_ablauf' => now()->addHours(48),
        ]));

        $mailErfolg = true;
        try {
            $link = route('einladung.show', $token);
            Mail::to($benutzer->email)->send(new EinladungMail($benutzer, $link));
        } catch (\Throwable $e) {
            $mailErfolg = false;
        }

        // Qualifikationen + Leistungsarten aus Neuerfassungsformular
        if ($request->has('qualifikation_ids')) {
            $benutzer->qualifikationen()->sync($request->input('qualifikation_ids', []));
        }
        if ($request->has('leistungsart_ids')) {
            $benutzer->erlaubteLeistungsarten()->sync($request->input('leistungsart_ids', []));
        }

        // Klient direkt zuweisen
        if ($request->filled('klient_id')) {
            KlientBenutzer::updateOrCreate(
                ['klient_id' => $request->klient_id, 'benutzer_id' => $benutzer->id],
                [
                    'rolle'           => $request->input('klient_rolle', 'betreuer'),
                    'beziehungstyp'   => $request->input('beziehungstyp', 'fachperson'),
                    'aktiv'           => true,
                ]
            );
        }

        $msg = $mailErfolg
            ? 'Mitarbeiter angelegt. Einladungs-E-Mail wurde gesendet.'
            : 'Mitarbeiter angelegt. E-Mail konnte nicht gesendet werden — bitte Einladung manuell versenden.';

        if ($request->input('_redirect') === 'angehoerige') {
            return redirect()->route('angehoerigenpflege.index')->with('erfolg', $msg);
        }
        if ($request->input('_redirect') === 'klient_angehoerig' && $request->filled('klient_id')) {
            return redirect()->route('klienten.show', $request->klient_id)->with('erfolg', $msg);
        }

        return redirect()->route('mitarbeiter.show', $benutzer)->with('erfolg', $msg);
    }

    public function einladungSenden(Benutzer $mitarbeiter)
    {
        if ($mitarbeiter->organisation_id !== $this->orgId()) abort(403);

        $token = Str::random(48);
        $mitarbeiter->update([
            'einladungs_token'        => $token,
            'einladungs_token_ablauf' => now()->addHours(48),
        ]);

        $link = route('einladung.show', $token);
        Mail::to($mitarbeiter->email)->send(new EinladungMail($mitarbeiter, $link));

        return back()->with('erfolg', 'Einladung wurde erneut gesendet.');
    }

    public function update(Request $request, Benutzer $mitarbeiter)
    {
        if ($mitarbeiter->organisation_id !== $this->orgId()) abort(403);

        $rules = [
            'anrede'          => ['nullable', 'string', 'max:20'],
            'geschlecht'      => ['nullable', 'in:m,f,d'],
            'geburtsdatum'    => ['nullable', 'date'],
            'nationalitaet'   => ['nullable', 'string', 'max:60'],
            'zivilstand'      => ['nullable', 'string', 'max:40'],
            'vorname'         => ['required', 'string', 'max:100'],
            'nachname'        => ['required', 'string', 'max:100'],
            'strasse'         => ['nullable', 'string', 'max:150'],
            'plz'             => ['nullable', 'string', 'max:10'],
            'ort'             => ['nullable', 'string', 'max:100'],
            'telefon'         => ['nullable', 'string', 'max:30'],
            'telefax'         => ['nullable', 'string', 'max:30'],
            'email'           => ['required', 'email', 'unique:benutzer,email,' . $mitarbeiter->id],
            'email_privat'    => ['nullable', 'email', 'max:100'],
            'ahv_nr'          => ['nullable', 'string', 'max:20'],
            'gln'             => ['nullable', 'digits:13'],
            'nareg_nr'        => ['nullable', 'string', 'max:20'],
            'iban'            => ['nullable', 'string', 'max:25'],
            'bank'            => ['nullable', 'string', 'max:100'],
            'pensum'          => ['nullable', 'integer', 'min:0', 'max:100'],
            'eintrittsdatum'  => ['nullable', 'date'],
            'austrittsdatum'  => ['nullable', 'date'],
            'rolle'           => ['required', 'in:admin,pflege,buchhaltung'],
            'anstellungsart'  => ['nullable', 'in:fachperson,angehoerig,freiwillig,praktikum'],
            'aktiv'           => ['boolean'],
            'notizen'         => ['nullable', 'string', 'max:5000'],
        ];

        $daten = $request->validate($rules);

        if ($request->filled('password')) {
            $request->validate(['password' => ['min:8']]);
            $daten['password'] = Hash::make($request->password);
        }

        $alteAnstellungsart = $mitarbeiter->anstellungsart;
        $mitarbeiter->update($daten);

        // Wenn neu auf "angehoerig" gesetzt → Leistungsarten automatisch einschränken (KLV)
        if (($daten['anstellungsart'] ?? null) === 'angehoerig' && $alteAnstellungsart !== 'angehoerig') {
            $erlaubteIds = Leistungsart::whereIn('bezeichnung', ['Hauswirtschaft', 'Grundpflege', 'Pauschale'])
                ->pluck('id');
            $mitarbeiter->erlaubteLeistungsarten()->sync($erlaubteIds);
        }

        return back()->with('erfolg', 'Mitarbeiter wurde aktualisiert.');
    }

    public function qualifikationenSpeichern(Request $request, Benutzer $mitarbeiter)
    {
        if ($mitarbeiter->organisation_id !== $this->orgId()) abort(403);

        $ids = $request->input('qualifikation_ids', []);
        $mitarbeiter->qualifikationen()->sync($ids);

        return redirect(url()->previous() . '#qualifikationen')
            ->with('erfolg_qual', 'Qualifikationen gespeichert.');
    }

    public function leistungsartenSpeichern(Request $request, Benutzer $mitarbeiter)
    {
        if ($mitarbeiter->organisation_id !== $this->orgId()) abort(403);

        $ids = $request->input('leistungsart_ids', []);
        $mitarbeiter->erlaubteLeistungsarten()->sync($ids);

        return back()->with('erfolg', 'Erlaubte Leistungsarten wurden gespeichert.');
    }

    public function klientZuweisen(Request $request, Benutzer $mitarbeiter)
    {
        if ($mitarbeiter->organisation_id !== $this->orgId()) abort(403);

        $request->validate([
            'klient_id'       => ['required', 'exists:klienten,id'],
            'rolle'           => ['required', 'in:hauptbetreuer,betreuer,vertretung'],
            'beziehungstyp'   => ['nullable', 'in:fachperson,angehoerig_pflegend,freiwillig'],
        ]);

        KlientBenutzer::updateOrCreate(
            ['klient_id' => $request->klient_id, 'benutzer_id' => $mitarbeiter->id],
            ['rolle' => $request->rolle, 'beziehungstyp' => $request->beziehungstyp, 'aktiv' => true]
        );

        return back()->with('erfolg', 'Klient zugewiesen.');
    }

    public function klientEntfernen(Benutzer $mitarbeiter, KlientBenutzer $zuweisung)
    {
        if ($mitarbeiter->organisation_id !== $this->orgId()) abort(403);
        $zuweisung->delete();
        return back()->with('erfolg', 'Zuweisung entfernt.');
    }

    public function destroy(Benutzer $mitarbeiter)
    {
        if ($mitarbeiter->organisation_id !== $this->orgId()) abort(403);

        $hatEinsaetze = \App\Models\Einsatz::where('benutzer_id', $mitarbeiter->id)->exists();
        $hatRechnungen = \DB::table('rechnungen')->where('organisation_id', $this->orgId())
            ->whereExists(fn($q) => $q->from('rechnungs_positionen')
                ->whereColumn('rechnungs_positionen.rechnung_id', 'rechnungen.id'))
            ->exists(); // vereinfacht — kein direkter benutzer_id auf rechnungen

        if ($hatEinsaetze) {
            return back()->with('fehler', 'Mitarbeiter kann nicht gelöscht werden — es existieren Einsätze.');
        }

        $mitarbeiter->delete();
        return redirect()->route('mitarbeiter.index')->with('erfolg', 'Mitarbeiter wurde gelöscht.');
    }
}
