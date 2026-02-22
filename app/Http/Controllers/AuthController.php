<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (!Auth::user()->aktiv) {
                Auth::logout();
                return back()->withErrors(['email' => 'Ihr Konto ist deaktiviert.']);
            }

            AuditLog::schreiben(
                aktion: 'login',
                beschreibung: 'Erfolgreich angemeldet',
            );

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'E-Mail oder Passwort ist falsch.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        AuditLog::schreiben(aktion: 'logout', beschreibung: 'Abgemeldet');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
