<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TinTuc extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tin_tuc';

    protected $fillable = [
        'tieu_de',
        'slug',
        'tom_tat',
        'noi_dung',
        'hinh_anh',
        'tac_gia',
        'the_loai',
        'luot_xem',
        'noi_bat',
        'trang_thai',
        'ngay_dang',
    ];

    protected $casts = [
        'luot_xem' => 'integer',
        'noi_bat' => 'boolean',
        'trang_thai' => 'boolean',
        'ngay_dang' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tinTuc) {
            if (empty($tinTuc->slug)) {
                $tinTuc->slug = Str::slug($tinTuc->tieu_de);
            }
            if (empty($tinTuc->ngay_dang)) {
                $tinTuc->ngay_dang = now();
            }
        });

        static::updating(function ($tinTuc) {
            if ($tinTuc->isDirty('tieu_de') && empty($tinTuc->slug)) {
                $tinTuc->slug = Str::slug($tinTuc->tieu_de);
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('trang_thai', true)
            ->where('ngay_dang', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('noi_bat', true);
    }

    public function incrementViews()
    {
        $this->increment('luot_xem');
    }

    public function getImageUrlAttribute()
    {
        if ($this->hinh_anh) {
            if (filter_var($this->hinh_anh, FILTER_VALIDATE_URL)) {
                return $this->hinh_anh;
            }
            return asset('storage/' . $this->hinh_anh);
        }
        return asset('images/no-image.svg');
    }
}

