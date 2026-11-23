<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Keep the public booking grid populated: top up two weeks of dated showtimes
// every night and drop slots that are already in the past.
Schedule::command('showtimes:generate --prune')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();
