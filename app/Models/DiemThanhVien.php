<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiemThanhVien extends Model
{
    use HasFactory;

    protected $table = 'diem_thanh_vien';
    protected $fillable = [
        'id_nguoi_dung',
        'tong_diem',
        'ngay_het_han',
    ];
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
}
