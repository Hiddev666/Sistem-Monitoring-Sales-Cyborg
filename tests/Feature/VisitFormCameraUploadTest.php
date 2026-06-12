<?php

namespace Tests\Feature;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VisitFormCameraUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_camera_photo_upload_is_accepted_for_checkin_and_checkout(): void
    {
        Storage::fake('local');

        $sales = $this->createUserWithRole('sales');
        $jadwalKlien = $this->createEditableVisit($sales);

        $checkinResponse = $this->actingAs($sales)->postJson(route('sales.pjp.upload-photo', $jadwalKlien), [
            'photo' => UploadedFile::fake()->image('camera-checkin.jpg'),
            'type' => 'checkin',
            'capture_source' => 'camera',
        ]);

        $checkinResponse->assertOk()
            ->assertJson(['success' => true]);

        $jadwalKlien->refresh();

        $this->assertNotNull($jadwalKlien->foto_checkin);
        Storage::disk('local')->assertExists($jadwalKlien->foto_checkin);

        $checkoutResponse = $this->actingAs($sales)->postJson(route('sales.pjp.upload-photo', $jadwalKlien), [
            'photo' => UploadedFile::fake()->image('camera-checkout.jpg'),
            'type' => 'checkout',
            'capture_source' => 'camera',
        ]);

        $checkoutResponse->assertOk()
            ->assertJson(['success' => true]);

        $jadwalKlien->refresh();

        $this->assertNotNull($jadwalKlien->foto_checkout);
        Storage::disk('local')->assertExists($jadwalKlien->foto_checkout);
    }

    public function test_photo_upload_without_camera_flag_is_rejected(): void
    {
        Storage::fake('local');

        $sales = $this->createUserWithRole('sales');
        $jadwalKlien = $this->createEditableVisit($sales);

        $this->actingAs($sales)->postJson(route('sales.pjp.upload-photo', $jadwalKlien), [
            'photo' => UploadedFile::fake()->image('gallery-photo.jpg'),
            'type' => 'checkin',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['capture_source']);

        $jadwalKlien->refresh();

        $this->assertNull($jadwalKlien->foto_checkin);
    }

    private function createEditableVisit(User $sales): JadwalKlien
    {
        $wilayah = Wilayah::factory()->create();
        $klien = Klien::factory()->create([
            'wilayah_id' => $wilayah->id,
        ]);

        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $sales->id,
            'tanggal' => today(),
            'status' => JadwalKunjungan::STATUS_ACTIVE,
        ]);

        return JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $klien->id,
            'urutan' => 1,
            'status' => JadwalKlien::STATUS_ACTIVE,
        ]);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
