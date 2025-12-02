<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KhuyenMai extends Model
{
    use HasFactory;
    protected $table = 'khuyen_mai';
    protected $fillable = [
        'ma_km','loai_giam', 'mo_ta', 'ngay_bat_dau', 'ngay_ket_thuc', 'gia_tri_giam', 'gia_tri_giam_toi_da', 'dieu_kien', 'trang_thai'
    ];
}
