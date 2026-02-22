<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\Nachricht;
use App\Models\NachrichtEmpfaenger;
use App\Models\Rapport;
use Illuminate\Http\Request;

class RapporteController extends Controller
{
    private function orgId(): int { return auth()->user()->organisation_id; }

    public function index(Request $request)
    {
        $query = Rapport::where('organisation_id', $this->orgId())
            ->with('klient', 'benutzer')
            ->orderByDesc('datum')
            ->orderByDesc('id');

        if ($request->filled('klient_id')) {
            $query->where('klient_id', $request->klient_id);
        }
        if ($request->filled('typ')) {
            $query->where('rapport_typ', $request->typ);
        }
        if ($request->filled('benutzer_id')) {
            $query->where('benutzer_id', $request->benutzer_id);
        }
        if ($request->filled('datum_von')) {
            $query->where('datum', '>=', $request->datum_von);
        }
        if ($request->filled('datum_bis')) {
            $query->where('datum', '<=', $request->datum_bis);
        }

        if (auth()->user()->rolle === 'pflege') {
            $query->where('benutzer_id', auth()->id());
        }

        $rapporte    = $query->paginate(25)->withQueryString();
        $klienten    = Klient::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();
        $mitarbeiter = Benutzer::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();

        return view('rapporte.index', compact('rapporte', 'klienten', 'mitarbeiter'));
    }

    public function create(Request $request)
    {
        $klienten = Klient::where('organisation_id', $this->orgId())->where('aktiv', true)->orderBy('nachname')->get();

        // Klient aus URL-Param oder Einsatz-Param
        $einsatz = null;
        $klient  = null;

        if ($request->filled('einsatz_id')) {
            $einsatz = Einsatz::where('id', $request->einsatz_id)
                ->where('organisation_id', $this->orgId())
                ->with('klient')
                ->first();
            $klient = $einsatz?->klient;
        } elseif ($request->filled('klient_id')) {
            $klient = Klient::find($request->klient_id);
        }

        $mitarbeiter = Benutzer::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->where('id', '!=', auth()->id())
            ->orderBy('nachname')
            ->get();

        return view('rapporte.create', compact('klienten', 'klient', 'einsatz', 'mitarbeiter'));
    }

    public function store(Request $request)
    {
        $daten = $request->validate([
            'klient_id'   => ['required', 'exists:klienten,id'],
            'einsatz_id'  => ['nullable', 'exists:einsaetze,id'],
            'datum'       => ['required', 'date'],
            'zeit_von'    => ['nullable', 'date_format:H:i'],
            'zeit_bis'    => ['nullable', 'date_format:H:i'],
            'rapport_typ' => ['required', 'in:pflege,verlauf,information,zwischenfall,medikament'],
            'inhalt'      => ['required', 'string', 'max:10000'],
            'vertraulich' => ['boolean'],
        ]);

        $rapport = Rapport::create(array_merge($daten, [
            'organisation_id' => $this->orgId(),
            'benutzer_id'     => auth()->id(),
        ]));

        // Auto-Benachrichtigung bei Zwischenfall → alle Admins
        if ($rapport->rapport_typ === 'zwischenfall') {
            $this->benachrichtigeAdmins($rapport);
        }

        // Optionale Benachrichtigung: Empfänger-IDs aus Formular
        if ($request->filled('notify_ids')) {
            $this->sendeNachricht(
                $rapport,
                array_filter((array) $request->notify_ids),
                $request->notify_betreff ?: 'Rapport: ' . $rapport->klient->vollname()
            );
        }

        return redirect()->route('klienten.show', $rapport->klient_id)
            ->with('erfolg', 'Rapport wurde gespeichert.');
    }

    public function show(Rapport $rapport)
    {
        if ($rapport->organisation_id !== $this->orgId()) abort(403);
        $rapport->load('klient', 'benutzer', 'einsatz');
        return view('rapporte.show', compact('rapport'));
    }

    // ── Hilfsmethoden ──────────────────────────────────────────────

    private function benachrichtigeAdmins(Rapport $rapport): void
    {
        $admins = Benutzer::where('organisation_id', $this->orgId())
            ->where('rolle', 'admin')
            ->where('id', '!=', auth()->id())
            ->where('aktiv', true)
            ->pluck('id');

        if ($admins->isEmpty()) return;

        $klientName = $rapport->klient->vollname();
        $verfasser  = auth()->user()->name;

        $nachricht = Nachricht::create([
            'absender_id'  => auth()->id(),
            'betreff'      => "⚠ Zwischenfall — {$klientName}",
            'inhalt'       => "Zwischenfall-Rapport erfasst am {$rapport->datum->format('d.m.Y')} "
                . "für {$klientName} von {$verfasser}.\n\n"
                . mb_substr($rapport->inhalt, 0, 300) . (mb_strlen($rapport->inhalt) > 300 ? '…' : ''),
            'referenz_typ' => 'rapport',
            'referenz_id'  => $rapport->id,
        ]);

        foreach ($admins as $adminId) {
            NachrichtEmpfaenger::create([
                'nachricht_id'  => $nachricht->id,
                'empfaenger_id' => $adminId,
            ]);
        }
    }

    private function sendeNachricht(Rapport $rapport, array $empfaengerIds, string $betreff): void
    {
        if (empty($empfaengerIds)) return;

        $nachricht = Nachricht::create([
            'absender_id'  => auth()->id(),
            'betreff'      => $betreff,
            'inhalt'       => mb_substr($rapport->inhalt, 0, 500) . (mb_strlen($rapport->inhalt) > 500 ? '…' : ''),
            'referenz_typ' => 'rapport',
            'referenz_id'  => $rapport->id,
        ]);

        foreach ($empfaengerIds as $id) {
            NachrichtEmpfaenger::create([
                'nachricht_id'  => $nachricht->id,
                'empfaenger_id' => (int) $id,
            ]);
        }
    }
}
