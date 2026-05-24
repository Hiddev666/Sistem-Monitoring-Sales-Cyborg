<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    use HasFactory;

    protected $table = 'wilayah';

    protected $fillable = [
        'nama_wilayah',
        'keterangan',
    ];

    /**
     * Relationship: Wilayah has many Users
     */
    public function users()
    {
        return $this->hasMany(User::class, 'wilayah_id');
    }

    /**
     * Relationship: Wilayah has many Klien
     */
    public function klien()
    {
        return $this->hasMany(Klien::class, 'wilayah_id');
    }
}
