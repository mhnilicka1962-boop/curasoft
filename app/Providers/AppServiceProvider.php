<?php

namespace App\Providers;

use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\NachrichtEmpfaenger;
use App\Models\Rechnung;
use App\Observers\AuditObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // HTTPS in Produktion erzwingen
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Ungelesene Nachrichten-Badge für Navigation
        View::composer('layouts.partials.nav', function ($view) {
            if (auth()->check()) {
                $ungelesen = NachrichtEmpfaenger::where('empfaenger_id', auth()->id())
                    ->whereNull('gelesen_am')
                    ->where('archiviert', false)
                    ->count();
                $view->with('navNachrichtenUngelesen', $ungelesen);
            }
        });

        // Sensitive Modelle automatisch auditieren
        Klient::observe(AuditObserver::class);
        Einsatz::observe(AuditObserver::class);
        Rechnung::observe(AuditObserver::class);
        Benutzer::observe(AuditObserver::class);
    }
}
