<?php

namespace Database\Seeders;

use App\Models\Configuration;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configurations = [
            [
                'key' => 'gps_radius_tolerance',
                'value' => '100',
                'type' => 'integer',
                'description' => 'Toleransi radius GPS untuk validasi check-in (dalam meter)',
            ],
            [
                'key' => 'session_timeout_minutes',
                'value' => '120',
                'type' => 'integer',
                'description' => 'Timeout sesi user (dalam menit)',
            ],
            [
                'key' => 'export_format',
                'value' => 'pdf',
                'type' => 'string',
                'description' => 'Format ekspor laporan default (pdf, excel, csv)',
            ],
            [
                'key' => 'app_name',
                'value' => 'Monitoring Sales Force',
                'type' => 'string',
                'description' => 'Nama aplikasi',
            ],
            [
                'key' => 'app_version',
                'value' => '2.0.0',
                'type' => 'string',
                'description' => 'Versi aplikasi',
            ],
        ];

        foreach ($configurations as $config) {
            Configuration::create($config);
        }

        $this->command->info('Configuration default values created successfully!');
    }
}
