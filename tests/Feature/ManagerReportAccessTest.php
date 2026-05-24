<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerReportAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_access_analytics_pages(): void
    {
        $manager = $this->createUserWithRole('manager');

        $this->actingAs($manager)->get(route('admin.analytics.dashboard'))->assertOk();
        $this->actingAs($manager)->get(route('admin.analytics.sales-performance'))->assertOk();
        $this->actingAs($manager)->get(route('admin.analytics.regional-performance'))->assertOk();
        $this->actingAs($manager)->get(route('admin.analytics.klien-analysis'))->assertOk();
    }

    public function test_sales_cannot_access_analytics_pages(): void
    {
        $sales = $this->createUserWithRole('sales');

        $this->actingAs($sales)->get(route('admin.analytics.dashboard'))->assertForbidden();
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
