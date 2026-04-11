<?php

return [

    /*
    |----------------------------------------------------------------------
    | Master Switch — set ADS_ENABLED=false untuk matikan semua iklan
    |----------------------------------------------------------------------
    */
    'enabled'  => env('ADS_ENABLED', true),

    /*
    |----------------------------------------------------------------------
    | Active Provider: 'adsterra' | 'adsense' | 'none'
    |----------------------------------------------------------------------
    */
    'provider' => env('ADS_PROVIDER', 'adsterra'),

    /*
    |----------------------------------------------------------------------
    | Slot Visibility
    | Kontrol slot mana yang aktif tanpa sentuh blade
    |----------------------------------------------------------------------
    */
    'slots' => [
        'header'  => env('ADS_SLOT_HEADER',  true),
        'content' => env('ADS_SLOT_CONTENT', true),
        'sidebar' => env('ADS_SLOT_SIDEBAR', true),
        'result'  => env('ADS_SLOT_RESULT',  true),
    ],

    /*
    |----------------------------------------------------------------------
    | Google AdSense — aktif setelah approved
    | Ganti ADS_PROVIDER=adsense di .env, tidak perlu ubah blade apapun
    |----------------------------------------------------------------------
    */
    'adsense' => [
        'client_id' => env('ADSENSE_CLIENT_ID', ''),
        'slots' => [
            'header'  => env('ADSENSE_SLOT_HEADER',  ''),
            'content' => env('ADSENSE_SLOT_CONTENT', ''),
            'sidebar' => env('ADSENSE_SLOT_SIDEBAR', ''),
            'result'  => env('ADSENSE_SLOT_RESULT',  ''),
        ],
    ],

];