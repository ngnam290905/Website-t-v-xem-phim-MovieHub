<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatVe extends Model
{
    protected $table = 'dat_ve';
    
    protected $fillable = [
        'id_nguoi_dung',
        'id_suat_chieu',
        'id_khuyen_mai',
        'trang_thai'
    ];

    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    public function suatChieu()
    {
        return $this->belongsTo(SuatChieu::class, 'id_suat_chieu');
    }

    public function khuyenMai()
    {
        return $this->belongsTo(KhuyenMai::class, 'id_khuyen_mai');
    }

    public function chiTietDatVe()
    {
        return $this->hasMany(ChiTietDatVe::class, 'id_dat_ve');
    }

    public function getTongTienAttribute()
    {
        return $this->chiTietDatVe()->sum('gia_ve');
    }
}
