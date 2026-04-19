<?php

namespace App\Console\Commands;

use App\Models\DailyVisitReport;
use App\Models\GeneralTask;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

/**
 * Sends a daily reminder notification to each user with pending work.
 *
 * `--when=morning` (08:00): counts things due TODAY.
 *   - DVR follow-ups whose follow_up_date = today and not yet done.
 *   - General tasks due today (pending / in_progress).
 *
 * `--when=evening` (20:00): counts things due TOMORROW (heads-up).
 *
 * One notification per user; skips users with zero in both buckets.
 */
class SendDailyReminders extends Command
{
    protected $signature = 'reminders:send-daily {--when=morning : "morning" (today) or "evening" (tomorrow)}';

    protected $description = 'Send daily DVR follow-up + task due-date reminder notifications';

    public function __construct(private NotificationService $notifications)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $when = $this->option('when') === 'evening' ? 'evening' : 'morning';
        $targetDate = $when === 'evening'
            ? CarbonImmutable::tomorrow()->toDateString()
            : CarbonImmutable::today()->toDateString();

        $dvrByUser = DailyVisitReport::query()
            ->where('follow_up_needed', true)
            ->where('is_follow_up_done', false)
            ->whereDate('follow_up_date', $targetDate)
            ->selectRaw('user_id, COUNT(*) as cnt')
            ->groupBy('user_id')
            ->pluck('cnt', 'user_id');

        $taskByUser = GeneralTask::query()
            ->whereIn('status', [GeneralTask::STATUS_PENDING, GeneralTask::STATUS_IN_PROGRESS])
            ->whereDate('due_date', $targetDate)
            ->selectRaw('assigned_to, COUNT(*) as cnt')
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->pluck('cnt', 'assigned_to');

        $userIds = $dvrByUser->keys()->merge($taskByUser->keys())->unique();
        $users = User::whereIn('id', $userIds)->where('is_active', true)->get();

        $sent = 0;
        foreach ($users as $user) {
            $dvrCount = (int) ($dvrByUser[$user->id] ?? 0);
            $taskCount = (int) ($taskByUser[$user->id] ?? 0);

            if ($dvrCount === 0 && $taskCount === 0) {
                continue;
            }

            $title = $when === 'morning' ? "Today's reminders" : "Tomorrow's reminders";
            $parts = [];
            if ($dvrCount > 0) {
                $parts[] = "{$dvrCount} DVR follow-up".($dvrCount === 1 ? '' : 's');
            }
            if ($taskCount > 0) {
                $parts[] = "{$taskCount} task".($taskCount === 1 ? '' : 's');
            }
            $when_phrase = $when === 'morning' ? 'today' : 'tomorrow';
            $message = 'You have '.implode(' and ', $parts).' due '.$when_phrase.'.';

            $this->notifications->notify(
                $user->id,
                $title,
                $message,
                'info',
                null,
                null,
                route('dashboard'),
            );
            $sent++;
        }

        $this->info("Sent {$when} reminders to {$sent} user(s) for {$targetDate}.");

        return self::SUCCESS;
    }
}
