<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Carbon\Carbon;

class SuatChieu extends Model
{
    protected $table = 'suat_chieu';
    
    public $timestamps = false;
    
    protected $fillable = [
        'id_phim',
        'id_phong',
        'thoi_gian_bat_dau',
        'thoi_gian_ket_thuc',
        'trang_thai'
    ];

    protected $casts = [
        'thoi_gian_bat_dau' => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
        'trang_thai' => 'integer',
    ];

    // Accessors và Mutators để map với tên cũ
    public function getStartTimeAttribute()
    {
        return $this->thoi_gian_bat_dau;
    }

    public function setStartTimeAttribute($value)
    {
        $this->attributes['thoi_gian_bat_dau'] = $value;
    }

    public function getEndTimeAttribute()
    {
        return $this->thoi_gian_ket_thuc;
    }

    public function setEndTimeAttribute($value)
    {
        $this->attributes['thoi_gian_ket_thuc'] = $value;
    }

    public function getMovieIdAttribute()
    {
        return $this->id_phim;
    }

    public function setMovieIdAttribute($value)
    {
        $this->attributes['id_phim'] = $value;
    }

    public function getRoomIdAttribute()
    {
        return $this->id_phong;
    }

    public function setRoomIdAttribute($value)
    {
        $this->attributes['id_phong'] = $value;
    }

    public function getStatusAttribute()
    {
        // Map trang_thai (0/1) to status string
        if ($this->trang_thai === 1) {
            // Check if ongoing or finished
            $now = Carbon::now();
            if ($this->thoi_gian_bat_dau && $this->thoi_gian_ket_thuc) {
                if ($now->lt($this->thoi_gian_bat_dau)) {
                    return 'coming';
                } elseif ($now->between($this->thoi_gian_bat_dau, $this->thoi_gian_ket_thuc)) {
                    return 'ongoing';
                } else {
                    return 'finished';
                }
            }
            return 'coming';
        }
        return 'inactive';
    }

    public function setStatusAttribute($value)
    {
        // Map status string to trang_thai
        if (in_array($value, ['coming', 'ongoing', 'finished'])) {
            $this->attributes['trang_thai'] = 1;
        } else {
            $this->attributes['trang_thai'] = 0;
        }
    }


    // Relationship with Phim
    public function phim(): BelongsTo
    {
        return $this->belongsTo(Phim::class, 'id_phim');
    }

    // Alias for backward compatibility
    public function movie(): BelongsTo
    {
        return $this->phim();
    }

    // Relationship with PhongChieu
    public function phongChieu(): BelongsTo
    {
        return $this->belongsTo(PhongChieu::class, 'id_phong');
    }

    // Alias for backward compatibility
    public function room(): BelongsTo
    {
        return $this->phongChieu();
    }

    // Get total seats count
    public function getTotalSeatsCountAttribute()
    {
        return $this->seats()->count();
    }

    // Get occupancy percentage
    public function getOccupancyPercentageAttribute()
    {
        $total = $this->total_seats_count;
        if ($total === 0) return 0;
        
        return round(($this->booked_seats_count / $total) * 100, 2);
    }

    // Update status based on current time
    public function updateStatus()
    {
        $now = Carbon::now();
        
        if ($this->thoi_gian_bat_dau && $this->thoi_gian_ket_thuc) {
            if ($now->lt($this->thoi_gian_bat_dau)) {
                $this->trang_thai = 1; // Active, coming
            } elseif ($now->between($this->thoi_gian_bat_dau, $this->thoi_gian_ket_thuc)) {
                $this->trang_thai = 1; // Active, ongoing
            } else {
                $this->trang_thai = 1; // Active, finished (still active in DB)
            }
            $this->save();
        }
    }

    // Relationship with DatVe
    public function datVe(): HasMany
    {
        return $this->hasMany(DatVe::class, 'id_suat_chieu');
    }
}
