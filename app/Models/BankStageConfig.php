<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStageConfig extends Model
{
    protected $fillable = ['bank_id', 'stage_id', 'assigned_role', 'phase_roles'];

    protected function casts(): array
    {
        return [
            'phase_roles' => 'array',
        ];
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }
}
