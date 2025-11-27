<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HangThanhVien extends Model
{
    use HasFactory;

    protected $table = 'hang_thanh_vien';
    public $timestamps = false;

    protected $fillable = [
        'id_nguoi_dung',
        'id_tier',
        'ten_hang', 
        'uu_dai',
        'diem_toi_thieu',
        'ngay_cap_nhat_hang'
    ];

    protected $casts = [
        'ngay_cap_nhat_hang' => 'datetime',
    ];

    /**
     * Quan hệ: Thuộc về 1 người dùng
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    /**
     * Quan hệ: Thuộc về 1 tier
     */
    public function tier()
    {
        return $this->belongsTo(Tier::class, 'id_tier');
    }

    /**
     * Tự động cập nhật hạng dựa vào điểm
     */
    public function capNhatHangTheoTier()
    {
        $nguoiDung = $this->nguoiDung;
        $diemThanhVien = $nguoiDung->diemThanhVien;
        
        if (!$diemThanhVien) {
            return false;
        }

        // Tìm tier phù hợp với số điểm hiện tại
        $tierMoi = Tier::getTierByPoints($diemThanhVien->tong_diem);
        
        if (!$tierMoi) {
            return false;
        }

        // Nếu tier mới khác tier hiện tại thì cập nhật
        if ($this->id_tier !== $tierMoi->id) {
            $this->update([
                'id_tier' => $tierMoi->id,
                'ten_hang' => $tierMoi->ten_hang,
                'uu_dai' => $tierMoi->uu_dai,
                'diem_toi_thieu' => $tierMoi->diem_toi_thieu,
                'ngay_cap_nhat_hang' => now(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Lấy % giảm giá vé từ tier
     */
    public function getGiamGiaVeAttribute()
    {
        return $this->tier ? $this->tier->giam_gia_ve : 0;
    }

    /**
     * Lấy % giảm giá combo từ tier
     */
    public function getGiamGiaComboAttribute()
    {
        return $this->tier ? $this->tier->giam_gia_combo : 0;
    }

    /**
     * Lấy tỷ lệ tích điểm từ tier
     */
    public function getTyLeTichDiemAttribute()
    {
        return $this->tier ? $this->tier->ty_le_tich_diem : 1.0;
    }
}
