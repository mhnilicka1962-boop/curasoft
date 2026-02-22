<?php

namespace App\Http\Controllers;

use App\Models\Dokument;
use App\Models\Klient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DokumenteController extends Controller
{
    private function orgId(): int { return auth()->user()->organisation_id; }

    public function store(Request $request)
    {
        $request->validate([
            'klient_id'    => ['nullable', 'exists:klienten,id'],
            'dokument_typ' => ['required', 'in:pflegeplanung,vertrag,vollmacht,arztzeugnis,bericht,rechnung_kopie,sonstiges'],
            'bezeichnung'  => ['required', 'string', 'max:255'],
            'vertraulich'  => ['boolean'],
            'datei'        => ['required', 'file', 'max:20480', 'mimes:pdf,docx,xlsx,jpg,jpeg,png,gif'],
        ]);

        $datei = $request->file('datei');
        $orgId = $this->orgId();
        $verzeichnis = "dokumente/{$orgId}";
        $dateiname = Str::uuid() . '.' . $datei->getClientOriginalExtension();
        $pfad = $datei->storeAs($verzeichnis, $dateiname);

        if ($request->filled('klient_id')) {
            $klient = Klient::findOrFail($request->klient_id);
            if ($klient->organisation_id !== $orgId) abort(403);
        }

        Dokument::create([
            'organisation_id' => $orgId,
            'klient_id'       => $request->klient_id ?: null,
            'hochgeladen_von' => auth()->id(),
            'dokument_typ'    => $request->dokument_typ,
            'bezeichnung'     => $request->bezeichnung,
            'dateiname'       => $datei->getClientOriginalName(),
            'dateipfad'       => $pfad,
            'mime_type'       => $datei->getMimeType(),
            'groesse_bytes'   => $datei->getSize(),
            'vertraulich'     => $request->boolean('vertraulich'),
        ]);

        return back()->with('erfolg', 'Dokument wurde hochgeladen.');
    }

    public function download(Dokument $dokument)
    {
        if ($dokument->organisation_id !== $this->orgId()) abort(403);
        if (!Storage::exists($dokument->dateipfad)) abort(404);

        return Storage::download($dokument->dateipfad, $dokument->dateiname);
    }

    public function destroy(Dokument $dokument)
    {
        if ($dokument->organisation_id !== $this->orgId()) abort(403);

        Storage::delete($dokument->dateipfad);
        $dokument->delete();

        return back()->with('erfolg', 'Dokument wurde gelöscht.');
    }
}
