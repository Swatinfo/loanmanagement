<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStageUser extends Model
{
    protected $fillable = ['product_stage_id', 'branch_id', 'location_id', 'user_id', 'is_default', 'phase_index'];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function productStage(): BelongsTo
    {
        return $this->belongsTo(ProductStage::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
