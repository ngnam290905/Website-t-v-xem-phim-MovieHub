<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietDatVe extends Model
{
    protected $table = 'chi_tiet_dat_ve';
    public $timestamps = false;
    
<<<<<<< HEAD
    /**
     * Giải thích các cột:
     * - gia: Giá gốc của ghế tại thời điểm đặt (giá niêm yết)
     * - gia_ve: Giá thực tế áp dụng cho ghế (có thể khác do chương trình khuyến mãi đặc biệt)
     * 
     * Lưu ý: Giảm giá từ hạng thành viên và điểm được tính ở cấp đơn hàng (bảng dat_ve),
     * không phải ở cấp chi tiết ghế
     */
    protected $fillable = [
        'id_dat_ve',
        'id_ghe',
        'gia',
        'gia_ve'
    ];

    protected $casts = [
        'gia' => 'decimal:2',
<<<<<<< HEAD
        'gia_ve' => 'decimal:2',
=======
>>>>>>> 7c41d7cf79cbaa269a41f5d8314177793bcddb1f
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
