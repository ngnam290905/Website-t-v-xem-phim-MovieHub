<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhToan extends Model
{
    use HasFactory;
    protected $table = 'thanh_toan';
    public $timestamps = false;
    protected $fillable = ['id_dat_ve', 'phuong_thuc', 'so_tien', 'ma_giao_dich', 'trang_thai', 'thoi_gian'];

    public function datVe()
    {
        return $this->belongsTo(DatVe::class, 'id_dat_ve');
    }
}
