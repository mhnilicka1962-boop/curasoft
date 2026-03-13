<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\KlientBenutzer;
use Illuminate\Http\Request;

class AngehoerigenpflegeController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    public function index()
    {
        $orgId = $this->orgId();

        // Alle aktiven Pflegeverhältnisse: benutzer (Angehöriger) → klient
        $verhaeltnisse = KlientBenutzer::with(['benutzer', 'klient.region'])
            ->whereHas('benutzer', fn($q) => $q->where('organisation_id', $orgId)->where('aktiv', true))
            ->where('beziehungstyp', 'angehoerig_pflegend')
            ->orderBy('aktiv', 'desc')
            ->get();

        $benutzerId = $verhaeltnisse->pluck('benutzer_id')->unique();

        // Stunden diesen Monat
        $von = now()->startOfMonth()->toDateString();
        $bis = now()->endOfMonth()->toDateString();

        $monatsStats = Einsatz::where('organisation_id', $orgId)
            ->whereIn('benutzer_id', $benutzerId)
            ->whereBetween('datum', [$von, $bis])
            ->whereNotIn('status', ['storniert'])
            ->selectRaw('benutzer_id, COALESCE(SUM(minuten),0)::int AS plan_min, COUNT(*) AS anzahl')
            ->groupBy('benutzer_id')
            ->get()
            ->keyBy('benutzer_id');

        // Letzter Einsatz pro Angehöriger
        $letzteEinsaetze = Einsatz::where('organisation_id', $orgId)
            ->whereIn('benutzer_id', $benutzerId)
            ->whereNotIn('status', ['storniert'])
            ->orderByDesc('datum')
            ->get()
            ->groupBy('benutzer_id')
            ->map->first();

        return view('angehoerigenpflege.index', compact(
            'verhaeltnisse', 'monatsStats', 'letzteEinsaetze'
        ));
    }
}
