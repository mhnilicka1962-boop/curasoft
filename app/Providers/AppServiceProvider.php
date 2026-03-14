<?php

namespace App\Providers;

use App\Models\Benutzer;
use App\Models\Einsatz;
use App\Models\Klient;
use App\Models\NachrichtEmpfaenger;
use Illuminate\Support\Facades\DB;
use App\Models\Rechnung;
use App\Observers\AuditObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(\App\Providers\TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        // HTTPS in Produktion erzwingen
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }


        // Chat Ungelesen-Badge für Navigation
        View::composer('layouts.partials.nav', function ($view) {
            if (auth()->check()) {
                $userId = auth()->id();
                $ungelesen = DB::table('chat_nachrichten as cn')
                    ->join('chat_teilnehmer as ct', function ($j) use ($userId) {
                        $j->on('ct.chat_id', '=', 'cn.chat_id')
                          ->where('ct.benutzer_id', $userId);
                    })
                    ->whereNull('cn.geloescht_am')
                    ->where('cn.absender_id', '!=', $userId)
                    ->whereRaw('cn.id > ct.letzte_gesehen_id')
                    ->count();
                $view->with('navChatUngelesen', $ungelesen);
            }
        });

        // Sensitive Modelle automatisch auditieren
        Klient::observe(AuditObserver::class);
        Einsatz::observe(AuditObserver::class);
        Rechnung::observe(AuditObserver::class);
        Benutzer::observe(AuditObserver::class);
    }
}
