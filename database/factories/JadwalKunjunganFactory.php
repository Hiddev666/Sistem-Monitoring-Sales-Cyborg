<?php

namespace Database\Factories;

use App\Models\JadwalKunjungan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JadwalKunjungan>
 */
class JadwalKunjunganFactory extends Factory
{
    protected $model = JadwalKunjungan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'tanggal' => fake()->dateTimeBetween('-1 week', '+1 week')->format('Y-m-d'),
            'keterangan' => fake()->optional()->sentence(),
            'status' => 'pending',
            'created_by' => User::factory(),
            'waktu_mulai' => null,
            'waktu_selesai' => null,
        ];
    }
}
