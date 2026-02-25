<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\School;
use App\Models\Partner;
use App\Enums\RoleEnum;
use App\Enums\AccountTypeEnum;
use App\Enums\SubscriptionTypeEnum;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function basePayload(array $overrides = []): array
    {
        return array_merge([
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ], $overrides);
    }

    public function test_student_can_register_as_individual(): void
    {
        $this->postJson('/api/register', $this->basePayload())
             ->assertStatus(201)
             ->assertJsonPath('data.user.account_type', 'individual')
             ->assertJsonStructure(['data' => ['user', 'token']]);

        $this->assertDatabaseHas('users', [
            'email'        => 'test@example.com',
            'role'         => RoleEnum::STUDENT->value,
            'account_type' => AccountTypeEnum::INDIVIDUAL->value,
        ]);
    }

    public function test_student_can_register_with_school_code(): void
    {
        $school = School::create([
            'name'              => 'Test High School',
            'unique_code'       => 'SCH123',
            'subscription_type' => SubscriptionTypeEnum::SCHOOL_STANDARD->value,
        ]);

        $this->postJson('/api/register', $this->basePayload(['school_code' => 'SCH123']))
             ->assertStatus(201)
             ->assertJsonPath('data.user.account_type', 'school')
             ->assertJsonPath('data.user.school_id', $school->id);

        $this->assertDatabaseHas('users', [
            'email'        => 'test@example.com',
            'account_type' => AccountTypeEnum::SCHOOL->value,
            'school_id'    => $school->id,
        ]);
    }

    public function test_student_can_register_with_referral_code(): void
    {
        $partner = Partner::create([
            'name'          => 'My Partner',
            'referral_code' => 'REF001',
        ]);

        $this->postJson('/api/register', $this->basePayload(['referral_code' => 'REF001']))
             ->assertStatus(201)
             ->assertJsonPath('data.user.account_type', 'individual');

        $this->assertDatabaseHas('users', [
            'email'                  => 'test@example.com',
            'referred_by_partner_id' => $partner->id,
        ]);
    }

    public function test_registration_fails_with_invalid_school_code(): void
    {
        $this->postJson('/api/register', $this->basePayload(['school_code' => 'NONEXISTENT']))
             ->assertStatus(422)
             ->assertJsonValidationErrors(['school_code']);
    }

    public function test_registration_fails_with_invalid_referral_code(): void
    {
        $this->postJson('/api/register', $this->basePayload(['referral_code' => 'BADCODE']))
             ->assertStatus(422)
             ->assertJsonValidationErrors(['referral_code']);
    }

    public function test_registration_fails_without_password_confirmation(): void
    {
        $this->postJson('/api/register', [
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => 'password',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_with_duplicate_email(): void
    {
        $this->postJson('/api/register', $this->basePayload());
        $this->postJson('/api/register', $this->basePayload())
             ->assertStatus(422)
             ->assertJsonValidationErrors(['email']);
    }
}
