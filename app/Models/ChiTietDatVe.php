<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietDatVe extends Model
{
    protected $table = 'chi_tiet_dat_ve';
    public $timestamps = false;
    
    protected $fillable = [
        'id_dat_ve',
        'id_ghe',
        'gia',
        'gia_ve'
    ];

    protected $casts = [
        'gia' => 'decimal:2',
    ];

    // Relationship with DatVe
    public function datVe(): BelongsTo
    {
        return $this->belongsTo(DatVe::class, 'id_dat_ve');
    }

    // Relationship with Ghe
    public function ghe(): BelongsTo
    {
        return $this->belongsTo(Ghe::class, 'id_ghe');
    }
}
