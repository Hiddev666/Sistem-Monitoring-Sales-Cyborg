<?php

namespace Tests\Unit\Phase5;

use App\Models\LokasiRealtime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase 5: Dashboard & Monitoring - LokasiRealtime Model Tests
 *
 * Test cases for LokasiRealtime model functionality
 */
class LokasiRealtimeTest extends TestCase
{
    use RefreshDatabase;

    protected User $salesUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a sales user for testing
        $this->salesUser = $this->createUserWithRole('sales');
    }

    /**
     * Test creating a new location record
     */
    public function test_can_create_location_record()
    {
        $location = LokasiRealtime::create([
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
            'akurasi_meter' => 8.5,
            'recorded_at' => now(),
        ]);

        $this->assertDatabaseHas('lokasi_realtime', [
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ]);

        $this->assertEquals($this->salesUser->id, $location->user_id);
        $this->assertEquals(-2.9760971, $location->latitude);
        $this->assertEquals(104.7553750, $location->longitude);
    }

    /**
     * Test location belongs to user
     */
    public function test_location_belongs_to_user()
    {
        $location = LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
        ]);

        $this->assertInstanceOf(User::class, $location->user);
        $this->assertEquals($this->salesUser->id, $location->user->id);
    }

    /**
     * Test scopeToday filters locations from today
     */
    public function test_scope_today_filters_locations_from_today()
    {
        // Create locations for different dates
        LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'recorded_at' => now()->subDays(2),
        ]);

        $todayLocation = LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'recorded_at' => now(),
        ]);

        $todayLocations = LokasiRealtime::today()->get();

        $this->assertCount(1, $todayLocations);
        $this->assertEquals($todayLocation->id, $todayLocations->first()->id);
    }

    /**
     * Test scopeLatestPerUser gets latest location for each user
     */
    public function test_scope_latest_per_user_gets_latest_location()
    {
        $otherUser = $this->createUserWithRole('sales');

        // Create multiple locations for same user
        $oldLocation1 = LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'recorded_at' => now()->subHours(2),
        ]);

        $oldLocation2 = LokasiRealtime::factory()->create([
            'user_id' => $otherUser->id,
            'recorded_at' => now()->subHours(1),
        ]);

        $newLocation1 = LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
            'recorded_at' => now(),
        ]);

        $newLocation2 = LokasiRealtime::factory()->create([
            'user_id' => $otherUser->id,
            'recorded_at' => now()->subMinutes(30),
        ]);

        $latestLocations = LokasiRealtime::latestPerUser()->get();

        $this->assertCount(2, $latestLocations);
        $this->assertTrue($latestLocations->contains($newLocation1));
        $this->assertTrue($latestLocations->contains($newLocation2));
        $this->assertFalse($latestLocations->contains($oldLocation1));
        $this->assertFalse($latestLocations->contains($oldLocation2));
    }

    /**
     * Test updateOrCreate creates new location
     */
    public function test_update_or_create_creates_new_location()
    {
        $location = LokasiRealtime::updateOrCreate(
            ['user_id' => $this->salesUser->id],
            [
                'latitude' => -2.9760971,
                'longitude' => 104.7553750,
                'akurasi_meter' => 8.5,
                'recorded_at' => now(),
            ]
        );

        $this->assertDatabaseHas('lokasi_realtime', [
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9760971,
        ]);
    }

    /**
     * Test updateOrCreate updates existing location
     */
    public function test_update_or_create_updates_existing_location()
    {
        // Create initial location
        $location = LokasiRealtime::create([
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
            'akurasi_meter' => 8.5,
            'recorded_at' => now()->subMinutes(5),
        ]);

        $initialId = $location->id;

        // Update location
        $updatedLocation = LokasiRealtime::updateOrCreate(
            ['user_id' => $this->salesUser->id],
            [
                'latitude' => -2.9765000,
                'longitude' => 104.7558000,
                'akurasi_meter' => 5.2,
                'recorded_at' => now(),
            ]
        );

        // Should update existing record, not create new one
        $this->assertEquals($initialId, $updatedLocation->id);
        $this->assertDatabaseHas('lokasi_realtime', [
            'id' => $initialId,
            'latitude' => -2.9765000,
            'longitude' => 104.7558000,
        ]);

        $this->assertDatabaseMissing('lokasi_realtime', [
            'latitude' => -2.9760971,
        ]);
    }

    /**
     * Test latitude validation
     */
    public function test_latitude_must_be_between_minus_90_and_90()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        LokasiRealtime::create([
            'user_id' => $this->salesUser->id,
            'latitude' => 91.0, // Invalid latitude
            'longitude' => 104.7553750,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Test longitude validation
     */
    public function test_longitude_must_be_between_minus_180_and_180()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        LokasiRealtime::create([
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9760971,
            'longitude' => 181.0, // Invalid longitude
            'recorded_at' => now(),
        ]);
    }

    /**
     * Test accuracy can be nullable
     */
    public function test_accuracy_can_be_nullable()
    {
        $location = LokasiRealtime::create([
            'user_id' => $this->salesUser->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
            'akurasi_meter' => null,
            'recorded_at' => now(),
        ]);

        $this->assertNull($location->akurasi_meter);
    }

    /**
     * Test location deletion cascades to user deletion
     */
    public function test_location_deletion_cascades_to_user_deletion()
    {
        $location = LokasiRealtime::factory()->create([
            'user_id' => $this->salesUser->id,
        ]);

        $this->assertDatabaseHas('lokasi_realtime', [
            'id' => $location->id,
        ]);

        $this->salesUser->delete();

        $this->assertDatabaseMissing('lokasi_realtime', [
            'id' => $location->id,
        ]);
    }

    private function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
