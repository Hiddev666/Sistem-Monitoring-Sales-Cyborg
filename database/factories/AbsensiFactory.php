<?php

namespace Database\Factories;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Absensi>
 */
class AbsensiFactory extends Factory
{
    protected $model = Absensi::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tanggal' => today(),
            'waktu_masuk' => now()->subHours(8)->format('H:i:s'),
            'lat_masuk' => fake()->latitude(-2.99, -2.96),
            'lng_masuk' => fake()->longitude(104.74, 104.77),
            'accuracy_masuk' => fake()->randomFloat(2, 1, 20),
            'waktu_keluar' => null,
            'lat_keluar' => null,
            'lng_keluar' => null,
            'accuracy_keluar' => null,
            'total_jam' => null,
            'status' => 'pending',
        ];
    }
}
