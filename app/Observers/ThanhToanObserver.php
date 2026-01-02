<?php

namespace App\Observers;

use App\Models\ThanhToan;
use App\Models\DiemThanhVien;
use App\Models\HangThanhVien;
use App\Models\NguoiDung;
use Illuminate\Support\Facades\DB;

class ThanhToanObserver
{
    public function created(ThanhToan $thanhToan)
    {
        if ($thanhToan->trang_thai != 1) return; // chỉ xử lý khi thanh toán thành công

        $datVe = $thanhToan->datVe;
        if (!$datVe || !$datVe->id_nguoi_dung) return;

        $user = $datVe->nguoiDung;
        $soTien = $thanhToan->so_tien;

        // === 1. CẬP NHẬT TỔNG CHI TIÊU ===
        if ($user) {
            // increment trả về int/void, không cập nhật instance model ngay lập tức nếu không refresh, 
            // nhưng ở đây ta không dùng giá trị sau đó nên không sao.
            $user->increment('tong_chi_tieu', $soTien);
        }

        // === 2. TÍCH ĐIỂM: 100.000đ = 1 điểm ===
        $diemMoi = floor($soTien / 100000);
        
        if ($diemMoi > 0) {
            $diemTV = DiemThanhVien::updateOrCreate(
                ['id_nguoi_dung' => $user->id],
                ['tong_diem' => DB::raw("COALESCE(tong_diem, 0) + {$diemMoi}")]
            );

            // [QUAN TRỌNG] Refresh để lấy giá trị số thực từ DB thay vì object DB::raw
            $diemTV->refresh();

            // === 3. TỰ ĐỘNG LÊN HẠNG ===
            // Ép kiểu (int) cho chắc chắn, dù refresh() đã trả về số
            $this->capNhatHangThanhVien($user, (int)$diemTV->tong_diem);
        }
    }

    private function capNhatHangThanhVien(NguoiDung $user, $tongDiem)
    {
        // Đảm bảo $tongDiem là số
        $tongDiem = (int) $tongDiem;

        $hangMoi = match (true) {
            $tongDiem >= 40 => 'Kim Cương',
            $tongDiem >= 30 => 'Bạch Kim',
            $tongDiem >= 20 => 'Vàng',
            $tongDiem >= 10 => 'Bạc',
            default => 'Thường',
        };

        // Load lại quan hệ hạng thành viên để đảm bảo so sánh đúng
        $user->load('hangThanhVien');
        $hangHienTai = $user->hangThanhVien?->ten_hang;

        if ($hangHienTai !== $hangMoi) {
            HangThanhVien::updateOrCreate(
                ['id_nguoi_dung' => $user->id],
                ['ten_hang' => $hangMoi]
            );
        }
    }
}