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

        DB::statement('ALTER TABLE lokasi_realtime ADD CONSTRAINT lokasi_realtime_latitude_check CHECK (latitude BETWEEN -90 AND 90)');
        DB::statement('ALTER TABLE lokasi_realtime ADD CONSTRAINT lokasi_realtime_longitude_check CHECK (longitude BETWEEN -180 AND 180)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE lokasi_realtime DROP CHECK lokasi_realtime_latitude_check');
        DB::statement('ALTER TABLE lokasi_realtime DROP CHECK lokasi_realtime_longitude_check');
    }
};
