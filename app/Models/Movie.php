<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'duration_minutes',
        'rating',
        'synopsis',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
    ];

    public function shows(): HasMany
    {
        return $this->hasMany(Show::class);
    }
}

