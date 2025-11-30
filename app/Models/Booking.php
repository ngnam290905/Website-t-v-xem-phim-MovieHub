<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'show_id',
        'status',
        'lock_expires_at',
        'subtotal',
        'discount',
        'total',
        'payment_provider',
        'payment_ref',
    ];

    protected $casts = [
        'lock_expires_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class);
    }

    public function bookingSeats(): HasMany
    {
        return $this->hasMany(BookingSeat::class);
    }

    public function bookingCombos(): HasMany
    {
        return $this->hasMany(BookingCombo::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}

