<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EinladungController extends Controller
{
    /** Einladungslink — direkt einloggen */
    public function show(string $token)
    {
        $benutzer = Benutzer::where('einladungs_token', $token)->first();

        if (!$benutzer) {
            return view('einladung.passwort', ['fehler' => 'Dieser Einladungslink ist ungültig.', 'token' => $token, 'benutzer' => null]);
        }

        if ($benutzer->einladungs_token_ablauf < now()) {
            return view('einladung.passwort', ['fehler' => 'Dieser Einladungslink ist abgelaufen. Bitte einen Administrator bitten, eine neue Einladung zu senden.', 'token' => $token, 'benutzer' => null]);
        }

        // Token einmalig verbrauchen + einloggen
        $benutzer->update([
            'einladungs_token'        => null,
            'einladungs_token_ablauf' => null,
        ]);

        Auth::login($benutzer);

        return redirect()->route('dashboard')
            ->with('erfolg', 'Willkommen, ' . $benutzer->vorname . '! Du bist jetzt eingeloggt.');
    }
}
