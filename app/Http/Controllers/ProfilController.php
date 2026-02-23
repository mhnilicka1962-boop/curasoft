<?php

namespace App\Http\Controllers;

use App\Models\WebAuthnCredential;
use Illuminate\Support\Facades\Auth;

class ProfilController extends Controller
{
    public function index()
    {
        $passkeys = WebAuthnCredential::where('benutzer_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('profil.index', compact('passkeys'));
    }
}
