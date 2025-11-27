<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ShowtimeSeat extends Model
{
    protected $table = 'suat_chieu_ghe';

    protected $fillable = [
        'id_suat_chieu',
        'id_ghe',
        'status',
        'hold_expires_at',
    ];

    protected $casts = [
        'hold_expires_at' => 'datetime',
    ];

    public function suatChieu(): BelongsTo
    {
        return $this->belongsTo(SuatChieu::class, 'id_suat_chieu');
    }

    public function ghe(): BelongsTo
    {
        return $this->belongsTo(Ghe::class, 'id_ghe');
    }

    public function isAvailable(): bool
    {
        if ($this->status === 'booked' || $this->status === 'blocked') {
            return false;
        }

        if ($this->status === 'holding') {
            if ($this->hold_expires_at && $this->hold_expires_at->isPast()) {
                return true;
            }
            return false;
        }

        return $this->status === 'available';
    }

    public function isHolding(): bool
    {
        if ($this->status === 'holding') {
            if ($this->hold_expires_at && $this->hold_expires_at->isPast()) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function isBooked(): bool
    {
        return $this->status === 'booked';
    }

    public function releaseIfExpired(): bool
    {
        if ($this->status === 'holding' && $this->hold_expires_at && $this->hold_expires_at->isPast()) {
            $this->status = 'available';
            $this->hold_expires_at = null;
            $this->save();
            return true;
        }
        return false;
    }

    public static function releaseExpiredSeats($showtimeId = null)
    {
        $query = static::where('status', 'holding')
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<', Carbon::now());

        if ($showtimeId) {
            $query->where('id_suat_chieu', $showtimeId);
        }

        return $query->update([
            'status' => 'available',
            'hold_expires_at' => null,
        ]);
    }
}
