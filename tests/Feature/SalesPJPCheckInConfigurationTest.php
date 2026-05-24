<?php

namespace Tests\Feature;

use App\Models\Configuration;
use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesPJPCheckInConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_in_uses_configured_gps_radius_tolerance(): void
    {
        $sales = $this->createUserWithRole('sales');
        $jadwalKlien = $this->createJadwalKlien($sales);

        Configuration::setValue(
            Configuration::GPS_RADIUS_TOLERANCE_KEY,
            50,
            'integer',
            'Toleransi radius GPS untuk validasi check-in (dalam meter)'
        );

        $rejectedResponse = $this->actingAs($sales)->postJson(
            route('sales.pjp.checkin-klien', $jadwalKlien),
            $this->checkInPayload()
        );

        $rejectedResponse->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $jadwalKlien->refresh();
        $this->assertSame('pending', $jadwalKlien->status);

        Configuration::setValue(
            Configuration::GPS_RADIUS_TOLERANCE_KEY,
            200,
            'integer',
            'Toleransi radius GPS untuk validasi check-in (dalam meter)'
        );

        $acceptedResponse = $this->actingAs($sales)->postJson(
            route('sales.pjp.checkin-klien', $jadwalKlien),
            $this->checkInPayload()
        );

        $acceptedResponse->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $jadwalKlien->refresh();
        $this->assertSame('active', $jadwalKlien->status);
        $this->assertSame('-2.9750971', (string) $jadwalKlien->lat_checkin);
        $this->assertSame('104.7553750', (string) $jadwalKlien->lng_checkin);
    }

    public function test_check_in_uses_default_gps_radius_when_configuration_is_missing(): void
    {
        $sales = $this->createUserWithRole('sales');
        $jadwalKlien = $this->createJadwalKlien($sales);

        $response = $this->actingAs($sales)->postJson(
            route('sales.pjp.checkin-klien', $jadwalKlien),
            $this->checkInPayload()
        );

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);

        $jadwalKlien->refresh();
        $this->assertSame('pending', $jadwalKlien->status);
    }

    private function createJadwalKlien(User $sales): JadwalKlien
    {
        $wilayah = Wilayah::factory()->create();
        $klien = Klien::factory()->create([
            'wilayah_id' => $wilayah->id,
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
        ]);
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
            'status' => 'aktif',
        ]);

        return JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $klien->id,
            'status' => 'pending',
        ]);
    }

    private function checkInPayload(): array
    {
        return [
            'latitude' => -2.9750971,
            'longitude' => 104.7553750,
            'accuracy' => 8.5,
        ];
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
