<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DatVe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DeleteCancelledBookings extends Command
{
    // Tên lệnh để chạy thủ công hoặc lên lịch
    protected $signature = 'booking:delete-cancelled';

    // Mô tả lệnh
    protected $description = 'Xóa vĩnh viễn các vé ĐÃ HỦY sau một khoảng thời gian nhất định';

    public function handle()
    {
        $cutoffTime = Carbon::now()->subMinutes(1); 

        $cancelledBookings = DatVe::where('trang_thai', 2)->get();

        if ($cancelledBookings->isEmpty()) {
            $this->info('Không có vé đã hủy nào đủ điều kiện để xóa.');
            return;
        }

        $count = 0;
        $this->info("Tìm thấy {$cancelledBookings->count()} vé đã hủy quá hạn. Đang tiến hành xóa...");

        foreach ($cancelledBookings as $booking) {
            try {
                DB::transaction(function () use ($booking) {
                    // 1. Xóa chi tiết đặt vé (Ghế)
                    DB::table('chi_tiet_dat_ve')->where('id_dat_ve', $booking->id)->delete();
                    
                    // 2. Xóa chi tiết combo
                    // (Lưu ý: kiểm tra tên bảng pivot combo của bạn, ví dụ: chi_tiet_dat_ve_combo)
                    if (Schema::hasTable('chi_tiet_dat_ve_combo')) {
                        DB::table('chi_tiet_dat_ve_combo')->where('id_dat_ve', $booking->id)->delete();
                    }

                    // 3. Xóa lịch sử thanh toán
                    DB::table('thanh_toan')->where('id_dat_ve', $booking->id)->delete();

                    // 4. Xóa Vé
                    $booking->delete();
                });

                $count++;
                // Log::info("Deleted cancelled booking #{$booking->id}");
            } catch (\Exception $e) {
                Log::error("Lỗi khi xóa vé hủy #{$booking->id}: " . $e->getMessage());
                $this->error("Lỗi vé #{$booking->id}");
            }
        }

        $this->info("Hoàn tất! Đã xóa vĩnh viễn {$count} vé đã hủy.");
    }
}