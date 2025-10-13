<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DatVe extends Model
{
    protected $table = 'dat_ve';
    
    protected $fillable = [
        'id_nguoi_dung',
        'id_suat_chieu',
        'id_khuyen_mai',
        'ten_khach_hang',
        'so_dien_thoai',
        'email',
        'tong_tien',
        'trang_thai'
    ];

    protected $casts = [
        'tong_tien' => 'decimal:2',
        'trang_thai' => 'boolean',
    ];

    // Relationship with User
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_nguoi_dung');
    }

    // Relationship with SuatChieu
    public function suatChieu(): BelongsTo
    {
        return $this->belongsTo(SuatChieu::class, 'id_suat_chieu');
    }

    // Relationship with ChiTietDatVe
    public function chiTietDatVe(): HasMany
    {
        return $this->hasMany(ChiTietDatVe::class, 'id_dat_ve');
    }
}
