<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Check for inactive sessions every 5 minutes
Schedule::command('attendance:check-inactive')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Send WhatsApp reminders hourly
Schedule::command('whatsapp:send-reminders')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();
