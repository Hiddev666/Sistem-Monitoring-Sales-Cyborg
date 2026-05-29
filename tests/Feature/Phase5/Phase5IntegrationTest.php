<?php

namespace Tests\Feature\Phase5;

use App\Models\Absensi;
use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\LokasiRealtime;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 5: Dashboard & Monitoring - Integration Tests
 *
 * Test correlations between Phase 5 and other phases
 */
class Phase5IntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $salesUser;
    protected User $managerUser;
    protected Klien $klien;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->salesUser = $this->createUserWithRole('sales');
        $this->managerUser = $this->createUserWithRole('manager');

        $this->salesUser->absensi()->create([
            'tanggal' => today(),
            'waktu_masuk' => now()->format('H:i:s'),
            'status' => 'pending',
        ]);

        // Create test klien
        $wilayah = Wilayah::factory()->create();
        $this->klien = Klien::factory()->create([
            'wilayah_id' => $wilayah->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ]);
    }

    /**
     * Test Phase 5 integration with Phase 1 (Authentication)
     * Manager dashboard should only be accessible by authenticated manager
     */
    public function test_phase5_integration_with_phase1_authentication()
    {
        // Unauthenticated user cannot access manager dashboard
        $response = $this->get('/manager/dashboard');
        $response->assertRedirect('/login');

        // Sales user cannot access manager dashboard
        $response = $this->actingAs($this->salesUser)
            ->get('/manager/dashboard');
        $response->assertStatus(403);

        // Manager user can access manager dashboard
        $response = $this->actingAs($this->managerUser)
            ->get('/manager/dashboard');
        $response->assertStatus(200);
    }

    /**
     * Test Phase 5 integration with Phase 2 (Master Data)
     * Dashboard should show sales from master data
     */
    public function test_phase5_integration_with_phase2_master_data()
    {
        // Create multiple sales users
        $sales1 = $this->createUserWithRole('sales', ['name' => 'Sales 1']);
        $sales2 = $this->createUserWithRole('sales', ['name' => 'Sales 2']);

        // Create locations for sales users
        LokasiRealtime::factory()->create([
            'user_id' => $sales1->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ]);

        LokasiRealtime::factory()->create([
            'user_id' => $sales2->id,
            'latitude' => -2.9765000,
            'longitude' => 104.7558000,
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');

        // Should show both sales users from master data
        $this->assertCount(2, $salesData);
        $salesNames = collect($salesData)->pluck('name');
        $this->assertTrue($salesNames->contains('Sales 1'));
        $this->assertTrue($salesNames->contains('Sales 2'));
    }

    /**
     * Test Phase 5 integration with Phase 3 (Attendance)
     * Dashboard should count active sales based on attendance
     */
    public function test_phase5_integration_with_phase3_attendance()
    {
        // Create attendance for sales user (checked in but not checked out)
        Absensi::factory()->create([
            'user_id' => $this->salesUser->id,
            'tanggal' => today(),
            'waktu_masuk' => now()->subHours(4),
            'waktu_keluar' => null,
        ]);

        // Create location for sales user
        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/statistics');

        $activeSales = $response->json('activeSales');

        // Should count sales user as active
        $this->assertEquals(1, $activeSales);
    }

    /**
     * Test Phase 5 integration with Phase 3 (PJP)
     * Dashboard should show visit counts from PJP
     */
    public function test_phase5_integration_with_phase3_pjp()
    {
        // Create schedule with multiple klien
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $this->salesUser->id,
            'tanggal' => today(),
        ]);

        // Create 5 klien visits
        JadwalKlien::factory()->count(5)->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $this->klien->id,
        ]);

        // Create location for sales user
        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');

        // Should show 5 total visits from PJP
        $this->assertEquals(5, $salesData[0]['visitCount']);
    }

    /**
     * Test Phase 5 integration with Phase 4 (Kunjungan)
     * Dashboard should show completed visits from kunjungan
     */
    public function test_phase5_integration_with_phase4_kunjungan()
    {
        // Create schedule with visits
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $this->salesUser->id,
            'tanggal' => today(),
        ]);

        // Create 3 completed visits and 2 pending visits
        JadwalKlien::factory()->count(3)->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'status' => 'completed',
        ]);

        JadwalKlien::factory()->count(2)->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'status' => 'pending',
        ]);

        // Create location for sales user
        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');

        // Should show 3 completed visits from kunjungan
        $this->assertEquals(3, $salesData[0]['completedCount']);
        $this->assertEquals(5, $salesData[0]['visitCount']);
    }

    /**
     * Test Phase 5 integration with Phase 4 (GPS Validation)
     * Location tracking should use GPS coordinates from kunjungan
     */
    public function test_phase5_integration_with_phase4_gps_validation()
    {
        // Create kunjungan with GPS coordinates
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $this->salesUser->id,
            'tanggal' => today(),
        ]);

        $jadwalKlien = JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $this->klien->id,
            'lat_checkin' => -2.9760971,
            'lng_checkin' => 104.7553750,
        ]);

        // Sales user sends location near klien
        $response = $this->actingAs($this->salesUser)
            ->postJson('/api/location/update', [
                'latitude' => -2.9761000, // ~10 meters from klien
                'longitude' => 104.7553800,
                'accuracy' => 8.5,
            ]);

        $response->assertStatus(200);

        // Verify location was recorded
        $this->assertDatabaseHas('lokasi_realtime', [
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9761000,
            'longitude' => 104.7553800,
        ]);
    }

    /**
     * Test Phase 5 integration with Phase 6 (Analytics)
     * Dashboard statistics should match analytics data
     */
    public function test_phase5_integration_with_phase6_analytics()
    {
        // Create schedule with completed visits
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $this->salesUser->id,
            'tanggal' => today(),
        ]);

        JadwalKlien::factory()->count(3)->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'status' => 'completed',
        ]);

        // Get dashboard statistics
        $dashboardResponse = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/statistics');

        $dashboardVisits = $dashboardResponse->json('totalVisits');
        $dashboardCompleted = $dashboardResponse->json('completedVisits');

        // Get analytics data
        $analyticsResponse = $this->actingAs($this->managerUser)
            ->get('/admin/analytics/dashboard');

        $analyticsResponse->assertStatus(200);

        // Dashboard and analytics should show consistent data
        $this->assertEquals(3, $dashboardVisits);
        $this->assertEquals(3, $dashboardCompleted);
    }

    /**
     * Test complete flow: Attendance -> PJP -> Kunjungan -> Location Tracking
     */
    public function test_complete_flow_across_phases()
    {
        // Phase 3: Sales checks in
        $attendance = Absensi::factory()->create([
            'user_id' => $this->salesUser->id,
            'tanggal' => today(),
            'waktu_masuk' => now()->subHours(6),
        ]);

        // Phase 3: Sales has PJP
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $this->salesUser->id,
            'tanggal' => today(),
            'status' => 'aktif',
        ]);

        $jadwalKlien = JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $this->klien->id,
        ]);

        // Phase 4: Sales checks in to klien
        $jadwalKlien->update([
            'status' => 'completed',
            'lat_checkin' => -2.9760971,
            'lng_checkin' => 104.7553750,
        ]);

        // Phase 5: Sales sends location
        $this->actingAs($this->salesUser)
            ->postJson('/api/location/update', [
                'latitude' => -2.9761000,
                'longitude' => 104.7553800,
            ]);

        // Verify all data is integrated correctly
        $this->assertDatabaseHas('absensi', [
            'user_id' => $this->salesUser->id,
            'waktu_masuk' => $attendance->getRawOriginal('waktu_masuk'),
        ]);

        $this->assertDatabaseHas('jadwal_kunjungan', [
            'user_id' => $this->salesUser->id,
            'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('jadwal_klien', [
            'id' => $jadwalKlien->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('lokasi_realtime', [
            'user_id' => $this->salesUser->id,
        ]);

        // Verify dashboard shows correct data
        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');

        $this->assertEquals('active', $salesData[0]['status']);
        $this->assertEquals(1, $salesData[0]['visitCount']);
        $this->assertEquals(1, $salesData[0]['completedCount']);
    }

    /**
     * Test location tracking respects sales role from Phase 1
     */
    public function test_location_tracking_respects_sales_role()
    {
        // Create admin user
        $adminUser = $this->createUserWithRole('admin');

        // Admin tries to update location
        $response = $this->actingAs($adminUser)
            ->postJson('/api/location/update', [
                'latitude' => -2.9760971,
                'longitude' => 104.7553750,
            ]);

        // Only checked-in sales users can update real-time location.
        $response->assertStatus(403);

        // But dashboard should only show sales users
        LokasiRealtime::factory()->create([
            'user_id' => $adminUser->id,
        ]);

        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');

        // Should only show sales user, not admin
        $this->assertCount(1, $salesData);
        $this->assertEquals($this->salesUser->id, $salesData[0]['id']);
    }

    /**
     * Test dashboard respects wilayah from Phase 2
     */
    public function test_dashboard_respects_wilayah_from_phase2()
    {
        // Create sales users in different wilayah
        $wilayah1 = Wilayah::factory()->create(['nama_wilayah' => 'Wilayah 1']);
        $wilayah2 = Wilayah::factory()->create(['nama_wilayah' => 'Wilayah 2']);

        $sales1 = $this->createUserWithRole('sales', [
            'name' => 'Sales Wilayah 1',
            'wilayah_id' => $wilayah1->id,
        ]);

        $sales2 = $this->createUserWithRole('sales', [
            'name' => 'Sales Wilayah 2',
            'wilayah_id' => $wilayah2->id,
        ]);

        // Create locations for both sales
        LokasiRealtime::factory()->create([
            'user_id' => $sales1->id,
        ]);

        LokasiRealtime::factory()->create([
            'user_id' => $sales2->id,
        ]);

        $response = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData = $response->json('sales');

        // Should show both sales users regardless of wilayah
        $this->assertCount(2, $salesData);
    }

    /**
     * Test location data persistence across page refreshes
     */
    public function test_location_data_persistence_across_refreshes()
    {
        // Sales sends location
        $this->actingAs($this->salesUser)
            ->postJson('/api/location/update', [
                'latitude' => -2.9760971,
                'longitude' => 104.7553750,
            ]);

        // Manager fetches locations
        $response1 = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData1 = $response1->json('sales');

        // Sales updates location
        $this->actingAs($this->salesUser)
            ->postJson('/api/location/update', [
                'latitude' => -2.9765000,
                'longitude' => 104.7558000,
            ]);

        // Manager fetches locations again
        $response2 = $this->actingAs($this->managerUser)
            ->getJson('/api/dashboard/sales-locations');

        $salesData2 = $response2->json('sales');

        // Location should be updated, not duplicated
        $this->assertCount(1, $salesData2);
        $this->assertEquals(-2.9765000, $salesData2[0]['latitude']);
        $this->assertEquals(104.7558000, $salesData2[0]['longitude']);
    }

    private function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
