<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KhuyenMai extends Model
{
    protected $table = 'khuyen_mai';
    
    protected $fillable = [
        'ma_km',
        'mo_ta',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'gia_tri_giam',
        'dieu_kien',
        'trang_thai'
    ];

    protected $casts = [
        'ngay_bat_dau' => 'date',
        'ngay_ket_thuc' => 'date',
        'gia_tri_giam' => 'decimal:2',
    ];

    public function datVe()
    {
        return $this->hasMany(DatVe::class, 'id_khuyen_mai');
    }
}
