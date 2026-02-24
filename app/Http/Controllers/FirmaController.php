<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\OrganisationRegion;
use App\Models\Region;
use App\Services\BexioService;
use Illuminate\Http\Request;

class FirmaController extends Controller
{
    private function org(): Organisation
    {
        return Organisation::findOrFail(auth()->user()->organisation_id);
    }

    public function index()
    {
        $org = $this->org();
        $org->load('regionen.region');

        $alleRegionen    = Region::orderBy('kuerzel')->get();
        $orgRegionenMap  = $org->regionen->keyBy('region_id');

        return view('stammdaten.firma.index', compact('org', 'alleRegionen', 'orgRegionenMap'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name'                     => ['required', 'string', 'max:150'],
            'zsr_nr'                   => ['nullable', 'string', 'max:20'],
            'mwst_nr'                  => ['nullable', 'string', 'max:30'],
            'adresse'                  => ['nullable', 'string', 'max:200'],
            'postfach'                 => ['nullable', 'string', 'max:50'],
            'adresszusatz'             => ['nullable', 'string', 'max:150'],
            'plz'                      => ['nullable', 'string', 'max:10'],
            'ort'                      => ['nullable', 'string', 'max:100'],
            'telefon'                  => ['nullable', 'string', 'max:50'],
            'fax'                      => ['nullable', 'string', 'max:50'],
            'email'                    => ['nullable', 'email', 'max:150'],
            'website'                  => ['nullable', 'string', 'max:150'],
            'bank'                     => ['nullable', 'string', 'max:100'],
            'bankadresse'              => ['nullable', 'string', 'max:150'],
            'iban'                     => ['nullable', 'string', 'max:30'],
            'postcheckkonto'           => ['nullable', 'string', 'max:30'],
            'rechnungsadresse_position'=> ['nullable', 'in:links,rechts'],
            'logo_ausrichtung'         => ['nullable', 'in:links_anschrift_rechts,rechts_anschrift_links,mitte_anschrift_fusszeile'],
            'logo'                     => ['nullable', 'image', 'max:2048'],
            'theme_farbe_primaer'      => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme_layout'             => ['nullable', 'in:sidebar,topnav'],
        ]);

        $org = $this->org();

        $updates = array_merge(
            $request->only([
                'name', 'zsr_nr', 'mwst_nr', 'adresse', 'postfach', 'adresszusatz',
                'plz', 'ort', 'telefon', 'fax', 'email', 'website',
                'bank', 'bankadresse', 'iban', 'postcheckkonto',
                'rechnungsadresse_position', 'logo_ausrichtung',
            ]),
            ['druck_mit_firmendaten' => $request->boolean('druck_mit_firmendaten', true)]
        );

        // Logo hochladen
        if ($request->hasFile('logo')) {
            $file      = $request->file('logo');
            $dateiname = 'logo.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $dateiname);
            $updates['logo_pfad'] = 'uploads/' . $dateiname;
        }

        // Design-Einstellungen
        $envUpdates = [];
        if ($request->filled('theme_farbe_primaer')) {
            $updates['theme_farbe_primaer'] = $request->theme_farbe_primaer;
            $envUpdates['CS_FARBE_PRIMAER'] = $request->theme_farbe_primaer;
        }
        if ($request->filled('theme_layout')) {
            $updates['theme_layout'] = $request->theme_layout;
            $envUpdates['CS_LAYOUT']  = $request->theme_layout;
        }
        if (!empty($envUpdates)) {
            $this->updateEnv($envUpdates);
            \Artisan::call('config:clear');
        }

        $org->update($updates);

        return back()->with('erfolg', 'Firmadaten wurden gespeichert.');
    }

    private function updateEnv(array $values): void
    {
        $envPfad = base_path('.env');
        $inhalt  = file_get_contents($envPfad);
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

    /** Kanton-Einstellungen speichern (ESR / IBAN-Override) */
    public function regionSpeichern(Request $request)
    {
        $request->validate([
            'region_id'       => ['required', 'exists:regionen,id'],
            'aktiv'           => ['boolean'],
            'zsr_nr'          => ['nullable', 'string', 'max:20'],
            'iban'            => ['nullable', 'string', 'max:30'],
            'postcheckkonto'  => ['nullable', 'string', 'max:30'],
            'bank'            => ['nullable', 'string', 'max:100'],
            'bankadresse'     => ['nullable', 'string', 'max:150'],
            'esr_teilnehmernr'=> ['nullable', 'string', 'max:20'],
            'qr_iban'         => ['nullable', 'string', 'max:30'],
            'bemerkung'       => ['nullable', 'string', 'max:500'],
        ]);

        $org = $this->org();

        OrganisationRegion::updateOrCreate(
            ['organisation_id' => $org->id, 'region_id' => $request->region_id],
            array_merge(
                $request->only(['zsr_nr', 'iban', 'postcheckkonto', 'bank', 'bankadresse', 'esr_teilnehmernr', 'qr_iban', 'bemerkung']),
                ['aktiv' => $request->boolean('aktiv', true)]
            )
        );

        return back()->with('erfolg', 'Kanton-Einstellungen gespeichert.');
    }

    public function bexioSpeichern(Request $request)
    {
        $request->validate([
            'bexio_api_key'    => ['nullable', 'string', 'max:200'],
            'bexio_mandant_id' => ['nullable', 'string', 'max:50'],
        ]);

        $org = $this->org();

        // Nur aktualisieren wenn ein neuer Key eingegeben wurde (nicht die Platzhalter)
        $updates = ['bexio_mandant_id' => $request->bexio_mandant_id];
        if ($request->bexio_api_key && $request->bexio_api_key !== '••••••••') {
            $updates['bexio_api_key'] = $request->bexio_api_key;
        }

        $org->update($updates);
        return back()->with('erfolg', 'Bexio-Einstellungen gespeichert.');
    }

    public function bexioTesten()
    {
        $org     = $this->org();
        $service = new BexioService($org);
        $result  = $service->verbindungTesten();

        if ($result['ok']) {
            return back()->with('erfolg', 'Bexio-Verbindung OK: ' . ($result['info'] ?? ''));
        }
        return back()->with('fehler', 'Bexio-Verbindung fehlgeschlagen: ' . ($result['fehler'] ?? 'Unbekannter Fehler'));
    }

    /** Kanton aus Org entfernen */
    public function regionEntfernen(Request $request, Region $region)
    {
        OrganisationRegion::where('organisation_id', $this->org()->id)
            ->where('region_id', $region->id)
            ->delete();

        return back()->with('erfolg', "Kanton {$region->kuerzel} wurde entfernt.");
    }
}
