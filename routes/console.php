<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule reminder sending daily at 9 AM
Schedule::command('reminders:send --create')
    ->dailyAt('09:00')
    ->description('Send payment and lease reminders to tenants');

// Also run reminder creation separately to catch any missed ones
Schedule::command('reminders:send --create')
    ->dailyAt('14:00')
    ->description('Send afternoon payment and lease reminders');
