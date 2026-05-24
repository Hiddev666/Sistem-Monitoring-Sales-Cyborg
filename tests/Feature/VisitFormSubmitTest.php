<?php

namespace Tests\Feature;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitFormSubmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_form_preserves_uploaded_signature(): void
    {
        $sales = $this->createUserWithRole('sales');
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
            'status' => 'aktif',
        ]);
        $jadwalKlien = JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'status' => 'active',
            'foto_checkin' => 'visits/checkin.jpg',
            'foto_checkout' => 'visits/checkout.jpg',
            'tanda_tangan' => 'signatures/customer.png',
        ]);

        $response = $this->actingAs($sales)->postJson(
            route('sales.pjp.submit-form', $jadwalKlien),
            $this->validPayload()
        );

        $response->assertOk()
            ->assertJson(['success' => true]);

        $jadwalKlien->refresh();

        $this->assertSame('signatures/customer.png', $jadwalKlien->tanda_tangan);
        $this->assertTrue($jadwalKlien->isFormComplete());
    }

    public function test_submit_form_requires_signature(): void
    {
        $sales = $this->createUserWithRole('sales');
        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
            'status' => 'aktif',
        ]);
        $jadwalKlien = JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'status' => 'active',
            'foto_checkin' => 'visits/checkin.jpg',
            'foto_checkout' => 'visits/checkout.jpg',
            'tanda_tangan' => null,
        ]);

        $response = $this->actingAs($sales)->postJson(
            route('sales.pjp.submit-form', $jadwalKlien),
            $this->validPayload()
        );

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Digital signature is required',
            ]);

        $jadwalKlien->refresh();

        $this->assertNull($jadwalKlien->tanda_tangan);
        $this->assertFalse($jadwalKlien->isFormComplete());
    }

    private function validPayload(): array
    {
        return [
            'catatan_kunjungan' => 'Catatan kunjungan valid',
            'hasil_tipe' => 'pembelian',
            'nominal_transaksi' => 150000,
            'lat_checkout' => -2.9760971,
            'lng_checkout' => 104.7553750,
            'accuracy_checkout' => 8.5,
        ];
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
