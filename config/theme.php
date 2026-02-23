<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Standard-Layout
    | 'sidebar'  = Sidebar links (Standard)
    | 'topnav'   = Navigation oben
    |--------------------------------------------------------------------------
    */
    'layout' => env('CS_LAYOUT', 'sidebar'),

    /*
    |--------------------------------------------------------------------------
    | Standard-Farben (überschreibbar per Organisation in DB)
    |--------------------------------------------------------------------------
    */
    'farbe_primaer'        => env('CS_FARBE_PRIMAER', '#2563eb'),
    'farbe_primaer_hell'   => env('CS_FARBE_PRIMAER_HELL', '#eff6ff'),
    'farbe_primaer_dunkel' => env('CS_FARBE_PRIMAER_DUNKEL', '#1d4ed8'),

    /*
    |--------------------------------------------------------------------------
    | App-Name & Logo
    |--------------------------------------------------------------------------
    */
    'app_name' => env('CS_APP_NAME', 'Spitex'),
    'logo'     => env('CS_LOGO', null), // Pfad zu Default-Logo

];
