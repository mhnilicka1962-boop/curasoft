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

        // Auto-Archivierung: einmal täglich, Nachrichten >90 Tage archivieren
        $cacheKey = 'nachrichten_archiv_' . auth()->id();
        if (!\Cache::has($cacheKey)) {
            NachrichtEmpfaenger::where('empfaenger_id', auth()->id())
                ->where('archiviert', false)
                ->whereHas('nachricht', fn($q) => $q->where('created_at', '<', now()->subDays(90)))
                ->update(['archiviert' => true]);
            \Cache::put($cacheKey, true, now()->addHours(24));
        }

        $posteingang = NachrichtEmpfaenger::where('empfaenger_id', auth()->id())
            ->where('archiviert', false)
            ->whereHas('nachricht', fn($q) => $q->whereNull('parent_id'))
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
        // Wenn es eine Antwort ist → zur Root-Nachricht weiterleiten
        if ($nachricht->parent_id) {
            return redirect()->route('nachrichten.show', $nachricht->parent_id);
        }

        // Zugriff: Absender oder Empfänger der Root-Nachricht oder einer Antwort
        $istEmpfaenger = $nachricht->empfaenger()
            ->where('empfaenger_id', auth()->id())
            ->exists();
        $istAbsender = $nachricht->absender_id === auth()->id();

        if (!$istEmpfaenger && !$istAbsender) {
            // Evtl. Zugriff über eine Antwort
            $hatZugriff = $nachricht->antworten()
                ->where(fn($q) => $q
                    ->where('absender_id', auth()->id())
                    ->orWhereHas('empfaenger', fn($q2) => $q2->where('empfaenger_id', auth()->id()))
                )
                ->exists();
            if (!$hatZugriff) abort(403);
        }

        // Root als gelesen markieren
        if ($istEmpfaenger) {
            NachrichtEmpfaenger::where('nachricht_id', $nachricht->id)
                ->where('empfaenger_id', auth()->id())
                ->whereNull('gelesen_am')
                ->update(['gelesen_am' => now()]);
        }

        // Alle Antworten als gelesen markieren
        $antwortIds = $nachricht->antworten()->pluck('id');
        if ($antwortIds->isNotEmpty()) {
            NachrichtEmpfaenger::whereIn('nachricht_id', $antwortIds)
                ->where('empfaenger_id', auth()->id())
                ->whereNull('gelesen_am')
                ->update(['gelesen_am' => now()]);
        }

        $nachricht->load(['absender', 'empfaenger.empfaenger', 'antworten.absender']);

        return view('nachrichten.show', compact('nachricht', 'istEmpfaenger', 'istAbsender'));
    }

    /** Antworten */
    public function antworten(Request $request, Nachricht $nachricht)
    {
        $request->validate([
            'inhalt' => ['required', 'string', 'max:10000'],
        ]);

        // Root-Nachricht ermitteln (Falls doch mal eine Antwort übergeben wird)
        $root = $nachricht->parent_id
            ? Nachricht::with('empfaenger')->findOrFail($nachricht->parent_id)
            : $nachricht;

        $antwort = Nachricht::create([
            'absender_id' => auth()->id(),
            'parent_id'   => $root->id,
            'betreff'     => str_starts_with($root->betreff, 'Re: ')
                ? $root->betreff
                : 'Re: ' . $root->betreff,
            'inhalt'      => $request->inhalt,
        ]);

        // Empfänger: Absender antwortet → alle ursprünglichen Empfänger
        //            Empfänger antwortet → ursprünglicher Absender
        if (auth()->id() === $root->absender_id) {
            $recipients = $root->empfaenger->pluck('empfaenger_id')->toArray();
        } else {
            $recipients = [$root->absender_id];
        }

        foreach (array_unique($recipients) as $id) {
            if ($id !== auth()->id()) {
                NachrichtEmpfaenger::create([
                    'nachricht_id'  => $antwort->id,
                    'empfaenger_id' => $id,
                ]);
            }
        }

        return redirect()->route('nachrichten.show', $root->id)
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
