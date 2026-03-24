<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EinladungController extends Controller
{
    /** Einladungslink öffnen */
    public function show(string $token)
    {
        $benutzer = Benutzer::where('einladungs_token', $token)->first();

        if (!$benutzer) {
            return view('einladung.passwort', ['fehler' => 'Dieser Einladungslink ist ungültig.', 'token' => $token, 'benutzer' => null]);
        }

        if ($benutzer->einladungs_token_ablauf < now()) {
            return view('einladung.passwort', ['fehler' => 'Dieser Einladungslink ist abgelaufen. Bitte einen Administrator bitten, eine neue Einladung zu senden.', 'token' => $token, 'benutzer' => null]);
        }

        return view('einladung.passwort', compact('benutzer', 'token'));
    }

    /** Passwort speichern */
    public function store(Request $request, string $token)
    {
        $benutzer = Benutzer::where('einladungs_token', $token)->first();

        if (!$benutzer || $benutzer->einladungs_token_ablauf < now()) {
            return redirect()->route('einladung.show', $token)
                ->with('fehler', 'Dieser Einladungslink ist abgelaufen. Bitte einen Administrator bitten, eine neue Einladung zu senden.');
        }

        $request->validate([
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'password.min'       => 'Das Passwort muss mindestens 8 Zeichen lang sein.',
            'password.confirmed' => 'Die Passwörter stimmen nicht überein.',
        ]);

        $benutzer->update([
            'password'                => Hash::make($request->password),
            'einladungs_token'        => null,
            'einladungs_token_ablauf' => null,
        ]);

        return redirect()->route('login')
            ->with('status', 'Passwort wurde gesetzt. Du kannst dich jetzt anmelden.');
    }
}
