<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SuperAdminAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_configuration_page(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');

        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));

        $response
            ->assertOk()
            ->assertSeeText('Kontrol Super Admin')
            ->assertSeeText('Konfigurasi Sistem')
            ->assertSee(route('admin.configuration.index'), false)
            ->assertSeeText('Operasional')
            ->assertSeeText('Monitoring Real-Time')
            ->assertSee(route('admin.analytics.dashboard'), false)
            ->assertSee(route('admin.reports.export-sales-performance'), false);

        $this->actingAs($superAdmin)
            ->get(route('admin.configuration.index'))
            ->assertOk()
            ->assertSeeText('Konfigurasi Sistem');
    }

    public function test_admin_cannot_access_configuration_page(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.configuration.index'))
            ->assertForbidden();
    }

    public function test_admin_user_listing_is_scoped_to_sales_users(): void
    {
        $admin = $this->createUserWithRole('admin');
        $sales = $this->createUserWithRole('sales');
        $manager = $this->createUserWithRole('manager');

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSeeText('Mode akses:');
        $response->assertSeeText('Anda masuk sebagai Admin. Daftar user difokuskan ke user role sales.');
        $response->assertSeeText('Super Admin');
        $response->assertSeeText('Admin');

        $response = $this->actingAs($admin)->getJson(route('admin.users.data'));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['email' => $sales->email]);
        $response->assertJsonMissing(['email' => $manager->email]);
    }

    public function test_super_admin_user_listing_includes_all_roles(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');
        $sales = $this->createUserWithRole('sales');
        $manager = $this->createUserWithRole('manager');

        $response = $this->actingAs($superAdmin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSeeText('Mode akses:');
        $response->assertSeeText('Anda masuk sebagai Super Admin. Seluruh data user dan role tersedia.');

        $response = $this->actingAs($superAdmin)->getJson(route('admin.users.data'));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');
        $response->assertJsonFragment(['email' => $sales->email]);
        $response->assertJsonFragment(['email' => $manager->email]);
    }

    public function test_admin_can_create_operational_sales_user_but_not_manager_user(): void
    {
        $admin = $this->createUserWithRole('admin');
        $wilayah = Wilayah::factory()->create();
        $salesRole = Role::findByName('sales');
        $managerRole = Role::findByName('manager');

        $successResponse = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Sales Baru',
            'email' => 'sales.baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'wilayah_id' => $wilayah->id,
            'role' => $salesRole->id,
            'is_active' => 1,
        ]);

        $successResponse->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'sales.baru@example.com',
            'wilayah_id' => $wilayah->id,
        ]);

        $createdUser = User::where('email', 'sales.baru@example.com')->firstOrFail();
        $this->assertTrue($createdUser->hasRole('sales'));

        $invalidResponse = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Manager Baru',
            'email' => 'manager.baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567891',
            'wilayah_id' => $wilayah->id,
            'role' => $managerRole->id,
            'is_active' => 1,
        ]);

        $invalidResponse->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', [
            'email' => 'manager.baru@example.com',
        ]);
    }

    public function test_admin_cannot_change_existing_user_role_on_update(): void
    {
        $admin = $this->createUserWithRole('admin');
        $sales = $this->createUserWithRole('sales');
        $wilayah = Wilayah::factory()->create();
        $sales->update(['wilayah_id' => $wilayah->id]);

        $managerRole = Role::findByName('manager');

        $response = $this->actingAs($admin)->put(route('admin.users.update', $sales), [
            'name' => 'Sales Diperbarui',
            'email' => $sales->email,
            'password' => '',
            'password_confirmation' => '',
            'phone' => '081234567892',
            'wilayah_id' => $wilayah->id,
            'role' => $managerRole->id,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $sales->refresh();

        $this->assertTrue($sales->hasRole('sales'));
        $this->assertFalse($sales->hasRole('manager'));
        $this->assertSame('Sales Diperbarui', $sales->name);
    }

    public function test_admin_cannot_edit_super_admin_user(): void
    {
        $admin = $this->createUserWithRole('admin');
        $superAdmin = $this->createUserWithRole('super_admin');

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $superAdmin))
            ->assertForbidden();
    }

    public function test_admin_user_form_is_locked_to_sales_role(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->get(route('admin.users.create'));

        $response->assertOk()
            ->assertSeeText('Role Terbatas')
            ->assertSeeText('Admin hanya dapat membuat atau memperbarui user dengan role sales.')
            ->assertSeeText('Sales')
            ->assertDontSeeText('Super Admin dapat memilih role apa pun, termasuk admin, manager, dan sales.')
            ->assertDontSeeText('Manager');
    }

    public function test_super_admin_user_form_shows_all_role_options(): void
    {
        $superAdmin = $this->createUserWithRole('super_admin');

        $response = $this->actingAs($superAdmin)->get(route('admin.users.create'));

        $response->assertOk()
            ->assertSeeText('Super Admin dapat memilih role apa pun, termasuk admin, manager, dan sales.')
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
