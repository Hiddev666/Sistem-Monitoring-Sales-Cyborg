<?php

namespace Database\Seeders;

use App\Models\Klien;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;
use RuntimeException;

class KlienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $wilayah = Wilayah::firstOrCreate(
            ['nama_wilayah' => 'Wilayah Palembang'],
            ['keterangan' => 'Wilayah Klien Area Palembang']
        );

        $wilayah = Wilayah::firstOrCreate(
            ['nama_wilayah' => 'Wilayah Banyuasin'],
            ['keterangan' => 'Wilayah Klien Area Banyuasin']
        );

        $kliens = $this->getKliensFromSql();

        foreach ($kliens as $klien) {
            Klien::updateOrCreate(
                [
                    'nama_klien' => $klien['nama_klien'],
                    'alamat' => $klien['alamat'],
                ],
                [
                    'kategori' => $klien['kategori'],
                    'wilayah_id' => 2,
                    'latitude' => $klien['latitude'],
                    'longitude' => $klien['longitude'],
                    'contact_person' => $klien['contact_person'],
                    'phone' => $klien['phone'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info(count($kliens) . ' klien data seeded successfully!');
    }

    /**
     * Parse the legacy INSERT statement from klien.sql into Laravel seed data.
     */
    private function getKliensFromSql(): array
    {
        $path = base_path('klien.sql');

        if (!file_exists($path)) {
            throw new RuntimeException('klien.sql not found at project root.');
        }

        $sql = file_get_contents($path);

        preg_match_all('/^\s*\((.*)\)\s*(?:,|;)\s*$/m', $sql, $matches);

        if (empty($matches[1])) {
            throw new RuntimeException('No klien rows found in klien.sql.');
        }

        return array_map(function (string $row): array {
            $columns = str_getcsv($row, ',', "'", '\\');

            if (count($columns) < 10) {
                throw new RuntimeException('Invalid klien.sql row: ' . $row);
            }

            return [
                'nama_klien' => $columns[0],
                'kategori' => $columns[1],
                'alamat' => $columns[2],
                'latitude' => (float) $columns[4],
                'longitude' => (float) $columns[5],
                'contact_person' => $columns[6],
                'phone' => $columns[7],
            ];
        }, $matches[1]);
    }
}
