<?php

namespace App\Services;

use App\Models\User;
use App\Enums\AccountTypeEnum;

class SubscriptionService
{
    /**
     * Determine if a user has an active subscription.
     */
    public function isUserActive(User $user): bool
    {
        $now = now();

        if ($user->account_type === AccountTypeEnum::SCHOOL) {
            $school = $user->school;
            
            if ($school && $school->subscription_expiry && $school->subscription_expiry->isFuture()) {
                return true;
            }
        }

        if ($user->account_type === AccountTypeEnum::INDIVIDUAL) {
            if ($user->subscription_expiry && $user->subscription_expiry->isFuture()) {
                return true;
            }
        }

        return false;
    }
}
