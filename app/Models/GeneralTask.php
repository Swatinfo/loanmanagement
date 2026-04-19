<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneralTask extends Model
{
    const STATUS_PENDING = 'pending';

    const STATUS_IN_PROGRESS = 'in_progress';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    const STATUS_LABELS = [
        'pending' => ['label' => 'Pending', 'badge' => 'shf-badge-gray'],
        'in_progress' => ['label' => 'In Progress', 'badge' => 'shf-badge-blue'],
        'completed' => ['label' => 'Completed', 'badge' => 'shf-badge-green'],
        'cancelled' => ['label' => 'Cancelled', 'badge' => 'shf-badge-red'],
    ];

    const PRIORITY_LOW = 'low';

    const PRIORITY_NORMAL = 'normal';

    const PRIORITY_HIGH = 'high';

    const PRIORITY_URGENT = 'urgent';

    const PRIORITIES = [
        self::PRIORITY_LOW,
        self::PRIORITY_NORMAL,
        self::PRIORITY_HIGH,
        self::PRIORITY_URGENT,
    ];

    const PRIORITY_LABELS = [
        'low' => ['label' => 'Low', 'badge' => 'shf-badge-gray'],
        'normal' => ['label' => 'Normal', 'badge' => 'shf-badge-blue'],
        'high' => ['label' => 'High', 'badge' => 'shf-badge-orange'],
        'urgent' => ['label' => 'Urgent', 'badge' => 'shf-badge-red'],
    ];

    protected $table = 'general_tasks';

    protected $fillable = [
        'title',
        'description',
        'created_by',
        'assigned_to',
        'loan_detail_id',
        'quotation_id',
        'status',
        'priority',
        'due_date',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    // ── Relationships ──

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(LoanDetail::class, 'loan_detail_id');
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(GeneralTaskComment::class)->latest();
    }

    // ── Scopes ──

    public function scopeVisibleTo($query, User $user): void
    {
        if ($user->hasPermission('view_all_tasks')) {
            return; // admin/super_admin see all
        }

        $query->where(function ($q) use ($user) {
            // Own tasks (created or assigned)
            $q->where('created_by', $user->id)
                ->orWhere('assigned_to', $user->id);

            // BDH can see tasks from users in their branches
            if ($user->hasRole('bdh')) {
                $branchUserIds = \Illuminate\Support\Facades\DB::table('user_branches')
                    ->whereIn('branch_id', $user->branches()->pluck('branches.id'))
                    ->pluck('user_id')
                    ->unique()
                    ->toArray();

                if ($branchUserIds) {
                    $q->orWhereIn('created_by', $branchUserIds)
                        ->orWhereIn('assigned_to', $branchUserIds);
                }
            }
        });
    }

    public function scopePending($query): void
    {
        $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    // ── Authorization helpers ──

    public function isVisibleTo(User $user): bool
    {
        if ($user->hasPermission('view_all_tasks')) {
            return true;
        }

        if ($this->created_by === $user->id || $this->assigned_to === $user->id) {
            return true;
        }

        // BDH can see tasks from users in their branches
        if ($user->hasRole('bdh')) {
            $branchUserIds = \Illuminate\Support\Facades\DB::table('user_branches')
                ->whereIn('branch_id', $user->branches()->pluck('branches.id'))
                ->pluck('user_id')
                ->unique()
                ->toArray();

            return in_array($this->created_by, $branchUserIds)
                || in_array($this->assigned_to, $branchUserIds);
        }

        return false;
    }

    public function isEditableBy(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    public function canChangeStatus(User $user): bool
    {
        return $this->created_by === $user->id || $this->assigned_to === $user->id;
    }

    public function isDeletableBy(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    // ── Accessors ──

    public function getStatusBadgeHtmlAttribute(): string
    {
        $info = self::STATUS_LABELS[$this->status] ?? ['label' => ucfirst($this->status), 'badge' => 'shf-badge-gray'];

        return '<span class="shf-badge '.$info['badge'].'">'.$info['label'].'</span>';
    }

    public function getPriorityBadgeHtmlAttribute(): string
    {
        $info = self::PRIORITY_LABELS[$this->priority] ?? ['label' => ucfirst($this->priority), 'badge' => 'shf-badge-gray'];

        return '<span class="shf-badge '.$info['badge'].'">'.$info['label'].'</span>';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }
}
