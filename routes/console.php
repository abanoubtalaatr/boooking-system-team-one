<?php

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