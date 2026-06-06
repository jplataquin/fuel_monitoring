<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChargeableAccountOffset extends Model
{
    use HasFactory;

    protected $fillable = [
        'chargeable_account_id',
        'quantity',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function chargeableAccount(): BelongsTo
    {
        return $this->belongsTo(ChargeableAccount::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }
}
