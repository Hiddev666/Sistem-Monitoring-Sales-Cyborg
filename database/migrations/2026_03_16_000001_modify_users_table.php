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
        Schema::table('users', function (Blueprint $table) {
            // Add new columns to existing users table
            $table->string('phone', 20)->nullable()->after('password');
            $table->string('photo', 255)->nullable()->after('phone');
            $table->foreignId('wilayah_id')->nullable()->constrained('wilayah')->onDelete('set null')->after('photo');
            $table->boolean('is_active')->default(true)->after('wilayah_id');
            $table->softDeletes()->after('updated_at');
            
            // Add indexes
            $table->index('email');
            $table->index('wilayah_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop the columns we added
            $table->dropIndex(['email']);
            $table->dropIndex(['wilayah_id']);
            $table->dropIndex(['is_active']);
            
            $table->dropColumn(['phone', 'photo', 'wilayah_id', 'is_active', 'deleted_at']);
        });
    }
};
