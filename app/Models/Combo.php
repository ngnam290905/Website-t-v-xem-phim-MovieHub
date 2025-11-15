<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Combo extends Model
{
    use HasFactory;

    protected $table = 'combo';

    protected $fillable = [
        'ten',
        'mo_ta',
        'gia',
        'gia_goc',
        'anh',
        'combo_noi_bat',
        'so_luong_toi_da',
        'yeu_cau_it_nhat_ve',
        'ngay_bat_dau',
        'ngay_ket_thuc',
        'trang_thai',
    ];

    protected $casts = [
        'gia' => 'decimal:2',
        'gia_goc' => 'decimal:2',
        'combo_noi_bat' => 'boolean',
        'so_luong_toi_da' => 'integer',
        'yeu_cau_it_nhat_ve' => 'integer',
        'ngay_bat_dau' => 'datetime',
        'ngay_ket_thuc' => 'datetime',
        'trang_thai' => 'boolean',
    ];

    // Accessors for backward compatibility
    public function getNameAttribute()
    {
        return $this->ten;
    }

    public function getDescriptionAttribute()
    {
        return $this->mo_ta;
    }

    public function getPriceAttribute()
    {
        return $this->gia;
    }

    public function getIsActiveAttribute()
    {
        return $this->trang_thai;
    }

    // Mutators for backward compatibility
    public function setNameAttribute($value)
    {
        $this->attributes['ten'] = $value;
    }

    public function setDescriptionAttribute($value)
    {
        $this->attributes['mo_ta'] = $value;
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['gia'] = $value;
    }

    public function setIsActiveAttribute($value)
    {
        $this->attributes['trang_thai'] = $value;
    }

    // Relationships
    public function bookingCombos(): HasMany
    {
        return $this->hasMany(BookingCombo::class, 'combo_id');
    }

    public function chiTietCombo(): HasMany
    {
        return $this->hasMany(ChiTietCombo::class, 'id_combo');
    }

    /**
     * Get the combo image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->anh) {
            // Check if it's a full URL (external) or local path
            if (filter_var($this->anh, FILTER_VALIDATE_URL)) {
                return $this->anh;
            }
            return asset('storage/' . $this->anh);
        }
        return asset('images/default-combo.jpg');
    }
}
