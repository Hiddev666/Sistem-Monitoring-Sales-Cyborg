<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class SalesTrackingIndicatorTest extends TestCase
{
    public function test_sales_layout_shows_tracking_indicator(): void
    {
        $sales = $this->createUserWithRole('sales');

        $this->actingAs($sales)
            ->get(route('sales.dashboard'))
            ->assertOk()
            ->assertSee('id="trackingStatus"', false)
            ->assertSee('Tracking nonaktif')
            ->assertSee('Memeriksa status absensi');
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
