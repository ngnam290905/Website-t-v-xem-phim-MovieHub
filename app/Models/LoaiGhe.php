<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoaiGhe extends Model
{
    protected $table = 'loai_ghe';
    public $timestamps = false;
    
    protected $fillable = [
        'ten_loai',
        'he_so_gia'
    ];

    protected $casts = [
        'he_so_gia' => 'decimal:2',
    ];

    // Relationship with Ghe
    public function ghe(): HasMany
    {
        return $this->hasMany(Ghe::class, 'id_loai');
    }

}
