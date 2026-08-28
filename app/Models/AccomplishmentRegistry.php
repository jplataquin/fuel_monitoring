<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccomplishmentRegistry extends Model
{
    use HasFactory;

    protected $table = 'accomplishment_registry';

    protected $fillable = [
        'sub_account_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function subAccount(): BelongsTo
    {
        return $this->belongsTo(SubAccount::class);
    }
}
