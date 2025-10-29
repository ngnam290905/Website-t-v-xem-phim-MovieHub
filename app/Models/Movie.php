<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $table = 'phim';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'ten_phim',
        'ten_goc',
        'do_dai',
        'poster',
        'mo_ta',
        'dao_dien',
        'dien_vien',
        'the_loai',
        'quoc_gia',
        'ngon_ngu',
        'do_tuoi',
        'ngay_khoi_chieu',
        'ngay_ket_thuc',
        'trailer',
        'trang_thai',
        'diem_danh_gia',
        'so_luot_danh_gia',
    ];
}
