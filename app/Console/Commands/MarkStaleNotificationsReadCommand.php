<?php

namespace App\Console\Commands;

use App\Models\ShfNotification;
use Illuminate\Console\Command;

/**
 * Marks any unread SHF notification older than N hours as read.
 *
 * Keeps the notifications list focused — users don't accumulate a stale
 * unread pile from last week. Default 48 hours is configurable via --hours=
 * in case we want a different horizon later.
 */
class MarkStaleNotificationsReadCommand extends Command
{
    protected $signature = 'notifications:mark-stale-read {--hours=48 : Unread notifications older than this are auto-marked read}';

    protected $description = 'Auto-mark notifications older than the given hours as read';

    public function handle(): int
    {
        $hours = max(1, (int) $this->option('hours'));
        $cutoff = now()->subHours($hours);

        $count = ShfNotification::where('is_read', false)
            ->where('created_at', '<', $cutoff)
            ->update(['is_read' => true]);

        $this->info("Marked {$count} notification(s) older than {$hours}h as read.");

        return self::SUCCESS;
    }
}
