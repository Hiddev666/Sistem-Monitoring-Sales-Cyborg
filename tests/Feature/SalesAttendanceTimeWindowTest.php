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

class SalesAttendanceTimeWindowTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_checkin_is_allowed_at_start_of_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 08:00:00'));

        $sales = $this->createUserWithRole('sales');

        $this->actingAs($sales)
            ->postJson(route('sales.attendance.checkin'), $this->gpsPayload())
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('absensi', [
            'user_id' => $sales->id,
            'tanggal' => '2026-06-11 00:00:00',
            'waktu_masuk' => '08:00:00',
        ]);
    }

    public function test_checkin_is_allowed_at_end_of_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 16:30:00'));

        $sales = $this->createUserWithRole('sales');

        $this->actingAs($sales)
            ->postJson(route('sales.attendance.checkin'), $this->gpsPayload())
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('absensi', [
            'user_id' => $sales->id,
            'tanggal' => '2026-06-11 00:00:00',
            'waktu_masuk' => '16:30:00',
        ]);
    }

    public function test_checkin_is_blocked_before_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 07:59:59'));

        $sales = $this->createUserWithRole('sales');

        $this->actingAs($sales)
            ->postJson(route('sales.attendance.checkin'), $this->gpsPayload())
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Absensi hanya dapat dilakukan antara pukul 08:00 sampai 16:30.',
            ]);

        $this->assertDatabaseMissing('absensi', [
            'user_id' => $sales->id,
            'tanggal' => '2026-06-11 00:00:00',
        ]);
    }

    public function test_checkin_is_blocked_after_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 16:30:01'));

        $sales = $this->createUserWithRole('sales');

        $this->actingAs($sales)
            ->postJson(route('sales.attendance.checkin'), $this->gpsPayload())
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Absensi hanya dapat dilakukan antara pukul 08:00 sampai 16:30.',
            ]);

        $this->assertDatabaseMissing('absensi', [
            'user_id' => $sales->id,
            'tanggal' => '2026-06-11 00:00:00',
        ]);
    }

    public function test_attendance_page_shows_closed_window_state_before_opening_hours(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 07:30:00'));

        $sales = $this->createUserWithRole('sales');

        $this->actingAs($sales)
            ->get(route('sales.attendance.index'))
            ->assertOk()
            ->assertSee('Jam operasional absensi', false)
            ->assertSee('08:00 - 16:30', false)
            ->assertSee('Di luar jam absensi', false)
            ->assertSee('Absensi hanya dapat dilakukan antara pukul 08:00 sampai 16:30.', false);
    }

    public function test_checkout_is_allowed_within_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 10:00:00'));

        $sales = $this->createUserWithRole('sales');
        $this->createCheckedInAttendance($sales);
        $this->createCompletedSchedule($sales);

        $this->actingAs($sales)
            ->postJson(route('sales.attendance.checkout'), $this->gpsPayload())
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('absensi', [
            'user_id' => $sales->id,
            'tanggal' => '2026-06-11 00:00:00',
            'waktu_keluar' => '10:00:00',
        ]);
    }

    public function test_checkout_is_blocked_before_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 07:59:59'));

        $sales = $this->createUserWithRole('sales');
        $this->createCheckedInAttendance($sales);

        $this->actingAs($sales)
            ->postJson(route('sales.attendance.checkout'), $this->gpsPayload())
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Absensi hanya dapat dilakukan antara pukul 08:00 sampai 16:30.',
            ]);

        $this->assertDatabaseHas('absensi', [
            'user_id' => $sales->id,
            'tanggal' => '2026-06-11 00:00:00',
            'waktu_keluar' => null,
        ]);
    }

    public function test_checkout_is_blocked_after_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-06-11 16:30:01'));

        $sales = $this->createUserWithRole('sales');
        $this->createCheckedInAttendance($sales);

        $this->actingAs($sales)
            ->postJson(route('sales.attendance.checkout'), $this->gpsPayload())
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Absensi hanya dapat dilakukan antara pukul 08:00 sampai 16:30.',
            ]);

        $this->assertDatabaseHas('absensi', [
            'user_id' => $sales->id,
            'tanggal' => '2026-06-11 00:00:00',
            'waktu_keluar' => null,
        ]);
    }

    private function createCheckedInAttendance(User $sales): Absensi
    {
        return Absensi::factory()->create([
            'user_id' => $sales->id,
            'tanggal' => today(),
            'waktu_masuk' => '09:00:00',
            'waktu_keluar' => null,
            'status' => 'pending',
        ]);
    }

    private function createCompletedSchedule(User $sales): JadwalKunjungan
    {
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
            'status' => JadwalKunjungan::STATUS_ACTIVE,
        ]);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => Klien::factory()->create()->id,
            'urutan' => 1,
            'status' => JadwalKlien::STATUS_COMPLETED,
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
