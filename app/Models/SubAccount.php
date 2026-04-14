<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['chargeable_account_id', 'name'];

    public function chargeableAccount(): BelongsTo
    {
        return $this->belongsTo(ChargeableAccount::class);
    }

    public function budgets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SubAccountBudget::class);
    }
}
