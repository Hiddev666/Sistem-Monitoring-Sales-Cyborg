<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_real_operational_counts(): void
    {
        $admin = $this->createUserWithRole('admin', ['is_active' => true]);
        $sales = $this->createUserWithRole('sales', ['is_active' => true]);
        Wilayah::factory()->create();
        $klien = Klien::factory()->create(['is_active' => true]);

        Absensi::factory()->create([
            'user_id' => $sales->id,
            'tanggal' => today(),
            'waktu_masuk' => now()->format('H:i:s'),
            'waktu_keluar' => null,
            'status' => 'pending',
        ]);

        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $admin->id,
            'tanggal' => today(),
        ]);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $klien->id,
            'status' => JadwalKlien::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSeeText('Pengguna Aktif')
            ->assertSeeText('Sales aktif: 1')
            ->assertSeeText('Klien Aktif')
            ->assertSeeText('PJP Hari Ini')
            ->assertSeeText('Kunjungan: 1')
            ->assertSeeText('Absensi aktif: 1')
            ->assertSee(route('admin.users.index'), false)
            ->assertSee(route('admin.pjp.create'), false)
            ->assertSee(route('admin.reports.export-sales-performance'), false)
            ->assertSee(route('admin.monitoring.index'), false);
    }

    public function test_sales_cannot_access_admin_dashboard(): void
    {
        $sales = $this->createUserWithRole('sales');

        $this->actingAs($sales)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    private function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
