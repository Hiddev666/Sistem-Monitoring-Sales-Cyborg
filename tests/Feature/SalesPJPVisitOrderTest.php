<?php

namespace Tests\Feature;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesPJPVisitOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_cannot_check_in_second_client_while_first_is_pending(): void
    {
        $sales = $this->createUserWithRole('sales');
        $jadwal = $this->createActiveScheduleWithClients($sales);

        $secondClient = $jadwal->jadwalKlien()->where('urutan', 2)->firstOrFail();

        $this->actingAs($sales)
            ->postJson(route('sales.pjp.checkin-klien', $secondClient), $this->gpsPayload())
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonFragment([
                'Ikuti urutan PJP. Klien yang bisa di-check-in saat ini adalah klien urutan 1.',
            ]);

        $this->assertDatabaseHas('jadwal_klien', [
            'id' => $secondClient->id,
            'status' => JadwalKlien::STATUS_PENDING,
        ]);
    }

    public function test_sales_can_check_in_second_client_after_first_is_completed(): void
    {
        $sales = $this->createUserWithRole('sales');
        $jadwal = $this->createActiveScheduleWithClients($sales);

        $firstClient = $jadwal->jadwalKlien()->where('urutan', 1)->firstOrFail();
        $firstClient->update([
            'status' => JadwalKlien::STATUS_COMPLETED,
        ]);

        $secondClient = $jadwal->jadwalKlien()->where('urutan', 2)->firstOrFail();

        $this->actingAs($sales)
            ->postJson(route('sales.pjp.checkin-klien', $secondClient), $this->gpsPayload())
            ->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('jadwal_klien', [
            'id' => $secondClient->id,
            'status' => JadwalKlien::STATUS_ACTIVE,
        ]);
    }

    public function test_sales_cannot_checkout_second_client_while_first_is_active(): void
    {
        $sales = $this->createUserWithRole('sales');
        $jadwal = $this->createActiveScheduleWithClients($sales);

        $firstClient = $jadwal->jadwalKlien()->where('urutan', 1)->firstOrFail();
        $secondClient = $jadwal->jadwalKlien()->where('urutan', 2)->firstOrFail();
        $firstClient->update([
            'status' => JadwalKlien::STATUS_ACTIVE,
        ]);
        $secondClient->update([
            'status' => JadwalKlien::STATUS_CHECKING_OUT,
        ]);

        $this->actingAs($sales)
            ->postJson(route('sales.pjp.checkout-klien', $secondClient))
            ->assertStatus(400)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonFragment([
                'Ikuti urutan PJP. Klien yang bisa di-check-out saat ini adalah klien urutan 1',
            ]);

        $this->assertDatabaseHas('jadwal_klien', [
            'id' => $secondClient->id,
            'status' => JadwalKlien::STATUS_CHECKING_OUT,
        ]);
    }

    public function test_next_klien_prioritizes_active_visit_over_pending(): void
    {
        $sales = $this->createUserWithRole('sales');
        $jadwal = $this->createActiveScheduleWithClients($sales);

        $firstClient = $jadwal->jadwalKlien()->where('urutan', 1)->firstOrFail();
        $secondClient = $jadwal->jadwalKlien()->where('urutan', 2)->firstOrFail();

        $firstClient->update(['status' => JadwalKlien::STATUS_COMPLETED]);
        $secondClient->update(['status' => JadwalKlien::STATUS_ACTIVE]);

        $response = $this->actingAs($sales)
            ->getJson(route('sales.pjp.next-klien', $jadwal));

        $response->assertOk()
            ->assertJson([
                'id' => $secondClient->id,
                'urutan' => 2,
            ]);
    }

    private function createActiveScheduleWithClients(User $sales): JadwalKunjungan
    {
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
            'status' => JadwalKunjungan::STATUS_ACTIVE,
        ]);

        $firstKlien = Klien::factory()->create([
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ]);

        $secondKlien = Klien::factory()->create([
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ]);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $firstKlien->id,
            'urutan' => 1,
            'status' => JadwalKlien::STATUS_PENDING,
        ]);

        JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $secondKlien->id,
            'urutan' => 2,
            'status' => JadwalKlien::STATUS_PENDING,
        ]);

        return $jadwal;
    }

    private function gpsPayload(): array
    {
        return [
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
            'accuracy' => 5,
        ];
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
