<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatVe extends Model
{
    use HasFactory;
    protected $table = 'dat_ve';
    public $timestamps = false;
    protected $fillable = ['id_nguoi_dung', 'id_suat_chieu', 'id_khuyen_mai', 'trang_thai'];
    
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }
    public function suatChieu()
    {
        return $this->belongsTo(SuatChieu::class, 'id_suat_chieu');
    }
    public function chiTietDatVe()
    {
        return $this->hasMany(ChiTietDatVe::class, 'id_dat_ve');
    }
    public function chiTietCombo()
    {
        return $this->hasMany(ChiTietCombo::class, 'id_dat_ve');
    }
    public function thanhToan()
    {
        return $this->hasOne(ThanhToan::class, 'id_dat_ve');
    }
}
