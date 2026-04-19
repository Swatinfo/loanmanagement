<?php

namespace App\Services;

use App\Models\LoanDetail;
use App\Models\ShfNotification;
use App\Models\Stage;
use App\Models\User;

class NotificationService
{
    public function notify(int $userId, string $title, string $message, string $type = 'info', ?int $loanId = null, ?string $stageKey = null, ?string $link = null): ShfNotification
    {
        // Default deep-link: if this notification belongs to a loan, send the
        // user to that loan's stages page. Callers can still pass an explicit
        // link to override (e.g. general-tasks go to the task page).
        if ($link === null && $loanId) {
            try {
                $link = route('loans.stages', $loanId);
            } catch (\Throwable) {
                $link = null;
            }
        }

        return ShfNotification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'loan_id' => $loanId,
            'stage_key' => $stageKey,
            'link' => $link,
        ]);
    }

    public function notifyStageAssignment(LoanDetail $loan, string $stageKey, int $assignedUserId): ShfNotification
    {
        $stageName = Stage::where('stage_key', $stageKey)->value('stage_name_en') ?? $stageKey;

        return $this->notify(
            $assignedUserId,
            'Stage Assigned',
            "You have been assigned to '{$stageName}' for Loan of ({$loan->customer_name}) #{$loan->loan_number}",
            'assignment',
            $loan->id,
            $stageKey,
            route('loans.stages', $loan),
        );
    }

    public function notifyStageCompleted(LoanDetail $loan, string $stageKey): void
    {
        $stageName = Stage::where('stage_key', $stageKey)->value('stage_name_en') ?? $stageKey;
        $message = "Stage '{$stageName}' completed for Loan of ({$loan->customer_name}) #{$loan->loan_number}";
        $link = route('loans.stages', $loan);

        $notifyUsers = collect([$loan->created_by, $loan->assigned_advisor])
            ->filter()
            ->unique()
            ->reject(fn ($id) => $id === auth()->id());

        foreach ($notifyUsers as $userId) {
            $this->notify(
                $userId,
                'Stage Completed',
                $message,
                'stage_update',
                $loan->id,
                $stageKey,
                $link
            );
        }
    }

    public function notifyLoanCompleted(LoanDetail $loan): void
    {
        $message = "Loan of ({$loan->customer_name}) #{$loan->loan_number} has been completed!";
        $link = route('loans.stages', $loan);

        $notifyUsers = collect([$loan->created_by, $loan->assigned_advisor])
            ->filter()->unique()->reject(fn ($id) => $id === auth()->id());

        foreach ($notifyUsers as $userId) {
            $this->notify($userId, 'Loan Completed', $message, 'success', $loan->id, null, $link);
        }
    }

    public function markRead(ShfNotification $notification): void
    {
        $notification->update(['is_read' => true]);
    }

    public function markAllRead(int $userId): void
    {
        ShfNotification::forUser($userId)->unread()->update(['is_read' => true]);
    }

    public function getUnreadCount(int $userId): int
    {
        return ShfNotification::forUser($userId)->unread()->count();
    }
}
