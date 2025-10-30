<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    use HasFactory;
    protected $table = 'combo';
    public $timestamps = true;
    protected $fillable = [
        'ten',
        'mo_ta',
        'gia',
        'gia_goc',
        'anh',
        'combo_noi_bat',
        'so_luong_toi_da',
        'yeu_cau_it_nhat_ve',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'created_by',
        'updated_by',
        'trang_thai',
    ];

    protected $casts = [
        'gia' => 'decimal:2',
        'gia_goc' => 'decimal:2',
        'combo_noi_bat' => 'boolean',
        'trang_thai' => 'boolean',
        'so_luong_toi_da' => 'integer',
        'yeu_cau_it_nhat_ve' => 'integer',
        'ngay_bat_dau' => 'datetime',
        'ngay_ket_thuc' => 'datetime',
    ];
}
