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
        'type',
        'quantity',
        'unit',
    ];

    protected $appends = [
        'display_name',
    ];

    protected $casts = [
        'merged_at' => 'datetime',
        'quantity' => 'float',
    ];

    public function accomplishments(): HasMany
    {
        return $this->hasMany(AccomplishmentRegistry::class);
    }

    public function getAccomplishmentAttribute(): float
    {
        if ($this->quantity && $this->quantity > 0) {
            $latestAccomplishment = $this->accomplishments()->latest('date_at')->latest('id')->first();

            if ($latestAccomplishment) {
                return ($latestAccomplishment->quantity / $this->quantity) * 100;
            }
        }

        return 0.0;
    }

    public function chargeableAccount(): BelongsTo
    {
        return $this->belongsTo(ChargeableAccount::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(SubAccountBudget::class);
    }

    public function offsets(): HasMany
    {
        return $this->hasMany(SubAccountOffset::class);
    }

    public function utilizationEntries(): HasMany
    {
        return $this->hasMany(UtilizationEntry::class);
    }

    public function mergedTo(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class, 'merged_to_id')->withTrashed();
    }

    public function mergedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_by');
    }

    public function consumedBudget(): float
    {
        // 1. Asset-based consumption via utilization entries attached to DONE fuel orders
        $entries = $this->utilizationEntries()
            ->with('fuelOrder')
            ->whereHas('fuelOrder', function ($q) {
                $q->where('status', 'DONE');
            })
            ->get();

        $assetConsumed = 0;
        $entriesByOrder = $entries->groupBy('fuel_order_id');

        foreach ($entriesByOrder as $orderId => $orderEntries) {
            $fuelOrder = $orderEntries->first()->fuelOrder;
            if (! $fuelOrder) {
                continue;
            }

            $subCalcQty = 0;
            foreach ($orderEntries as $entry) {
                $subCalcQty += $entry->calculated_quantity;
            }

            if ($fuelOrder->calculated_quantity > 0) {
                $assetConsumed += ($subCalcQty / $fuelOrder->calculated_quantity) * $fuelOrder->actual_quantity;
            } else {
                $assetConsumed += $subCalcQty;
            }
        }

        // 2. Direct fuel orders consumption (actual_quantity of DONE direct orders)
        $directConsumed = FuelOrder::whereNull('asset_id')
            ->where('sub_account_id', $this->id)
            ->where('status', 'DONE')
            ->sum('actual_quantity');

        return $assetConsumed + $directConsumed;
    }

    public function remainingBudget(): float
    {
        $approvedBudget = $this->budgets()->where('status', 'Approved')->sum('budget_quantity');
        $approvedOffsets = $this->offsets()->where('status', 'Approved')->sum('quantity');

        $totalApproved = $approvedBudget + $approvedOffsets;

        return max(0.0, (float) $totalApproved - $this->consumedBudget());
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name.($this->type === 'Uncontrolled' ? ' 🔓' : '');
    }
}
