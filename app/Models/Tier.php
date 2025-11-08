<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tier extends Model
{
    use HasFactory;

    protected $table = 'tier';

    protected $fillable = [
        'ten_hang',
        'mo_ta',
        'uu_dai',
        'diem_toi_thieu',
        'diem_toi_da',
        'giam_gia_ve',
        'giam_gia_combo',
        'ty_le_tich_diem',
        'so_thu_tu',
        'mau_sac',
        'icon',
        'trang_thai',
    ];

    protected $casts = [
        'diem_toi_thieu' => 'integer',
        'diem_toi_da' => 'integer',
        'giam_gia_ve' => 'decimal:2',
        'giam_gia_combo' => 'decimal:2',
        'ty_le_tich_diem' => 'decimal:2',
        'so_thu_tu' => 'integer',
        'trang_thai' => 'boolean',
    ];

    /**
     * Quan hệ: 1 tier có nhiều thành viên
     */
    public function hangThanhVien()
    {
        return $this->hasMany(HangThanhVien::class, 'id_tier');
    }

    /**
     * Scope: Chỉ lấy tier đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', 1);
    }

    /**
     * Scope: Sắp xếp theo thứ tự
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('so_thu_tu', 'asc');
    }

    /**
     * Kiểm tra điểm có thuộc tier này không
     */
    public function isPointInRange($diem)
    {
        if ($this->diem_toi_da === null) {
            // Tier cao nhất, không giới hạn trên
            return $diem >= $this->diem_toi_thieu;
        }
        
        return $diem >= $this->diem_toi_thieu && $diem <= $this->diem_toi_da;
    }

    /**
     * Lấy tier phù hợp với số điểm
     */
    public static function getTierByPoints($diem)
    {
        return self::active()
            ->ordered()
            ->get()
            ->first(function ($tier) use ($diem) {
                return $tier->isPointInRange($diem);
            });
    }

    /**
     * Lấy tên tier có màu sắc
     */
    public function getColoredNameAttribute()
    {
        return "<span style='color: {$this->mau_sac}'>{$this->ten_hang}</span>";
    }
}
