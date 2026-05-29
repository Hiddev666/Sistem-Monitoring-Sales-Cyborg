<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_dashboard_shows_quick_actions_for_reporting(): void
    {
        $manager = $this->createUserWithRole('manager');

        $this->actingAs($manager)
            ->get(route('manager.dashboard'))
            ->assertOk()
            ->assertSeeText('Ringkasan Hari Ini')
            ->assertSeeText('Aksi Cepat')
            ->assertSeeText('Monitoring Real-Time')
            ->assertSee(route('manager.analytics.dashboard'), false)
            ->assertSee(route('manager.analytics.sales-performance'), false)
            ->assertSee(route('manager.reports.export-sales-performance'), false)
            ->assertDontSee(route('admin.configuration.index'), false);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
