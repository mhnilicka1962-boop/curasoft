<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kein Schedule hier — direkt via Cronjob auf Server:
// 0 2 * * * /usr/local/bin/php /home/devitjob/public_html/spitex/artisan einsaetze:generieren
