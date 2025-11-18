<?php

namespace App\Observers;

use App\Models\DatVe;
use App\Models\DiemThanhVien;

class DatVeObserver
{
    /**
     * Handle the DatVe "updated" event.
     * Tự động tích điểm khi đơn hàng chuyển sang trạng thái đã thanh toán
     */
    public function updated(DatVe $datVe): void
    {
        // Kiểm tra nếu trạng thái thay đổi thành 1 (đã thanh toán)
        if ($datVe->isDirty('trang_thai') && $datVe->trang_thai == 1) {
            $this->tichDiemChoKhachHang($datVe);
        }
    }

    /**
     * Tích điểm cho khách hàng sau khi thanh toán
     */
    private function tichDiemChoKhachHang(DatVe $datVe): void
    {
        // Chỉ tích điểm nếu có user và có điểm tích lũy
        if (!$datVe->id_nguoi_dung || !$datVe->diem_tich_luy || $datVe->diem_tich_luy <= 0) {
            return;
        }

        // Tìm bản ghi điểm thành viên
        $diemThanhVien = DiemThanhVien::where('id_nguoi_dung', $datVe->id_nguoi_dung)->first();
        
        if (!$diemThanhVien) {
            return;
        }

        // Thêm điểm (tự động trigger capNhatHangTheoTier)
        $diemThanhVien->themDiem(
            $datVe->diem_tich_luy,
            "Tích điểm từ đơn đặt vé #" . $datVe->id
        );
    }

    /**
     * Handle the DatVe "created" event.
     */
    public function created(DatVe $datVe): void
    {
        //
    }

    /**
     * Handle the DatVe "deleted" event.
     */
    public function deleted(DatVe $datVe): void
    {
        //
    }

    /**
     * Handle the DatVe "restored" event.
     */
    public function restored(DatVe $datVe): void
    {
        //
    }

    /**
     * Handle the DatVe "force deleted" event.
     */
    public function forceDeleted(DatVe $datVe): void
    {
        //
    }
}
