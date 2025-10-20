<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ghe extends Model
{
    protected $table = 'ghe';
    
    protected $fillable = [
        'id_phong',
        'so_ghe',
        'so_hang',
        'so_cot',
        'id_loai',
        'trang_thai'
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
    ];

    // Relationship with PhongChieu
    public function phongChieu(): BelongsTo
    {
        return $this->belongsTo(PhongChieu::class, 'id_phong');
    }

    // Relationship with LoaiGhe
    public function loaiGhe(): BelongsTo
    {
        return $this->belongsTo(LoaiGhe::class, 'id_loai');
    }

    // Relationship with ChiTietDatVe
    public function chiTietDatVe(): HasMany
    {
        return $this->hasMany(ChiTietDatVe::class, 'id_ghe');
    }

    // Accessor for seat name
    public function getTenGheAttribute()
    {
        return $this->so_ghe ?? ($this->so_hang . chr(64 + $this->so_cot)); // A, B, C, etc.
    }
}
