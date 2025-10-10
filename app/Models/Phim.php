<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phim extends Model
{
    use HasFactory;

    protected $table = 'phim';

    public $timestamps = false;

    protected $fillable = [
        'ten_phim',
        'do_dai',
        'poster',
        'mo_ta',
        'dao_dien',
        'dien_vien',
        'trailer',
        'trang_thai',
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
    ];

    /**
     * Get the movie's showtimes
     */
    public function suatChieu()
    {
        return $this->hasMany(SuatChieu::class, 'id_phim');
    }

    /**
     * Scope for active movies
     */
    public function scopeActive($query)
    {
        return $query->where('trang_thai', 1);
    }

    /**
     * Scope for inactive movies
     */
    public function scopeInactive($query)
    {
        return $query->where('trang_thai', 0);
    }
}
