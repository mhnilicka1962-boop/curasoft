<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Einsatz;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KalenderController extends Controller
{
    public function index()
    {
        $orgId = $this->orgId();
        $von   = now()->subDays(3)->toDateString();
        $bis   = now()->addDays(14)->toDateString();

        $mitarbeiter = Benutzer::where('organisation_id', $orgId)
            ->where('aktiv', true)
            ->whereIn('rolle', ['admin', 'pflege'])
            ->where('anstellungsart', '!=', 'angehoerig')
            ->orderBy('nachname')
            ->get(['id', 'vorname', 'nachname', 'rolle', 'anstellungsart']);

        $counts = Einsatz::where('organisation_id', $orgId)
            ->whereNotIn('status', ['storniert'])
            ->whereBetween('datum', [$von, $bis])
            ->whereNotNull('benutzer_id')
            ->selectRaw('benutzer_id, COUNT(*) as anzahl')
            ->groupBy('benutzer_id')
            ->pluck('anzahl', 'benutzer_id');

        $mitarbeiter = $mitarbeiter
            ->filter(fn($m) => isset($counts[$m->id]))
            ->sort(function ($a, $b) use ($counts) {
                $diff = ($counts[$b->id] ?? 0) - ($counts[$a->id] ?? 0);
                return $diff !== 0 ? $diff : strcmp($a->nachname, $b->nachname);
            })->values();

        return view('kalender.index', compact('mitarbeiter'));
    }

    // JSON-API für FullCalendar
    public function einsaetze(Request $request)
    {
        $von = $request->get('start') ? Carbon::parse($request->get('start')) : Carbon::today()->startOfWeek();
        $bis = $request->get('end')   ? Carbon::parse($request->get('end'))   : Carbon::today()->endOfWeek();

        $einsaetze = Einsatz::with(['klient', 'benutzer', 'helfer', 'leistungsart'])
            ->where('organisation_id', $this->orgId())
            ->whereNotIn('status', ['storniert'])
            ->whereBetween('datum', [$von->toDateString(), $bis->toDateString()])
            ->get();

        // Doppelbelegungen berechnen (gleicher MA, überlappende Zeit)
        $doppelt = $this->berechneDoppelbelegungen($einsaetze);

        return response()->json($einsaetze->map(function ($e) use ($doppelt) {
            $hatZeit   = $e->zeit_von && $e->zeit_bis;
            $start     = $e->datum->format('Y-m-d') . ($hatZeit ? 'T' . $e->zeit_von : '');
            $end       = $e->datum->format('Y-m-d') . ($hatZeit ? 'T' . $e->zeit_bis : '');
            $istDoppelt = in_array($e->id, $doppelt);

            return [
                'id'              => $e->id,
                'resourceId'      => $e->benutzer_id ? (string) $e->benutzer_id : 'unzugeteilt',
                'title'           => ($e->klient ? $e->klient->vorname . ' ' . $e->klient->nachname : '?')
                                   . ($hatZeit ? ' ' . substr($e->zeit_von, 0, 5) : ''),
                'start'           => $start,
                'end'             => $end,
                'allDay'          => !$hatZeit,
                'backgroundColor' => $istDoppelt ? '#dc2626' : $this->statusFarbe($e->status),
                'borderColor'     => $istDoppelt ? '#991b1b' : $this->statusFarbe($e->status),
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'status'       => $e->status,
                    'statusLabel'  => $e->statusLabel(),
                    'leistungsart' => $e->leistungsart?->bezeichnung,
                    'klient_id'    => $e->klient_id,
                    'klient_name'  => $e->klient ? $e->klient->vorname . ' ' . $e->klient->nachname : '?',
                    'benutzer_name'=> $e->benutzer ? $e->benutzer->vorname . ' ' . $e->benutzer->nachname : '—',
                    'helfer_name'  => $e->helfer ? $e->helfer->vorname . ' ' . $e->helfer->nachname : null,
                    'doppelt'      => $istDoppelt,
                    'zeit_von'     => $e->zeit_von ? substr($e->zeit_von, 0, 5) : null,
                    'zeit_bis'     => $e->zeit_bis ? substr($e->zeit_bis, 0, 5) : null,
                ],
            ];
        }));
    }

    // Drag & Drop: Mitarbeiter oder Zeit ändern
    public function aktualisieren(Request $request, Einsatz $einsatz)
    {
        if ($einsatz->organisation_id !== $this->orgId()) abort(403);

        $request->validate([
            'datum'       => ['sometimes', 'date'],
            'benutzer_id' => ['sometimes', 'nullable', 'exists:benutzer,id'],
            'zeit_von'    => ['sometimes', 'nullable', 'date_format:H:i'],
            'zeit_bis'    => ['sometimes', 'nullable', 'date_format:H:i'],
        ]);

        $data = array_filter($request->only(['datum', 'benutzer_id', 'zeit_von', 'zeit_bis']),
            fn($v) => $v !== null);

        // Mitarbeiter-Freigabe prüfen
        if (!empty($data['benutzer_id'])) {
            $ma = Benutzer::find($data['benutzer_id']);
            if ($ma && !$ma->darfLeistungsart($einsatz->leistungsart_id)) {
                return response()->json([
                    'fehler' => $ma->vorname . ' ' . $ma->nachname . ' ist für diese Leistungsart nicht freigegeben.'
                ], 422);
            }
        }

        $einsatz->update($data);

        return response()->json(['ok' => true]);
    }

    private function berechneDoppelbelegungen($einsaetze): array
    {
        $doppelt = [];
        $mitEinsaetzen = $einsaetze->filter(fn($e) => $e->benutzer_id && $e->zeit_von && $e->zeit_bis);

        foreach ($mitEinsaetzen as $a) {
            foreach ($mitEinsaetzen as $b) {
                if ($a->id >= $b->id) continue;
                if ($a->benutzer_id !== $b->benutzer_id) continue;
                if ($a->datum->format('Y-m-d') !== $b->datum->format('Y-m-d')) continue;

                // Überlappung: A beginnt vor B endet UND B beginnt vor A endet
                if ($a->zeit_von < $b->zeit_bis && $b->zeit_von < $a->zeit_bis) {
                    $doppelt[] = $a->id;
                    $doppelt[] = $b->id;
                }
            }
        }

        return array_unique($doppelt);
    }

    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    private function statusFarbe(string $status): string
    {
        return match($status) {
            'geplant'       => '#2563eb',
            'aktiv'         => '#d97706',
            'abgeschlossen' => '#16a34a',
            default         => '#6b7280',
        };
    }
}
