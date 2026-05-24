<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * 
     * Order is important:
     * 1. RoleSeeder - Create roles and permissions first
     * 2. CreateTestUsersSeeder - Users need roles to be assigned
     * 3. KlienSeeder - Create client data
     * 4. ConfigurationSeeder - System configuration
     * 5. JadwalKunjunganSeeder - Create schedules (needs users and klien)
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            CreateTestUsersSeeder::class,
            KlienSeeder::class,
            ConfigurationSeeder::class,
            JadwalKunjunganSeeder::class,
        ]);
    }
}