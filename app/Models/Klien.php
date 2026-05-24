<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Klien extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'klien';

    protected $fillable = [
        'nama_klien',
        'kategori',
        'alamat',
        'wilayah_id',
        'latitude',
        'longitude',
        'contact_person',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
    ];

    /**
     * Get the wilayah that owns the klien
     */
    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    /**
     * Scope: Active only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By wilayah
     */
    public function scopeByWilayah($query, $wilayahId)
    {
        return $query->where('wilayah_id', $wilayahId);
    }

    /**
     * Scope: By kategori
     */
    public function scopeByKategori($query, $kategori)
    {
        return $query->where('kategori', $kategori);
    }

    /**
     * Format GPS coordinates for display
     */
    public function getGpsFormatted()
    {
        return "{$this->latitude}, {$this->longitude}";
    }

    /**
     * Relationship: Many-to-many with schedules
     */
    public function jadwalKunjungan()
    {
        return $this->belongsToMany(JadwalKunjungan::class, 'jadwal_klien', 'klien_id', 'jadwal_kunjungan_id')
            ->withPivot('urutan', 'status', 'waktu_checkin', 'waktu_checkout', 'lat_checkin', 
                        'lng_checkin', 'durasi_kunjungan', 'hasil_kunjungan', 'keterangan', 'accuracy_checkin');
    }

    /**
     * Relationship: Klien has many schedule entries (via pivot)
     */
    public function jadwalKlien()
    {
        return $this->hasMany(JadwalKlien::class, 'klien_id');
    }
}
