<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE absensi MODIFY accuracy_masuk DECIMAL(8, 2) NULL');
        DB::statement('ALTER TABLE absensi MODIFY accuracy_keluar DECIMAL(8, 2) NULL');
        DB::statement('ALTER TABLE jadwal_klien MODIFY accuracy_checkin DECIMAL(8, 2) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE absensi MODIFY accuracy_masuk DECIMAL(5, 2) NULL');
        DB::statement('ALTER TABLE absensi MODIFY accuracy_keluar DECIMAL(5, 2) NULL');
        DB::statement('ALTER TABLE jadwal_klien MODIFY accuracy_checkin DECIMAL(5, 2) NULL');
    }
};
