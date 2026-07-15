<?php

namespace App\Console\Scheduling;

use Illuminate\Support\Facades\Log;

final class LogSchedulerHeartbeat
{
    public function __invoke(): void
    {
        Log::info('Scheduler heartbeat: ran at '.now()->toDateTimeString());
    }
}
