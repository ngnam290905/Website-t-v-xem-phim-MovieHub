<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DatVe;
use App\Models\ShowtimeSeat;
use App\Models\ThanhToan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CancelExpiredBookings extends Command
{
    /**
     * Tên lệnh dùng để gọi trong terminal hoặc schedule
     */
    protected $signature = 'booking:cancel-expired';

    /**
     * Mô tả lệnh
     */
    protected $description = 'Hủy các đơn vé Online/Offline quá hạn thanh toán';

    public function handle()
    {
        // Lấy thời điểm hiện tại
        $now = Carbon::now();

        // Tìm các vé:
        // 1. Trạng thái là 0 (Chờ thanh toán)
        // 2. Có thời gian hết hạn (expires_at) nhỏ hơn thời điểm hiện tại
        // Lưu ý: Đảm bảo bảng dat_ve có cột expires_at (datetime, nullable)
        $expiredBookings = DatVe::where('trang_thai', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->get();

        $count = 0;

        foreach ($expiredBookings as $booking) {
            try {
                DB::transaction(function () use ($booking) {
                    // 1. NHẢ GHẾ (Quan trọng)
                    if ($booking->id_nguoi_dung) {
                        ShowtimeSeat::where('id_suat_chieu', $booking->id_suat_chieu)
                            ->where('id_nguoi_dung', $booking->id_nguoi_dung)
                            // Chỉ nhả ghế đang bị giữ hoặc đã đặt tạm
                            ->whereIn('trang_thai', ['booked', 'holding', 'dang_giu']) 
                            ->update([
                                'trang_thai' => 'available', 
                                'thoi_gian_het_han' => null
                            ]);
                    }

                    // 2. Cập nhật trạng thái vé thành HỦY (2) hoặc HẾT HẠN (nếu có status riêng)
                    $booking->update(['trang_thai' => 2]); // 2 = Đã hủy

                    // 3. Cập nhật trạng thái thanh toán (nếu có)
                    if ($booking->thanhToan) {
                        $booking->thanhToan()->update(['trang_thai' => 2]); // 2 = Thất bại/Hủy
                    }
                });

                $count++;
                Log::info("Đã hủy tự động đơn vé quá hạn #{$booking->id}");
                $this->info("Đã hủy đơn #{$booking->id}");

            } catch (\Exception $e) {
                Log::error("Lỗi khi hủy đơn #{$booking->id}: " . $e->getMessage());
            }
        }

        $this->info("Hoàn tất quét. Đã xử lý {$count} đơn vé.");
    }
}