<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ghe extends Model
{
    use HasFactory;
    protected $table = 'ghe';
    public $timestamps = false;
    protected $fillable = ['id_phong', 'so_ghe', "so_hang", 'id_loai', 'trang_thai'];

    public function phongChieu()
    {
        return $this->belongsTo(PhongChieu::class, 'id_phong');
    }

    public function chiTietDatVe()
    {
        return $this->hasMany(ChiTietDatVe::class, 'id_ghe');
    }
    public function loaiGhe()
    {
        return $this->belongsTo(LoaiGhe::class, 'id_loai');
    }
}
