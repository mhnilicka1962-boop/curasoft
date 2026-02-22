<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use App\Models\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetupController extends Controller
{
    public function index()
    {
        return view('setup.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'org_name'          => ['required', 'string', 'max:255'],
            'logo'              => ['nullable', 'image', 'max:2048'],
            'farbe_primaer'     => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'layout'            => ['required', 'in:sidebar,topnav'],
            'vorname'           => ['required', 'string', 'max:100'],
            'nachname'          => ['required', 'string', 'max:100'],
            'email'             => ['required', 'email', 'max:255'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Logo hochladen
        $logoPfad = null;
        if ($request->hasFile('logo')) {
            $file     = $request->file('logo');
            $dateiname = 'logo.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $dateiname);
            $logoPfad = 'uploads/' . $dateiname;
        }

        // Organisation anlegen
        $org = Organisation::create([
            'name'               => $request->org_name,
            'logo_pfad'          => $logoPfad,
            'theme_layout'       => $request->layout,
            'theme_farbe_primaer' => $request->farbe_primaer,
        ]);

        // Admin-Benutzer anlegen
        $benutzer = Benutzer::create([
            'organisation_id' => $org->id,
            'vorname'         => $request->vorname,
            'nachname'        => $request->nachname,
            'email'           => $request->email,
            'password'        => $request->password,
            'rolle'           => 'admin',
            'aktiv'           => true,
        ]);

        // .env aktualisieren
        $this->updateEnv([
            'CS_APP_NAME'          => '"' . $request->org_name . '"',
            'CS_LOGO'              => $logoPfad ?? '',
            'CS_FARBE_PRIMAER'     => $request->farbe_primaer,
            'CS_LAYOUT'            => $request->layout,
        ]);

        // Config-Cache leeren damit neue .env-Werte greifen
        \Artisan::call('config:clear');

        // Direkt einloggen
        Auth::login($benutzer);

        return redirect()->route('dashboard')->with('status', 'CuraSoft wurde erfolgreich eingerichtet. Willkommen!');
    }

    private function updateEnv(array $values): void
    {
        $envPfad  = base_path('.env');
        $inhalt   = file_get_contents($envPfad);

        foreach ($values as $key => $value) {
            $pattern     = '/^' . preg_quote($key, '/') . '=.*/m';
            $replacement = $key . '=' . $value;

            if (preg_match($pattern, $inhalt)) {
                $inhalt = preg_replace($pattern, $replacement, $inhalt);
            } else {
                $inhalt .= "\n" . $replacement;
            }
        }

        file_put_contents($envPfad, $inhalt);
    }
}
