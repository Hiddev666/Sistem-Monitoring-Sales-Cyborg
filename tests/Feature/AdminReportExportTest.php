<?php

namespace Tests\Feature;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_sales_performance_report(): void
    {
        $admin = $this->createUserWithRole('admin');
        $sales = $this->createUserWithRole('sales');
        $wilayah = Wilayah::factory()->create();
        $sales->update(['wilayah_id' => $wilayah->id]);
        $this->createCompletedVisit($sales, $wilayah);

        $response = $this->actingAs($admin)->get(route('admin.reports.export-sales-performance', [
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
            'wilayah_id' => $wilayah->id,
        ]));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_export_sales_performance_report_as_pdf(): void
    {
        $admin = $this->createUserWithRole('admin');
        $sales = $this->createUserWithRole('sales');
        $wilayah = Wilayah::factory()->create();
        $sales->update(['wilayah_id' => $wilayah->id]);
        $this->createCompletedVisit($sales, $wilayah);

        $response = $this->actingAs($admin)->get(route('admin.reports.export-sales-performance', [
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
            'wilayah_id' => $wilayah->id,
            'format' => 'pdf',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('.pdf', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_export_regional_performance_report(): void
    {
        $admin = $this->createUserWithRole('admin');
        $sales = $this->createUserWithRole('sales');
        $wilayah = Wilayah::factory()->create();
        $sales->update(['wilayah_id' => $wilayah->id]);
        $this->createCompletedVisit($sales, $wilayah);

        $response = $this->actingAs($admin)->get(route('admin.reports.export-regional-performance', [
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_admin_can_export_klien_analysis_report(): void
    {
        $admin = $this->createUserWithRole('admin');
        $sales = $this->createUserWithRole('sales');
        $wilayah = Wilayah::factory()->create();
        $this->createCompletedVisit($sales, $wilayah);

        $response = $this->actingAs($admin)->get(route('admin.reports.export-klien-analysis', [
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    public function test_sales_cannot_export_admin_reports(): void
    {
        $sales = $this->createUserWithRole('sales');

        $response = $this->actingAs($sales)->get(route('admin.reports.export-sales-performance'));

        $response->assertForbidden();
    }

    public function test_manager_can_export_reports(): void
    {
        $manager = $this->createUserWithRole('manager');
        $sales = $this->createUserWithRole('sales');
        $wilayah = Wilayah::factory()->create();
        $this->createCompletedVisit($sales, $wilayah);

        $response = $this->actingAs($manager)->get(route('admin.reports.export-sales-performance', [
            'start_date' => today()->toDateString(),
            'end_date' => today()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
    }

    private function createCompletedVisit(User $sales, Wilayah $wilayah): JadwalKlien
    {
        $klien = Klien::factory()->create([
            'wilayah_id' => $wilayah->id,
        ]);

        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
        ]);

        return JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $klien->id,
            'status' => 'completed',
            'hasil_tipe' => 'pembelian',
            'nominal_transaksi' => 150000,
            'durasi_kunjungan' => 25,
        ]);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
