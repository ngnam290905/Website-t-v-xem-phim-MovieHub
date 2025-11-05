<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThanhToan extends Model
{
    use HasFactory;

    protected $table = 'thanh_toan';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'id_dat_ve',
        'so_tien',
        'phuong_thuc',
        'trang_thai',
        'ngay_thanh_toan',
    ];

    public function datVe()
    {
        return $this->belongsTo(DatVe::class, 'id_dat_ve');
    }
}
