<?php

namespace App\Observers;

use App\Models\ThanhToan;
use App\Models\DiemThanhVien;
use App\Models\HangThanhVien;
use App\Models\NguoiDung;

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
        $user->increment('tong_chi_tieu', $soTien);

        // === 2. TÍCH ĐIỂM: 100.000đ = 1 điểm ===
        $diemMoi = floor($soTien / 100000);
        if ($diemMoi > 0) {
            $diemTV = DiemThanhVien::updateOrCreate(
                ['id_nguoi_dung' => $user->id],
                ['tong_diem' => \DB::raw("tong_diem + {$diemMoi}")]
            );

            // === 3. TỰ ĐỘNG LÊN HẠNG ===
            $this->capNhatHangThanhVien($user, $diemTV->tong_diem);
        }
    }

    private function capNhatHangThanhVien(NguoiDung $user, $tongDiem)
    {
        $hangMoi = match (true) {
            $tongDiem >= 40 => 'Kim Cương',
            $tongDiem >= 30 => 'Bạch Kim',
            $tongDiem >= 20 => 'Vàng',
            $tongDiem >= 10 => 'Bạc',
            default => 'Thường',
        };

        // Chỉ cập nhật nếu thay đổi
        $hangHienTai = $user->hangThanhVien?->ten_hang;
        if ($hangHienTai !== $hangMoi) {
            HangThanhVien::updateOrCreate(
                ['id_nguoi_dung' => $user->id],
                ['ten_hang' => $hangMoi]
            );
        }
    }
}