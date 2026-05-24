<?php

namespace Tests\Feature\Phase5;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\LokasiRealtime;
use App\Models\User;
use App\Services\GpsValidationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 5: Dashboard & Monitoring - LocationController API Tests
 *
 * Test cases for LocationController API endpoints
 */
class LocationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $salesUser;
    protected User $managerUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->salesUser = $this->createUserWithRole('sales');
        $this->managerUser = $this->createUserWithRole('manager');
    }

    /**
     * Test sales can update location
     */
    public function test_sales_can_update_location()
    {
        $locationData = [
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
            'accuracy' => 8.5,
        ];

        $response = $this->actingAs($this->salesUser)
            ->postJson('/api/location/update', $locationData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Location updated successfully'
            ]);

        $this->assertDatabaseHas('lokasi_realtime', [
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
            'akurasi_meter' => 8.5,
        ]);
    }

    /**
     * Test location update keeps history points for movement detection
     */
    public function test_location_update_keeps_history_points_for_movement_detection()
    {
        Carbon::setTestNow(Carbon::parse('2026-05-22 09:00:00'));

        $this->actingAs($this->salesUser)->postJson('/api/location/update', [
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ])->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-05-22 10:05:00'));

        $this->actingAs($this->salesUser)->postJson('/api/location/update', [
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ])->assertOk();

        Carbon::setTestNow();

        $this->assertDatabaseCount('lokasi_realtime', 2);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $response->assertOk();
        $this->assertEquals(65, $response->json('sales.0.noMovementMinutes'));
        $this->assertEquals('idle', $response->json('sales.0.status'));
    }

    /**
     * Test location update validates latitude
     */
    public function test_location_update_validates_latitude_range()
    {
        $response = $this->actingAs($this->salesUser)
            ->postJson('/api/location/update', [
                'latitude' => 91.0, // Invalid: > 90
                'longitude' => 104.7553750,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude']);
    }

    /**
     * Test location update validates longitude
     */
    public function test_location_update_validates_longitude_range()
    {
        $response = $this->actingAs($this->salesUser)
            ->postJson('/api/location/update', [
                'latitude' => -2.9760971,
                'longitude' => 181.0, // Invalid: > 180
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['longitude']);
    }

    /**
     * Test location update requires authentication
     */
    public function test_location_update_requires_authentication()
    {
        $response = $this->postJson('/api/location/update', [
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test manager can get sales locations
     */
    public function test_manager_can_get_sales_locations()
    {
        // Create location for sales user
        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'sales' => [
                    '*' => [
                        'id',
                        'name',
                        'latitude',
                        'longitude',
                        'accuracy',
                        'status',
                        'visitCount',
                        'completedCount',
                        'noMovementMinutes',
                        'lastUpdate',
                    ]
                ],
                'activeSales',
                'totalVisits',
                'completedVisits',
                'notMoving',
            ]);

        $salesData = $response->json('sales');
        $this->assertCount(1, $salesData);
        $this->assertEquals($this->salesUser->id, $salesData[0]['id']);
        $this->assertEquals($this->salesUser->name, $salesData[0]['name']);
    }

    /**
     * Test sales locations only includes sales users
     */
    public function test_sales_locations_only_includes_sales_users()
    {
        // Create admin user with location
        $adminUser = $this->createUserWithRole('admin');
        LokasiRealtime::factory()->create([
            'user_id' => $adminUser->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ]);

        // Create sales user with location
        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');

        // Should only include sales user
        $this->assertCount(1, $salesData);
        $this->assertEquals($this->salesUser->id, $salesData[0]['id']);
    }

    /**
     * Test dashboard statistics endpoint
     */
    public function test_dashboard_statistics_endpoint()
    {
        // Create attendance for sales user
        $this->salesUser->absensi()->create([
            'tanggal' => today(),
            'waktu_masuk' => now()->subHours(8),
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'activeSales',
                'totalVisits',
                'completedVisits',
                'notMoving',
            ]);

        $this->assertEquals(1, $response->json('activeSales'));
    }

    /**
     * Test no movement calculation
     */
    public function test_no_movement_calculation()
    {
        // Create two locations with same coordinates (within 10m)
        $baseLocation = [
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ];

        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'latitude' => $baseLocation['latitude'],
            'longitude' => $baseLocation['longitude'],
            'recorded_at' => now()->subMinutes(65), // 65 minutes ago
        ]);

        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'latitude' => $baseLocation['latitude'],
            'longitude' => $baseLocation['longitude'],
            'recorded_at' => now()->subMinutes(5), // 5 minutes ago
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');
        $noMovementMinutes = $salesData[0]['noMovementMinutes'];

        // Should detect 60 minutes of no movement (65 - 5 = 60)
        $this->assertEquals(60, $noMovementMinutes);
    }

    /**
     * Test status determination for active journey
     */
    public function test_status_determination_for_active_journey()
    {
        // Create active journey for sales user
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $this->salesUser->id,
            'tanggal' => today(),
            'status' => 'aktif',
        ]);

        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');
        $status = $salesData[0]['status'];

        $this->assertEquals('active', $status);
    }

    /**
     * Test status determination for idle (no movement > 60 min)
     */
    public function test_status_determination_for_idle()
    {
        // Create two locations with same coordinates > 60 minutes apart
        $baseLocation = [
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ];

        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'latitude' => $baseLocation['latitude'],
            'longitude' => $baseLocation['longitude'],
            'recorded_at' => now()->subMinutes(70),
        ]);

        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'latitude' => $baseLocation['latitude'],
            'longitude' => $baseLocation['longitude'],
            'recorded_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');
        $status = $salesData[0]['status'];

        $this->assertEquals('idle', $status);
    }

    /**
     * Test status determination for completed visits
     */
    public function test_status_determination_for_completed()
    {
        // Create completed visits
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $this->salesUser->id,
            'tanggal' => today(),
            'status' => 'selesai',
        ]);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'status' => 'completed',
        ]);

        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');
        $status = $salesData[0]['status'];

        $this->assertEquals('completed', $status);
    }

    /**
     * Test visit count calculation
     */
    public function test_visit_count_calculation()
    {
        // Create schedule with visits
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $this->salesUser->id,
            'tanggal' => today(),
        ]);

        JadwalKlien::factory()->count(3)->create([
            'jadwal_kunjungan_id' => $jadwal->id,
        ]);

        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');
        $visitCount = $salesData[0]['visitCount'];

        $this->assertEquals(3, $visitCount);
    }

    /**
     * Test completed visit count calculation
     */
    public function test_completed_visit_count_calculation()
    {
        // Create schedule with mixed visit statuses
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $this->salesUser->id,
            'tanggal' => today(),
        ]);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'status' => 'completed',
        ]);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'status' => 'pending',
        ]);

        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');
        $completedCount = $salesData[0]['completedCount'];

        $this->assertEquals(1, $completedCount);
    }

    /**
     * Test not moving count in statistics
     */
    public function test_not_moving_count_in_statistics()
    {
        // Create sales user with no movement
        $baseLocation = [
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ];

        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'latitude' => $baseLocation['latitude'],
            'longitude' => $baseLocation['longitude'],
            'recorded_at' => now()->subMinutes(70),
        ]);

        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'latitude' => $baseLocation['latitude'],
            'longitude' => $baseLocation['longitude'],
            'recorded_at' => now()->subMinutes(5),
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $notMovingCount = $response->json('notMoving');

        $this->assertEquals(1, $notMovingCount);
    }

    private function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
