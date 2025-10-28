<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuatChieu extends Model
{
    use HasFactory;

    protected $table = 'suat_chieu';

    public $timestamps = false;

    protected $fillable = [
        'id_phim',
        'id_phong',
        'thoi_gian_bat_dau',
        'thoi_gian_ket_thuc',
        'trang_thai',
    ];

    protected $casts = [
        'thoi_gian_bat_dau' => 'datetime',
        'thoi_gian_ket_thuc' => 'datetime',
        'trang_thai' => 'boolean',
    ];

    /**
     * Get the movie for this showtime
     */
    public function phim(): BelongsTo
    {
        return $this->belongsTo(Phim::class, 'id_phim');
    }

    /**
     * Get the cinema room for this showtime
     */
    public function phongChieu(): BelongsTo
    {
        return $this->belongsTo(PhongChieu::class, 'id_phong');
    }

    /**
     * Relationship with DatVe
     */
    public function datVe(): HasMany
    {
        return $this->hasMany(DatVe::class, 'id_suat_chieu');
    }

    /**
     * Relationship with Ghe through PhongChieu
     */
    public function ghe(): HasMany
    {
    return $this->hasMany(Ghe::class, 'id_phong', 'id_phong');
    }
}
