<?php

namespace Tests\Feature;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use Tests\TestCase;

class SalesPJPNextKlienTest extends TestCase
{
    public function test_next_klien_only_reads_pending_or_active_clients_from_requested_schedule(): void
    {
        $sales = $this->createUserWithRole('sales');
        $otherSales = $this->createUserWithRole('sales');

        $requestedSchedule = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
            'status' => JadwalKunjungan::STATUS_ACTIVE,
        ]);

        $otherSchedule = JadwalKunjungan::factory()->create([
            'user_id' => $otherSales->id,
            'created_by' => $otherSales->id,
            'tanggal' => today(),
            'status' => JadwalKunjungan::STATUS_ACTIVE,
        ]);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $requestedSchedule->id,
            'klien_id' => Klien::factory()->create()->id,
            'urutan' => 1,
            'status' => JadwalKlien::STATUS_COMPLETED,
        ]);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $otherSchedule->id,
            'klien_id' => Klien::factory()->create()->id,
            'urutan' => 1,
            'status' => JadwalKlien::STATUS_PENDING,
        ]);

        $response = $this->actingAs($sales)->getJson(route('sales.pjp.next-klien', $requestedSchedule));

        $response->assertOk()
            ->assertJson([
                'message' => 'Tidak ada klien lagi untuk dikunjungi',
            ]);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
