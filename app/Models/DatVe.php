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
    public $timestamps = true;
    
    protected $fillable = [
        'id_nguoi_dung',
        'id_suat_chieu',
        'id_khuyen_mai',
        'tong_tien_goc',
        'tien_giam_khuyen_mai',
        'tien_giam_thanh_vien',
        'diem_su_dung',
        'tien_giam_diem',
        'diem_tich_luy',
        'tong_tien',
        'ten_khach_hang',
        'so_dien_thoai',
        'email',
        'tong_tien',
        'trang_thai',
        'phuong_thuc_thanh_toan',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'tong_tien_goc' => 'decimal:2',
        'tien_giam_khuyen_mai' => 'decimal:2',
        'tien_giam_thanh_vien' => 'decimal:2',
        'tien_giam_diem' => 'decimal:2',
        'tong_tien' => 'decimal:2',
        'diem_su_dung' => 'integer',
        'diem_tich_luy' => 'integer',
        'trang_thai' => 'integer',
        'phuong_thuc_thanh_toan' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
        // Nếu đã có tong_tien thì dùng luôn
        if (isset($this->attributes['tong_tien']) && $this->attributes['tong_tien'] > 0) {
            return (float) $this->attributes['tong_tien'];
        }

        // Nếu chưa có thì tính toán
        return $this->tinhTongTien();
    }

    /**
     * Tính tổng tiền đặt vé (ghế + combo)
     */
    public function tinhTongTienGoc(): float
    {
        // Dùng gia_ve là giá thực tế đã áp dụng cho ghế
        $seatTotal = (float) DB::table('chi_tiet_dat_ve')
            ->where('id_dat_ve', $this->id)
            ->sum('gia_ve');

        $comboTotal = (float) DB::table('chi_tiet_dat_ve_combo')
            ->where('id_dat_ve', $this->id)
            ->sum(DB::raw('gia_ap_dung * COALESCE(so_luong,1)'));

        return $seatTotal + $comboTotal;
    }

    /**
     * Tính tiền giảm từ mã khuyến mãi
     */
    public function tinhTienGiamKhuyenMai(): float
    {
        if (!$this->id_khuyen_mai) {
            return 0;
        }

        $promo = KhuyenMai::where('id', $this->id_khuyen_mai)
            ->where('trang_thai', 1)
            ->whereDate('ngay_bat_dau', '<=', now())
            ->whereDate('ngay_ket_thuc', '>=', now())
            ->first();

        if (!$promo) {
            return 0;
        }

        $tongTienGoc = $this->tinhTongTienGoc();

        if ($promo->loai_giam === 'phantram') {
            return round($tongTienGoc * ((float)$promo->gia_tri_giam / 100));
        }

        return (float) $promo->gia_tri_giam;
    }

    /**
     * Tính tiền giảm từ hạng thành viên (dùng Tier mới)
     */
    public function tinhTienGiamThanhVien(): float
    {
        if (!$this->id_nguoi_dung) {
            return 0;
        }

        $hangThanhVien = HangThanhVien::where('id_nguoi_dung', $this->id_nguoi_dung)->first();
        
        if (!$hangThanhVien || !$hangThanhVien->tier) {
            return 0;
        }

        $tier = $hangThanhVien->tier;
        
        // Tính tiền giảm cho vé (dùng gia_ve là giá thực tế)
        $seatTotal = (float) DB::table('chi_tiet_dat_ve')
            ->where('id_dat_ve', $this->id)
            ->sum('gia_ve');
        
        $giamGiaVe = $seatTotal * ($tier->giam_gia_ve / 100);

        // Tính tiền giảm cho combo
        $comboTotal = (float) DB::table('chi_tiet_dat_ve_combo')
            ->where('id_dat_ve', $this->id)
            ->sum(DB::raw('gia_ap_dung * COALESCE(so_luong,1)'));
        
        $giamGiaCombo = $comboTotal * ($tier->giam_gia_combo / 100);

        return round($giamGiaVe + $giamGiaCombo);
    }

    /**
     * Tính tiền giảm từ điểm (100 điểm = 10.000đ)
     */
    public function tinhTienGiamDiem(): float
    {
        if (!$this->diem_su_dung || $this->diem_su_dung <= 0) {
            return 0;
        }

        return ($this->diem_su_dung / 100) * 10000;
    }

    /**
     * Tính tổng tiền cuối cùng
     */
    public function tinhTongTien(): float
    {
        $tongTienGoc = $this->tinhTongTienGoc();
        $tienGiamKM = $this->tinhTienGiamKhuyenMai();
        $tienGiamTV = $this->tinhTienGiamThanhVien();
        $tienGiamDiem = $this->tinhTienGiamDiem();

        return max(0, $tongTienGoc - $tienGiamKM - $tienGiamTV - $tienGiamDiem);
    }

    /**
     * Tính điểm tích lũy được từ đơn hàng này
     */
    public function tinhDiemTichLuy(): int
    {
        if (!$this->id_nguoi_dung) {
            return 0;
        }

        $hangThanhVien = HangThanhVien::where('id_nguoi_dung', $this->id_nguoi_dung)->first();
        
        if (!$hangThanhVien || !$hangThanhVien->tier) {
            // Mặc định: 1 điểm / 10.000đ
            return (int) floor($this->tinhTongTien() / 10000);
        }

        $tier = $hangThanhVien->tier;
        
        // Tính điểm dựa vào tỷ lệ tích điểm của tier
        // Công thức: (tổng tiền / 10000) * tỷ lệ tích điểm
        return (int) floor(($this->tinhTongTien() / 10000) * (float) $tier->ty_le_tich_diem);
    }

    /**
     * Cập nhật tất cả các giá trị tính toán
     * @return $this
     * @phpstan-ignore method.nonObject
     */
    public function capNhatGiaTri()
    {
        // @phpstan-ignore assign.propertyType (Laravel auto-cast)
        $this->tong_tien_goc = $this->tinhTongTienGoc();
        // @phpstan-ignore assign.propertyType (Laravel auto-cast)
        $this->tien_giam_khuyen_mai = $this->tinhTienGiamKhuyenMai();
        // @phpstan-ignore assign.propertyType (Laravel auto-cast)
        $this->tien_giam_thanh_vien = $this->tinhTienGiamThanhVien();
        // @phpstan-ignore assign.propertyType (Laravel auto-cast)
        $this->tien_giam_diem = $this->tinhTienGiamDiem();
        // @phpstan-ignore assign.propertyType (Laravel auto-cast)
        $this->tong_tien = $this->tinhTongTien();
        // @phpstan-ignore assign.propertyType (Laravel auto-cast)
        $this->diem_tich_luy = $this->tinhDiemTichLuy();
        
        $this->save();
        
        return $this;
    }

    /**
     * Xác nhận thanh toán và tích điểm tự động
     */
    public function xacNhanThanhToan()
    {
        if ($this->trang_thai == 1) {
            return false; // Đã thanh toán rồi
        }

        DB::beginTransaction();
        try {
            // Cập nhật trạng thái
            $this->trang_thai = 1;
            $this->save();

            // Nếu có người dùng thì tích điểm
            if ($this->id_nguoi_dung && $this->diem_tich_luy > 0) {
                $diemThanhVien = DiemThanhVien::where('id_nguoi_dung', $this->id_nguoi_dung)->first();
                if ($diemThanhVien) {
                    $diemThanhVien->themDiem(
                        $this->diem_tich_luy, 
                        "Tích điểm từ đơn đặt vé #" . $this->id
                    );
                }
            }

            // Cập nhật doanh thu/lợi nhuận phim
            $suatChieu = $this->suatChieu;
            if ($suatChieu && $suatChieu->id_phim) {
                $phim = \App\Models\Phim::find($suatChieu->id_phim);
                if ($phim) {
                    $phim->updateDoanhThuLoiNhuan();
                }
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Format tiền hiển thị
     * @return string
     */
    public function getFormattedTongTienAttribute()
    {
        // @phpstan-ignore variable.undefined
        $amount = $this->tong_tien ?? 0;
        return number_format((float) $amount, 0, ',', '.') . ' VNĐ';
    }
}
