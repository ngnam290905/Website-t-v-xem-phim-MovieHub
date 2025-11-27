<?php

namespace App\Observers;

use App\Models\ThanhToan;
use App\Models\Phim;

class ThanhToanObserver
{
    /**
     * Handle the ThanhToan "created" event.
     */
    public function created(ThanhToan $thanhToan): void
    {
        $this->updatePhimRevenue($thanhToan);
    }

    /**
     * Handle the ThanhToan "updated" event.
     */
    public function updated(ThanhToan $thanhToan): void
    {
        // Chỉ cập nhật nếu trạng thái thay đổi
        if ($thanhToan->isDirty('trang_thai')) {
            $this->updatePhimRevenue($thanhToan);
        }
    }

    /**
     * Handle the ThanhToan "deleted" event.
     */
    public function deleted(ThanhToan $thanhToan): void
    {
        $this->updatePhimRevenue($thanhToan);
    }

    /**
     * Cập nhật doanh thu và lợi nhuận của phim
     */
    protected function updatePhimRevenue(ThanhToan $thanhToan): void
    {
        // Lấy thông tin đặt vé -> suất chiếu -> phim
        $datVe = $thanhToan->datVe;
        if ($datVe && $datVe->suatChieu) {
            $phim = $datVe->suatChieu->phim;
            if ($phim) {
                // Cập nhật doanh thu & lợi nhuận
                $phim->updateDoanhThuLoiNhuan();
            }
        }
    }

    /**
     * Handle the ThanhToan "restored" event.
     */
    public function restored(ThanhToan $thanhToan): void
    {
        $this->updatePhimRevenue($thanhToan);
    }

    /**
     * Handle the ThanhToan "force deleted" event.
     */
    public function forceDeleted(ThanhToan $thanhToan): void
    {
        $this->updatePhimRevenue($thanhToan);
    }
}
