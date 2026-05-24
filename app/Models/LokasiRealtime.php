<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LokasiRealtime extends Model
{
    use HasFactory;

    protected $table = 'lokasi_realtime';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'latitude',
        'longitude',
        'akurasi_meter',
        'recorded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'akurasi_meter' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the user that owns the location.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include locations from today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('recorded_at', today());
    }

    /**
     * Scope a query to get the latest location for each user.
     */
    public function scopeLatestPerUser($query)
    {
        return $query->whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('lokasi_realtime')
                ->groupBy('user_id');
        });
    }
}
