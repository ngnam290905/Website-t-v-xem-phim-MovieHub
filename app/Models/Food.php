<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Food extends Model
{
    protected $table = 'foods';
    
    protected $fillable = [
        'name',
        'price',
        'image',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the food orders for this food
     */
    public function chiTietFood(): HasMany
    {
        return $this->hasMany(ChiTietFood::class, 'food_id');
    }

    /**
     * Get the food image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            if (filter_var($this->image, FILTER_VALIDATE_URL)) {
                return $this->image;
            }
            return asset('storage/' . $this->image);
        }
        return asset('images/no-poster.svg');
    }

    /**
     * Check if food is available (has stock and is active)
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }
}
