<?php

namespace Tests\Unit\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $this->assertTrue($user->is_active);
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plainpassword',
        ]);

        $this->assertNotEquals('plainpassword', $user->password);
        $this->assertTrue(Hash::check('plainpassword', $user->password));
    }

    public function test_user_scope_active_returns_only_active_users(): void
    {
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => false]);
        User::factory()->create(['is_active' => true]);

        $activeUsers = User::active()->get();

        $this->assertCount(2, $activeUsers);
        $this->assertTrue($activeUsers->every(fn($user) => $user->is_active));
    }

    public function test_user_can_have_roles(): void
    {
        $user = User::factory()->create();

        $user->assignRole('sales');

        $this->assertTrue($user->hasRole('sales'));
        $this->assertTrue($user->isSales());
        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isManager());
    }

    public function test_get_role_label(): void
    {
        $salesUser = User::factory()->create();
        $salesUser->assignRole('sales');

        $this->assertEquals('Sales', $salesUser->getRoleLabel());
    }
}
