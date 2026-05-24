<?php

namespace Database\Factories;

use App\Models\LokasiRealtime;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LokasiRealtime>
 */
class LokasiRealtimeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LokasiRealtime::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Palembang coordinates as default
        return [
            'user_id' => User::factory(),
            'latitude' => $this->faker->latitude(-2.99, -2.96),
            'longitude' => $this->faker->longitude(104.74, 104.77),
            'akurasi_meter' => $this->faker->randomFloat(2, 1, 20),
            'recorded_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ];
    }
}
