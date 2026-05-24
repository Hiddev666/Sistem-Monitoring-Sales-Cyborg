<?php

namespace Database\Factories;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JadwalKlien>
 */
class JadwalKlienFactory extends Factory
{
    protected $model = JadwalKlien::class;

    public function definition(): array
    {
        return [
            'jadwal_kunjungan_id' => JadwalKunjungan::factory(),
            'klien_id' => Klien::factory(),
            'urutan' => fake()->numberBetween(1, 20),
            'status' => 'pending',
            'waktu_checkin' => null,
            'waktu_checkout' => null,
            'lat_checkin' => null,
            'lng_checkin' => null,
            'accuracy_checkin' => null,
            'durasi_kunjungan' => null,
            'hasil_kunjungan' => null,
            'keterangan' => null,
        ];
    }
}
