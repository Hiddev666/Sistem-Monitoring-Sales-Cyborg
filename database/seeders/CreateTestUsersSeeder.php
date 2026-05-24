<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class CreateTestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First ensure we have a wilayah
        $wilayah = Wilayah::first() ?? Wilayah::create([
            'nama_wilayah' => 'Wilayah Jakarta',
            'keterangan' => 'Wilayah utama untuk operasional Jakarta',
        ]);

        // Create Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super_admin@sistem.test',
            'password' => bcrypt('password'),
            'phone' => '081234567890',
            'wilayah_id' => $wilayah->id,
            'is_active' => true,
        ]);
        $superAdmin->assignRole('super_admin');

        // Create Admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@sistem.test',
            'password' => bcrypt('password'),
            'phone' => '081234567891',
            'wilayah_id' => $wilayah->id,
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Create Manager
        $manager = User::create([
            'name' => 'Manager',
            'email' => 'manager@sistem.test',
            'password' => bcrypt('password'),
            'phone' => '081234567892',
            'wilayah_id' => $wilayah->id,
            'is_active' => true,
        ]);
        $manager->assignRole('manager');

        // Create Sales
        $sales = User::create([
            'name' => 'Sales',
            'email' => 'sales@sistem.test',
            'password' => bcrypt('password'),
            'phone' => '081234567893',
            'wilayah_id' => $wilayah->id,
            'is_active' => true,
        ]);
        $sales->assignRole('sales');

        // Create additional sample sales users
        $salesUsers = [
            ['name' => 'Budi Santoso', 'email' => 'budi@sistem.test', 'phone' => '081234567894'],
            ['name' => 'Siti Rahayu', 'email' => 'siti@sistem.test', 'phone' => '081234567895'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@sistem.test', 'phone' => '081234567896'],
        ];

        foreach ($salesUsers as $index => $salesData) {
            $user = User::create([
                'name' => $salesData['name'],
                'email' => $salesData['email'],
                'password' => bcrypt('password'),
                'phone' => $salesData['phone'],
                'wilayah_id' => $wilayah->id,
                'is_active' => true,
            ]);
            $user->assignRole('sales');
        }

        $this->command->info('Test users created successfully!');
        $this->command->table(
            ['Name', 'Email', 'Role'],
            [
                ['Super Admin', 'super_admin@sistem.test', 'super_admin'],
                ['Admin', 'admin@sistem.test', 'admin'],
                ['Manager', 'manager@sistem.test', 'manager'],
                ['Sales', 'sales@sistem.test', 'sales'],
                ['Budi Santoso', 'budi@sistem.test', 'sales'],
                ['Siti Rahayu', 'siti@sistem.test', 'sales'],
                ['Ahmad Fauzi', 'ahmad@sistem.test', 'sales'],
            ]
        );
    }
}