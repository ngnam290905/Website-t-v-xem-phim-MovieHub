<?php

namespace App\Observers;

use App\Models\ThanhToan;
<<<<<<< HEAD

class ThanhToanObserver
{
    /**
     * Handle the ThanhToan "created" event.
     */
    public function created(ThanhToan $thanhToan): void
    {
        // Implement logic after a payment is created, if needed.
    }

    /**
     * Handle the ThanhToan "updated" event.
     */
    public function updated(ThanhToan $thanhToan): void
    {
        // Implement logic when a payment is updated, if needed.
    }

    /**
     * Handle the ThanhToan "deleted" event.
     */
    public function deleted(ThanhToan $thanhToan): void
    {
        // Implement logic when a payment is deleted, if needed.
    }

    /**
     * Handle the ThanhToan "restored" event.
     */
    public function restored(ThanhToan $thanhToan): void
    {
        // Implement logic when a payment is restored, if needed.
    }

    /**
     * Handle the ThanhToan "force deleted" event.
     */
    public function forceDeleted(ThanhToan $thanhToan): void
    {
        // Implement logic when a payment is force deleted, if needed.
    }
}
=======
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
>>>>>>> origin/hoanganh
