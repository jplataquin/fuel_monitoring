<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChargeableAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'status'];

    protected static function booted(): void
    {
        static::deleted(function (ChargeableAccount $account) {
            $account->subAccounts()->delete();
        });

        static::restored(function (ChargeableAccount $account) {
            $account->subAccounts()->withTrashed()->restore();
        });
    }

    public function subAccounts(): HasMany
    {
        return $this->hasMany(SubAccount::class);
    }
}
