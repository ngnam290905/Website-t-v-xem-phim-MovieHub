<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuatChieu extends Model
{
    use HasFactory;
    protected $table = 'suat_chieu';
    public $timestamps = false;
    protected $fillable = ['id_phim', 'id_phong', 'thoi_gian_bat_dau', 'thoi_gian_ket_thuc', 'trang_thai'];

    public function phim()
    {
        return $this->belongsTo(Phim::class, 'id_phim');
    }
    public function phongChieu()
    {
        return $this->belongsTo(PhongChieu::class, 'id_phong');
    }
    public function datVe()
    {
        return $this->hasMany(DatVe::class, 'id_suat_chieu');
    }
    protected $casts = [
        'thoi_gian_bat_dau' => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
    ];
}
