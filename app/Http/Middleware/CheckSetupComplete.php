<?php

namespace App\Http\Middleware;

use App\Models\Benutzer;
use Closure;
use Illuminate\Http\Request;

class CheckSetupComplete
{
    public function handle(Request $request, Closure $next)
    {
        $setupDone = Benutzer::exists();

        // Auf /setup zugreifen obwohl schon eingerichtet → Login
        if ($request->routeIs('setup.*') && $setupDone) {
            return redirect()->route('login');
        }

        // Auf andere Seiten zugreifen ohne Einrichtung → Setup
        if (!$request->routeIs('setup.*') && !$setupDone) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }
}
