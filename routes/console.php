<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto cancel expired offline bookings every minute
Schedule::command('bookings:auto-cancel-expired')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
Schedule::command('booking:delete-guest-trash')->everyMinute();
Schedule::command('booking:delete-cancelled')->everyMinute();

// Release expired seat holds every minute
Schedule::command('seats:release-expired')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
