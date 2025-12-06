<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SeatHold extends Model
{
    use HasFactory;

    protected $table = 'seat_holds';

    protected $fillable = [
        'showtime_id',
        'seat_id',
        'user_id',
        'session_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function showtime(): BelongsTo
    {
        return $this->belongsTo(SuatChieu::class, 'showtime_id');
    }

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Ghe::class, 'seat_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', Carbon::now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }

    public function scopeForShowtime($query, $showtimeId)
    {
        return $query->where('showtime_id', $showtimeId);
    }

    public function scopeForSeat($query, $seatId)
    {
        return $query->where('seat_id', $seatId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Release expired holds (static method for cron job)
     */
    public static function releaseExpired(): int
    {
        return static::expired()->delete();
    }

    /**
     * Release holds for a specific showtime
     */
    public static function releaseForShowtime(int $showtimeId): int
    {
        return static::forShowtime($showtimeId)->delete();
    }

    /**
     * Release holds for a specific seat
     */
    public static function releaseForSeat(int $showtimeId, int $seatId): int
    {
        return static::forShowtime($showtimeId)
            ->forSeat($seatId)
            ->delete();
    }

    /**
     * Release holds for a user
     */
    public static function releaseForUser(int $userId, ?int $showtimeId = null): int
    {
        $query = static::forUser($userId);
        if ($showtimeId) {
            $query->forShowtime($showtimeId);
        }
        return $query->delete();
    }
}

