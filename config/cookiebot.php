<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cookiebot
    |--------------------------------------------------------------------------
    |
    | Usercentrics Cookiebot: set COOKIEBOT_DOMAIN_GROUP_ID from the dashboard
    | (Domain group ID). When enabled, the consent script is injected on public
    | pages only (not under /admin).
    |
    */

    'enabled' => filter_var(env('COOKIEBOT_ENABLED', false), FILTER_VALIDATE_BOOLEAN),

    'domain_group_id' => env('COOKIEBOT_DOMAIN_GROUP_ID', ''),

];
