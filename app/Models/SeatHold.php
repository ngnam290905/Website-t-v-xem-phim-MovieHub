<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SeatHold extends Model
{
    use HasFactory;

    // 1. Map đúng tên bảng trong SQL
    protected $table = 'tam_giu_ghe';

    // 2. Map đúng tên cột trong SQL (Tiếng Việt)
    protected $fillable = [
        'id_suat_chieu',    // Thay cho showtime_id
        'id_ghe',           // Thay cho seat_id
        'id_nguoi_dung',    // Thay cho user_id
        'session_id',
        'gia_giu',
        'trang_thai',       // Thay cho status
        'thoi_gian_giu',
        'thoi_gian_het_han', // Thay cho expires_at
    ];

    protected $casts = [
        'thoi_gian_giu' => 'datetime',
        'thoi_gian_het_han' => 'datetime',
        'gia_giu' => 'decimal:2',
    ];

    // --- ALIAS (QUAN TRỌNG) ---
    // Giúp code Service dùng key tiếng Anh vẫn tự động map sang cột tiếng Việt khi Create/Update

    public function setShowtimeIdAttribute($value) { $this->attributes['id_suat_chieu'] = $value; }
    public function getShowtimeIdAttribute() { return $this->getAttribute('id_suat_chieu'); }

    public function setSeatIdAttribute($value) { $this->attributes['id_ghe'] = $value; }
    public function getSeatIdAttribute() { return $this->getAttribute('id_ghe'); }

    public function setUserIdAttribute($value) { $this->attributes['id_nguoi_dung'] = $value; }
    public function getUserIdAttribute() { return $this->getAttribute('id_nguoi_dung'); }

    public function setExpiresAtAttribute($value) { $this->attributes['thoi_gian_het_han'] = $value; }
    public function getExpiresAtAttribute() { return $this->getAttribute('thoi_gian_het_han'); }

    // --- RELATIONSHIPS ---

    public function showtime(): BelongsTo
    {
        return $this->belongsTo(SuatChieu::class, 'id_suat_chieu');
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Ghe::class, 'id_ghe');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    // --- SCOPES (Đây là phần gây lỗi, cần sửa tên cột trong where) ---

    public function scopeActive($query)
    {
        // Sửa 'expires_at' -> 'thoi_gian_het_han'
        return $query->where('thoi_gian_het_han', '>', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->where('thoi_gian_het_han', '<=', Carbon::now());
    }

    public function scopeForShowtime($query, $showtimeId)
    {
        // Sửa 'showtime_id' -> 'id_suat_chieu'
        return $query->where('id_suat_chieu', $showtimeId);
    }

    public function scopeForSeat($query, $seatId)
    {
        // Sửa 'seat_id' -> 'id_ghe'
        return $query->where('id_ghe', $seatId);
    }

    public function scopeForUser($query, $userId)
    {
        // Sửa 'user_id' -> 'id_nguoi_dung'
        return $query->where('id_nguoi_dung', $userId);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    // --- HELPER METHODS ---

    public function isExpired(): bool
    {
        if (!$this->thoi_gian_het_han) return true;
        return $this->thoi_gian_het_han->isPast();
    }

    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    // --- STATIC METHODS ---

    public static function releaseExpired(): int
    {
        return static::expired()->delete();
    }

    public static function releaseForShowtime(int $showtimeId): int
    {
        return static::forShowtime($showtimeId)->delete();
    }

    public static function releaseForSeat(int $showtimeId, int $seatId): int
    {
        return static::forShowtime($showtimeId)
            ->forSeat($seatId)
            ->delete();
    }

    public static function releaseForUser(int $userId, ?int $showtimeId = null): int
    {
        $query = static::forUser($userId);
        if ($showtimeId) {
            $query->forShowtime($showtimeId);
        }
        return $query->delete();
    }
}