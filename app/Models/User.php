<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\RoleEnum;
use App\Enums\AccountTypeEnum;
use App\Enums\SubscriptionTypeEnum;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'account_type',
        'school_id',
        'referred_by_partner_id',
        'subscription_type',
        'subscription_expiry',
        'is_suspended',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => RoleEnum::class,
        'account_type' => AccountTypeEnum::class,
        'subscription_type' => SubscriptionTypeEnum::class,
        'subscription_expiry' => 'datetime',
        'is_suspended' => 'boolean',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === RoleEnum::SYSTEM_ADMIN;
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function referredByPartner()
    {
        return $this->belongsTo(Partner::class, 'referred_by_partner_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
