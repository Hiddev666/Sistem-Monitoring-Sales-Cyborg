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
        Schema::table('jadwal_klien', function (Blueprint $table) {
            // Photo fields
            $table->string('foto_checkin')->nullable()->after('lat_checkin')->comment('File path for check-in photo');
            $table->string('foto_checkout')->nullable()->after('accuracy_checkin')->comment('File path for check-out photo');
            
            // Visit form fields
            $table->text('catatan_kunjungan')->nullable()->after('foto_checkout')->comment('Visit notes from salesperson');
            $table->string('tanda_tangan')->nullable()->after('catatan_kunjungan')->comment('File path for digital signature');
            
            // Results tracking
            $table->enum('hasil_tipe', ['pembelian', 'tidak_ada_uang', 'tidak_ada_orang', 'tidak_ada_minat', 'dilanjutkan', 'lainnya'])
                ->nullable()
                ->after('tanda_tangan')
                ->comment('Type of visit result');
            
            $table->decimal('nominal_transaksi', 15, 2)->nullable()->after('hasil_tipe')->comment('Transaction amount if any');
            
            // GPS accuracy at checkout
            $table->decimal('lat_checkout', 10, 7)->nullable()->after('nominal_transaksi')->comment('Latitude at check-out');
            $table->decimal('lng_checkout', 10, 7)->nullable()->after('lat_checkout')->comment('Longitude at check-out');
            $table->decimal('accuracy_checkout', 8, 2)->nullable()->after('lng_checkout')->comment('GPS accuracy at check-out in meters');
            
            // Status tracking
            $table->timestamp('waktu_form_selesai')->nullable()->after('accuracy_checkout')->comment('When visit form was completed');
            $table->timestamp('updated_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_klien', function (Blueprint $table) {
            $table->dropColumn([
                'foto_checkin',
                'foto_checkout',
                'catatan_kunjungan',
                'tanda_tangan',
                'hasil_tipe',
                'nominal_transaksi',
                'lat_checkout',
                'lng_checkout',
                'accuracy_checkout',
                'waktu_form_selesai',
            ]);
        });
    }
};
