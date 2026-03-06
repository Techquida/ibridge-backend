<?php

namespace App\Services;

use App\Enums\AccountTypeEnum;
use App\Enums\RoleEnum;
use App\Models\User;

class SubscriptionService
{
    /**
     * Determine if a user has access to practice features.
     *
     * Access rules (in priority order):
     *  1. SYSTEM_ADMIN always has full access.
     *  2. School users: valid if their school subscription is still active.
     *  3. Individual users: valid if subscription_expiry is set and in the future.
     *  4. New users (no expiry set): 7-day grace period from account creation (trial).
     */
    public function isUserActive(User $user): bool
    {
        // System admins always have full access
        if ($user->role === RoleEnum::SYSTEM_ADMIN) {
            return true;
        }

        $now = now();

        // School-type account
        if ($user->account_type === AccountTypeEnum::SCHOOL) {
            $school = $user->school;
            return $school
                && $school->subscription_expiry
                && $school->subscription_expiry->isFuture();
        }

        // Individual or null account_type — check subscription_expiry
        if ($user->subscription_expiry) {
            return $user->subscription_expiry->isFuture();
        }

        // No expiry set at all → grant 7-day trial grace from account creation
        return $user->created_at->diffInDays($now) <= 7;
    }
}
