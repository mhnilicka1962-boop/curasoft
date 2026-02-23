<?php

namespace App\Http\Controllers;

use App\Mail\MagicLinkMail;
use App\Models\AuditLog;
use App\Models\Benutzer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    private const MAGIC_LINK_MINUTEN = 15;

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

        $rateLimiterKey = 'login:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimiterKey, maxAttempts: 5)) {
            $sekunden = RateLimiter::availableIn($rateLimiterKey);
            return back()->withErrors([
                'email' => "Zu viele Anmeldeversuche. Bitte warten Sie {$sekunden} Sekunden.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            RateLimiter::clear($rateLimiterKey);
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

        RateLimiter::hit($rateLimiterKey, decay: 900); // 15 Minuten Sperre nach 5 Versuchen

        return back()->withErrors([
            'email' => 'E-Mail oder Passwort ist falsch.',
        ])->onlyInput('email');
    }

    /** Magic Link: E-Mail mit Login-Link senden */
    public function sendMagicLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $rateLimiterKey = 'magic-link:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateLimiterKey, maxAttempts: 5)) {
            $sekunden = RateLimiter::availableIn($rateLimiterKey);
            return back()->withErrors([
                'email' => "Zu viele Anfragen. Bitte warten Sie {$sekunden} Sekunden.",
            ]);
        }
        RateLimiter::hit($rateLimiterKey, decay: 900);

        $benutzer = Benutzer::where('email', $request->email)->where('aktiv', true)->first();

        if (!$benutzer) {
            return back()->withErrors(['email' => 'Kein aktives Konto mit dieser E-Mail gefunden.']);
        }

        $token = Str::random(48);
        DB::table('login_tokens')->insert([
            'email'      => $benutzer->email,
            'token'      => hash('sha256', $token),
            'expires_at' => now()->addMinutes(self::MAGIC_LINK_MINUTEN),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $link = route('login.verify', ['token' => $token]);
        Mail::to($benutzer->email)->send(new MagicLinkMail($link, self::MAGIC_LINK_MINUTEN));

        return back()->with('status', 'Login-Link wurde an ' . $benutzer->email . ' gesendet. Der Link ist ' . self::MAGIC_LINK_MINUTEN . ' Minuten gültig.');
    }

    /** Magic Link: Token prüfen und einloggen */
    public function verifyMagicLink(Request $request, string $token)
    {
        $hash = hash('sha256', $token);
        $row  = DB::table('login_tokens')
            ->where('token', $hash)
            ->where('expires_at', '>', now())
            ->first();

        if (!$row) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Der Login-Link ist ungültig oder abgelaufen. Bitte erneut anfordern.']);
        }

        DB::table('login_tokens')->where('token', $hash)->delete();

        $benutzer = Benutzer::where('email', $row->email)->firstOrFail();
        Auth::login($benutzer, true);
        $request->session()->regenerate();

        AuditLog::schreiben('login', 'Erfolgreich angemeldet (Magic Link)');

        return redirect()->intended(route('dashboard'));
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
