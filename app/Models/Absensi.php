<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Absensi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'absensi';

    protected $fillable = [
        'user_id',
        'tanggal',
        'waktu_masuk',
        'lat_masuk',
        'lng_masuk',
        'accuracy_masuk',
        'waktu_keluar',
        'lat_keluar',
        'lng_keluar',
        'accuracy_keluar',
        'total_jam',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_masuk' => 'datetime:H:i:s',
        'waktu_keluar' => 'datetime:H:i:s',
        'lat_masuk' => 'decimal:7',
        'lng_masuk' => 'decimal:7',
        'lat_keluar' => 'decimal:7',
        'lng_keluar' => 'decimal:7',
        'accuracy_masuk' => 'decimal:2',
        'accuracy_keluar' => 'decimal:2',
    ];

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get today's attendance record for user
     */
    public static function todayFor($userId)
    {
        return self::where('user_id', $userId)
            ->whereDate('tanggal', now()->toDateString())
            ->first();
    }

    /**
     * Calculate duration in minutes (checkout - checkin)
     */
    public function calculateDuration()
    {
        if ($this->waktu_masuk && $this->waktu_keluar) {
            $checkin = strtotime($this->waktu_masuk);
            $checkout = strtotime($this->waktu_keluar);
            $this->total_jam = ($checkout - $checkin) / 60; // minutes
            return $this->save();
        }
        return false;
    }

    /**
     * Get formatted GPS coordinates for check-in
     */
    public function getGpsCheckInFormatted()
    {
        return $this->lat_masuk . ', ' . $this->lng_masuk;
    }

    /**
     * Get formatted GPS coordinates for check-out
     */
    public function getGpsCheckOutFormatted()
    {
        return $this->lat_keluar . ', ' . $this->lng_keluar;
    }

    /**
     * Scope: Get attendance records for a date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal', [$startDate, $endDate]);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get completed attendance
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed')
            ->whereNotNull('waktu_masuk')
            ->whereNotNull('waktu_keluar');
    }

    /**
     * Scope: Get pending attendance (checked in but not checked out)
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->whereNotNull('waktu_masuk')
            ->whereNull('waktu_keluar');
    }
}
