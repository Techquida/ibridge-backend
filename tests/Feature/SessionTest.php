<?php

namespace Tests\Feature;

use App\Enums\AccountTypeEnum;
use App\Enums\RoleEnum;
use App\Enums\SessionModeEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use RefreshDatabase;

    private function sessionPayload(array $overrides = []): array
    {
        return array_merge([
            'subject' => 'Mathematics',
            'mode' => SessionModeEnum::LIGHT->value,
            'score' => 80,
            'accuracy' => 85.5,
            'time_used' => 600,
        ], $overrides);
    }

    private function activeStudent(): User
    {
        return User::factory()->create([
            'role' => RoleEnum::STUDENT->value,
            'account_type' => AccountTypeEnum::INDIVIDUAL->value,
            'subscription_expiry' => now()->addDays(30),
        ]);
    }

    private function inactiveStudent(): User
    {
        return User::factory()->create([
            'role' => RoleEnum::STUDENT->value,
            'account_type' => AccountTypeEnum::INDIVIDUAL->value,
            'subscription_expiry' => now()->subDay(),
        ]);
    }

    public function test_active_student_can_store_session(): void
    {
        $user = $this->activeStudent();

        $this->actingAs($user)->postJson('/api/sessions', $this->sessionPayload())
            ->assertStatus(201)
            ->assertJsonPath('data.subject', 'Mathematics')
            ->assertJsonStructure(['data' => ['id', 'subject', 'mode', 'score', 'accuracy', 'time_used']]);

        $this->assertDatabaseHas('exam_sessions', ['user_id' => $user->id, 'subject' => 'Mathematics']);
    }

    public function test_inactive_student_cannot_store_session(): void
    {
        $this->actingAs($this->inactiveStudent())
            ->postJson('/api/sessions', $this->sessionPayload())
            ->assertStatus(403);
    }

    public function test_session_requires_valid_mode(): void
    {
        $this->actingAs($this->activeStudent())
            ->postJson('/api/sessions', $this->sessionPayload(['mode' => 'invalid_mode']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['mode']);
    }

    public function test_student_can_retrieve_their_sessions(): void
    {
        $user = $this->activeStudent();

        $this->actingAs($user)->postJson('/api/sessions', $this->sessionPayload());
        $this->actingAs($user)->postJson('/api/sessions', $this->sessionPayload(['subject' => 'Physics']));

        $this->actingAs($user)->getJson('/api/sessions')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);
    }

    public function test_unauthenticated_user_cannot_store_session(): void
    {
        $this->postJson('/api/sessions', $this->sessionPayload())
            ->assertStatus(401);
    }
}
