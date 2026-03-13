<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Organisation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PersonalabrechnungController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    private function parseMonat(?string $monat): array
    {
        if ($monat && preg_match('/^\d{4}-\d{2}$/', $monat)) {
            $von = Carbon::createFromFormat('Y-m', $monat)->startOfMonth();
        } else {
            $von = now()->startOfMonth();
        }
        return [$von, $von->copy()->endOfMonth()];
    }

    public function index(Request $request)
    {
        $monat = $request->input('monat', now()->format('Y-m'));
        [$von, $bis] = $this->parseMonat($monat);

        $stats = Einsatz::where('organisation_id', $this->orgId())
            ->whereBetween('datum', [$von->toDateString(), $bis->toDateString()])
            ->whereNotIn('status', ['storniert'])
            ->whereNotNull('benutzer_id')
            ->selectRaw("
                benutzer_id,
                COUNT(*) AS anzahl,
                COALESCE(SUM(minuten), 0)::int AS plan_minuten,
                COALESCE(SUM(
                    CASE WHEN checkin_zeit IS NOT NULL AND checkout_zeit IS NOT NULL
                        THEN EXTRACT(EPOCH FROM (checkout_zeit - checkin_zeit)) / 60
                        ELSE 0
                    END
                )::int, 0) AS ist_minuten,
                COUNT(CASE WHEN status = 'abgeschlossen' THEN 1 END) AS abgeschlossen
            ")
            ->groupBy('benutzer_id')
            ->get()
            ->keyBy('benutzer_id');

        $mitarbeiter = Benutzer::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->orderBy('nachname')
            ->orderBy('vorname')
            ->get()
            ->map(function ($b) use ($stats) {
                $s = $stats->get($b->id);
                $b->stat_anzahl        = $s?->anzahl ?? 0;
                $b->stat_plan_min      = (int) ($s?->plan_minuten ?? 0);
                $b->stat_ist_min       = (int) ($s?->ist_minuten ?? 0);
                $b->stat_abgeschlossen = (int) ($s?->abgeschlossen ?? 0);
                return $b;
            })
            ->sortByDesc('stat_anzahl');

        $monate = collect(range(0, 17))->map(
            fn($i) => now()->startOfMonth()->subMonths($i)->format('Y-m')
        );

        return view('personalabrechnung.index', compact('mitarbeiter', 'monat', 'von', 'bis', 'monate'));
    }

    public function show(Request $request, Benutzer $benutzer)
    {
        abort_if($benutzer->organisation_id !== $this->orgId(), 403);

        $monat = $request->input('monat', now()->format('Y-m'));
        [$von, $bis] = $this->parseMonat($monat);

        $einsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->where('benutzer_id', $benutzer->id)
            ->whereBetween('datum', [$von->toDateString(), $bis->toDateString()])
            ->whereNotIn('status', ['storniert'])
            ->with(['klient', 'leistungsart'])
            ->orderBy('datum')
            ->orderBy('zeit_von')
            ->get()
            ->map(function ($e) {
                $e->ist_minuten = ($e->checkin_zeit && $e->checkout_zeit)
                    ? (int) $e->checkin_zeit->diffInMinutes($e->checkout_zeit)
                    : null;
                return $e;
            });

        $monate = collect(range(0, 17))->map(
            fn($i) => now()->startOfMonth()->subMonths($i)->format('Y-m')
        );

        return view('personalabrechnung.show', compact('benutzer', 'einsaetze', 'monat', 'von', 'bis', 'monate'));
    }

    public function exportCsv(Request $request, Benutzer $benutzer)
    {
        abort_if($benutzer->organisation_id !== $this->orgId(), 403);

        $monat = $request->input('monat', now()->format('Y-m'));
        [$von, $bis] = $this->parseMonat($monat);

        $einsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->where('benutzer_id', $benutzer->id)
            ->whereBetween('datum', [$von->toDateString(), $bis->toDateString()])
            ->whereNotIn('status', ['storniert'])
            ->with(['klient', 'leistungsart'])
            ->orderBy('datum')
            ->orderBy('zeit_von')
            ->get();

        $filename = 'personalabrechnung_' . $benutzer->nachname . '_' . $monat . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($einsaetze, $benutzer, $monat) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM für Excel

            fputcsv($handle, ['Personalabrechnung', $benutzer->vorname . ' ' . $benutzer->nachname, $monat], ';');
            fputcsv($handle, [], ';');
            fputcsv($handle, [
                'Datum', 'Klient', 'Leistungsart',
                'Geplant von', 'Geplant bis', 'Geplant Min',
                'Ist von', 'Ist bis', 'Ist Min',
                'Differenz Min', 'Status',
            ], ';');

            $totalPlan = 0;
            $totalIst  = 0;

            foreach ($einsaetze as $e) {
                $planMin = (int) ($e->minuten ?? 0);
                $istMin  = ($e->checkin_zeit && $e->checkout_zeit)
                    ? (int) $e->checkin_zeit->diffInMinutes($e->checkout_zeit)
                    : null;
                $diff = $istMin !== null ? ($istMin - $planMin) : '';

                $totalPlan += $planMin;
                if ($istMin !== null) {
                    $totalIst += $istMin;
                }

                fputcsv($handle, [
                    $e->datum->format('d.m.Y'),
                    ($e->klient?->vorname . ' ' . $e->klient?->nachname),
                    $e->leistungsart?->bezeichnung ?? '—',
                    $e->zeit_von ?? '',
                    $e->zeit_bis ?? '',
                    $planMin,
                    $e->checkin_zeit?->format('H:i') ?? '',
                    $e->checkout_zeit?->format('H:i') ?? '',
                    $istMin ?? '',
                    $diff,
                    $e->statusLabel(),
                ], ';');
            }

            fputcsv($handle, [], ';');
            fputcsv($handle, [
                'TOTAL', '', '', '', '',
                $totalPlan,
                '', '',
                $totalIst,
                $totalIst - $totalPlan,
                '',
            ], ';');

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function pdfExport(Request $request, Benutzer $benutzer)
    {
        abort_if($benutzer->organisation_id !== $this->orgId(), 403);

        $monat = $request->input('monat', now()->format('Y-m'));
        [$von, $bis] = $this->parseMonat($monat);

        $einsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->where('benutzer_id', $benutzer->id)
            ->whereBetween('datum', [$von->toDateString(), $bis->toDateString()])
            ->whereNotIn('status', ['storniert'])
            ->with(['klient', 'leistungsart'])
            ->orderBy('datum')
            ->orderBy('zeit_von')
            ->get()
            ->map(function ($e) {
                $e->ist_minuten = ($e->checkin_zeit && $e->checkout_zeit)
                    ? (int) $e->checkin_zeit->diffInMinutes($e->checkout_zeit)
                    : null;
                return $e;
            });

        $org = Organisation::findOrFail($this->orgId());

        // Logo einbetten
        $logoBase64 = null;
        if ($org->logo_pfad && Storage::exists($org->logo_pfad)) {
            $logoBase64 = base64_encode(Storage::get($org->logo_pfad));
        }

        $html = view('pdfs.personalabrechnung', compact(
            'benutzer', 'einsaetze', 'von', 'bis', 'org', 'logoBase64'
        ))->render();

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');

        $filename = 'zeitnachweis_' . $benutzer->nachname . '_' . $monat . '.pdf';

        return $pdf->download($filename);
    }
}
