<?php

namespace App\Http\Controllers;

use App\Services\ClaudeService;
use Illuminate\Http\Request;

class KiController extends Controller
{
    public function rapportVorschlag(Request $request)
    {
        $request->validate([
            'stichworte'  => ['required', 'string', 'max:2000'],
            'klient_name' => ['required', 'string', 'max:200'],
            'rapport_typ' => ['required', 'string'],
            'datum'       => ['required', 'string'],
        ]);

        try {
            $claude = new ClaudeService();
            $text   = $claude->rapportVorschlag(
                $request->stichworte,
                $request->klient_name,
                $request->rapport_typ,
                $request->datum
            );

            return response()->json(['success' => true, 'text' => $text]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => 'KI nicht verfügbar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
