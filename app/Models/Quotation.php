<?php

namespace App\Models;

use App\Services\ConfigService;
use App\Traits\HasAuditColumns;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasAuditColumns, SoftDeletes;

    const STATUS_ACTIVE = 'active';

    const STATUS_ON_HOLD = 'on_hold';

    const STATUS_CANCELLED = 'cancelled';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_ON_HOLD,
        self::STATUS_CANCELLED,
    ];

    const STATUS_LABELS = [
        'active' => ['label' => 'Active', 'badge' => 'shf-badge-green'],
        'on_hold' => ['label' => 'On Hold', 'badge' => 'shf-badge-orange'],
        'cancelled' => ['label' => 'Cancelled', 'badge' => 'shf-badge-red'],
    ];

    protected $fillable = [
        'user_id',
        'loan_id',
        'customer_name',
        'customer_type',
        'loan_amount',
        'pdf_filename',
        'pdf_path',
        'additional_notes',
        'prepared_by_name',
        'prepared_by_mobile',
        'selected_tenures',
        'location_id',
        'branch_id',
        'status',
        'hold_reason_key',
        'hold_note',
        'hold_follow_up_date',
        'held_at',
        'held_by',
        'cancel_reason_key',
        'cancel_note',
        'cancelled_at',
        'cancelled_by',
    ];

    protected function casts(): array
    {
        return [
            'loan_amount' => 'integer',
            'selected_tenures' => 'array',
            'hold_follow_up_date' => 'date',
            'held_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function banks(): HasMany
    {
        return $this->hasMany(QuotationBank::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(QuotationDocument::class);
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(LoanDetail::class, 'loan_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function heldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'held_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // ── Scopes ──

    public function scopeActive($query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeOnHold($query): void
    {
        $query->where('status', self::STATUS_ON_HOLD);
    }

    public function scopeCancelled($query): void
    {
        $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Restrict the query to quotations visible to the given user.
     *  - view_all_quotations → everything
     *  - own (user_id)
     *  - branch_manager/bdh → any quotation in their branches
     */
    public function scopeVisibleTo($query, User $user): void
    {
        if ($user->hasPermission('view_all_quotations')) {
            return;
        }

        $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id);

            if ($user->hasAnyRole(['branch_manager', 'bdh'])) {
                $branchIds = $user->branches()->pluck('branches.id')->toArray();
                if ($branchIds) {
                    $q->orWhereIn('branch_id', $branchIds);
                }
            }
        });
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->hasPermission('view_all_quotations')) {
            return true;
        }

        if ($this->user_id === $user->id) {
            return true;
        }

        if ($user->hasAnyRole(['branch_manager', 'bdh']) && $this->branch_id) {
            return $user->branches()->where('branches.id', $this->branch_id)->exists();
        }

        return false;
    }

    // ── Accessors ──

    public function getIsConvertedAttribute(): bool
    {
        return $this->loan_id !== null;
    }

    public function getIsOnHoldAttribute(): bool
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getFormattedAmountAttribute(): string
    {
        return "₹\u{00A0}".$this->formatIndianNumber($this->loan_amount);
    }

    public function getStatusBadgeHtmlAttribute(): string
    {
        $info = self::STATUS_LABELS[$this->status] ?? ['label' => ucfirst((string) $this->status), 'badge' => 'shf-badge-gray'];

        return '<span class="shf-badge '.$info['badge'].'">'.$info['label'].'</span>';
    }

    public function getHoldReasonLabelAttribute(): ?string
    {
        return $this->hold_reason_key
            ? $this->resolveReasonLabel('quotationHoldReasons', $this->hold_reason_key)
            : null;
    }

    public function getCancelReasonLabelAttribute(): ?string
    {
        return $this->cancel_reason_key
            ? $this->resolveReasonLabel('quotationCancelReasons', $this->cancel_reason_key)
            : null;
    }

    public function getTypeLabel(): string
    {
        return match ($this->customer_type) {
            'proprietor' => 'Proprietor / પ્રોપ્રાઇટર',
            'partnership_llp' => 'Partnership / LLP / પાર્ટનરશિપ / LLP',
            'pvt_ltd' => 'PVT LTD / પ્રાઇવેટ લિમિટેડ',
            'salaried' => 'Salaried / પગારદાર',
            'all' => 'All Types / બધા પ્રકાર',
            default => $this->customer_type,
        };
    }

    private function resolveReasonLabel(string $configKey, string $key): string
    {
        $reasons = app(ConfigService::class)->get($configKey, []);
        foreach ((array) $reasons as $reason) {
            if (($reason['key'] ?? null) === $key) {
                return $reason['label_en'] ?? $key;
            }
        }

        return $key;
    }

    private function formatIndianNumber($num): string
    {
        $num = (int) $num;
        if ($num < 1000) {
            return (string) $num;
        }

        $result = '';
        $lastThree = $num % 1000;
        $remaining = (int) ($num / 1000);

        if ($remaining > 0) {
            $remainStr = (string) $remaining;
            $len = strlen($remainStr);
            $groups = [];
            $i = $len;
            while ($i > 0) {
                $start = max(0, $i - 2);
                $groups[] = substr($remainStr, $start, $i - $start);
                $i = $start;
            }
            $result = implode(',', array_reverse($groups)).',';
        }

        $result .= str_pad($lastThree, 3, '0', STR_PAD_LEFT);

        return ltrim($result, '0,') ?: '0';
    }
}
