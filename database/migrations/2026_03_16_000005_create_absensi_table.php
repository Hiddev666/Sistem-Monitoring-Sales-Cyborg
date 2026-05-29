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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            
            // Check-in fields
            $table->time('waktu_masuk')->nullable();
            $table->decimal('lat_masuk', 10, 7)->nullable();
            $table->decimal('lng_masuk', 11, 7)->nullable();
            $table->decimal('accuracy_masuk', 8, 2)->nullable(); // GPS accuracy in meters
            
            // Check-out fields
            $table->time('waktu_keluar')->nullable();
            $table->decimal('lat_keluar', 10, 7)->nullable();
            $table->decimal('lng_keluar', 11, 7)->nullable();
            $table->decimal('accuracy_keluar', 8, 2)->nullable();
            
            // Calculated fields
            $table->integer('total_jam')->nullable(); // Duration in minutes
            $table->string('status')->default('pending'); // pending, completed
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'tanggal']);
            $table->index('tanggal');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
