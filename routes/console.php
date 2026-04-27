<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// SCH-02: dispatch due posts every minute
Schedule::command('posts:dispatch-due')->everyMinute();

// CON-07: refresh expiring social tokens every 15 minutes
// 15-min cadence covers Snapchat (30-min TTL) with enough margin
Schedule::command('social:refresh-tokens')->everyFifteenMinutes();

// ANL-07: sync Meta campaign insights daily at 06:00 UTC (09:00 Riyadh)
Schedule::command('campaigns:sync-insights')->dailyAt('06:00');
