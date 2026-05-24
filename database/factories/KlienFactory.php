<?php

namespace Database\Factories;

use App\Models\Klien;
use App\Models\Wilayah;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Klien>
 */
class KlienFactory extends Factory
{
    protected $model = Klien::class;

    public function definition(): array
    {
        return [
            'nama_klien' => fake()->company(),
            'kategori' => fake()->randomElement(['apotek', 'toko_obat', 'rs_klinik', 'lainnya']),
            'alamat' => fake()->address(),
            'wilayah_id' => Wilayah::factory(),
            'latitude' => fake()->latitude(-2.99, -2.96),
            'longitude' => fake()->longitude(104.74, 104.77),
            'contact_person' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
        ];
    }
}
