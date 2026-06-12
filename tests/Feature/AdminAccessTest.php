<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_configuration_page(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSeeText('Konfigurasi Sistem')
            ->assertSeeText('Konfigurasi Sistem')
            ->assertSee(route('admin.configuration.index'), false)
            ->assertSeeText('Operasional')
            ->assertSeeText('Monitoring Real-Time')
            ->assertSee(route('admin.analytics.dashboard'), false)
            ->assertSee(route('admin.reports.export-sales-performance'), false);

        $this->actingAs($admin)
            ->get(route('admin.configuration.index'))
            ->assertOk()
            ->assertSeeText('Konfigurasi Sistem');
    }

    public function test_manager_cannot_access_configuration_page(): void
    {
        $manager = $this->createUserWithRole('manager');

        $this->actingAs($manager)
            ->get(route('admin.configuration.index'))
            ->assertForbidden();
    }

    public function test_admin_user_listing_includes_all_roles(): void
    {
        $admin = $this->createUserWithRole('admin');
        $sales = $this->createUserWithRole('sales');
        $manager = $this->createUserWithRole('manager');

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSeeText('Mode akses:');
        $response->assertSeeText('Anda masuk sebagai Admin. Seluruh data user dan role tersedia.');
        $response->assertSeeText('Admin');

        $response = $this->actingAs($admin)->getJson(route('admin.users.data'));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonFragment(['email' => $sales->email]);
        $response->assertJsonFragment(['email' => $manager->email]);
    }

    public function test_admin_can_create_and_update_roles(): void
    {
        $admin = $this->createUserWithRole('admin');
        $wilayah = Wilayah::factory()->create();
        $salesRole = Role::findByName('sales');
        $managerRole = Role::findByName('manager');

        $successResponse = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Manager Baru',
            'email' => 'manager.baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'wilayah_id' => $wilayah->id,
            'role' => $managerRole->id,
            'is_active' => 1,
        ]);

        $successResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'manager.baru@example.com',
            'wilayah_id' => $wilayah->id,
        ]);

        $createdUser = User::where('email', 'manager.baru@example.com')->firstOrFail();
        $this->assertTrue($createdUser->hasRole('manager'));

        $updateResponse = $this->actingAs($admin)->put(route('admin.users.update', $createdUser), [
            'name' => 'Admin Baru',
            'email' => 'manager.baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'wilayah_id' => $wilayah->id,
            'role' => $salesRole->id,
            'is_active' => 1,
        ]);

        $updateResponse->assertRedirect(route('admin.users.index'));
        $createdUser->refresh();

        $this->assertTrue($createdUser->hasRole('sales'));
        $this->assertFalse($createdUser->hasRole('manager'));
        $this->assertSame('Admin Baru', $createdUser->name);
    }

    public function test_admin_user_form_shows_all_role_options(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.users.create'));

        $response->assertOk()
            ->assertSeeText('Admin dapat menetapkan role admin, manager, dan sales.')
            ->assertSeeText('Role')
            ->assertSeeText('Admin')
            ->assertSeeText('Manager')
            ->assertSeeText('Sales');
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
