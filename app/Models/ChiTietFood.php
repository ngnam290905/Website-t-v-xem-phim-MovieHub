<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietFood extends Model
{
    protected $table = 'chi_tiet_dat_ve_food';
    
    public $timestamps = true;

    protected $fillable = [
        'id_dat_ve',
        'food_id',
        'quantity',
        'price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Get the booking this food order belongs to
     */
    public function datVe(): BelongsTo
    {
        return $this->belongsTo(DatVe::class, 'id_dat_ve');
    }

    /**
     * Get the food this order is for
     */
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    /**
     * Calculate total price for this food order
     */
    public function getTotalPriceAttribute(): float
    {
        return (float) $this->price * $this->quantity;
    }
}
