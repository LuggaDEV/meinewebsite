<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('equipment:check-prices')->daily();

/*
 * Automatic price updates require the scheduler to run, e.g. cron every minute:
 * * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
 *
 * If you cannot run schedule:run on the server, set EQUIPMENT_PRICE_REFRESH_TOKEN in .env
 * and call GET /internal/cron/equipment-prices/{token} from an external cron service daily.
 */
