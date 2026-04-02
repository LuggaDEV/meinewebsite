<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Equipment price cron token
    |--------------------------------------------------------------------------
    |
    | Long random secret for GET /internal/cron/equipment-prices/{token}.
    | Use an external scheduler (e.g. cron-job.org) to hit this URL daily if
    | you cannot run `php artisan schedule:run` on the server.
    |
    */

    'price_refresh_token' => env('EQUIPMENT_PRICE_REFRESH_TOKEN'),

];
