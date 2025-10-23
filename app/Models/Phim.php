<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Phim extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'phim';
    
    protected $primaryKey = 'id';

    protected $fillable = [
        'ten_phim',
        'ten_goc',
        'poster',
        'trailer',
        'dao_dien',
        'dien_vien',
        'the_loai',
        'quoc_gia',
        'ngon_ngu',
        'do_tuoi',
        'do_dai',
        'ngay_khoi_chieu',
        'ngay_ket_thuc',
        'mo_ta',
        'diem_danh_gia',
        'so_luot_danh_gia',
        'trang_thai',
    ];

    protected $casts = [
        'ngay_khoi_chieu' => 'date',
        'ngay_ket_thuc' => 'date',
        'diem_danh_gia' => 'decimal:1',
        'so_luot_danh_gia' => 'integer',
        'do_dai' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the movie's showtimes
     */
    public function suatChieu()
    {
        return $this->hasMany(SuatChieu::class, 'id_phim');
    }

    /**
     * Scope for movies that are currently showing
     */
    public function scopeDangChieu($query)
    {
        return $query->where('trang_thai', 'dang_chieu');
    }

    /**
     * Scope for movies that are coming soon
     */
    public function scopeSapChieu($query)
    {
        return $query->where('trang_thai', 'sap_chieu');
    }

    /**
     * Scope for movies that have stopped showing
     */
    public function scopeNgungChieu($query)
    {
        return $query->where('trang_thai', 'ngung_chieu');
    }

    /**
     * Scope for active movies (currently showing or coming soon)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('trang_thai', ['dang_chieu', 'sap_chieu']);
    }

    /**
     * Get the poster URL
     */
    public function getPosterUrlAttribute()
    {
        if ($this->poster) {
            return asset('storage/' . $this->poster);
        }
        return asset('images/no-poster.svg');
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->do_dai / 60);
        $minutes = $this->do_dai % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        return $minutes . ' phút';
    }

    /**
     * Get formatted rating
     */
    public function getFormattedRatingAttribute()
    {
        if ($this->so_luot_danh_gia > 0) {
            return number_format($this->diem_danh_gia, 1) . '/10';
        }
        return 'Chưa có đánh giá';
    }
}
