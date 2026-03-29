<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\Leistungsart;
use App\Models\Serie;
use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SerienController extends Controller
{
    private function orgId(): int { return auth()->user()->organisation_id; }

    // ─────────────────────────────────────────────────────────────────────────
    // STORE — neue Serie erstellen
    // ─────────────────────────────────────────────────────────────────────────

    public function store(Request $request, Klient $klient)
    {
        if ($klient->organisation_id !== $this->orgId()) abort(403);

        $request->merge([
            'leistungsarten' => collect($request->input('leistungsarten', []))
                ->filter(fn($la) => !empty($la['id']))->values()->toArray(),
        ]);

        $daten = $request->validate([
            'rhythmus'                 => ['required', 'in:taeglich,woechentlich'],
            'wochentage'               => ['nullable', 'array'],
            'wochentage.*'             => ['integer', 'between:0,6'],
            'leistungsarten'           => ['required', 'array', 'min:1'],
            'leistungsarten.*.id'      => ['required', 'exists:leistungsarten,id'],
            'leistungsarten.*.minuten' => ['required', 'integer', 'min:5'],
            'gueltig_ab'               => ['required', 'date'],
            'gueltig_bis'              => ['required', 'date', 'after_or_equal:gueltig_ab'],
            'zeit_von'                 => ['nullable', 'date_format:H:i'],
            'zeit_bis'                 => ['nullable', 'date_format:H:i'],
            'benutzer_id'              => ['nullable', 'exists:benutzer,id'],
            'leistungserbringer_typ'   => ['nullable', 'in:fachperson,angehoerig'],
            'bemerkung'                => ['nullable', 'string', 'max:500'],
        ]);

        $serieId    = (string) Str::uuid();
        $benutzerId = $daten['benutzer_id'] ?? auth()->id();
        $leTyp      = $daten['leistungserbringer_typ'] ?? 'fachperson';

        Serie::create([
            'id'                     => $serieId,
            'organisation_id'        => $this->orgId(),
            'klient_id'              => $klient->id,
            'benutzer_id'            => $daten['benutzer_id'] ?? null,
            'rhythmus'               => $daten['rhythmus'],
            'wochentage'             => ($daten['rhythmus'] === 'woechentlich') ? ($daten['wochentage'] ?? []) : null,
            'leistungsarten'         => $daten['leistungsarten'],
            'gueltig_ab'             => $daten['gueltig_ab'],
            'gueltig_bis'            => $daten['gueltig_bis'],
            'zeit_von'               => $daten['zeit_von'] ?? null,
            'zeit_bis'               => $daten['zeit_bis'] ?? null,
            'leistungserbringer_typ' => $leTyp,
            'bemerkung'              => $daten['bemerkung'] ?? null,
        ]);

        $anzahl = $this->generiereEinsaetze(
            $serieId, $klient, $benutzerId, $daten,
            \Carbon\Carbon::parse($daten['gueltig_ab']),
            \Carbon\Carbon::parse($daten['gueltig_bis'])
        );

        return redirect()->route('klienten.show', $klient)
            ->with('erfolg', $anzahl . ' Einsatz' . ($anzahl !== 1 ? 'ätze' : '') . ' angelegt und Touren zugewiesen.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EDIT / UPDATE — Serie bearbeiten
    // ─────────────────────────────────────────────────────────────────────────

    public function edit(Klient $klient, Serie $serie)
    {
        if ($serie->organisation_id !== $this->orgId()) abort(403);
        if ($serie->klient_id !== $klient->id) abort(403);

        $leistungsarten = Leistungsart::where('aktiv', true)->where('einheit', '!=', 'tage')->orderBy('bezeichnung')->get();
        $mitarbeiter    = Benutzer::where('organisation_id', $this->orgId())
            ->where('aktiv', true)->where('anstellungsart', '!=', 'angehoerig')->orderBy('nachname')->get();

        return view('serien.edit', compact('klient', 'serie', 'leistungsarten', 'mitarbeiter'));
    }

    public function update(Request $request, Klient $klient, Serie $serie)
    {
        if ($serie->organisation_id !== $this->orgId()) abort(403);
        if ($serie->klient_id !== $klient->id) abort(403);

        $request->merge([
            'leistungsarten' => collect($request->input('leistungsarten', []))
                ->filter(fn($la) => !empty($la['id']))->values()->toArray(),
        ]);

        $daten = $request->validate([
            'rhythmus'                 => ['required', 'in:taeglich,woechentlich'],
            'wochentage'               => ['nullable', 'array'],
            'wochentage.*'             => ['integer', 'between:0,6'],
            'leistungsarten'           => ['required', 'array', 'min:1'],
            'leistungsarten.*.id'      => ['required', 'exists:leistungsarten,id'],
            'leistungsarten.*.minuten' => ['required', 'integer', 'min:5'],
            'gueltig_bis'              => ['nullable', 'date'],
            'zeit_von'                 => ['nullable', 'date_format:H:i'],
            'zeit_bis'                 => ['nullable', 'date_format:H:i'],
            'benutzer_id'              => ['nullable', 'exists:benutzer,id'],
            'leistungserbringer_typ'   => ['nullable', 'in:fachperson,angehoerig'],
            'bemerkung'                => ['nullable', 'string', 'max:500'],
        ]);

        $benutzerId = $daten['benutzer_id'] ?? auth()->id();
        $gueltigBis = $daten['gueltig_bis'] ? \Carbon\Carbon::parse($daten['gueltig_bis']) : null;

        // Serie-Record aktualisieren
        $serie->update([
            'rhythmus'               => $daten['rhythmus'],
            'wochentage'             => ($daten['rhythmus'] === 'woechentlich') ? ($daten['wochentage'] ?? []) : null,
            'leistungsarten'         => $daten['leistungsarten'],
            'gueltig_bis'            => $gueltigBis,
            'zeit_von'               => $daten['zeit_von'] ?? null,
            'zeit_bis'               => $daten['zeit_bis'] ?? null,
            'benutzer_id'            => $daten['benutzer_id'] ?? null,
            'leistungserbringer_typ' => $daten['leistungserbringer_typ'] ?? 'fachperson',
            'bemerkung'              => $daten['bemerkung'] ?? null,
        ]);

        // Alle zukünftigen geplanten Einsätze löschen + leere Touren bereinigen
        $this->loescheZukuenftigeEinsaetze($serie->id);

        // Neu generieren ab heute
        $ende   = $gueltigBis ?? today()->addMonths(3);
        $anzahl = $this->generiereEinsaetze($serie->id, $klient, $benutzerId, $daten, today(), $ende);

        return redirect()->route('klienten.show', $klient)
            ->with('erfolg', 'Serie aktualisiert — ' . $anzahl . ' zukünftige Einsätze neu generiert und Touren zugewiesen.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BEENDEN
    // ─────────────────────────────────────────────────────────────────────────

    public function beenden(Request $request, Klient $klient, Serie $serie)
    {
        if ($serie->organisation_id !== $this->orgId()) abort(403);
        if ($serie->klient_id !== $klient->id) abort(403);

        $daten    = $request->validate(['gueltig_bis' => ['required', 'date']]);
        $neuesBis = \Carbon\Carbon::parse($daten['gueltig_bis']);

        $geloescht = $this->loescheZukuenftigeEinsaetze($serie->id, $neuesBis);
        $serie->update(['gueltig_bis' => $neuesBis]);

        $meldung = 'Serie beendet am ' . $neuesBis->format('d.m.Y') . '.';
        if ($geloescht > 0) {
            $meldung .= ' ' . $geloescht . ' Einsatz' . ($geloescht !== 1 ? 'ätze' : '') . ' gelöscht.';
        }

        return redirect()->route('klienten.show', $klient)->with('erfolg', $meldung);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────────────────

    public function destroy(Klient $klient, Serie $serie)
    {
        if ($serie->organisation_id !== $this->orgId()) abort(403);
        if ($serie->klient_id !== $klient->id) abort(403);

        $geloescht = $this->loescheZukuenftigeEinsaetze($serie->id);

        $hatAlte = Einsatz::where('serie_id', $serie->id)->exists();
        if (!$hatAlte) {
            $serie->delete();
            return redirect()->route('klienten.show', $klient)
                ->with('erfolg', 'Serie und ' . $geloescht . ' Einsatz' . ($geloescht !== 1 ? 'ätze' : '') . ' gelöscht.');
        }

        $serie->update(['gueltig_bis' => today()->subDay()]);
        return redirect()->route('klienten.show', $klient)
            ->with('erfolg', $geloescht . ' zukünftige Einsatz' . ($geloescht !== 1 ? 'ätze' : '') . ' gelöscht. Serie als beendet markiert.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Einsätze generieren + für Fachpersonen Tour zuweisen.
     * Gibt Anzahl erstellter Einsätze zurück.
     */
    private function generiereEinsaetze(
        string $serieId,
        Klient $klient,
        int $benutzerId,
        array $daten,
        \Carbon\Carbon $von,
        \Carbon\Carbon $bis
    ): int {
        $minuten    = collect($daten['leistungsarten'])->sum('minuten');
        $wochentage = array_map('intval', $daten['wochentage'] ?? []);
        $leTyp      = $daten['leistungserbringer_typ'] ?? 'fachperson';
        $current    = $von->copy()->startOfDay();
        $anzahl     = 0;

        while ($current->lte($bis) && $anzahl < 500) {
            $passt = match($daten['rhythmus']) {
                'taeglich'     => true,
                'woechentlich' => empty($wochentage) || in_array($current->dayOfWeek, $wochentage),
                default        => false,
            };

            if ($passt) {
                $e = Einsatz::create([
                    'organisation_id'        => $this->orgId(),
                    'klient_id'              => $klient->id,
                    'benutzer_id'            => $benutzerId,
                    'region_id'              => $klient->region_id,
                    'datum'                  => $current->format('Y-m-d'),
                    'zeit_von'               => $daten['zeit_von'] ?? null,
                    'zeit_bis'               => $daten['zeit_bis'] ?? null,
                    'minuten'                => $minuten ?: null,
                    'leistungserbringer_typ' => $leTyp,
                    'bemerkung'              => $daten['bemerkung'] ?? null,
                    'status'                 => 'geplant',
                    'serie_id'               => $serieId,
                ]);

                foreach ($daten['leistungsarten'] as $la) {
                    $e->einsatzLeistungsarten()->create([
                        'leistungsart_id' => $la['id'],
                        'minuten'         => $la['minuten'],
                    ]);
                }

                // Fachpersonen → Tour zuweisen (Angehörige brauchen keine Tour)
                if ($leTyp !== 'angehoerig') {
                    $this->einsatzZurTourZuweisen($e, $benutzerId, $current->format('Y-m-d'));
                }

                $anzahl++;
            }
            $current->addDay();
        }

        return $anzahl;
    }

    /**
     * Einsatz einer bestehenden Tour zuweisen oder neue Tour erstellen.
     */
    private array $benutzerCache = [];

    private function einsatzZurTourZuweisen(Einsatz $einsatz, int $benutzerId, string $datum): void
    {
        $tour = Tour::where('organisation_id', $this->orgId())
            ->where('benutzer_id', $benutzerId)
            ->whereDate('datum', $datum)
            ->first();

        if (!$tour) {
            if (!isset($this->benutzerCache[$benutzerId])) {
                $this->benutzerCache[$benutzerId] = Benutzer::find($benutzerId);
            }
            $ma = $this->benutzerCache[$benutzerId];
            $tour = Tour::create([
                'organisation_id' => $this->orgId(),
                'benutzer_id'     => $benutzerId,
                'datum'           => $datum,
                'bezeichnung'     => 'Tour ' . ($ma?->vorname ?? '') . ' · ' . \Carbon\Carbon::parse($datum)->format('d.m.Y'),
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

    /**
     * Zukünftige geplante Einsätze einer Serie löschen.
     * Optional: nur ab einem bestimmten Datum (inkl.).
     * Leere Touren werden ebenfalls gelöscht.
     * Gibt Anzahl gelöschter Einsätze zurück.
     */
    private function loescheZukuenftigeEinsaetze(string $serieId, ?\Carbon\Carbon $ab = null): int
    {
        $ab ??= today();

        $einsaetze = Einsatz::where('serie_id', $serieId)
            ->whereDate('datum', '>=', $ab)
            ->where('status', 'geplant')
            ->where('verrechnet', false)
            ->get();

        $tourIds = $einsaetze->pluck('tour_id')->filter()->unique()->values();

        foreach ($einsaetze as $e) {
            $e->delete();
        }

        // Leere Touren löschen
        foreach ($tourIds as $tourId) {
            $tour = Tour::find($tourId);
            if ($tour && $tour->einsaetze()->count() === 0) {
                $tour->delete();
            }
        }

        return $einsaetze->count();
    }
}
