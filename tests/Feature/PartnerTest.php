<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Partner;
use App\Enums\RoleEnum;
use App\Enums\AccountTypeEnum;

class PartnerTest extends TestCase
{
    use RefreshDatabase;

    private function partnerUser(Partner $partner): User
    {
        return User::factory()->create([
            'name'         => $partner->name,
            'role'         => RoleEnum::PARTNER->value,
            'account_type' => AccountTypeEnum::INDIVIDUAL->value,
        ]);
    }

    public function test_partner_can_access_dashboard(): void
    {
        $partner = Partner::create(['name' => 'Bright Tutorials', 'referral_code' => 'PTR123']);

        User::factory()->create([
            'role'                   => RoleEnum::STUDENT->value,
            'referred_by_partner_id' => $partner->id,
            'subscription_expiry'    => now()->addDays(30),
            'account_type'           => AccountTypeEnum::INDIVIDUAL->value,
        ]);

        User::factory()->create([
            'role'                   => RoleEnum::STUDENT->value,
            'referred_by_partner_id' => $partner->id,
            'subscription_expiry'    => now()->subDays(30),
            'account_type'           => AccountTypeEnum::INDIVIDUAL->value,
        ]);

        $this->actingAs($this->partnerUser($partner))
             ->getJson('/api/partner/dashboard')
             ->assertStatus(200)
             ->assertJsonPath('data.total_referred_students', 2)
             ->assertJsonPath('data.active_referred_students', 1)
             ->assertJsonStructure(['data' => [
                 'total_referred_students',
                 'active_referred_students',
                 'total_commissions_earned',
                 'unpaid_commissions',
             ]]);
    }

    public function test_student_cannot_access_partner_dashboard(): void
    {
        $user = User::factory()->create([
            'role'         => RoleEnum::STUDENT->value,
            'account_type' => AccountTypeEnum::INDIVIDUAL->value,
        ]);

        $this->actingAs($user)->getJson('/api/partner/dashboard')
             ->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_access_partner_dashboard(): void
    {
        $this->getJson('/api/partner/dashboard')->assertStatus(401);
    }
}
