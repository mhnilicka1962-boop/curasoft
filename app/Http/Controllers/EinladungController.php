<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EinladungController extends Controller
{
    /** Einladungslink — Passwort-Formular anzeigen */
    public function show(string $token)
    {
        $benutzer = Benutzer::where('einladungs_token', $token)->first();

        if (!$benutzer) {
            return view('einladung.passwort', ['fehler' => 'Dieser Einladungslink ist ungültig.', 'token' => $token, 'benutzer' => null]);
        }

        if ($benutzer->einladungs_token_ablauf < now()) {
            return view('einladung.passwort', ['fehler' => 'Dieser Einladungslink ist abgelaufen. Bitte einen Administrator bitten, eine neue Einladung zu senden.', 'token' => $token, 'benutzer' => null]);
        }

        return view('einladung.passwort', compact('token', 'benutzer'));
    }

    /** Passwort speichern, Token verbrauchen, einloggen */
    public function store(Request $request, string $token)
    {
        $benutzer = Benutzer::where('einladungs_token', $token)->first();

        if (!$benutzer || $benutzer->einladungs_token_ablauf < now()) {
            return redirect()->route('einladung.show', $token)
                ->withErrors('Dieser Einladungslink ist abgelaufen oder ungültig.');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $benutzer->update([
            'password'                => $request->password,
            'einladungs_token'        => null,
            'einladungs_token_ablauf' => null,
        ]);

        Auth::login($benutzer);

        return redirect()->route('dashboard')
            ->with('erfolg', 'Willkommen, ' . $benutzer->vorname . '! Dein Passwort wurde gesetzt.');
    }
}
