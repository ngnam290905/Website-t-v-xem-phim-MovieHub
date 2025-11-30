<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Phim extends Model
{
    protected $table = 'phim';
    
    protected $fillable = [
        'ten_phim',
        'ten_goc',
        'do_dai',
        'poster',
        'mo_ta',
        'dao_dien',
        'dien_vien',
        'trailer',
        'the_loai',
        'quoc_gia',
        'ngon_ngu',
        'do_tuoi',
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

    /**
     * Get formatted revenue
     */
    public function getFormattedDoanhThuAttribute()
    {
        if ($this->doanh_thu !== null) {
            return number_format((float)$this->doanh_thu, 0, ',', '.') . ' VNĐ';
        }
        return 'Chưa có dữ liệu';
    }

    /**
     * Get formatted profit
     */
    public function getFormattedLoiNhuanAttribute()
    {
        if ($this->loi_nhuan !== null) {
            return number_format((float)$this->loi_nhuan, 0, ',', '.') . ' VNĐ';
        }
        return 'Chưa có dữ liệu';
    }

    /**
     * Tính tổng doanh thu từ tất cả các vé đã thanh toán thành công
     */
    public function calculateDoanhThu()
    {
        // Lấy tất cả đặt vé đã thanh toán thành công qua các suất chiếu của phim này
        $tongDoanhThu = DB::table('dat_ve')
            ->join('suat_chieu', 'dat_ve.id_suat_chieu', '=', 'suat_chieu.id')
            ->join('thanh_toan', 'dat_ve.id', '=', 'thanh_toan.id_dat_ve')
            ->where('suat_chieu.id_phim', $this->id)
            ->where('thanh_toan.trang_thai', 1) // Chỉ tính thanh toán thành công
            ->sum('thanh_toan.so_tien');

        return (float)$tongDoanhThu;
    }

    /**
     * Tính lợi nhuận (giả định lợi nhuận = 30% doanh thu)
     * Bạn có thể điều chỉnh công thức tùy theo logic kinh doanh
     */
    public function calculateLoiNhuan()
    {
        $doanhThu = $this->calculateDoanhThu();
        // Giả định: Chi phí = 70% doanh thu, lợi nhuận = 30% doanh thu
        // Bạn có thể thay đổi tỷ lệ này theo thực tế
        $tyLeLoiNhuan = 0.30; // 30%
        
        return $doanhThu * $tyLeLoiNhuan;
    }

    /**
     * Cập nhật doanh thu và lợi nhuận
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
     * Số lượng vé đã bán
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
}
