<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatVe extends Model
{
    use HasFactory;

    protected $table = 'dat_ve';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_nguoi_dung',
        'ngay_dat',
        'tong_tien',
        'trang_thai',
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function thanhToan()
    {
        return $this->hasMany(ThanhToan::class, 'id_dat_ve');
    }
}
