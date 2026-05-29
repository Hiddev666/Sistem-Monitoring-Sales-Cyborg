<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_real_time_monitoring_page(): void
    {
        $admin = $this->createUserWithRole('admin');

        $this->actingAs($admin)
            ->get(route('admin.monitoring.index'))
            ->assertOk()
            ->assertSeeText('Monitoring Real-Time Administrator')
            ->assertSee(route('admin.reports.export-sales-performance'), false);
    }

    public function test_sales_cannot_access_admin_monitoring_page(): void
    {
        $sales = $this->createUserWithRole('sales');

        $this->actingAs($sales)
            ->get(route('admin.monitoring.index'))
            ->assertForbidden();
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
