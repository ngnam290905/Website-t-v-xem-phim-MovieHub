<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ghe extends Model
{
    protected $table = 'ghe';
    
    protected $fillable = [
        'room_id',
        'id_loai',
        'seat_code',
        'row_label',
        'col_number',
        'so_ghe',
        'status',
        'price'
    ];

    protected $casts = [
        'status' => 'string',
        'price' => 'decimal:2',
    ];

    // Relationship with PhongChieu (Room)
    public function room(): BelongsTo
    {
        return $this->belongsTo(PhongChieu::class, 'room_id');
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
        return $this->seat_code ?? ($this->row_label . $this->col_number);
    }

    // Accessor for seat name (legacy)
    public function getTenGheAttribute()
    {
        return $this->getDisplayNameAttribute();
    }

    // Scope for available seats
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    // Scope for booked seats
    public function scopeBooked($query)
    {
        return $query->where('status', 'booked');
    }

    // Scope for seat type
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope for VIP seats
    public function scopeVip($query)
    {
        return $query->where('type', 'vip');
    }

    // Scope for normal seats
    public function scopeNormal($query)
    {
        return $query->where('type', 'normal');
    }

    // Check if seat is available
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    // Check if seat is booked
    public function isBooked()
    {
        return $this->status === 'booked';
    }

    // Check if seat is locked
    public function isLocked()
    {
        return $this->status === 'locked';
    }
}
