<?php

namespace App\Console\Commands;

use App\Models\Einsatz;
use App\Models\EinsatzLeistungsart;
use App\Models\Organisation;
use App\Models\Serie;
use App\Models\Tagespauschale;
use App\Models\Tour;
use App\Models\Benutzer;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EinsaetzeGenerieren extends Command
{
    protected $signature = 'einsaetze:generieren {--tenant= : Nur diesen Tenant verarbeiten (Subdomain)}';
    protected $description = 'Einsätze für alle Serien mit auto_verlaengern=true generieren (rolling window)';

    public function handle(): int
    {
        $tenants = DB::connection('master')->table('tenants')
            ->where('aktiv', true)
            ->when($this->option('tenant'), fn($q, $sub) => $q->where('subdomain', $sub))
            ->get();

        if ($tenants->isEmpty()) {
            $this->warn('Keine aktiven Tenants gefunden.');
            return self::SUCCESS;
        }

        $gesamtFehler = 0;

        foreach ($tenants as $tenant) {
            $this->line("── {$tenant->subdomain}");

            Config::set('database.connections.tenant', [
                'driver'   => 'pgsql',
                'host'     => env('DB_HOST', 'localhost'),
                'port'     => env('DB_PORT', '5432'),
                'database' => $tenant->db_name,
                'username' => $tenant->db_user,
                'password' => $tenant->db_password,
                'charset'  => 'utf8',
                'schema'   => 'public',
            ]);
            DB::purge('tenant');
            DB::setDefaultConnection('tenant');

            try {
                $fehler = $this->verarbeite($tenant);
                $gesamtFehler += $fehler;
            } catch (\Exception $e) {
                $this->error("   ✗ {$tenant->subdomain}: " . $e->getMessage());
                $this->benachrichtigeAdmin($tenant->subdomain, $e->getMessage());
                $gesamtFehler++;
            }

            DB::setDefaultConnection('pgsql');
            $this->newLine();
        }

        return $gesamtFehler > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function verarbeite(object $tenant): int
    {
        $org = Organisation::first();
        if (!$org) {
            $this->warn("   Keine Organisation gefunden.");
            return 0;
        }

        $vorlauf = max(5, min(30, $org->einsatz_vorlauf_tage ?? 10));
        $horizon = today()->addDays($vorlauf);

        // Alle aktiven Serien für aktive Klienten:
        // - auto_verlaengern=true: bis Horizont auffüllen
        // - auto_verlaengern=false: bis Enddatum auffüllen (sofern noch nicht erreicht)
        $serien = Serie::whereHas('klient', fn($q) => $q->where('aktiv', true))
            ->where('gueltig_ab', '<=', today())
            ->where(fn($q) => $q
                ->where('auto_verlaengern', true)
                ->orWhere(fn($q2) => $q2
                    ->where('auto_verlaengern', false)
                    ->whereNotNull('gueltig_bis')
                    ->where('gueltig_bis', '>', today())
                )
            )
            ->with('klient')
            ->get();

        $this->line("   Serien: {$serien->count()}, Horizont: {$horizon->format('d.m.Y')}");

        $totalGeneriert = 0;
        $fehler = 0;
        $benutzerCache = [];

        foreach ($serien as $serie) {
            try {
                // auto_verlaengern=false: bis Enddatum generieren, aber max. bis Horizont
                $serieHorizont = $serie->auto_verlaengern
                    ? $horizon
                    : ($serie->gueltig_bis->lt($horizon) ? $serie->gueltig_bis : $horizon);
                $anzahl = $this->generiereFehlende($serie, $serieHorizont, $benutzerCache);
                if ($anzahl > 0) {
                    $this->line("   + {$anzahl} Einsätze für Serie #{$serie->id} ({$serie->klient->nachname})");
                    $totalGeneriert += $anzahl;
                }
            } catch (\Exception $e) {
                $this->error("   ✗ Serie {$serie->id}: " . $e->getMessage());
                $fehler++;
            }
        }

        // Tagespauschalen: gleiche Rolling-Window-Logik
        $pauschalen = Tagespauschale::whereHas('klient', fn($q) => $q->where('aktiv', true))
            ->where('datum_von', '<=', today())
            ->where(fn($q) => $q
                ->where('auto_verlaengern', true)
                ->orWhere(fn($q2) => $q2
                    ->where('auto_verlaengern', false)
                    ->whereNotNull('datum_bis')
                    ->where('datum_bis', '>', today())
                )
            )
            ->get();

        $this->line("   Tagespauschalen: {$pauschalen->count()}");

        foreach ($pauschalen as $tp) {
            try {
                $anzahl = $tp->generiereFehlende($horizon);
                if ($anzahl > 0) {
                    $this->line("   + {$anzahl} Tages-Einsätze für Tagespauschale #{$tp->id}");
                    $totalGeneriert += $anzahl;
                }
            } catch (\Exception $e) {
                $this->error("   ✗ Tagespauschale {$tp->id}: " . $e->getMessage());
                $fehler++;
            }
        }

        // Letzten Lauf festhalten
        $org->update(['letzter_generierungs_lauf' => now()]);

        // Log-Eintrag schreiben
        DB::table('generierungs_log')->insert([
            'ausgefuehrt_at'      => now(),
            'einsaetze_generiert' => $totalGeneriert,
            'fehler'              => $fehler,
            'via'                 => 'auto',
            'meldung'             => $fehler > 0 ? "{$fehler} Fehler beim Generieren" : null,
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        $this->line("   ✓ {$totalGeneriert} Einsätze generiert" . ($fehler > 0 ? ", {$fehler} Fehler" : ''));

        if ($fehler > 0) {
            $this->benachrichtigeAdmin($tenant->subdomain ?? '?', "{$fehler} Serien konnten nicht verarbeitet werden.");
            $this->benachrichtigeTenantAdmin($org, "{$fehler} Serien konnten beim automatischen Generieren nicht verarbeitet werden.");
        }

        return $fehler;
    }

    public function generiereFehlendPublic(Serie $serie, Carbon $horizon): int
    {
        $cache = [];
        return $this->generiereFehlende($serie, $horizon, $cache);
    }

    private function generiereFehlende(Serie $serie, Carbon $horizon, array &$benutzerCache): int
    {
        $klient   = $serie->klient;
        $leTyp    = $serie->leistungserbringer_typ ?? 'fachperson';
        $wochentage = array_map('intval', $serie->wochentage ?? []);


        // Bis wohin generieren: min(horizon, gueltig_bis)
        $bis = $serie->gueltig_bis
            ? ($serie->gueltig_bis->lt($horizon) ? $serie->gueltig_bis : $horizon)
            : $horizon;

        // Letzten bereits generierten Einsatz dieser Serie finden
        $letzter = Einsatz::where('serie_id', $serie->id)
            ->orderByDesc('datum')
            ->value('datum');

        $ab = $letzter
            ? Carbon::parse($letzter)->addDay()
            : Carbon::parse($serie->gueltig_ab)->max(today());

        if ($ab->gt($bis)) {
            return 0;
        }

        $benutzerId = $serie->benutzer_id ?? null;
        $anzahl = 0;
        $current = $ab->copy()->startOfDay();

        while ($current->lte($bis) && $anzahl < 5000) {
            $passt = match ($serie->rhythmus) {
                'taeglich'     => true,
                'woechentlich' => empty($wochentage) || in_array($current->dayOfWeek, $wochentage),
                default        => false,
            };

            if ($passt) {
                $minuten = collect($serie->leistungsarten)->sum('minuten');

                $e = Einsatz::create([
                    'organisation_id'        => $serie->organisation_id,
                    'klient_id'              => $klient->id,
                    'benutzer_id'            => $benutzerId,
                    'region_id'              => $klient->region_id,
                    'datum'                  => $current->format('Y-m-d'),
                    'zeit_von'               => $serie->zeit_von,
                    'zeit_bis'               => $serie->zeit_bis,
                    'minuten'                => $minuten ?: null,
                    'leistungserbringer_typ' => $leTyp,
                    'bemerkung'              => $serie->bemerkung,
                    'status'                 => 'geplant',
                    'serie_id'               => $serie->id,
                ]);

                foreach ($serie->leistungsarten as $la) {
                    EinsatzLeistungsart::create([
                        'einsatz_id'      => $e->id,
                        'leistungsart_id' => $la['id'],
                        'minuten'         => $la['minuten'],
                    ]);
                }

                if ($leTyp !== 'angehoerig' && $benutzerId) {
                    $this->einsatzZurTourZuweisen($e, $benutzerId, $current->format('Y-m-d'), $serie->organisation_id, $benutzerCache);
                }

                $anzahl++;
            }

            $current->addDay();
        }

        return $anzahl;
    }

    private function einsatzZurTourZuweisen(Einsatz $einsatz, int $benutzerId, string $datum, int $orgId, array &$benutzerCache): void
    {
        $tour = Tour::where('organisation_id', $orgId)
            ->where('benutzer_id', $benutzerId)
            ->whereDate('datum', $datum)
            ->first();

        if (!$tour) {
            if (!isset($benutzerCache[$benutzerId])) {
                $benutzerCache[$benutzerId] = Benutzer::find($benutzerId);
            }
            $ma = $benutzerCache[$benutzerId];
            $tour = Tour::create([
                'organisation_id' => $orgId,
                'benutzer_id'     => $benutzerId,
                'datum'           => $datum,
                'bezeichnung'     => 'Tour ' . ($ma?->vorname ?? '') . ' · ' . Carbon::parse($datum)->format('d.m.Y'),
                'start_zeit'      => $einsatz->zeit_von ?? '08:00:00',
                'status'          => 'geplant',
            ]);
        }

        $max = $tour->einsaetze()->max('tour_reihenfolge') ?? 0;
        $einsatz->update([
            'tour_id'          => $tour->id,
            'tour_reihenfolge' => $max + 1,
        ]);
    }

    private function benachrichtigeAdmin(string $subdomain, string $meldung): void
    {
        try {
            Mail::raw(
                "Fehler beim automatischen Generieren von Einsätzen\n\nTenant: {$subdomain}\n\n{$meldung}",
                fn($m) => $m->to('info@itjob.ch')->subject("Einsatz-Generierung Fehler: {$subdomain}")
            );
        } catch (\Exception) {
            // Mail-Fehler nicht eskalieren
        }
    }

    private function benachrichtigeTenantAdmin(Organisation $org, string $meldung): void
    {
        if (!$org->email) return;
        try {
            Mail::raw(
                "Automatische Einsatz-Generierung\n\n{$meldung}\n\nBitte prüfen Sie die Serien unter Firmengrunddaten.",
                fn($m) => $m->to($org->email)->subject('Einsatz-Generierung: Fehler aufgetreten')
            );
        } catch (\Exception) {
            // Mail-Fehler nicht eskalieren
        }
    }
}
