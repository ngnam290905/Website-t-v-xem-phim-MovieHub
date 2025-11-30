<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ShowtimeSeat extends Model
{
    // Khớp với tên bảng trong SQL dump
    protected $table = 'tam_giu_ghe';

    // Khớp với các cột trong SQL dump
    protected $fillable = [
        'id_suat_chieu',
        'id_ghe',
        'id_nguoi_dung',      // Thêm cái này để tracking user
        'session_id',         // Thêm cái này nếu muốn tracking khách vãng lai
        'gia_giu',            // Thêm cái này để lưu giá vé lúc đặt
        'trang_thai',         // Sửa từ 'status' -> 'trang_thai'
        'thoi_gian_het_han',  // Sửa từ 'hold_expires_at' -> 'thoi_gian_het_han'
    ];

    protected $casts = [
        'thoi_gian_het_han' => 'datetime',
        'gia_giu' => 'decimal:2',
    ];

    public function suatChieu(): BelongsTo
    {
        return $this->belongsTo(SuatChieu::class, 'id_suat_chieu');
    }

    public function ghe(): BelongsTo
    {
        return $this->belongsTo(Ghe::class, 'id_ghe');
    }

    // --- LOGIC KIỂM TRA TRẠNG THÁI ---

    // Kiểm tra ghế có đang available không
    public function isAvailable(): bool
    {
        // Nếu trạng thái là 'booked' (đã đặt cứng) hoặc 'blocked' -> Không available
        if ($this->trang_thai === 'booked' || $this->trang_thai === 'blocked') {
            return false;
        }

        // Nếu đang giữ (holding/dang_giu)
        if ($this->trang_thai === 'holding' || $this->trang_thai === 'dang_giu') {
            // Kiểm tra xem đã hết hạn chưa
            if ($this->thoi_gian_het_han && $this->thoi_gian_het_han->isPast()) {
                return true; // Đã hết hạn -> coi như Available
            }
            return false; // Chưa hết hạn -> Đang bận
        }

        // Nếu trạng thái là available -> OK
        return $this->trang_thai === 'available';
    }

    // Kiểm tra ghế có đang được giữ (tạm thời) không
    public function isHolding(): bool
    {
        if ($this->trang_thai === 'holding' || $this->trang_thai === 'dang_giu') {
            // Nếu đã quá giờ giữ -> Không còn là holding nữa
            if ($this->thoi_gian_het_han && $this->thoi_gian_het_han->isPast()) {
                return false;
            }
            return true;
        }
        return false;
    }

    // Kiểm tra ghế đã đặt cứng (đã thanh toán hoặc đặt tại quầy)
    public function isBooked(): bool
    {
        return $this->trang_thai === 'booked';
    }

    // --- CÁC HÀM HỖ TRỢ XỬ LÝ (STATIC & INSTANCE) ---

    // Hàm giải phóng 1 ghế cụ thể nếu hết hạn
    public function releaseIfExpired(): bool
    {
        if (($this->trang_thai === 'holding' || $this->trang_thai === 'dang_giu') 
            && $this->thoi_gian_het_han 
            && $this->thoi_gian_het_han->isPast()) {
            
            $this->trang_thai = 'available';
            $this->thoi_gian_het_han = null;
            $this->save();
            return true;
        }
        return false;
    }

    // Hàm static: Quét và nhả toàn bộ ghế hết hạn của 1 suất chiếu
    public static function releaseExpiredSeats($showtimeId = null)
    {
        // Tìm các ghế đang giữ (holding hoặc dang_giu)
        $query = static::whereIn('trang_thai', ['holding', 'dang_giu'])
            ->whereNotNull('thoi_gian_het_han')
            ->where('thoi_gian_het_han', '<', Carbon::now());

        if ($showtimeId) {
            $query->where('id_suat_chieu', $showtimeId);
        }

        // Cập nhật về available
        return $query->update([
            'trang_thai' => 'available',
            'thoi_gian_het_han' => null,
        ]);
    }
    
    // Accessor ảo để code Controller cũ vẫn chạy được nếu bạn lỡ dùng $seat->status
    public function getStatusAttribute()
    {
        return $this->trang_thai;
    }
    
    public function setStatusAttribute($value)
    {
        $this->attributes['trang_thai'] = $value;
    }
    
    // Accessor ảo cho hold_expires_at
    public function getHoldExpiresAtAttribute()
    {
        return $this->thoi_gian_het_han;
    }

    public function setHoldExpiresAtAttribute($value)
    {
        $this->attributes['thoi_gian_het_han'] = $value;
    }
}