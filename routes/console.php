<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-mark unread notifications older than 48h as read. Runs hourly so the
// lag after the cutoff is at most 1 hour. Requires `php artisan schedule:run`
// to be wired to cron / Windows Task Scheduler — see .docs/ops.md.
Schedule::command('notifications:mark-stale-read --hours=48')->hourly();

// Queue worker driven by the scheduler — no systemd / supervisor needed.
// Each run drains any pending jobs and exits (`--stop-when-empty`). The
// `withoutOverlapping()` lock ensures only one worker is active at a time,
// and `runInBackground()` means other scheduled commands aren't blocked
// while this one is draining a long queue.
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=120')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// Daily reminders for DVR follow-ups and general tasks.
// 08:00 — things due today; 20:00 — things due tomorrow (heads-up).
Schedule::command('reminders:send-daily --when=morning')->dailyAt('08:00');
Schedule::command('reminders:send-daily --when=evening')->dailyAt('20:00');
