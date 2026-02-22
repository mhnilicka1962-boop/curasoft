<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Einsatz;
use App\Models\Klient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CheckInController extends Controller
{
    // QR-Code gescannt — zeigt heutige Einsätze für diesen Klienten
    public function scan(string $token)
    {
        $klient = Klient::where('qr_token', $token)
            ->where('organisation_id', auth()->user()->organisation_id)
            ->firstOrFail();

        $einsaetze = Einsatz::with('benutzer')
            ->where('klient_id', $klient->id)
            ->whereDate('datum', Carbon::today())
            ->orderBy('zeit_von')
            ->get();

        return view('checkin.scan', compact('klient', 'einsaetze', 'token'));
    }

    // Check-in via QR
    public function checkinQr(Request $request, string $token)
    {
        $klient = Klient::where('qr_token', $token)
            ->where('organisation_id', auth()->user()->organisation_id)
            ->firstOrFail();

        $einsatz = Einsatz::where('id', $request->einsatz_id)
            ->where('klient_id', $klient->id)
            ->whereNull('checkin_zeit')
            ->firstOrFail();

        $einsatz->update([
            'checkin_zeit'    => now(),
            'checkin_methode' => 'qr',
            'status'          => 'aktiv',
        ]);

        AuditLog::schreiben('checkin', 'Einsatz', $einsatz->id,
            "Check-in via QR: {$klient->vorname} {$klient->nachname}");

        return redirect()->route('checkin.aktiv', $einsatz)
            ->with('erfolg', 'Erfolgreich eingecheckt!');
    }

    // Check-in via GPS (von Einsatz-Detailseite)
    public function checkinGps(Request $request, Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);

        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $distanz = $this->berechneDistanz(
            $request->lat, $request->lng,
            $einsatz->klient->klient_lat, $einsatz->klient->klient_lng
        );

        $einsatz->update([
            'checkin_zeit'           => now(),
            'checkin_lat'            => $request->lat,
            'checkin_lng'            => $request->lng,
            'checkin_methode'        => 'gps',
            'checkin_distanz_meter'  => $distanz,
            'status'                 => 'aktiv',
        ]);

        AuditLog::schreiben('checkin', 'Einsatz', $einsatz->id,
            "Check-in via GPS" . ($distanz ? " ({$distanz}m vom Klienten)" : ''));

        return redirect()->route('checkin.aktiv', $einsatz)
            ->with('erfolg', 'Eingecheckt' . ($distanz ? " — {$distanz}m vom Standort" : '') . '.');
    }

    // Manueller Check-in
    public function checkinManuell(Request $request, Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);

        $request->validate([
            'checkin_zeit' => ['required', 'date_format:H:i'],
        ]);

        $einsatz->update([
            'checkin_zeit'    => Carbon::today()->setTimeFromTimeString($request->checkin_zeit),
            'checkin_methode' => 'manuell',
            'status'          => 'aktiv',
        ]);

        AuditLog::schreiben('checkin', 'Einsatz', $einsatz->id,
            "Check-in manuell: {$request->checkin_zeit}");

        return redirect()->route('checkin.aktiv', $einsatz)
            ->with('erfolg', 'Manuell eingecheckt.');
    }

    // Aktiver Einsatz — Laufansicht
    public function aktiv(Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);
        $einsatz->load('klient');
        return view('checkin.aktiv', compact('einsatz'));
    }

    // Check-out via GPS
    public function checkoutGps(Request $request, Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);

        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $distanz = $this->berechneDistanz(
            $request->lat, $request->lng,
            $einsatz->klient->klient_lat, $einsatz->klient->klient_lng
        );

        $einsatz->update([
            'checkout_zeit'          => now(),
            'checkout_lat'           => $request->lat,
            'checkout_lng'           => $request->lng,
            'checkout_methode'       => 'gps',
            'checkout_distanz_meter' => $distanz,
            'minuten'                => $einsatz->dauerMinuten(),
            'status'                 => 'abgeschlossen',
        ]);

        AuditLog::schreiben('checkout', 'Einsatz', $einsatz->id,
            "Check-out via GPS — Dauer: {$einsatz->dauerMinuten()} Min.");

        return redirect()->route('einsaetze.show', $einsatz)
            ->with('erfolg', "Einsatz abgeschlossen — Dauer: {$einsatz->fresh()->dauerMinuten()} Minuten.");
    }

    // Manueller Check-out
    public function checkoutManuell(Request $request, Einsatz $einsatz)
    {
        $this->autorisiereZugriff($einsatz);

        $request->validate([
            'checkout_zeit' => ['required', 'date_format:H:i'],
        ]);

        $checkout = Carbon::today()->setTimeFromTimeString($request->checkout_zeit);
        $einsatz->update([
            'checkout_zeit'    => $checkout,
            'checkout_methode' => 'manuell',
            'minuten'          => $einsatz->checkin_zeit
                ? (int) $einsatz->checkin_zeit->diffInMinutes($checkout)
                : null,
            'status'           => 'abgeschlossen',
        ]);

        AuditLog::schreiben('checkout', 'Einsatz', $einsatz->id,
            "Check-out manuell: {$request->checkout_zeit}");

        return redirect()->route('einsaetze.show', $einsatz)
            ->with('erfolg', 'Einsatz abgeschlossen.');
    }

    // Haversine-Formel: Distanz in Metern zwischen zwei GPS-Punkten
    private function berechneDistanz(?float $lat1, ?float $lng1, ?float $lat2, ?float $lng2): ?int
    {
        if (!$lat1 || !$lng1 || !$lat2 || !$lng2) return null;

        $r  = 6371000; // Erdradius in Metern
        $p1 = deg2rad($lat1);
        $p2 = deg2rad($lat2);
        $dp = deg2rad($lat2 - $lat1);
        $dl = deg2rad($lng2 - $lng1);

        $a = sin($dp / 2) ** 2 + cos($p1) * cos($p2) * sin($dl / 2) ** 2;
        return (int) round($r * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function autorisiereZugriff(Einsatz $einsatz): void
    {
        if ($einsatz->organisation_id !== auth()->user()->organisation_id) abort(403);
    }
}
