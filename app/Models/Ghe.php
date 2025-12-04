<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ghe extends Model
{
    protected $table = 'ghe';
    public $timestamps = false;
    
    protected $fillable = [
        'id_phong',
        'id_loai',
        'so_hang',
        'so_ghe',
        'trang_thai',
        'pos_x',
        'pos_y',
        'zone',
        'meta',
        'is_double',
        'pair_id'
    ];

    protected $casts = [
        'trang_thai' => 'integer',
        'pos_x' => 'integer',
        'pos_y' => 'integer',
        'meta' => 'array',
        'is_double' => 'boolean',
        'pair_id' => 'integer',
    ];

    // Relationship with PhongChieu (Room)
    public function room(): BelongsTo
    {
        return $this->belongsTo(PhongChieu::class, 'id_phong');
    }

    // Legacy relationship for backward compatibility
    public function phongChieu(): BelongsTo
    {
        return $this->room();
    }

    // Relationship with LoaiGhe (Seat Type)
    public function seatType(): BelongsTo
    {
        return $this->belongsTo(LoaiGhe::class, 'id_loai');
    }

    // Legacy relationship for backward compatibility
    public function loaiGhe(): BelongsTo
    {
        return $this->seatType();
    }

    // Relationship with ChiTietDatVe (Booking Details)
    public function bookingDetails(): HasMany
    {
        return $this->hasMany(ChiTietDatVe::class, 'id_ghe');
    }

    // Legacy relationship for backward compatibility
    public function chiTietDatVe(): HasMany
    {
        return $this->bookingDetails();
    }

    // Accessor for seat display name
    public function getDisplayNameAttribute()
    {
        return $this->so_ghe;
    }

    // Accessor for seat name (legacy)
    public function getTenGheAttribute()
    {
        return $this->getDisplayNameAttribute();
    }

    // Virtual status mapping for backward compatibility
    public function getStatusAttribute()
    {
        return $this->trang_thai === 1 ? 'available' : 'locked';
    }

    // Scope for available seats
    public function scopeAvailable($query)
    {
        return $query->where('trang_thai', 1);
    }

    // Scope for booked seats
    public function scopeBooked($query)
    {
        // If you track booked status differently, adjust here. Default to none.
        return $query->whereRaw('1=0');
    }

    // Scope for seat type
    public function scopeOfType($query, $type)
    {
        return $query->whereHas('seatType', function($q) use ($type) {
            $q->where('ten_loai', $type);
        });
    }

    // Scope for VIP seats
    public function scopeVip($query)
    {
        return $query->whereHas('seatType', function($q){ $q->where('ten_loai', 'vip'); });
    }

    // Scope for normal seats
    public function scopeNormal($query)
    {
        return $query->whereHas('seatType', function($q){ $q->where('ten_loai', 'normal'); });
    }

    // Check if seat is available
    public function isAvailable()
    {
        return (int)$this->trang_thai === 1;
    }

    // Check if seat is booked
    public function isBooked()
    {
        return false;
    }

    // Check if seat is locked
    public function isLocked()
    {
        return (int)$this->trang_thai === 0;
    }
}
