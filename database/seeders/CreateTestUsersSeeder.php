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
            ['name' => 'Sirjudin', 'email' => 'sirjudin@gmail.com', 'phone' => '081234567894'],
            ['name' => 'Subrianto', 'email' => 'subrianto@sistem.test', 'phone' => '081234567895'],
            ['name' => 'Mustajab', 'email' => 'mustajab@sistem.test', 'phone' => '081234567896'],
            ['name' => 'Nuzul', 'email' => 'nuzul@sistem.test', 'phone' => '081234567897'],
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
                ['Admin', 'admin@sistem.test', 'admin'],
                ['Manager', 'manager@sistem.test', 'manager'],
                ['Sales', 'sales@sistem.test', 'sales'],
                ['Sirjudin', 'sirjudin@gmail.command', 'sales'],
                ['Subrianto', 'subrianto@sistem.test', 'sales'],
                ['Mustajab', 'mustajab@sistem.test', 'sales'],
                ['Nuzul', 'nuzul@sistem.test', 'sales'],
            ]
        );
    }
}
