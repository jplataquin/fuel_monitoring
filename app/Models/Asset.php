<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'fleet_no',
        'asset_type_id',
        'fuel_factor_km',
        'fuel_factor_hr',
        'plate_no',
        'fuel_type',
        'tank_capacity',
        'last_kilometer_reading',
        'last_engine_hours',
        'last_time',
        'last_date',
    ];

    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class);
    }

    public function utilizationEntries(): HasMany
    {
        return $this->hasMany(UtilizationEntry::class);
    }

    public function fuelOrders(): HasMany
    {
        return $this->hasMany(FuelOrder::class);
    }
}
