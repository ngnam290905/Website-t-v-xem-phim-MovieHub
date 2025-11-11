<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Combo extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'items',
        'price',
        'is_active',
    ];

    protected $casts = [
        'items' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function bookingCombos(): HasMany
    {
        return $this->hasMany(BookingCombo::class);
    }
}
