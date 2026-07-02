<?php

namespace Tests\Feature;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ReportPdfSignatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_view_renders_manager_signature_block(): void
    {
        $html = view('reports.pdf', [
            'title' => 'Laporan Uji',
            'startDate' => '2026-07-01',
            'endDate' => '2026-07-02',
            'rows' => [
                [
                    'Nama Sales' => 'Sales Uji',
                    'Wilayah' => 'Wilayah Uji',
                ],
            ],
            'managerName' => 'Manager Uji',
        ])->render();

        $this->assertStringContainsString('Mengetahui,', $html);
        $this->assertStringContainsString('Manager', $html);
        $this->assertStringContainsString('Manager Uji', $html);
    }

    public function test_pdf_generation_uses_regional_manager_name(): void
    {
        $service = app(ReportService::class);
        $wilayahTarget = Wilayah::factory()->create(['nama_wilayah' => 'Wilayah Target']);
        $wilayahLain = Wilayah::factory()->create(['nama_wilayah' => 'Wilayah Lain']);

        $targetManager = $this->createUserWithRole('manager', [
            'name' => 'Manager Target',
            'wilayah_id' => $wilayahTarget->id,
        ]);
        $this->createUserWithRole('manager', [
            'name' => 'Manager Lain',
            'wilayah_id' => $wilayahLain->id,
        ]);

        $sales = $this->createUserWithRole('sales', [
            'name' => 'Sales Target',
            'wilayah_id' => $wilayahTarget->id,
        ]);
        $this->createCompletedVisit($sales, $wilayahTarget);

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('reports.pdf', Mockery::on(function (array $data) use ($targetManager) {
                return isset($data['managerName'])
                    && $data['managerName'] === $targetManager->name
                    && isset($data['rows'])
                    && is_array($data['rows'])
                    && isset($data['title']);
            }))
            ->andReturnSelf();

        Pdf::shouldReceive('setPaper')
            ->once()
            ->with('a4', 'landscape')
            ->andReturnSelf();

        Pdf::shouldReceive('save')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturnSelf();

        $path = $service->generateSalesPerformancePdf(
            today()->toDateString(),
            today()->toDateString(),
            $wilayahTarget->id
        );

        $this->assertStringEndsWith('.pdf', $path);
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

    private function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }
}
