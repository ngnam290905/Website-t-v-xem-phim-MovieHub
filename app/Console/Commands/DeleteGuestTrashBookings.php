<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DatVe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeleteGuestTrashBookings extends Command
{
    // Tên lệnh
    protected $signature = 'booking:delete-guest-trash';
    protected $description = 'Xóa vĩnh viễn vé rác (treo/hủy) của khách vãng lai';

    public function handle()
    {
        $now = Carbon::now();

        // LỌC VÉ RÁC KHÁCH VÃNG LAI
        $trashBookings = DatVe::whereNull('id_nguoi_dung') // 1. Bắt buộc là khách vãng lai
            ->where(function ($query) use ($now) {
                
                // ĐIỀU KIỆN A: Vé đang chờ (0) nhưng đã quá hạn
                $query->where(function ($q) use ($now) {
                    $q->where('trang_thai', 0)
                      ->where(function ($sub) use ($now) {
                          $sub->where('expires_at', '<', $now)
                              ->orWhere(function($deep) use ($now) {
                                  // Dự phòng: Nếu không có expires_at thì lấy vé tạo quá 10 phút
                                  $deep->whereNull('expires_at')
                                       ->where('created_at', '<', $now->subMinutes(10));
                              });
                      });
                })
                
                // ĐIỀU KIỆN B: Vé ĐÃ HỦY (2) (Như trong hình của bạn)
                // Đối với khách vãng lai, hủy là xóa luôn, không cần lưu lịch sử
                ->orWhere('trang_thai', 2); 
            })
            ->get();

        $count = 0;

        foreach ($trashBookings as $booking) {
            try {
                DB::transaction(function () use ($booking) {
                    // A. NHẢ GHẾ (Nếu còn giữ)
                    $seatIds = DB::table('chi_tiet_dat_ve')
                        ->where('id_dat_ve', $booking->id)
                        ->pluck('id_ghe');
                    
                    if ($seatIds->isNotEmpty()) {
                        // Cập nhật bảng tam_giu_ghe về available
                        DB::table('tam_giu_ghe')
                            ->where('id_suat_chieu', $booking->id_suat_chieu)
                            ->whereIn('id_ghe', $seatIds)
                            ->update([
                                'trang_thai' => 'available',
                                'thoi_gian_het_han' => null
                            ]);
                    }

                    // B. XÓA DỮ LIỆU LIÊN QUAN
                    DB::table('chi_tiet_dat_ve')->where('id_dat_ve', $booking->id)->delete();
                    // Xóa combo nếu có
                    DB::table('chi_tiet_dat_ve_combo')->where('id_dat_ve', $booking->id)->delete(); 
                    // Xóa thanh toán
                    DB::table('thanh_toan')->where('id_dat_ve', $booking->id)->delete();

                    // C. XÓA VÉ VĨNH VIỄN
                    $booking->delete();
                });

                $count++;
            } catch (\Exception $e) {
                Log::error("Lỗi khi xóa vé rác #{$booking->id}: " . $e->getMessage());
            }
        }

        if ($count > 0) {
            $this->info("Đã dọn dẹp sạch {$count} vé rác/hủy của khách vãng lai.");
        } else {
            $this->info("Không có vé rác nào cần xóa.");
        }
    }
}