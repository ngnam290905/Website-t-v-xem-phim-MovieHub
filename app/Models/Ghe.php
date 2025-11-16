<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ghe extends Model
{
    protected $table = 'ghe';
    
    protected $fillable = [
        'id_phong',
        'so_ghe',
        'so_hang',
        'id_loai',
        'trang_thai'
    ];

    public function phongChieu()
    {
        return $this->belongsTo(PhongChieu::class, 'id_phong');
    }

    public function loaiGhe()
    {
        return $this->belongsTo(LoaiGhe::class, 'id_loai');
    }

    public function chiTietDatVe()
    {
        return $this->hasMany(ChiTietDatVe::class, 'id_ghe');
    }
}
