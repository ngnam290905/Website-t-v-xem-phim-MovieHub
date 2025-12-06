<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Phim extends Model
{
    use SoftDeletes;

    protected $table = 'phim';

    protected $fillable = [
        'ten_phim',
        'ten_goc',
        'poster',
        'trailer',
        'dao_dien',
        'dien_vien',
        'the_loai',
        'quoc_gia',
        'ngon_ngu',
        'do_tuoi',
        'do_dai',
        'ngay_khoi_chieu',
        'ngay_ket_thuc',
        'mo_ta',
        'mo_ta_ngan',
        'diem_danh_gia',
        'so_luot_danh_gia',
        'hot',
        'trang_thai',
        'doanh_thu',
        'loi_nhuan',
        'id_phong',
    ];

    protected $casts = [
        'hot' => 'boolean',
        'ngay_khoi_chieu' => 'date',
        'ngay_ket_thuc' => 'date',
        'diem_danh_gia' => 'decimal:1',
        'so_luot_danh_gia' => 'integer',
        'do_dai' => 'integer',
        'doanh_thu' => 'decimal:2',
        'loi_nhuan' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function suatChieu()
    {
        return $this->hasMany(SuatChieu::class, 'id_phim');
    }

    public function datVe()
    {
        return $this->hasManyThrough(DatVe::class, SuatChieu::class, 'id_phim', 'id_suat_chieu');
    }

    public function getTongDoanhThuAttribute()
    {
        return $this->datVe()
            ->where('trang_thai', 1)
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->sum('chi_tiet_dat_ve.gia_ve');
    }

    public function getSoVeBanAttribute()
    {
        return $this->datVe()
            ->where('trang_thai', 1)
            ->join('chi_tiet_dat_ve', 'dat_ve.id', '=', 'chi_tiet_dat_ve.id_dat_ve')
            ->count();
    }

    public function getFormattedDoanhThuAttribute()
    {
        if ($this->doanh_thu !== null) {
            return number_format((float) $this->doanh_thu, 0, ',', '.') . ' VNĐ';
        }

        return 'Chưa có dữ liệu';
    }

    public function getFormattedLoiNhuanAttribute()
    {
        if ($this->loi_nhuan !== null) {
            return number_format((float) $this->loi_nhuan, 0, ',', '.') . ' VNĐ';
        }

        return 'Chưa có dữ liệu';
    }

    /**
     * Tính tổng doanh thu từ tất cả các vé đã thanh toán thành công.
     */
    public function calculateDoanhThu()
    {
        return (float) DB::table('dat_ve')
            ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
            ->join('thanh_toan', 'dat_ve.id', '=', 'thanh_toan.id_dat_ve')
            ->where('suat_chieu.id_phim', $this->id)
            ->where('thanh_toan.trang_thai', 1)
            ->sum('thanh_toan.so_tien');
    }

    /**
     * Tính lợi nhuận dựa trên tỷ lệ lợi nhuận giả định.
     */
    public function calculateLoiNhuan()
    {
        $doanhThu = $this->calculateDoanhThu();
        $tyLeLoiNhuan = 0.30; // Giả định lợi nhuận = 30% doanh thu

        return $doanhThu * $tyLeLoiNhuan;
    }

    /**
     * Cập nhật doanh thu và lợi nhuận được lưu trên model.
     */
    public function updateDoanhThuLoiNhuan()
    {
        $doanhThu = $this->calculateDoanhThu();
        $loiNhuan = $this->calculateLoiNhuan();

        $this->update([
            'doanh_thu' => $doanhThu,
            'loi_nhuan' => $loiNhuan,
        ]);

        return $this;
    }

    /**
     * Số lượng vé đã bán (đã thanh toán thành công).
     */
    public function getSoVeDaBanAttribute()
    {
        return DB::table('chi_tiet_dat_ve')
            ->join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
            ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
            ->join('thanh_toan', 'dat_ve.id', '=', 'thanh_toan.id_dat_ve')
            ->where('suat_chieu.id_phim', $this->id)
            ->where('thanh_toan.trang_thai', 1)
            ->count();
    }

    /**
     * Build full poster URL with robust fallbacks.
     */
    public function getPosterUrlAttribute()
    {
        $fallback = asset('images/no-poster.svg');

        // If poster already a full URL
        if (!empty($this->poster) && filter_var($this->poster, FILTER_VALIDATE_URL)) {
            return $this->poster;
        }

        // Try storage path: public/posters/{filename} or stored path in column
        if (!empty($this->poster)) {
            // If column holds a relative path like posters/abc.jpg
            $path = $this->poster;
            if (!str_starts_with($path, 'posters/')) {
                $candidate = 'posters/' . ltrim($path, '/');
            } else {
                $candidate = $path;
            }

            if (Storage::disk('public')->exists($candidate)) {
                return Storage::disk('public')->url($candidate);
            }

            // Try public/images/posters
            $publicCandidate = public_path('images/' . ltrim($path, '/'));
            if (file_exists($publicCandidate)) {
                return asset('images/' . ltrim($path, '/'));
            }
        }

        return $fallback;
    }
}
