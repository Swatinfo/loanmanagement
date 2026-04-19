<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasAuditColumns, SoftDeletes;

    protected $fillable = [
        'customer_name',
        'mobile',
        'email',
        'date_of_birth',
        'pan_number',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    public function loans(): HasMany
    {
        return $this->hasMany(LoanDetail::class, 'customer_id');
    }

    /**
     * Restrict to customers visible to the given user.
     *
     *  - view_all_loans → everything (admin/super_admin supervisors)
     *  - own: customers created by the user, or linked to any loan they
     *    created / advise on / hold a stage for
     *  - branch_manager/bdh: customers with a loan in any of the user's branches
     */
    public function scopeVisibleTo($query, User $user): void
    {
        if ($user->hasPermission('view_all_loans')) {
            return;
        }

        $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhereHas('loans', function ($lq) use ($user) {
                    $lq->where('created_by', $user->id)
                        ->orWhere('assigned_advisor', $user->id)
                        ->orWhereHas('stageAssignments', fn ($sq) => $sq->where('assigned_to', $user->id));
                });

            if ($user->hasAnyRole(['branch_manager', 'bdh'])) {
                $branchIds = $user->branches()->pluck('branches.id')->toArray();
                if ($branchIds) {
                    $q->orWhereHas('loans', fn ($lq) => $lq->whereIn('branch_id', $branchIds));
                }
            }
        });
    }

    public function isVisibleTo(User $user): bool
    {
        return static::visibleTo($user)->whereKey($this->id)->exists();
    }

    public function isEditableBy(User $user): bool
    {
        return $user->hasPermission('manage_customers') && $this->isVisibleTo($user);
    }
}
