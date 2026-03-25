<?php

namespace App\Http\Controllers;

use App\Mail\Zeitnachweismail;
use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Organisation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use setasign\Fpdi\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class PersonalabrechnungController extends Controller
{
    private function orgId(): int
    {
        return auth()->user()->organisation_id;
    }

    private function vonBis(?int $jahr, ?int $monat): array
    {
        $jahr  = $jahr  ?: (int) now()->format('Y');
        $monat = $monat ?: (int) now()->format('n');
        $von   = Carbon::createFromDate($jahr, $monat, 1)->startOfMonth();
        return [$von, $von->copy()->endOfMonth()];
    }

    private function mitarbeiterMitStats(Carbon $von, Carbon $bis, ?string $suche = null)
    {
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

        $query = Benutzer::where('organisation_id', $this->orgId())
            ->where('aktiv', true)
            ->orderBy('nachname')
            ->orderBy('vorname');

        if ($suche) {
            $query->where(function ($q) use ($suche) {
                $q->whereRaw("LOWER(vorname || ' ' || nachname) LIKE ?", ['%' . mb_strtolower($suche) . '%'])
                  ->orWhereRaw("LOWER(nachname || ' ' || vorname) LIKE ?", ['%' . mb_strtolower($suche) . '%']);
            });
        }

        return $query->get()->map(function ($b) use ($stats) {
            $s = $stats->get($b->id);
            $b->stat_anzahl        = $s?->anzahl ?? 0;
            $b->stat_plan_min      = (int) ($s?->plan_minuten ?? 0);
            $b->stat_ist_min       = (int) ($s?->ist_minuten ?? 0);
            $b->stat_abgeschlossen = (int) ($s?->abgeschlossen ?? 0);
            return $b;
        })->sortByDesc('stat_anzahl');
    }

    public function index(Request $request)
    {
        $jahr  = (int) $request->input('jahr',  now()->format('Y'));
        $monat = (int) $request->input('monat', now()->format('n'));
        $suche = trim($request->input('suche', ''));

        [$von, $bis] = $this->vonBis($jahr, $monat);

        $mitarbeiter = $this->mitarbeiterMitStats($von, $bis, $suche ?: null);

        $jahre = range((int) now()->format('Y'), (int) now()->format('Y') - 3);

        return view('personalabrechnung.index', compact('mitarbeiter', 'jahr', 'monat', 'suche', 'von', 'bis', 'jahre'));
    }

    public function show(Request $request, Benutzer $benutzer)
    {
        abort_if($benutzer->organisation_id !== $this->orgId(), 403);
        // Pflege-Rolle darf nur eigene Daten sehen
        if (auth()->user()->rolle === 'pflege') {
            abort_if($benutzer->id !== auth()->id(), 403);
        }

        $jahr  = (int) $request->input('jahr',  now()->format('Y'));
        $monat = (int) $request->input('monat', now()->format('n'));
        [$von, $bis] = $this->vonBis($jahr, $monat);

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

        $jahre = range((int) now()->format('Y'), (int) now()->format('Y') - 3);

        return view('personalabrechnung.show', compact('benutzer', 'einsaetze', 'jahr', 'monat', 'von', 'bis', 'jahre'));
    }

    public function exportCsv(Request $request, Benutzer $benutzer)
    {
        abort_if($benutzer->organisation_id !== $this->orgId(), 403);
        // Pflege-Rolle darf nur eigene Daten sehen
        if (auth()->user()->rolle === 'pflege') {
            abort_if($benutzer->id !== auth()->id(), 403);
        }

        $jahr  = (int) $request->input('jahr',  now()->format('Y'));
        $monat = (int) $request->input('monat', now()->format('n'));
        [$von, $bis] = $this->vonBis($jahr, $monat);

        $einsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->where('benutzer_id', $benutzer->id)
            ->whereBetween('datum', [$von->toDateString(), $bis->toDateString()])
            ->whereNotIn('status', ['storniert'])
            ->with(['klient', 'leistungsart'])
            ->orderBy('datum')
            ->orderBy('zeit_von')
            ->get();

        $filename = 'personalabrechnung_' . $benutzer->nachname . '_' . $jahr . '-' . str_pad($monat, 2, '0', STR_PAD_LEFT) . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($einsaetze, $benutzer, $monat) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // BOM für Excel

            $monatStr = $jahr . '-' . str_pad($monat, 2, '0', STR_PAD_LEFT);
            fputcsv($handle, ['Personalabrechnung', $benutzer->vorname . ' ' . $benutzer->nachname, $monatStr], ';');
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
        // Pflege-Rolle darf nur eigene Daten sehen
        if (auth()->user()->rolle === 'pflege') {
            abort_if($benutzer->id !== auth()->id(), 403);
        }

        $jahr  = (int) $request->input('jahr',  now()->format('Y'));
        $monat = (int) $request->input('monat', now()->format('n'));
        [$von, $bis] = $this->vonBis($jahr, $monat);

        $org = Organisation::findOrFail($this->orgId());
        $logoBase64 = null;
        if ($org->logo_pfad && Storage::exists($org->logo_pfad)) {
            $logoBase64 = base64_encode(Storage::get($org->logo_pfad));
        }

        $filename  = 'zeitnachweis_' . $benutzer->nachname . '_' . $jahr . '-' . str_pad($monat, 2, '0', STR_PAD_LEFT) . '.pdf';
        $pdfInhalt = $this->pdfFuerBenutzer($benutzer, $von, $bis, $org, $logoBase64);

        return response($pdfInhalt, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function pdfFuerBenutzer(Benutzer $benutzer, Carbon $von, Carbon $bis, Organisation $org, ?string $logoBase64): string
    {
        $einsaetze = Einsatz::where('organisation_id', $this->orgId())
            ->where('benutzer_id', $benutzer->id)
            ->whereBetween('datum', [$von->toDateString(), $bis->toDateString()])
            ->whereNotIn('status', ['storniert'])
            ->with(['klient', 'leistungsart'])
            ->orderBy('datum')->orderBy('zeit_von')
            ->get()
            ->map(function ($e) {
                $e->ist_minuten = ($e->checkin_zeit && $e->checkout_zeit)
                    ? (int) $e->checkin_zeit->diffInMinutes($e->checkout_zeit)
                    : null;
                return $e;
            });

        $logoAusrichtung = $org->logo_ausrichtung ?? 'links_anschrift_rechts';
        $html = view('pdfs.personalabrechnung', compact('benutzer', 'einsaetze', 'von', 'bis', 'org', 'logoBase64', 'logoAusrichtung'))->render();
        return Pdf::loadHTML($html)->setOptions(['defaultFont' => 'DejaVu Sans'])->setPaper('A4', 'portrait')->output();
    }

    public function mailVersand(Request $request, Benutzer $benutzer)
    {
        abort_if($benutzer->organisation_id !== $this->orgId(), 403);
        abort_unless($benutzer->email_privat, 422, 'Keine private E-Mail-Adresse hinterlegt.');

        $jahr  = (int) $request->input('jahr',  now()->format('Y'));
        $monat = (int) $request->input('monat', now()->format('n'));
        [$von, $bis] = $this->vonBis($jahr, $monat);

        $org = Organisation::findOrFail($this->orgId());
        $logoBase64 = null;
        if ($org->logo_pfad && Storage::exists($org->logo_pfad)) {
            $logoBase64 = base64_encode(Storage::get($org->logo_pfad));
        }

        $monatStr  = $jahr . '-' . str_pad($monat, 2, '0', STR_PAD_LEFT);
        $dateiname = 'zeitnachweis_' . $benutzer->nachname . '_' . $monatStr . '.pdf';
        $pdfInhalt = $this->pdfFuerBenutzer($benutzer, $von, $bis, $org, $logoBase64);

        Mail::to($benutzer->email_privat)->send(new Zeitnachweismail($benutzer, $org, $pdfInhalt, $dateiname));

        return back()->with('erfolg', "Zeitnachweis an {$benutzer->email_privat} gesendet.");
    }

    public function sammelMail(Request $request)
    {
        $jahr  = (int) $request->input('jahr',  now()->format('Y'));
        $monat = (int) $request->input('monat', now()->format('n'));
        $suche = trim($request->input('suche', '')) ?: null;
        [$von, $bis] = $this->vonBis($jahr, $monat);

        $mitarbeiter = $this->mitarbeiterMitStats($von, $bis, $suche)->filter(fn($b) => $b->stat_anzahl > 0 && $b->email_privat);

        if ($mitarbeiter->isEmpty()) {
            return back()->with('fehler', 'Keine Mitarbeitenden mit Einsätzen und hinterlegter E-Mail gefunden.');
        }

        $org = Organisation::findOrFail($this->orgId());
        $logoBase64 = null;
        if ($org->logo_pfad && Storage::exists($org->logo_pfad)) {
            $logoBase64 = base64_encode(Storage::get($org->logo_pfad));
        }

        $monatStr = $jahr . '-' . str_pad($monat, 2, '0', STR_PAD_LEFT);
        $gesendet = 0;

        foreach ($mitarbeiter as $benutzer) {
            $dateiname = 'zeitnachweis_' . $benutzer->nachname . '_' . $monatStr . '.pdf';
            $pdfInhalt = $this->pdfFuerBenutzer($benutzer, $von, $bis, $org, $logoBase64);
            Mail::to($benutzer->email_privat)->send(new Zeitnachweismail($benutzer, $org, $pdfInhalt, $dateiname));
            $gesendet++;
        }

        return back()->with('erfolg', "{$gesendet} Zeitnachweis(e) versendet.");
    }

    public function sammelCsv(Request $request)
    {
        $jahr  = (int) $request->input('jahr',  now()->format('Y'));
        $monat = (int) $request->input('monat', now()->format('n'));
        $suche = trim($request->input('suche', '')) ?: null;
        [$von, $bis] = $this->vonBis($jahr, $monat);

        $mitarbeiter = $this->mitarbeiterMitStats($von, $bis, $suche)->filter(fn($b) => $b->stat_anzahl > 0);

        $monatStr = $jahr . '-' . str_pad($monat, 2, '0', STR_PAD_LEFT);
        $filename = 'personalabrechnung_alle_' . $monatStr . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($mitarbeiter, $von, $bis, $monatStr) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            foreach ($mitarbeiter as $benutzer) {
                $einsaetze = Einsatz::where('organisation_id', $this->orgId())
                    ->where('benutzer_id', $benutzer->id)
                    ->whereBetween('datum', [$von->toDateString(), $bis->toDateString()])
                    ->whereNotIn('status', ['storniert'])
                    ->with(['klient', 'leistungsart'])
                    ->orderBy('datum')
                    ->orderBy('zeit_von')
                    ->get();

                fputcsv($handle, ['Personalabrechnung', $benutzer->vorname . ' ' . $benutzer->nachname, $monatStr], ';');
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
                    if ($istMin !== null) $totalIst += $istMin;

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

                fputcsv($handle, ['TOTAL', '', '', '', '', $totalPlan, '', '', $totalIst, $totalIst - $totalPlan, ''], ';');
                fputcsv($handle, [], ';');
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function sammelPdf(Request $request)
    {
        $jahr  = (int) $request->input('jahr',  now()->format('Y'));
        $monat = (int) $request->input('monat', now()->format('n'));
        $suche = trim($request->input('suche', '')) ?: null;
        [$von, $bis] = $this->vonBis($jahr, $monat);

        $mitarbeiter = $this->mitarbeiterMitStats($von, $bis, $suche)->filter(fn($b) => $b->stat_anzahl > 0);

        if ($mitarbeiter->isEmpty()) {
            return back()->with('fehler', 'Keine Mitarbeitenden mit Einsätzen gefunden.');
        }

        $org = Organisation::findOrFail($this->orgId());
        $logoBase64 = null;
        if ($org->logo_pfad && Storage::exists($org->logo_pfad)) {
            $logoBase64 = base64_encode(Storage::get($org->logo_pfad));
        }

        // Jede Person als eigenständiges PDF rendern
        $pdfBytes = [];
        foreach ($mitarbeiter as $benutzer) {
            $pdfBytes[] = $this->pdfFuerBenutzer($benutzer, $von, $bis, $org, $logoBase64);
        }

        // Alle PDFs via FPDI zusammenmergen
        $tmpFiles = [];
        try {
            $fpdi = new Fpdi();
            $fpdi->SetAutoPageBreak(false);

            foreach ($pdfBytes as $bytes) {
                $tmp = tempnam(sys_get_temp_dir(), 'pdf_ma_');
                file_put_contents($tmp, $bytes);
                $tmpFiles[] = $tmp;

                $count = $fpdi->setSourceFile($tmp);
                for ($i = 1; $i <= $count; $i++) {
                    $tpl  = $fpdi->importPage($i);
                    $size = $fpdi->getTemplateSize($tpl);
                    $fpdi->AddPage('P', [$size['width'], $size['height']]);
                    $fpdi->useTemplate($tpl, 0, 0, $size['width'], $size['height']);
                }
            }

            $merged = $fpdi->Output('', 'S');
        } finally {
            foreach ($tmpFiles as $tmp) {
                @unlink($tmp);
            }
        }

        $monatStr = $jahr . '-' . str_pad($monat, 2, '0', STR_PAD_LEFT);
        return response($merged, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="personalabrechnung_alle_' . $monatStr . '.pdf"',
        ]);
    }
}
