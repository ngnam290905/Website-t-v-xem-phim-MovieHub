<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhongChieu extends Model
{
    use HasFactory;

    protected $table = 'phong_chieu';

    public $timestamps = false;

    protected $fillable = [
        'ten_phong',
        'so_hang',
        'so_cot',
        'suc_chua',
        'mo_ta',
        'trang_thai',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
    ];

    /**
     * Get the showtimes for this cinema room
     */
    public function suatChieu()
    {
        return $this->hasMany(SuatChieu::class, 'id_phong');
    }

    /**
     * Scope for active rooms
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', 1);
    }
}
