<?php

use App\Console\Scheduling\LogSchedulerHeartbeat;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

test('scheduler heartbeat writes a timestamped log entry', function () {
    $this->travelTo(now()->startOfMinute());
    Log::spy();

    (new LogSchedulerHeartbeat)();

    Log::shouldHaveReceived('info')
        ->once()
        ->with('Scheduler heartbeat: ran at '.now()->toDateTimeString());
});

test('scheduler heartbeat is registered', function () {
    Artisan::call('schedule:list');

    expect(Artisan::output())->toContain('scheduler-heartbeat');
});
