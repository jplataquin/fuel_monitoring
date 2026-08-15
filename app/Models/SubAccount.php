<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'chargeable_account_id',
        'name',
        'merged_to_id',
        'merged_by',
        'merged_at',
        'merge_remarks',
    ];

    protected $casts = [
        'merged_at' => 'datetime',
    ];

    public function chargeableAccount(): BelongsTo
    {
        return $this->belongsTo(ChargeableAccount::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(SubAccountBudget::class);
    }

    public function mergedTo(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class, 'merged_to_id')->withTrashed();
    }

    public function mergedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_by');
    }
}
