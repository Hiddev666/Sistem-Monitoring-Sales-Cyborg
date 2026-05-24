<?php

namespace Database\Seeders;

use App\Models\Klien;
use App\Models\Wilayah;
use Illuminate\Database\Seeder;

class KlienSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get wilayah
        $wilayah = Wilayah::first() ?? Wilayah::create([
            'nama_wilayah' => 'Wilayah Default',
            'keterangan' => 'Wilayah default untuk sample data',
        ]);

        // Sample klien data
        $kliens = [
            [
                'nama_klien' => 'Apotek Sehat Sentosa',
                'kategori' => 'apotek',
                'alamat' => 'Jl. Merdeka No. 123, Jakarta Pusat',
                'wilayah_id' => $wilayah->id,
                'latitude' => -2.9760971,
                'longitude' => 104.7553750,
                'contact_person' => 'Ibu Siti',
                'phone' => '081234567890',
                'is_active' => true,
            ],
            [
                'nama_klien' => 'Toko Obat Makmur',
                'kategori' => 'toko_obat',
                'alamat' => 'Jl. Sudirman No. 456, Jakarta Utara',
                'wilayah_id' => $wilayah->id,
                'latitude' => -3.1956269,
                'longitude' => 104.6803390,
                'contact_person' => 'Bapak Ahmad',
                'phone' => '081234567891',
                'is_active' => true,
            ],
            [
                'nama_klien' => 'Klinik Mitra Sehat',
                'kategori' => 'rs_klinik',
                'alamat' => 'Jl. Ahmad Yani No. 789, Jakarta Selatan',
                'wilayah_id' => $wilayah->id,
                'latitude' => -3.0131040,
                'longitude' => 104.7777750,
                'contact_person' => 'Dr. Hendra',
                'phone' => '081234567892',
                'is_active' => true,
            ],
            [
                'nama_klien' => 'Apotek 24 Jam Prima',
                'kategori' => 'apotek',
                'alamat' => 'Jl. Gatot Subroto No. 100, Jakarta Pusat',
                'wilayah_id' => $wilayah->id,
                'latitude' => -2.8297919,
                'longitude' => 104.7557151,
                'contact_person' => 'Ibu Ratna',
                'phone' => '081234567893',
                'is_active' => true,
            ],
            [
                'nama_klien' => 'Toko Obat Berkah',
                'kategori' => 'toko_obat',
                'alamat' => 'Jl. Hayam Wuruk No. 25, Jakarta Barat',
                'wilayah_id' => $wilayah->id,
                'latitude' => -2.9901961,
                'longitude' => 104.7455940,
                'contact_person' => 'Pak Didi',
                'phone' => '081234567894',
                'is_active' => true,
            ],
        ];

        foreach ($kliens as $klien) {
            Klien::create($klien);
        }

        $this->command->info('Klien sample data created successfully!');
    }
}
