<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;
use Illuminate\Support\Facades\DB;

class DatVe extends Model
{
    protected $table = 'dat_ve';
    public $timestamps = false;
    
    protected $fillable = [
        'id_nguoi_dung',
        'id_suat_chieu',
        'id_khuyen_mai',
        'ten_khach_hang',
        'so_dien_thoai',
        'email',
        'tong_tien',
        'trang_thai',
        'phuong_thuc_thanh_toan'
    ];

    protected $casts = [
        'tong_tien' => 'decimal:2',
        'trang_thai' => 'integer',
        'phuong_thuc_thanh_toan' => 'integer',
        'created_at' => 'datetime',
    ];

    // Relationship with NguoiDung
    public function nguoiDung(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    // Relationship with SuatChieu
    public function suatChieu(): BelongsTo
    {
        return $this->belongsTo(SuatChieu::class, 'id_suat_chieu');
    }

    // Alias for backward compatibility: some views/controllers call $datVe->showtime
    public function showtime(): BelongsTo
    {
        return $this->suatChieu();
    }

    // Relationship with ChiTietDatVe
    public function chiTietDatVe(): HasMany
    {
        return $this->hasMany(ChiTietDatVe::class, 'id_dat_ve');
    }

    // Relationship with ChiTietCombo
    public function chiTietCombo(): HasMany
    {
        return $this->hasMany(ChiTietCombo::class, 'id_dat_ve');
    }

    // Relationship with ThanhToan
    public function thanhToan(): HasOne
    {
        return $this->hasOne(ThanhToan::class, 'id_dat_ve');
    }

    // Relationship with KhuyenMai
    public function khuyenMai(): EloquentBelongsTo
    {
        return $this->belongsTo(KhuyenMai::class, 'id_khuyen_mai');
    }

    // Computed: tổng tiền hiển thị nếu cột tong_tien chưa được lưu
    public function getTongTienHienThiAttribute(): float
    {
        $stored = $this->attributes['tong_tien'] ?? null;
        if ($stored !== null) {
            return (float) $stored;
        }

        $seatTotal = (float) DB::table('chi_tiet_dat_ve')
            ->where('id_dat_ve', $this->id)
            ->sum('gia');

        $comboTotal = (float) DB::table('chi_tiet_dat_ve_combo')
            ->where('id_dat_ve', $this->id)
            ->sum(DB::raw('gia_ap_dung * COALESCE(so_luong,1)'));

        $discount = 0;
        if ($this->id_khuyen_mai) {
            $promo = KhuyenMai::where('id', $this->id_khuyen_mai)
                ->where('trang_thai', 1)
                ->whereDate('ngay_bat_dau', '<=', now())
                ->whereDate('ngay_ket_thuc', '>=', now())
                ->first();
            if ($promo) {
                if ($promo->loai_giam === 'phantram') {
                    $discount = round(($seatTotal + $comboTotal) * ((float)$promo->gia_tri_giam / 100));
                } else {
                    $discount = (float) $promo->gia_tri_giam;
                }
            }
        }

        $memberDiscount = 0;
        if ($this->id_nguoi_dung) {
            $tier = optional(HangThanhVien::where('id_nguoi_dung', $this->id_nguoi_dung)->first())->ten_hang;
            if ($tier) {
                $normalized = mb_strtolower($tier);
                if ($normalized === 'đồng' || $normalized === 'dong') { $memberDiscount = 10000; }
                elseif ($normalized === 'bạc' || $normalized === 'bac') { $memberDiscount = 15000; }
                elseif ($normalized === 'vàng' || $normalized === 'vang') { $memberDiscount = 20000; }
                elseif ($normalized === 'kim cương' || $normalized === 'kim cuong') { $memberDiscount = 25000; }
            }
        }

        return max(0, ($seatTotal + $comboTotal) - $discount - $memberDiscount);
    }
}
