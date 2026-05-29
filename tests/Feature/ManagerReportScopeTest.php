<?php

namespace Tests\Feature;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use App\Models\Wilayah;
use Tests\TestCase;

class ManagerReportScopeTest extends TestCase
{
    public function test_manager_only_sees_sales_performance_for_their_wilayah(): void
    {
        [$manager, $ownSales, $otherSales] = $this->createScopedUsers();

        $response = $this->actingAs($manager)->get(route('admin.analytics.sales-performance'));

        $response->assertOk();
        $response->assertSee($ownSales->name);
        $response->assertDontSee($otherSales->name);
    }

    public function test_manager_cannot_export_another_wilayah(): void
    {
        [$manager,, $otherSales] = $this->createScopedUsers();

        $response = $this->actingAs($manager)->get(route('admin.reports.export-sales-performance', [
            'wilayah_id' => $otherSales->wilayah_id,
        ]));

        $response->assertForbidden();
    }

    public function test_admin_and_manager_can_export_pdf_reports(): void
    {
        [$manager] = $this->createScopedUsers();
        $admin = $this->createUserWithRole('admin');

        $adminResponse = $this->actingAs($admin)->get(route('admin.reports.export-sales-performance', [
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
            'format' => 'pdf',
        ]));

        $adminResponse->assertOk();
        $this->assertStringContainsString('.pdf', $adminResponse->headers->get('content-disposition'));

        $managerResponse = $this->actingAs($manager)->get(route('admin.reports.export-regional-performance', [
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
            'format' => 'pdf',
        ]));

        $managerResponse->assertOk();
        $this->assertStringContainsString('.pdf', $managerResponse->headers->get('content-disposition'));
    }

    public function test_manager_without_wilayah_sees_safe_empty_scope(): void
    {
        $manager = $this->createUserWithRole('manager');
        $sales = $this->createUserWithRole('sales');
        $wilayah = Wilayah::factory()->create();
        $this->createCompletedVisit($sales, $wilayah);

        $response = $this->actingAs($manager)->get(route('admin.analytics.sales-performance'));

        $response->assertOk();
        $response->assertSeeText('Tidak ada data');
        $response->assertDontSee($sales->name);
    }

    private function createScopedUsers(): array
    {
        $ownWilayah = Wilayah::factory()->create(['nama_wilayah' => 'Wilayah Manager']);
        $otherWilayah = Wilayah::factory()->create(['nama_wilayah' => 'Wilayah Lain']);

        $manager = $this->createUserWithRole('manager', ['wilayah_id' => $ownWilayah->id]);
        $ownSales = $this->createUserWithRole('sales', ['wilayah_id' => $ownWilayah->id, 'name' => 'Sales Wilayah Sendiri']);
        $otherSales = $this->createUserWithRole('sales', ['wilayah_id' => $otherWilayah->id, 'name' => 'Sales Wilayah Lain']);

        $this->createCompletedVisit($ownSales, $ownWilayah);
        $this->createCompletedVisit($otherSales, $otherWilayah);

        return [$manager, $ownSales, $otherSales];
    }

    private function createCompletedVisit(User $sales, Wilayah $wilayah): JadwalKlien
    {
        $klien = Klien::factory()->create(['wilayah_id' => $wilayah->id]);
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
        ]);

        return JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $klien->id,
            'status' => JadwalKlien::STATUS_COMPLETED,
            'hasil_tipe' => 'pembelian',
            'nominal_transaksi' => 150000,
        ]);
    }

    private function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
