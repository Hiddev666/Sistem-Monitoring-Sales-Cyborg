<?php

namespace Tests\Feature;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPJPTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_pjp_without_klien(): void
    {
        $admin = $this->createUserWithRole('admin');
        $sales = $this->createUserWithRole('sales');

        $response = $this->actingAs($admin)->post(route('admin.pjp.store'), [
            'user_id' => $sales->id,
            'tanggal' => today()->toDateString(),
            'keterangan' => 'Jadwal tanpa klien',
        ]);

        $response->assertSessionHasErrors('klien');
        $this->assertDatabaseCount('jadwal_kunjungan', 0);
    }

    public function test_admin_can_create_pjp_with_ordered_klien(): void
    {
        $admin = $this->createUserWithRole('admin');
        $sales = $this->createUserWithRole('sales');
        $firstKlien = Klien::factory()->create();
        $secondKlien = Klien::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.pjp.store'), [
            'user_id' => $sales->id,
            'tanggal' => today()->toDateString(),
            'keterangan' => 'Jadwal valid',
            'klien' => [$secondKlien->id, $firstKlien->id],
        ]);

        $response->assertRedirect(route('admin.pjp.index'));

        $jadwal = JadwalKunjungan::first();
        $this->assertNotNull($jadwal);
        $this->assertSame($sales->id, $jadwal->user_id);

        $this->assertDatabaseHas('jadwal_klien', [
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $secondKlien->id,
            'urutan' => 1,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('jadwal_klien', [
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $firstKlien->id,
            'urutan' => 2,
            'status' => 'pending',
        ]);

        $this->assertSame(2, JadwalKlien::where('jadwal_kunjungan_id', $jadwal->id)->count());
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
