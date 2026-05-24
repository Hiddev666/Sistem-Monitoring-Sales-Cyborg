<?php

namespace Database\Factories;

use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wilayah>
 */
class WilayahFactory extends Factory
{
    protected $model = Wilayah::class;

    public function definition(): array
    {
        return [
            'nama_wilayah' => 'Wilayah ' . fake()->unique()->city(),
            'keterangan' => fake()->optional()->sentence(),
        ];
    }
}
