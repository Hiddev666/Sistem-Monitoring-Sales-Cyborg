<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalKlien extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ACTIVE_LEGACY = 'aktif';
    public const STATUS_CHECKING_OUT = 'checking_out';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SKIPPED = 'skipped';

    protected $table = 'jadwal_klien';

    protected $fillable = [
        'jadwal_kunjungan_id',
        'klien_id',
        'urutan',
        'status',
        'waktu_checkin',
        'waktu_checkout',
        'lat_checkin',
        'lng_checkin',
        'accuracy_checkin',
        'durasi_kunjungan',
        'hasil_kunjungan',
        'keterangan',
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
    ];

    protected $casts = [
        'waktu_checkin' => 'datetime:H:i:s',
        'waktu_checkout' => 'datetime:H:i:s',
        'lat_checkin' => 'decimal:7',
        'lng_checkin' => 'decimal:7',
        'accuracy_checkin' => 'decimal:2',
        'lat_checkout' => 'decimal:7',
        'lng_checkout' => 'decimal:7',
        'accuracy_checkout' => 'decimal:2',
        'waktu_form_selesai' => 'datetime',
        'nominal_transaksi' => 'decimal:2',
    ];

    /**
     * Relationship with JadwalKunjungan
     */
    public function jadwalKunjungan()
    {
        return $this->belongsTo(JadwalKunjungan::class, 'jadwal_kunjungan_id');
    }

    /**
     * Relationship with Klien
     */
    public function klien()
    {
        return $this->belongsTo(Klien::class, 'klien_id');
    }

    /**
     * Get previous klien in order
     */
    public function getPrevious()
    {
        return self::where('jadwal_kunjungan_id', $this->jadwal_kunjungan_id)
            ->where('urutan', '<', $this->urutan)
            ->orderBy('urutan', 'desc')
            ->first();
    }

    /**
     * Get next klien in order
     */
    public function getNext()
    {
        return self::where('jadwal_kunjungan_id', $this->jadwal_kunjungan_id)
            ->where('urutan', '>', $this->urutan)
            ->orderBy('urutan')
            ->first();
    }

    /**
     * Check if this is the current klien (last pending or currently active)
     */
    public function isCurrent()
    {
        return $this->isActiveStatus() ||
               ($this->isPendingStatus() && !self::where('jadwal_kunjungan_id', $this->jadwal_kunjungan_id)
                   ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_ACTIVE_LEGACY])->exists());
    }

    public function isPendingStatus(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActiveStatus(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_ACTIVE_LEGACY], true);
    }

    public function isCheckingOutStatus(): bool
    {
        return $this->status === self::STATUS_CHECKING_OUT;
    }

    public function isCompletedStatus(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isSkippedStatus(): bool
    {
        return $this->status === self::STATUS_SKIPPED;
    }

    public function isEditableStatus(): bool
    {
        return in_array($this->status, [
            self::STATUS_ACTIVE,
            self::STATUS_ACTIVE_LEGACY,
            self::STATUS_CHECKING_OUT,
        ], true);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_ACTIVE, self::STATUS_ACTIVE_LEGACY => 'Aktif',
            self::STATUS_CHECKING_OUT => 'Checkout',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_SKIPPED => 'Dilewati',
            default => ucfirst((string) $this->status),
        };
    }

    /**
     * Mark as completed
     */
    public function markCompleted($hasil = null, $keterangan = null)
    {
        $this->status = self::STATUS_COMPLETED;
        $this->waktu_checkout = now()->format('H:i:s');
        if ($hasil) $this->hasil_kunjungan = $hasil;
        if ($keterangan) $this->keterangan = $keterangan;
        
        if ($this->waktu_checkin) {
            $checkin = strtotime($this->waktu_checkin);
            $checkout = strtotime($this->waktu_checkout);
            $this->durasi_kunjungan = ($checkout - $checkin) / 60; // minutes
        }
        
        return $this->save();
    }

    /**
     * Get formatted GPS coordinates
     */
    public function getGpsFormatted()
    {
        if ($this->lat_checkin && $this->lng_checkin) {
            return $this->lat_checkin . ', ' . $this->lng_checkin;
        }
        return null;
    }

    /**
     * Get check-in photo URL
     */
    public function getFotoCheckinUrl()
    {
        if (!$this->foto_checkin) {
            return null;
        }
        return route('visit-photo.preview', [$this->id, 'checkin']);
    }

    /**
     * Get check-out photo URL
     */
    public function getFotoCheckoutUrl()
    {
        if (!$this->foto_checkout) {
            return null;
        }
        return route('visit-photo.preview', [$this->id, 'checkout']);
    }

    /**
     * Get signature URL
     */
    public function getTandaTanganUrl()
    {
        if (!$this->tanda_tangan) {
            return null;
        }
        return route('visit-photo.preview', [$this->id, 'signature']);
    }

    /**
     * Check if visit form is complete
     */
    public function isFormComplete(): bool
    {
        return $this->foto_checkin && $this->foto_checkout && 
               $this->catatan_kunjungan && $this->tanda_tangan && 
               $this->hasil_tipe && $this->waktu_form_selesai;
    }

    /**
     * Mark form as completed
     */
    public function completeForm(array $data): bool
    {
        $this->foto_checkin = $data['foto_checkin'] ?? $this->foto_checkin;
        $this->foto_checkout = $data['foto_checkout'] ?? $this->foto_checkout;
        $this->catatan_kunjungan = $data['catatan_kunjungan'] ?? $this->catatan_kunjungan;
        $this->tanda_tangan = $data['tanda_tangan'] ?? $this->tanda_tangan;
        $this->hasil_tipe = $data['hasil_tipe'] ?? $this->hasil_tipe;
        $this->nominal_transaksi = $data['nominal_transaksi'] ?? $this->nominal_transaksi;
        $this->lat_checkout = $data['lat_checkout'] ?? $this->lat_checkout;
        $this->lng_checkout = $data['lng_checkout'] ?? $this->lng_checkout;
        $this->accuracy_checkout = $data['accuracy_checkout'] ?? $this->accuracy_checkout;
        $this->waktu_checkout = now()->format('H:i:s');
        $this->waktu_form_selesai = now();
        $this->status = self::STATUS_COMPLETED;

        if ($this->waktu_checkin) {
            $checkin = strtotime($this->waktu_checkin);
            $checkout = strtotime($this->waktu_checkout);
            $this->durasi_kunjungan = max(0, ($checkout - $checkin) / 60);
        }

        return $this->save();
    }

    /**
     * Get hasil tipe label in Indonesian
     */
    public function getHasilTipeLabel(): string
    {
        $labels = [
            'pembelian' => 'Pembelian',
            'tidak_ada_uang' => 'Tidak Ada Uang',
            'tidak_ada_orang' => 'Tidak Ada Orang',
            'tidak_ada_minat' => 'Tidak Ada Minat',
            'dilanjutkan' => 'Dilanjutkan',
            'lainnya' => 'Lainnya'
        ];

        return $labels[$this->hasil_tipe] ?? 'Unknown';
    }

    /**
     * Get checkout GPS formatted
     */
    public function getGpsCheckoutFormatted(): ?string
    {
        if ($this->lat_checkout && $this->lng_checkout) {
            return "{$this->lat_checkout}, {$this->lng_checkout}";
        }
        return null;
    }

    /**
     * Scope: Filter by schedule
     */
    public function scopeByJadwal($query, $jadwalId)
    {
        return $query->where('jadwal_kunjungan_id', $jadwalId);
    }

    /**
     * Scope: Get completed visits
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Get pending visits
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Get active visits
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Ordered by urutan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan');
    }
}
