<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Klien;
use App\Models\JadwalKunjungan;
use App\Models\JadwalKlien;
use Illuminate\Database\Seeder;

class JadwalKunjunganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get sample sales users
        $salesUsers = User::role('sales')->active()->limit(2)->get();
        $allKlien = Klien::active()->get();

        if ($salesUsers->isEmpty() || $allKlien->isEmpty()) {
            echo "INFO: No sales users or klien found. Skipping schedule seeding.\n";
            return;
        }

        $today = now();

        // Create schedules for the next 5 days
        for ($day = 1; $day <= 5; $day++) {
            $tanggal = $today->copy()->addDays($day);

            foreach ($salesUsers as $index => $user) {
                // Create a schedule for each sales user
                $jadwal = JadwalKunjungan::create([
                    'user_id' => $user->id,
                    'tanggal' => $tanggal,
                    'keterangan' => 'Kunjungan rutin hari ' . $tanggal->dayName,
                    'status' => 'pending',
                    'created_by' => 1, // Assume admin with id 1
                ]);

                // Assign 3-5 random klien to this schedule
                $klienCount = rand(3, 5);
                $selectedKlien = $allKlien->random(min($klienCount, $allKlien->count()));

                foreach ($selectedKlien as $position => $klien) {
                    JadwalKlien::create([
                        'jadwal_kunjungan_id' => $jadwal->id,
                        'klien_id' => $klien->id,
                        'urutan' => $position + 1,
                        'status' => 'pending',
                    ]);
                }

                echo "✓ Schedule created for {$user->name} on {$tanggal->format('Y-m-d')} with {$selectedKlien->count()} klien\n";
            }
        }

        echo "\nINFO: PJP/Schedule sample data created successfully!\n";
    }
}
