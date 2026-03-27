<?php

namespace App\Http\Controllers;

use App\Models\Benutzer;
use Illuminate\Support\Facades\Auth;

class DemoController extends Controller
{
    /**
     * Demo-Auto-Login — nur aktiv wenn DEMO_MODE=true in .env
     * Auf Produktiv-Tenants → 404
     */
    public function login(string $rolle)
    {
        if (!env('DEMO_MODE')) {
            abort(404);
        }

        $email = match ($rolle) {
            'admin'  => 'admin@curasoft-demo.ch',
            'pflege' => 'sandra@curasoft-demo.ch',
            'peter'  => 'peter@curasoft-demo.ch',
            'anna'   => 'anna@curasoft-demo.ch',
            default  => null,
        };

        if (!$email) {
            abort(404);
        }

        $benutzer = Benutzer::where('email', $email)->where('aktiv', true)->first();

        if (!$benutzer) {
            abort(404);
        }

        Auth::login($benutzer, remember: true);

        return redirect()->intended('/dashboard');
    }
}
