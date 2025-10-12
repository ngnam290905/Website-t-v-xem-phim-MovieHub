<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuatChieu extends Model
{
    protected $table = 'suat_chieu';
    
    protected $fillable = [
        'id_phim',
        'id_phong',
        'thoi_gian_bat_dau',
        'thoi_gian_ket_thuc',
        'trang_thai'
    ];

    protected $casts = [
        'thoi_gian_bat_dau' => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
        'trang_thai' => 'boolean',
    ];

    // Relationship with Movie
    public function phim(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'id_phim');
    }

    // Relationship with PhongChieu
    public function phongChieu(): BelongsTo
    {
        return $this->belongsTo(PhongChieu::class, 'id_phong');
    }

    // Relationship with DatVe
    public function datVe(): HasMany
    {
        return $this->hasMany(DatVe::class, 'id_suat_chieu');
    }
}
