<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesAttendanceCheckoutRuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-06-11 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_checkout_is_blocked_when_active_schedule_has_unfinished_visits(): void
    {
        $sales = $this->createUserWithRole('sales');
        $this->createCheckedInAttendance($sales);
        $this->createActiveSchedule($sales, [
            JadwalKlien::STATUS_COMPLETED,
            JadwalKlien::STATUS_PENDING,
        ]);

        $this->actingAs($sales)
            ->postJson(route('sales.attendance.checkout'), $this->gpsPayload())
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Selesaikan semua kunjungan sebelum absensi pulang.',
            ]);

        $this->assertDatabaseMissing('absensi', [
            'user_id' => $sales->id,
            'waktu_keluar' => now()->format('H:i:s'),
        ]);
    }

    public function test_checkout_is_allowed_when_all_active_schedule_visits_are_completed_or_skipped(): void
    {
        $sales = $this->createUserWithRole('sales');
        $this->createCheckedInAttendance($sales);
        $this->createActiveSchedule($sales, [
            JadwalKlien::STATUS_COMPLETED,
            JadwalKlien::STATUS_SKIPPED,
        ]);

        $this->actingAs($sales)
            ->postJson(route('sales.attendance.checkout'), $this->gpsPayload())
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('absensi', [
            'user_id' => $sales->id,
        ]);
    }

    public function test_checkout_is_allowed_when_no_active_schedule_exists(): void
    {
        $sales = $this->createUserWithRole('sales');
        $this->createCheckedInAttendance($sales);
        $this->createPendingSchedule($sales);

        $this->actingAs($sales)
            ->postJson(route('sales.attendance.checkout'), $this->gpsPayload())
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    }

    private function createCheckedInAttendance(User $sales): Absensi
    {
        return Absensi::factory()->create([
            'user_id' => $sales->id,
            'tanggal' => today(),
            'waktu_masuk' => now()->subHours(8)->format('H:i:s'),
            'waktu_keluar' => null,
            'status' => 'pending',
        ]);
    }

    private function createActiveSchedule(User $sales, array $statuses): JadwalKunjungan
    {
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
            'status' => JadwalKunjungan::STATUS_ACTIVE,
        ]);

        foreach ($statuses as $index => $status) {
            $klien = Klien::factory()->create();

            JadwalKlien::factory()->create([
                'jadwal_kunjungan_id' => $jadwal->id,
                'klien_id' => $klien->id,
                'urutan' => $index + 1,
                'status' => $status,
            ]);
        }

        return $jadwal;
    }

    private function createPendingSchedule(User $sales): JadwalKunjungan
    {
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
            'status' => JadwalKunjungan::STATUS_PENDING,
        ]);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => Klien::factory()->create()->id,
            'urutan' => 1,
            'status' => JadwalKlien::STATUS_PENDING,
        ]);

        return $jadwal;
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function gpsPayload(): array
    {
        return [
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
            'accuracy' => 5,
        ];
    }
}
