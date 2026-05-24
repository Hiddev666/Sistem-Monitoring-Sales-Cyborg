<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalKunjungan extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'aktif';
    public const STATUS_COMPLETED = 'selesai';

    protected $table = 'jadwal_kunjungan';

    protected $fillable = [
        'user_id',
        'tanggal',
        'keterangan',
        'status',
        'created_by',
        'waktu_mulai',
        'waktu_selesai',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_mulai' => 'datetime:H:i:s',
        'waktu_selesai' => 'datetime:H:i:s',
    ];

    /**
     * Relationship with User (assigned salesman)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship with User (who created the schedule)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Many-to-many relationship with Klien
     */
    public function klien()
    {
        return $this->belongsToMany(Klien::class, 'jadwal_klien', 'jadwal_kunjungan_id', 'klien_id')
            ->withPivot('urutan', 'status', 'waktu_checkin', 'waktu_checkout', 'lat_checkin', 
                        'lng_checkin', 'durasi_kunjungan', 'hasil_kunjungan', 'keterangan', 'accuracy_checkin')
            ->orderBy('urutan');
    }

    /**
     * Get the ordered list of klien for this schedule (via pivot)
     */
    public function jadwalKlien()
    {
        return $this->hasMany(JadwalKlien::class, 'jadwal_kunjungan_id')
            ->orderBy('urutan');
    }

    /**
     * Scope: Get today's schedule for a user
     */
    public static function todayFor($userId)
    {
        return self::where('user_id', $userId)
            ->where('tanggal', now()->toDateString())
            ->first();
    }

    /**
     * Scope: Filter by date
     */
    public function scopeByDate($query, $tanggal)
    {
        return $query->where('tanggal', $tanggal);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Get active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Get pending schedules
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Get completed schedules
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Start journey: set status to aktif
     */
    public function mulaiPerjalanan()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->waktu_mulai = now()->format('H:i:s');
        return $this->save();
    }

    /**
     * End journey: set status to selesai
     */
    public function selesaiPerjalanan()
    {
        $this->status = self::STATUS_COMPLETED;
        $this->waktu_selesai = now()->format('H:i:s');
        return $this->save();
    }

    /**
     * Get count of completed klien visits
     */
    public function getCompletedKlienCount()
    {
        return $this->jadwalKlien()
            ->where('status', JadwalKlien::STATUS_COMPLETED)
            ->count();
    }

    /**
     * Get total klien in this schedule
     */
    public function getTotalKlienCount()
    {
        return $this->jadwalKlien()->count();
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage()
    {
        $total = $this->getTotalKlienCount();
        if ($total == 0) return 0;
        return round(($this->getCompletedKlienCount() / $total) * 100, 2);
    }
}
