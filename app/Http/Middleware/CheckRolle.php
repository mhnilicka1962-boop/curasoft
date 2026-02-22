<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRolle
{
    public function handle(Request $request, Closure $next, string ...$rollen): mixed
    {
        $benutzer = $request->user();

        if (!$benutzer) {
            return redirect()->route('login');
        }

        if (!in_array($benutzer->rolle, $rollen)) {
            abort(403, 'Sie haben keine Berechtigung für diesen Bereich.');
        }

        return $next($request);
    }
}
