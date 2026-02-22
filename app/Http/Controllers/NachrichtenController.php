<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Nachricht;
use App\Models\NachrichtEmpfaenger;
use Illuminate\Http\Request;

class NachrichtenController extends Controller
{
    /** Posteingang */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'posteingang');

        $posteingang = NachrichtEmpfaenger::where('empfaenger_id', auth()->id())
            ->where('archiviert', false)
            ->with(['nachricht.absender'])
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'seite');

        $gesendet = Nachricht::where('absender_id', auth()->id())
            ->with(['empfaenger.empfaenger'])
            ->orderByDesc('created_at')
            ->paginate(20, ['*'], 'seite');

        $ungelesen = NachrichtEmpfaenger::where('empfaenger_id', auth()->id())
            ->whereNull('gelesen_am')
            ->where('archiviert', false)
            ->count();

        return view('nachrichten.index', compact('posteingang', 'gesendet', 'ungelesen', 'tab'));
    }

    /** Neue Nachricht schreiben */
    public function create(Request $request)
    {
        $benutzer = Benutzer::where('id', '!=', auth()->id())
            ->where('organisation_id', auth()->user()->organisation_id)
            ->where('aktiv', true)
            ->orderBy('nachname')
            ->get();

        // Vorausfüllen wenn Reply oder Kontext
        $betreff        = $request->get('betreff', '');
        $empfaengerIds  = $request->get('empfaenger', []);

        return view('nachrichten.create', compact('benutzer', 'betreff', 'empfaengerIds'));
    }

    /** Nachricht absenden */
    public function store(Request $request)
    {
        $request->validate([
            'empfaenger_ids'   => ['required', 'array', 'min:1'],
            'empfaenger_ids.*' => ['exists:benutzer,id'],
            'betreff'          => ['required', 'string', 'max:200'],
            'inhalt'           => ['required', 'string', 'max:10000'],
        ]);

        $nachricht = Nachricht::create([
            'absender_id'  => auth()->id(),
            'betreff'      => $request->betreff,
            'inhalt'       => $request->inhalt,
            'referenz_typ' => $request->referenz_typ,
            'referenz_id'  => $request->referenz_id,
        ]);

        foreach ($request->empfaenger_ids as $empfaengerId) {
            NachrichtEmpfaenger::create([
                'nachricht_id'  => $nachricht->id,
                'empfaenger_id' => $empfaengerId,
            ]);
        }

        return redirect()->route('nachrichten.index')
            ->with('erfolg', 'Nachricht wurde gesendet.');
    }

    /** Nachricht lesen — automatisch als gelesen markieren */
    public function show(Nachricht $nachricht)
    {
        // Zugriff: Absender oder Empfänger
        $istEmpfaenger = $nachricht->empfaenger()
            ->where('empfaenger_id', auth()->id())
            ->exists();
        $istAbsender = $nachricht->absender_id === auth()->id();

        if (!$istEmpfaenger && !$istAbsender) {
            abort(403);
        }

        // Als gelesen markieren
        if ($istEmpfaenger) {
            NachrichtEmpfaenger::where('nachricht_id', $nachricht->id)
                ->where('empfaenger_id', auth()->id())
                ->whereNull('gelesen_am')
                ->update(['gelesen_am' => now()]);
        }

        $nachricht->load(['absender', 'empfaenger.empfaenger']);

        return view('nachrichten.show', compact('nachricht', 'istEmpfaenger', 'istAbsender'));
    }

    /** Antworten */
    public function antworten(Request $request, Nachricht $nachricht)
    {
        $request->validate([
            'inhalt' => ['required', 'string', 'max:10000'],
        ]);

        $empfaengerId = ($nachricht->absender_id === auth()->id())
            ? null   // Antwort an alle Empfänger
            : $nachricht->absender_id;

        $antwort = Nachricht::create([
            'absender_id' => auth()->id(),
            'betreff'     => Str_starts_with($nachricht->betreff, 'Re: ')
                ? $nachricht->betreff
                : 'Re: ' . $nachricht->betreff,
            'inhalt'      => $request->inhalt,
        ]);

        // An Original-Absender antworten
        NachrichtEmpfaenger::create([
            'nachricht_id'  => $antwort->id,
            'empfaenger_id' => $empfaengerId ?? $nachricht->absender_id,
        ]);

        return redirect()->route('nachrichten.show', $antwort)
            ->with('erfolg', 'Antwort wurde gesendet.');
    }

    /** Archivieren */
    public function archivieren(Nachricht $nachricht)
    {
        NachrichtEmpfaenger::where('nachricht_id', $nachricht->id)
            ->where('empfaenger_id', auth()->id())
            ->update(['archiviert' => true]);

        return redirect()->route('nachrichten.index')
            ->with('erfolg', 'Nachricht wurde archiviert.');
    }

    /** An alle in der Org senden (Rundschreiben) */
    public function rundschreiben(Request $request)
    {
        $request->validate([
            'betreff' => ['required', 'string', 'max:200'],
            'inhalt'  => ['required', 'string', 'max:10000'],
        ]);

        $nachricht = Nachricht::create([
            'absender_id' => auth()->id(),
            'betreff'     => $request->betreff,
            'inhalt'      => $request->inhalt,
        ]);

        $alleBenutzer = Benutzer::where('organisation_id', auth()->user()->organisation_id)
            ->where('id', '!=', auth()->id())
            ->where('aktiv', true)
            ->pluck('id');

        foreach ($alleBenutzer as $id) {
            NachrichtEmpfaenger::create([
                'nachricht_id'  => $nachricht->id,
                'empfaenger_id' => $id,
            ]);
        }

        return redirect()->route('nachrichten.index')
            ->with('erfolg', 'Rundschreiben an alle Mitarbeitenden gesendet.');
    }
}
