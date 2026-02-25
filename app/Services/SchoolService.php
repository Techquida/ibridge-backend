<?php

namespace App\Services;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SchoolService
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    public function resolveSchoolForUser(User $user): School
    {
        return School::findOr($user->school_id,
            fn() => throw new ModelNotFoundException('School profile not found.')
        );
    }

    public function getSummary(School $school): array
    {
        $users = $school->users()->get();

        $activeCount = $users->filter(
            fn(User $u) => $this->subscriptionService->isUserActive($u)
        )->count();

        return [
            'total_students' => $users->count(),
            'active_students' => $activeCount,
            'subscription_type' => $school->subscription_type?->value ?? $school->subscription_type,
            'subscription_expiry' => $school->subscription_expiry,
        ];
    }
}
