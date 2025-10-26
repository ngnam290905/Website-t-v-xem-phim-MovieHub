<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhongChieu extends Model
{
    protected $table = 'phong_chieu';
    
    protected $fillable = [
        'ten_phong',
        'so_hang',
        'so_cot',
        'suc_chua',
        'mo_ta',
        'trang_thai'
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
    ];

    // Relationship with SuatChieu
    public function suatChieu(): HasMany
    {
        return $this->hasMany(SuatChieu::class, 'id_phong');
    }

    // Relationship with Ghe
    public function ghe(): HasMany
    {
        return $this->hasMany(Ghe::class, 'id_phong');
    }
}
