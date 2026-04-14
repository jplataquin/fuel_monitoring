<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UtilizationEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'date',
        'start_time',
        'end_time',
        'reference',
        'particulars',
        'start_kilometer_reading',
        'end_kilometer_reading',
        'fuel_factor_km',
        'start_hour_reading',
        'end_hour_reading',
        'fuel_factor_hr',
        'driver_operator_name',
        'chargeable_account_id',
        'sub_account_id',
        'fuel_order_id',
        'calculation_type',
        'unbudgeted',
        'remarks',
        'last_kilometer_reading',
        'last_engine_hours',
        'last_date',
        'last_time',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'start_kilometer_reading' => 'float',
        'end_kilometer_reading' => 'float',
        'start_hour_reading' => 'float',
        'end_hour_reading' => 'float',
        'unbudgeted' => 'boolean',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function chargeableAccount(): BelongsTo
    {
        return $this->belongsTo(ChargeableAccount::class);
    }

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class);
    }

    public function fuelOrder(): BelongsTo
    {
        return $this->belongsTo(FuelOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by')->withTrashed();
    }
}
