<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_id',
        'calculated_quantity',
        'say_quantity',
        'calculated_hours',
        'calculated_kilometers',
        'fuel_factor_km',
        'fuel_factor_hr',
        'date_from',
        'date_to',
        'status',
        'actual_quantity',
        'created_by',
        'updated_by',
        'actualized_by',
        'actualized_at',
        'void_by',
        'void_at',
        'deleted_by',
    ];

    protected $casts = [
        'actualized_at' => 'datetime',
        'void_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function utilizationEntries(): HasMany
    {
        return $this->hasMany(UtilizationEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }

    public function actualizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualized_by')->withTrashed();
    }

    public function voider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'void_by')->withTrashed();
    }
}
