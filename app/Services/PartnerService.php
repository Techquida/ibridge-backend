<?php

namespace App\Services;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PartnerService
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    public function resolvePartnerForUser(User $user): Partner
    {
        return Partner::where('name', $user->name)->firstOr(
            fn () => throw new ModelNotFoundException('Partner profile not found.')
        );
    }

    public function getDashboardSummary(Partner $partner): array
    {
        $referredUsers = $partner->users()->get();

        $activeCount = $referredUsers->filter(
            fn (User $u) => $this->subscriptionService->isUserActive($u)
        )->count();

        return [
            'total_referred_students' => $referredUsers->count(),
            'active_referred_students' => $activeCount,
            'total_commissions_earned' => (float) $partner->commissions()->sum('amount'),
            'unpaid_commissions' => (float) $partner->commissions()->where('is_paid', false)->sum('amount'),
        ];
    }
}
