<?php

namespace App\Models;

use App\Enums\AccountTypeEnum;
use App\Enums\RoleEnum;
use App\Enums\SubscriptionTypeEnum;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'account_type',
        'exam_board',
        'school_id',
        'department_id',
        'referred_by_partner_id',
        'subscription_type',
        'subscription_expiry',
        'is_suspended',
        // Gamification fields
        'xp',
        'streak_days',
        'best_streak',
        'last_activity_date',
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
        'last_activity_date' => 'date',
        'is_suspended' => 'boolean',
        'xp' => 'integer',
        'streak_days' => 'integer',
        'best_streak' => 'integer',
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

    public function aiChats(): HasMany
    {
        return $this->hasMany(AiChat::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
