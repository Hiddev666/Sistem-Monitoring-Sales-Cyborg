<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'photo',
        'wilayah_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relationship: User belongs to Wilayah
     */
    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    /**
     * Scope: Only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('deleted_at', null);
    }

    /**
     * Check if user has a specific role
     */
    public function isSales()
    {
        return $this->hasRole('sales');
    }

    public function isManager()
    {
        return $this->hasRole('manager');
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    /**
     * Get user's role display name
     */
    public function getRoleLabel()
    {
        $labels = [
            'admin' => 'Administrator',
            'manager' => 'Manajer',
            'sales' => 'Sales',
        ];

        $role = $this->roles->first();
        return $labels[$role?->name] ?? 'Pengguna';
    }

    /**
     * Relationship: User has many attendance records
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'user_id');
    }

    /**
     * Relationship: User has many schedules
     */
    public function jadwalKunjungan()
    {
        return $this->hasMany(JadwalKunjungan::class, 'user_id');
    }

    /**
     * Relationship: User has created many schedules (as admin)
     */
    public function jadwalKunjunganCreated()
    {
        return $this->hasMany(JadwalKunjungan::class, 'created_by');
    }

    /**
     * Relationship: User has many realtime location history records.
     */
    public function lokasiRealtime()
    {
        return $this->hasMany(LokasiRealtime::class, 'user_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            $user->lokasiRealtime()->delete();
        });
    }
}
