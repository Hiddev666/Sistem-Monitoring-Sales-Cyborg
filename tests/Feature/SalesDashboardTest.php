<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use Tests\TestCase;

class SalesDashboardTest extends TestCase
{
    public function test_sales_login_redirects_to_sales_dashboard(): void
    {
        $sales = $this->createUserWithRole('sales', [
            'email' => 'sales-dashboard@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'sales-dashboard@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($sales);
        $response->assertRedirect(route('sales.dashboard'));
    }

    public function test_sales_dashboard_renders_without_attendance_or_schedule(): void
    {
        $sales = $this->createUserWithRole('sales');

        $response = $this->actingAs($sales)->get(route('sales.dashboard'));

        $response->assertOk();
        $response->assertSee('Belum Check-In');
        $response->assertSee('Belum Ada Jadwal Hari Ini');
        $response->assertSee(route('sales.attendance.index'), false);
        $response->assertSee(route('sales.pjp.today'), false);
        $response->assertDontSee('This will be implemented in Phase 3');
    }

    public function test_sales_dashboard_renders_real_attendance_and_schedule_data(): void
    {
        $sales = $this->createUserWithRole('sales');

        Absensi::factory()->create([
            'user_id' => $sales->id,
            'tanggal' => today(),
            'waktu_masuk' => '08:00:00',
            'status' => 'pending',
        ]);

        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
            'status' => JadwalKunjungan::STATUS_ACTIVE,
        ]);

        $completedKlien = Klien::factory()->create(['nama_klien' => 'Apotek Selesai']);
        $pendingKlien = Klien::factory()->create(['nama_klien' => 'Apotek Pending']);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $completedKlien->id,
            'urutan' => 1,
            'status' => JadwalKlien::STATUS_COMPLETED,
            'hasil_tipe' => 'pembelian',
            'nominal_transaksi' => 100000,
        ]);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $pendingKlien->id,
            'urutan' => 2,
            'status' => JadwalKlien::STATUS_PENDING,
        ]);

        $response = $this->actingAs($sales)->get(route('sales.dashboard'));

        $response->assertOk();
        $response->assertSee('Sedang Bekerja');
        $response->assertSee('1/2 Selesai');
        $response->assertSee('Perjalanan Berlangsung');
        $response->assertSee('Apotek Selesai');
        $response->assertSee('Apotek Pending');
        $response->assertDontSee('Belum Ada Jadwal Hari Ini');
        $response->assertDontSee('0 Kunjungan');
    }

    private function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
