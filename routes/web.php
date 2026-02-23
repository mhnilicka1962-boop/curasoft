<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EinsatzartenController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\EinsaetzeController;
use App\Http\Controllers\KlientenController;
use App\Http\Controllers\FirmaController;
use App\Http\Controllers\NachrichtenController;
use App\Http\Controllers\LeistungsartenController;
use App\Http\Controllers\RechnungenController;
use App\Http\Controllers\AerzteController;
use App\Http\Controllers\DokumenteController;
use App\Http\Controllers\KrankenkassenController;
use App\Http\Controllers\RapporteController;
use App\Http\Controllers\TourenController;
use App\Http\Controllers\KiController;
use App\Http\Controllers\MitarbeiterController;
use App\Http\Controllers\RegionenController;
use App\Http\Controllers\EinladungController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\WebAuthnController;
use App\Http\Controllers\ProfilController;
use Illuminate\Support\Facades\Route;

// Setup-Wizard (nur wenn noch kein Benutzer existiert)
Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
Route::post('/setup', [SetupController::class, 'store'])->name('setup.store');

// Startseite → Landing Page
Route::get('/', function () {
    return view('landing');
})->name('home');

// Kontaktformular Landing Page
Route::post('/kontakt', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'name'          => 'required|string|max:100',
        'organisation'  => 'nullable|string|max:150',
        'email'         => 'required|email|max:150',
        'mitarbeitende' => 'nullable|string|max:20',
        'nachricht'     => 'nullable|string|max:2000',
    ]);

    \Illuminate\Support\Facades\Mail::raw(
        "Neue Pilotanfrage von der Landing Page\n\n"
        . "Name:           {$data['name']}\n"
        . "Organisation:   " . ($data['organisation'] ?? '—') . "\n"
        . "E-Mail:         {$data['email']}\n"
        . "Mitarbeitende:  " . ($data['mitarbeitende'] ?? '—') . "\n\n"
        . "Nachricht:\n" . ($data['nachricht'] ?? '—'),
        function ($message) use ($data) {
            $message->to('mhn@itjob.ch')
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Spitex Pilotanfrage: ' . $data['name']);
        }
    );

    return response()->json(['ok' => true]);
})->name('kontakt.senden');

// Auth
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/magic', [AuthController::class, 'sendMagicLink'])->name('login.magic');
Route::get('/login/verify/{token}', [AuthController::class, 'verifyMagicLink'])->name('login.verify');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
Route::get('/logout', [AuthController::class, 'logout'])->middleware('auth');

// Einladung (kein Auth erforderlich)
Route::get('/einladung/{token}',  [EinladungController::class, 'show'])->name('einladung.show');
Route::post('/einladung/{token}', [EinladungController::class, 'store'])->name('einladung.store');

// WebAuthn — Login (kein Auth nötig)
Route::get('/webauthn/authenticate-options', [WebAuthnController::class, 'authenticateOptions'])->name('webauthn.authenticate.options');
Route::post('/webauthn/authenticate',        [WebAuthnController::class, 'authenticate'])->name('webauthn.authenticate');

// ----------------------------------------------------------------
// Geschützte Routen — alle eingeloggten Benutzer
// ----------------------------------------------------------------
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        $orgId = auth()->user()->organisation_id;
        $userId = auth()->id();
        $rolle = auth()->user()->rolle;

        $heute = today();

        $klientenAktiv = \App\Models\Klient::where('organisation_id', $orgId)
            ->where('aktiv', true)->count();

        $einsaetzeHeute = \App\Models\Einsatz::where('organisation_id', $orgId)
            ->whereDate('datum', $heute)
            ->when($rolle === 'pflege', fn($q) => $q->where('benutzer_id', $userId))
            ->count();

        $einsaetzeGeplant = \App\Models\Einsatz::where('organisation_id', $orgId)
            ->whereDate('datum', $heute)
            ->where('status', 'geplant')
            ->when($rolle === 'pflege', fn($q) => $q->where('benutzer_id', $userId))
            ->count();

        $offeneRechnungen = ($rolle !== 'pflege')
            ? \App\Models\Rechnung::where('organisation_id', $orgId)
                ->whereIn('status', ['entwurf', 'gesendet'])
                ->sum('betrag_total')
            : 0;

        $ungeleseneNachrichten = \App\Models\NachrichtEmpfaenger::where('empfaenger_id', $userId)
            ->whereNull('gelesen_am')->count();

        $letzteRapporte = \App\Models\Rapport::where('organisation_id', $orgId)
            ->with('klient', 'benutzer')
            ->when($rolle === 'pflege', fn($q) => $q->where('benutzer_id', $userId))
            ->orderByDesc('datum')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $meineTourenHeute = \App\Models\Tour::where('organisation_id', $orgId)
            ->whereDate('datum', $heute)
            ->when($rolle === 'pflege', fn($q) => $q->where('benutzer_id', $userId))
            ->with('benutzer', 'einsaetze')
            ->orderBy('start_zeit')
            ->get();

        return view('dashboard', compact(
            'klientenAktiv', 'einsaetzeHeute', 'einsaetzeGeplant',
            'offeneRechnungen', 'ungeleseneNachrichten',
            'letzteRapporte', 'meineTourenHeute'
        ));
    })->name('dashboard');

    // Profil + WebAuthn (Passkeys) — alle eingeloggten Benutzer
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
    Route::get('/webauthn/register-options',    [WebAuthnController::class, 'registerOptions'])->name('webauthn.register.options');
    Route::post('/webauthn/register',           [WebAuthnController::class, 'register'])->name('webauthn.register');
    Route::delete('/webauthn/credentials/{id}', [WebAuthnController::class, 'delete'])->name('webauthn.delete');

    // Internes Nachrichtensystem — alle eingeloggten Benutzer
    Route::get('/nachrichten', [NachrichtenController::class, 'index'])->name('nachrichten.index');
    Route::get('/nachrichten/neu', [NachrichtenController::class, 'create'])->name('nachrichten.create');
    Route::post('/nachrichten', [NachrichtenController::class, 'store'])->name('nachrichten.store');
    Route::get('/nachrichten/{nachricht}', [NachrichtenController::class, 'show'])->name('nachrichten.show');
    Route::post('/nachrichten/{nachricht}/antworten', [NachrichtenController::class, 'antworten'])->name('nachrichten.antworten');
    Route::patch('/nachrichten/{nachricht}/archivieren', [NachrichtenController::class, 'archivieren'])->name('nachrichten.archivieren');
    Route::post('/nachrichten/rundschreiben', [NachrichtenController::class, 'rundschreiben'])->name('nachrichten.rundschreiben');

    // KI — alle eingeloggten Benutzer
    Route::post('/ki/rapport', [KiController::class, 'rapportVorschlag'])->name('ki.rapport');

    // Betrieb — Pflege + Admin
    Route::middleware('rolle:admin,pflege')->group(function () {
        Route::resource('/klienten', KlientenController::class)->parameters(['klienten' => 'klient']);
        Route::get('/klienten/{klient}/qr', [KlientenController::class, 'qr'])->name('klienten.qr');
        Route::post('/klienten/{klient}/adressen', [KlientenController::class, 'adresseSpeichern'])->name('klienten.adresse.speichern');
        Route::delete('/klienten/{klient}/adressen/{adresse}', [KlientenController::class, 'adresseLoeschen'])->name('klienten.adresse.loeschen');

        // Klient — Unterbeziehungen
        Route::post('/klienten/{klient}/aerzte',                      [KlientenController::class, 'arztSpeichern'])->name('klienten.arzt.speichern');
        Route::delete('/klienten/{klient}/aerzte/{klientArzt}',       [KlientenController::class, 'arztEntfernen'])->name('klienten.arzt.entfernen');
        Route::post('/klienten/{klient}/krankenkassen',               [KlientenController::class, 'krankenkasseSpeichern'])->name('klienten.kk.speichern');
        Route::delete('/klienten/{klient}/krankenkassen/{klientKk}',  [KlientenController::class, 'krankenkasseEntfernen'])->name('klienten.kk.entfernen');
        Route::post('/klienten/{klient}/kontakte',                    [KlientenController::class, 'kontaktSpeichern'])->name('klienten.kontakt.speichern');
        Route::delete('/klienten/{klient}/kontakte/{kontakt}',        [KlientenController::class, 'kontaktEntfernen'])->name('klienten.kontakt.entfernen');
        Route::post('/klienten/{klient}/beitraege',                   [KlientenController::class, 'beitragSpeichern'])->name('klienten.beitrag.speichern');
        Route::delete('/klienten/{klient}/beitraege/{beitrag}',       [KlientenController::class, 'beitragLoeschen'])->name('klienten.beitrag.loeschen');
        Route::post('/klienten/{klient}/pflegestufen',                [KlientenController::class, 'pflegestufeSpeichern'])->name('klienten.pflegestufe.speichern');
        Route::post('/klienten/{klient}/diagnosen',                   [KlientenController::class, 'diagnoseSpeichern'])->name('klienten.diagnose.speichern');
        Route::delete('/klienten/{klient}/diagnosen/{diagnose}',      [KlientenController::class, 'diagnoseEntfernen'])->name('klienten.diagnose.entfernen');
        Route::post('/klienten/{klient}/verordnungen',                [KlientenController::class, 'verordnungSpeichern'])->name('klienten.verordnung.speichern');
        Route::delete('/klienten/{klient}/verordnungen/{verordnung}', [KlientenController::class, 'verordnungEntfernen'])->name('klienten.verordnung.entfernen');
        Route::post('/klienten/{klient}/bexio/sync',                  [KlientenController::class, 'bexioSync'])->name('klienten.bexio.sync');
        Route::get('/schnellerfassung',  [KlientenController::class, 'schnellerfassung'])->name('schnellerfassung');
        Route::post('/schnellerfassung', [KlientenController::class, 'schnellSpeichern'])->name('schnellerfassung.speichern');
        Route::resource('/einsaetze', EinsaetzeController::class)->only(['index','create','store','show','edit','update']);
        Route::delete('/einsaetze/serie/{serieId}', [EinsaetzeController::class, 'destroySerie'])->name('einsaetze.serie.loeschen');

        // Rapporte
        Route::resource('/rapporte', RapporteController::class)->only(['index', 'create', 'store', 'show'])->parameters(['rapporte' => 'rapport']);

        // Touren
        Route::resource('/touren', TourenController::class)
            ->only(['index', 'create', 'store', 'show', 'update'])
            ->parameters(['touren' => 'tour']);
        Route::post('/touren/{tour}/einsaetze',          [TourenController::class, 'einsatzZuweisen'])->name('touren.einsatz.zuweisen');
        Route::delete('/touren/{tour}/einsaetze/{einsatz}', [TourenController::class, 'einsatzEntfernen'])->name('touren.einsatz.entfernen');

        // Dokumente
        Route::post('/dokumente',              [DokumenteController::class, 'store'])->name('dokumente.store');
        Route::get('/dokumente/{dokument}/download', [DokumenteController::class, 'download'])->name('dokumente.download');
        Route::delete('/dokumente/{dokument}', [DokumenteController::class, 'destroy'])->name('dokumente.destroy');

        // Check-in / Check-out
        Route::get('/checkin/{token}',               [CheckInController::class, 'scan'])->name('checkin.scan');
        Route::post('/checkin/{token}',              [CheckInController::class, 'checkinQr'])->name('checkin.qr');
        Route::post('/checkin/{einsatz}/gps',        [CheckInController::class, 'checkinGps'])->name('checkin.gps');
        Route::post('/checkin/{einsatz}/manuell',    [CheckInController::class, 'checkinManuell'])->name('checkin.manuell');
        Route::get('/checkin/{einsatz}/aktiv',       [CheckInController::class, 'aktiv'])->name('checkin.aktiv');
        Route::post('/checkout/{einsatz}/gps',       [CheckInController::class, 'checkoutGps'])->name('checkout.gps');
        Route::post('/checkout/{einsatz}/manuell',   [CheckInController::class, 'checkoutManuell'])->name('checkout.manuell');
    });

    // Abrechnung — Buchhaltung + Admin
    Route::middleware('rolle:admin,buchhaltung')->group(function () {
        Route::resource('/rechnungen', RechnungenController::class)->only(['index','create','store','show']);
        Route::patch('/rechnungen/{rechnung}/status', [RechnungenController::class, 'statusUpdate'])->name('rechnungen.status');
        Route::patch('/rechnungen/positionen/{position}', [RechnungenController::class, 'positionUpdate'])->name('rechnungen.position.update');
        Route::get('/rechnungen/{rechnung}/xml',        [RechnungenController::class, 'xmlExport'])->name('rechnungen.xml');
        Route::post('/rechnungen/{rechnung}/bexio/sync', [RechnungenController::class, 'bexioSync'])->name('rechnungen.bexio.sync');
    });

    // Stammdaten + Audit — nur Admin
    Route::middleware('rolle:admin')->group(function () {
        // Firma / Organisation
        Route::get('/firma', [FirmaController::class, 'index'])->name('firma.index');
        Route::put('/firma', [FirmaController::class, 'update'])->name('firma.update');
        Route::post('/firma/regionen', [FirmaController::class, 'regionSpeichern'])->name('firma.region.speichern');
        Route::delete('/firma/regionen/{region}', [FirmaController::class, 'regionEntfernen'])->name('firma.region.entfernen');
        Route::post('/firma/bexio', [FirmaController::class, 'bexioSpeichern'])->name('firma.bexio.speichern');
        Route::get('/firma/bexio/testen', [FirmaController::class, 'bexioTesten'])->name('firma.bexio.testen');

        // Leistungsarten + Tarife
        Route::resource('/leistungsarten', LeistungsartenController::class)
            ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
            ->parameters(['leistungsarten' => 'leistungsart']);
        Route::post('/leistungsarten/{leistungsart}/tarife',
            [LeistungsartenController::class, 'tarifSpeichern'])->name('leistungsarten.tarif.speichern');
        Route::delete('/leistungsarten/{leistungsart}/tarife/{region}',
            [LeistungsartenController::class, 'tarifLoeschen'])->name('leistungsarten.tarif.loeschen');
        Route::get('/leistungsarten/{leistungsart}/tarife/{tarif}/bearbeiten',
            [LeistungsartenController::class, 'tarifeBearbeiten'])->name('leistungsarten.tarif.bearbeiten');
        Route::put('/leistungsarten/{leistungsart}/tarife/{tarif}',
            [LeistungsartenController::class, 'tarifeAktualisieren'])->name('leistungsarten.tarif.aktualisieren');

        // Regionen / Kantone
        Route::resource('/regionen', RegionenController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy'])
            ->parameters(['regionen' => 'region']);
        Route::post('/regionen/{region}/tarife', [RegionenController::class, 'tarifSpeichern'])->name('regionen.tarif.speichern');

        // Ärzte
        Route::resource('/aerzte', AerzteController::class)
            ->only(['index', 'create', 'store', 'edit', 'update'])
            ->parameters(['aerzte' => 'arzt']);

        // Krankenkassen
        Route::resource('/krankenkassen', KrankenkassenController::class)
            ->only(['index', 'create', 'store', 'edit', 'update'])
            ->parameters(['krankenkassen' => 'krankenkasse']);

        // Einsatzarten
        Route::get('/einsatzarten', [EinsatzartenController::class, 'index'])->name('einsatzarten.index');
        Route::post('/einsatzarten', [EinsatzartenController::class, 'store'])->name('einsatzarten.store');
        Route::get('/einsatzarten/{einsatzart}/bearbeiten', [EinsatzartenController::class, 'edit'])->name('einsatzarten.edit');
        Route::put('/einsatzarten/{einsatzart}', [EinsatzartenController::class, 'update'])->name('einsatzarten.update');
        Route::delete('/einsatzarten/{einsatzart}', [EinsatzartenController::class, 'destroy'])->name('einsatzarten.destroy');

        // Audit-Log
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');

        // Mitarbeitende
        Route::get('/mitarbeiter', [MitarbeiterController::class, 'index'])->name('mitarbeiter.index');
        Route::post('/mitarbeiter', [MitarbeiterController::class, 'store'])->name('mitarbeiter.store');
        Route::get('/mitarbeiter/{mitarbeiter}', [MitarbeiterController::class, 'show'])->name('mitarbeiter.show');
        Route::put('/mitarbeiter/{mitarbeiter}', [MitarbeiterController::class, 'update'])->name('mitarbeiter.update');
        Route::post('/mitarbeiter/{mitarbeiter}/qualifikationen', [MitarbeiterController::class, 'qualifikationenSpeichern'])->name('mitarbeiter.qualifikationen');
        Route::post('/mitarbeiter/{mitarbeiter}/klienten', [MitarbeiterController::class, 'klientZuweisen'])->name('mitarbeiter.klient.zuweisen');
        Route::delete('/mitarbeiter/{mitarbeiter}/klienten/{zuweisung}', [MitarbeiterController::class, 'klientEntfernen'])->name('mitarbeiter.klient.entfernen');
        Route::post('/mitarbeiter/{mitarbeiter}/einladung', [MitarbeiterController::class, 'einladungSenden'])->name('mitarbeiter.einladung');
    });

});
