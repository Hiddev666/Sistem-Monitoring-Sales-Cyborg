<?php

namespace Tests\Feature;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Phase10OperationalSmokeTest extends TestCase
{
    public function test_core_sales_admin_and_manager_flow_runs_without_fatal_errors(): void
    {
        Storage::fake('local');

        $admin = $this->createUserWithRole('admin');
        $manager = $this->createUserWithRole('manager');
        $sales = $this->createUserWithRole('sales');
        $jadwalKlien = $this->createTodayVisit($sales);
        $jadwal = $jadwalKlien->jadwalKunjungan;
        $klien = $jadwalKlien->klien;

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($manager)->get(route('manager.dashboard'))->assertOk();
        $this->actingAs($sales)->get(route('sales.dashboard'))->assertOk();

        $this->actingAs($sales)->postJson(route('sales.attendance.checkin'), $this->gpsPayload())
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($sales)->get(route('sales.pjp.today'))->assertOk();
        $this->actingAs($sales)->get(route('sales.pjp.show', $jadwal))->assertOk();

        $this->actingAs($sales)->postJson(route('sales.pjp.start', $jadwal))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($sales)->postJson(route('sales.pjp.checkin-klien', $jadwalKlien), [
            'latitude' => $klien->latitude,
            'longitude' => $klien->longitude,
            'accuracy' => 5,
        ])->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($sales)->postJson(route('sales.pjp.upload-photo', $jadwalKlien), [
            'photo' => UploadedFile::fake()->image('checkin.jpg'),
            'type' => 'checkin',
        ])->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($sales)->postJson(route('sales.pjp.upload-photo', $jadwalKlien), [
            'photo' => UploadedFile::fake()->image('checkout.jpg'),
            'type' => 'checkout',
        ])->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($sales)->postJson(route('sales.pjp.upload-signature', $jadwalKlien), [
            'signature' => $this->signatureDataUrl(),
        ])->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($sales)->postJson(route('sales.pjp.submit-form', $jadwalKlien), [
            'catatan_kunjungan' => 'Kunjungan berjalan baik.',
            'hasil_tipe' => 'pembelian',
            'nominal_transaksi' => 150000,
            'lat_checkout' => $klien->latitude,
            'lng_checkout' => $klien->longitude,
            'accuracy_checkout' => 6,
        ])->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($sales)->postJson(route('sales.pjp.checkout-klien', $jadwalKlien), [
            'hasil_kunjungan' => 'Pesanan diterima.',
            'keterangan' => 'Follow up minggu depan.',
        ])->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($sales)->postJson(route('sales.attendance.checkout'), $this->gpsPayload())
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($manager)->get(route('admin.analytics.dashboard'))->assertOk();
        $this->actingAs($manager)->get(route('admin.analytics.sales-performance'))->assertOk();
        $this->actingAs($manager)->get(route('admin.reports.export-sales-performance'))->assertOk();
    }

    public function test_negative_access_and_gps_cases_are_enforced(): void
    {
        $sales = $this->createUserWithRole('sales');
        $jadwalKlien = $this->createTodayVisit($sales);
        $klien = $jadwalKlien->klien;

        $this->get(route('sales.dashboard'))->assertRedirect(route('login'));

        $this->actingAs($sales)->get(route('admin.analytics.dashboard'))->assertForbidden();

        $this->actingAs($sales)->postJson(route('sales.pjp.checkin-klien', $jadwalKlien), [
            'latitude' => $klien->latitude + 1,
            'longitude' => $klien->longitude + 1,
            'accuracy' => 5,
        ])->assertStatus(400)
            ->assertJson(['success' => false]);
    }

    private function createTodayVisit(User $sales): JadwalKlien
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
            'status' => JadwalKunjungan::STATUS_PENDING,
        ]);

        return JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $klien->id,
            'urutan' => 1,
            'status' => JadwalKlien::STATUS_PENDING,
        ]);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function gpsPayload(): array
    {
        return [
            'latitude' => -2.9760971,
            'longitude' => 104.7553750,
            'accuracy' => 5,
        ];
    }

    private function signatureDataUrl(): string
    {
        return 'data:image/png;base64,' . base64_encode('signature');
    }
}
