<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_protected_route_before_timeout(): void
    {
        Configuration::setValue('session_timeout_minutes', 30, 'integer');

        $user = $this->createUserWithRole('admin');

        $this->actingAs($user)
            ->withSession([
                'last_activity_at' => now()->subMinutes(10)->toDateTimeString(),
            ]);

        $this->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_user_is_redirected_after_timeout(): void
    {
        Configuration::setValue('session_timeout_minutes', 30, 'integer');

        $user = $this->createUserWithRole('admin');

        $this->actingAs($user)
            ->withSession([
                'last_activity_at' => now()->subMinutes(31)->toDateTimeString(),
            ]);

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_configuration_change_affects_timeout_logic(): void
    {
        Configuration::setValue('session_timeout_minutes', 15, 'integer');

        $user = $this->createUserWithRole('manager');

        $this->actingAs($user)
            ->withSession([
                'last_activity_at' => now()->subMinutes(16)->toDateTimeString(),
            ]);

        $this->get(route('manager.dashboard'))
            ->assertRedirect(route('login'));
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
