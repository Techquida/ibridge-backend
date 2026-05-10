<?php

namespace App\Models;

use App\Enums\SubscriptionTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $fillable = [
        'name',
        'unique_code',
        'subscription_type',
        'subscription_expiry',
        'is_suspended',
    ];

    protected $casts = [
        'subscription_type' => SubscriptionTypeEnum::class,
        'subscription_expiry' => 'datetime',
        'is_suspended' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
