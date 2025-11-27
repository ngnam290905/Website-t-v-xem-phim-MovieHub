<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DiemThanhVien extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'diem_thanh_vien';

    protected $fillable = [
        'id_nguoi_dung',
        'tong_diem',
        'ngay_het_han',
    ];

    protected $casts = [
        'tong_diem' => 'integer',
        'ngay_het_han' => 'date',
    ];

    /**
     * Quan hệ: Thuộc về 1 người dùng
     */
    public function nguoiDung()
    {
        return $this->belongsTo(NguoiDung::class, 'id_nguoi_dung');
    }

    /**
     * Thêm điểm và tự động cập nhật hạng
     */
    public function themDiem($diem, $lyDo = null)
    {
        $this->increment('tong_diem', $diem);
        
        // Tự động cập nhật hạng
        $this->capNhatHangTheoTier();
        
        // Log lịch sử điểm nếu có bảng lich_su_diem
        if ($lyDo) {
            DB::table('lich_su_diem')->insert([
                'id_nguoi_dung' => $this->id_nguoi_dung,
                'diem_thay_doi' => $diem,
                'ly_do' => $lyDo,
                'ngay' => now(),
            ]);
        }
        
        return $this;
    }

    /**
     * Trừ điểm và tự động cập nhật hạng
     */
    public function truDiem($diem, $lyDo = null)
    {
        if ($this->tong_diem >= $diem) {
            $this->decrement('tong_diem', $diem);
            
            // Tự động cập nhật hạng
            $this->capNhatHangTheoTier();
            
            // Log lịch sử điểm
            if ($lyDo) {
                DB::table('lich_su_diem')->insert([
                    'id_nguoi_dung' => $this->id_nguoi_dung,
                    'diem_thay_doi' => -$diem,
                    'ly_do' => $lyDo,
                    'ngay' => now(),
                ]);
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * Tự động cập nhật hạng thành viên dựa vào điểm
     */
    protected function capNhatHangTheoTier()
    {
        $hangThanhVien = $this->nguoiDung->hangThanhVien;
        
        if ($hangThanhVien) {
            $hangThanhVien->capNhatHangTheoTier();
        }
    }

    /**
     * Kiểm tra điểm có còn hiệu lực không
     */
    public function isExpired()
    {
        if (!$this->ngay_het_han) {
            return false;
        }
        
        return now()->greaterThan($this->ngay_het_han);
    }

    /**
     * Format điểm hiển thị
     */
    public function getFormattedDiemAttribute()
    {
        return number_format($this->tong_diem) . ' điểm';
    }
}
