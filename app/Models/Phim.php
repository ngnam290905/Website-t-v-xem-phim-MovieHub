<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Phim extends Model
{
    protected $table = 'phim';
    
    protected $fillable = [
        'ten_phim',
        'do_dai',
        'poster',
        'mo_ta',
        'dao_dien',
        'dien_vien',
        'trailer',
        'trang_thai'
    ];

    public function suatChieu()
    {
        return $this->hasMany(SuatChieu::class, 'id_phim');
    }

    public function datVe()
    {
        return $this->hasManyThrough(DatVe::class, SuatChieu::class, 'id_phim', 'id_suat_chieu');
    }

    public function getTongDoanhThuAttribute()
    {
        return $this->datVe()
            ->where('trang_thai', 1)
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->sum('chi_tiet_dat_ve.gia_ve');
    }

    public function getSoVeBanAttribute()
    {
        return $this->datVe()
            ->where('trang_thai', 1)
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->count();
    }

    /**
     * Calculate total revenue from all showtimes of this movie
     */
    public function calculateDoanhThu()
    {
        return $this->suatChieu()
            ->with('datVe')
            ->get()
            ->sum(function ($suatChieu) {
                return $suatChieu->datVe->sum('tong_tien');
            });
    }
    /**
     * Calculate profit (revenue minus estimated costs)
     * For now, returns revenue as profit calculation requires cost data
     */
    public function calculateLoiNhuan()
    {
        return $this->calculateDoanhThu();
    }
}
