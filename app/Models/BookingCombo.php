<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCombo extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'combo_id',
        'unit_price',
        'qty',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'qty' => 'integer',
        'total_price' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class);
    }
}

