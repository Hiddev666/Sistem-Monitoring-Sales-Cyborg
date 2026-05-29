<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_klien', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_kunjungan_id')->constrained('jadwal_kunjungan')->cascadeOnDelete();
            $table->foreignId('klien_id')->constrained('klien')->cascadeOnDelete();
            
            // Priority/ordering in the schedule
            $table->integer('urutan'); // 1, 2, 3, ... (order to visit)
            
            // Visit status for this specific klien
            $table->string('status')->default('pending'); // pending, active, completed, skipped
            
            // Check-in/check-out times for this visit
            $table->time('waktu_checkin')->nullable();
            $table->time('waktu_checkout')->nullable();
            $table->decimal('lat_checkin', 10, 7)->nullable();
            $table->decimal('lng_checkin', 11, 7)->nullable();
            $table->decimal('accuracy_checkin', 8, 2)->nullable();
            
            // Visit form data
            $table->integer('durasi_kunjungan')->nullable(); // minutes
            $table->text('hasil_kunjungan')->nullable(); // visit notes/results
            $table->text('keterangan')->nullable(); // additional notes
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['jadwal_kunjungan_id', 'urutan']);
            $table->index('klien_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_klien');
    }
};
