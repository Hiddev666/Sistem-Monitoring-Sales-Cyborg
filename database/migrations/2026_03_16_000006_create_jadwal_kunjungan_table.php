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
        Schema::create('jadwal_kunjungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('keterangan')->nullable(); // e.g., "Klien area timur"
            $table->string('status')->default('pending'); // pending, aktif, selesai
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            
            // Journey tracking
            $table->time('waktu_mulai')->nullable(); // When journey started
            $table->time('waktu_selesai')->nullable(); // When journey completed
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'tanggal']);
            $table->index('tanggal');
            $table->index('status');
            $table->unique(['user_id', 'tanggal']); // One schedule per user per day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kunjungan');
    }
};
