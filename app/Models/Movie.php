<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    protected $table = 'phim';
    
    protected $fillable = [
        'ten_phim',
        'do_dai',
        'poster',
        'mo_ta',
        'dao_dien',
        'dien_vien',
        'trailer',
        'trang_thai'
    ];

    protected $casts = [
        'trang_thai' => 'boolean',
    ];

    // Accessor to get the title in English format
    public function getTitleAttribute()
    {
        return $this->ten_phim;
    }

    // Accessor to get the duration in minutes
    public function getDurationAttribute()
    {
        return $this->do_dai;
    }

    // Accessor to get the description
    public function getDescriptionAttribute()
    {
        return $this->mo_ta;
    }

    // Accessor to get the director
    public function getDirectorAttribute()
    {
        return $this->dao_dien;
    }

    // Accessor to get the actors
    public function getActorsAttribute()
    {
        return $this->dien_vien;
    }

    // Scope for active movies
    public function scopeActive($query)
    {
        return $query->where('trang_thai', 1);
    }
}
