<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('horizon:snapshot')
    ->everyFiveMinutes()
    ->onOneServer();

Schedule::command('ticketing:sync-catalogue')
    ->cron((string) config('ticketing.catalogue.sync_cron', '0 * * * *'))
    ->when(fn (): bool => (bool) config('ticketing.catalogue.sync_enabled', false))
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('ticketing:sync-prices')
    ->cron((string) config('ticketing.pricing.sync_cron', '*/15 * * * *'))
    ->when(fn (): bool => (bool) config('ticketing.pricing.sync_enabled', false))
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('ticketing:sync-journeys')
    ->cron((string) config('ticketing.journeys.sync_cron', '*/30 * * * *'))
    ->when(fn (): bool => (bool) config('ticketing.journeys.sync_enabled', false))
    ->withoutOverlapping()
    ->onOneServer();
