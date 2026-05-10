<?php

namespace App\Services;

use App\Enums\AccountTypeEnum;
use App\Enums\RoleEnum;
use App\Enums\SubscriptionTypeEnum;
use App\Models\Partner;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $accountType = AccountTypeEnum::INDIVIDUAL;
            $schoolId = null;
            $partnerId = null;
            $subscriptionExpiry = null;
            $subscriptionType = null;

            if (! empty($data['school_code'])) {
                $school = School::where('unique_code', $data['school_code'])->firstOrFail();
                $schoolId = $school->id;
                $accountType = AccountTypeEnum::SCHOOL;
                // Inherit school subscription expiry
                $subscriptionExpiry = $school->subscription_expiry;
                $subscriptionType = SubscriptionTypeEnum::SCHOOL_STANDARD->value;
            }

            if (! empty($data['referral_code'])) {
                $partnerId = Partner::where('referral_code', $data['referral_code'])->value('id');
            }

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => RoleEnum::STUDENT,
                'account_type' => $accountType,
                'exam_board' => $data['exam_board'] ?? null,
                'school_id' => $schoolId,
                'referred_by_partner_id' => $partnerId,
                'subscription_type' => $subscriptionType,
                'subscription_expiry' => $subscriptionExpiry,
            ]);

            return [
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
            ];
        });
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->fill(array_filter([
            'name' => $data['name'] ?? null,
            'exam_board' => $data['exam_board'] ?? null,
        ], fn ($v) => $v !== null));

        $user->save();

        return $user->fresh();
    }
}
