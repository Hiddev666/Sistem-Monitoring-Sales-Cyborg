<?php

namespace Tests\Feature;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPhotoGalleryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_download_uses_visit_date_in_filename(): void
    {
        Storage::fake('local');

        $admin = $this->createUserWithRole('admin');
        $sales = $this->createUserWithRole('sales');
        $wilayah = Wilayah::factory()->create();
        $klien = Klien::factory()->create([
            'nama_klien' => 'Toko Sumber Rejeki',
            'wilayah_id' => $wilayah->id,
        ]);

        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $admin->id,
            'tanggal' => '2026-05-20',
        ]);

        $photoPath = 'gallery/checkin.jpg';
        Storage::disk('local')->put($photoPath, 'fake-image');

        $jadwalKlien = JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $klien->id,
            'status' => JadwalKlien::STATUS_COMPLETED,
            'foto_checkin' => $photoPath,
            'created_at' => '2026-05-22 14:30:00',
            'updated_at' => '2026-05-22 14:30:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.photo-gallery.download', [
            'jadwalKlien' => $jadwalKlien->id,
            'type' => 'checkin',
        ]));

        $response->assertOk();

        $contentDisposition = $response->headers->get('content-disposition');
        $expected = Str::slug('Toko Sumber Rejeki') . '_20260520_checkin.jpg';

        $this->assertStringContainsString($expected, $contentDisposition);
        $this->assertStringNotContainsString('20260522', $contentDisposition);
    }

    public function test_admin_zip_export_uses_visit_date_folder_names(): void
    {
        Storage::fake('local');

        $admin = $this->createUserWithRole('admin');
        $sales = $this->createUserWithRole('sales');
        $wilayah = Wilayah::factory()->create();
        $klien = Klien::factory()->create([
            'nama_klien' => 'Toko Sumber Rejeki',
            'wilayah_id' => $wilayah->id,
        ]);

        $jadwal = JadwalKunjungan::factory()->create([
            'user_id' => $sales->id,
            'created_by' => $admin->id,
            'tanggal' => '2026-05-20',
        ]);

        $photoPath = 'gallery/checkout.jpg';
        Storage::disk('local')->put($photoPath, 'fake-image');

        $jadwalKlien = JadwalKlien::factory()->create([
            'jadwal_kunjungan_id' => $jadwal->id,
            'klien_id' => $klien->id,
            'status' => JadwalKlien::STATUS_COMPLETED,
            'foto_checkout' => $photoPath,
            'created_at' => '2026-05-22 14:30:00',
            'updated_at' => '2026-05-22 14:30:00',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.photo-gallery.export-zip'), [
            'start_date' => '2026-05-20',
            'end_date' => '2026-05-20',
            'date_basis' => 'visit_date',
        ]);

        $response->assertOk();

        $zipPath = $response->getFile()->getPathname();
        $zip = new \ZipArchive();

        $this->assertTrue($zip->open($zipPath));
        $this->assertSame(
            Str::slug('Toko Sumber Rejeki') . '_20260520/checkout.jpg',
            $zip->getNameIndex(0)
        );
        $zip->close();
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
