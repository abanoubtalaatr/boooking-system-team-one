<?php

use App\Console\Scheduling\LogSchedulerHeartbeat;
use App\Jobs\CompletePendingBookings;
use App\Jobs\ExpireBookingHolds;
use App\Jobs\RetryPendingRefunds;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule Prune Search History Command every day at 12:00 AM
Schedule::command('app:prune-search-history')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::job(new ExpireBookingHolds)
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::job(new RetryPendingRefunds)
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::job(new CompletePendingBookings)
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->onOneServer();

Schedule::call(new LogSchedulerHeartbeat)
    ->everyMinute()
    ->name('scheduler-heartbeat');
