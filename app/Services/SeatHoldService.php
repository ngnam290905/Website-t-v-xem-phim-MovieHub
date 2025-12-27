<?php

namespace App\Services;

use App\Models\SeatHold; // Đảm bảo Model này đã trỏ đúng bảng 'tam_giu_ghe'
use App\Models\Ghe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SeatHoldService
{
    private const HOLD_DURATION_MINUTES = 10;

    /**
     * Hold a seat for a user
     */
    public function holdSeat(int $showtimeId, int $seatId, ?int $userId = null, ?string $sessionId = null): array
    {
        try {
            return DB::transaction(function () use ($showtimeId, $seatId, $userId, $sessionId) {
                // 1. Kiểm tra ghế có tồn tại
                $seat = Ghe::find($seatId);
                if (!$seat) {
                    return ['success' => false, 'hold' => null, 'message' => 'Ghế không tồn tại'];
                }

                // 2. Kiểm tra ghế đã được đặt (booked - đã thanh toán)
                if ($this->isSeatBooked($showtimeId, $seatId)) {
                    return ['success' => false, 'hold' => null, 'message' => 'Ghế đã được đặt'];
                }

                // 3. Kiểm tra ghế đang được giữ bởi người khác (Query tên cột Tiếng Việt)
                $existingHold = SeatHold::where('id_suat_chieu', $showtimeId)
                    ->where('id_ghe', $seatId)
                    ->where('thoi_gian_het_han', '>', Carbon::now())
                    ->whereIn('trang_thai', ['dang_giu', 'holding'])
                    ->first();

                if ($existingHold) {
                    // Nếu cùng user/session -> Gia hạn thời gian
                    if (($userId && $existingHold->id_nguoi_dung == $userId) || 
                        ($sessionId && $existingHold->session_id == $sessionId)) {
                        
                        $existingHold->thoi_gian_het_han = Carbon::now()->addMinutes(self::HOLD_DURATION_MINUTES);
                        $existingHold->save();

                        Log::info('Seat hold extended', ['seat_id' => $seatId, 'user_id' => $userId]);

                        return [
                            'success' => true, 
                            'hold' => $existingHold, 
                            'message' => 'Ghế đã được gia hạn'
                        ];
                    } else {
                        return ['success' => false, 'hold' => null, 'message' => 'Ghế đang được người khác chọn'];
                    }
                }

                // 4. Tạo hold mới (Insert dùng key Tiếng Việt để tránh lỗi field default value)
                $hold = SeatHold::create([
                    'id_suat_chieu' => $showtimeId,
                    'id_ghe' => $seatId,
                    'id_nguoi_dung' => $userId,
                    'session_id' => $sessionId,
                    'gia_giu' => 0, 
                    'trang_thai' => 'dang_giu',
                    'thoi_gian_giu' => Carbon::now(),
                    'thoi_gian_het_han' => Carbon::now()->addMinutes(self::HOLD_DURATION_MINUTES),
                ]);

                Log::info('Seat held', ['seat_id' => $seatId, 'user_id' => $userId]);

                return [
                    'success' => true,
                    'hold' => $hold,
                    'message' => 'Ghế đã được giữ'
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error holding seat', [
                'showtime_id' => $showtimeId,
                'seat_id' => $seatId,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'hold' => null, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()];
        }
    }

    /**
     * Hold multiple seats at once
     */
    public function holdSeats(int $showtimeId, array $seatIds, ?int $userId = null, ?string $sessionId = null): array
    {
        $holds = [];
        $failedSeats = [];

        foreach ($seatIds as $seatId) {
            $result = $this->holdSeat($showtimeId, $seatId, $userId, $sessionId);
            
            if ($result['success']) {
                $holds[] = $result['hold'];
            } else {
                $failedSeats[] = [
                    'seat_id' => $seatId,
                    'message' => $result['message'] ?? 'Lỗi'
                ];
            }
        }

        // Nếu không giữ được ghế nào
        if (empty($holds)) {
            return [
                'success' => false,
                'holds' => [],
                'failed_seats' => $failedSeats,
                'message' => 'Không thể giữ ghế. Vui lòng thử lại.'
            ];
        }

        // Nếu giữ được một số ghế nhưng thất bại một số -> Rollback (nhả các ghế đã giữ)
        if (!empty($failedSeats)) {
            foreach ($holds as $hold) {
                // $hold->id_ghe vì dùng Model tiếng Việt
                $this->releaseSeat($showtimeId, $hold->id_ghe, $userId, $sessionId);
            }

            return [
                'success' => false,
                'holds' => [],
                'failed_seats' => $failedSeats,
                'message' => 'Một số ghế không thể giữ được (đã hủy toàn bộ yêu cầu)'
            ];
        }

        // Tạo booking_hold_id ảo để controller dùng
        $firstHold = $holds[0];
        $bookingHoldId = 'hold_' . $showtimeId . '_' . uniqid(); 

        return [
            'success' => true,
            'booking_hold_id' => $bookingHoldId, // Dữ liệu controller cần
            'hold_expires_at' => $firstHold->thoi_gian_het_han,
            'expires_in_seconds' => self::HOLD_DURATION_MINUTES * 60,
            'holds' => $holds,
            'failed_seats' => $failedSeats,
            'message' => 'Tất cả ghế đã được giữ thành công'
        ];
    }

    /**
     * Release a seat hold
     */
    public function releaseSeat(int $showtimeId, int $seatId, ?int $userId = null, ?string $sessionId = null): bool
    {
        try {
            // Query bằng tên cột Tiếng Việt
            $query = SeatHold::where('id_suat_chieu', $showtimeId)
                ->where('id_ghe', $seatId)
                ->whereIn('trang_thai', ['dang_giu', 'holding']);

            if ($userId) {
                $query->where('id_nguoi_dung', $userId);
            } elseif ($sessionId) {
                $query->where('session_id', $sessionId);
            }

            $deleted = $query->delete();

            if ($deleted > 0) {
                Log::info('Seat hold released', ['seat_id' => $seatId, 'user_id' => $userId]);
            }

            return $deleted > 0;
        } catch (\Exception $e) {
            Log::error('Error releasing seat hold', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Release multiple seats
     */
    public function releaseSeats(int $showtimeId, array $seatIds, ?int $userId = null, ?string $sessionId = null): int
    {
        $count = 0;
        foreach ($seatIds as $seatId) {
            if ($this->releaseSeat($showtimeId, $seatId, $userId, $sessionId)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Get seat hold status
     */
    public function getSeatStatus(int $showtimeId, int $seatId, ?int $currentUserId = null): string
    {
        // 1. Kiểm tra đã bán cứng chưa
        if ($this->isSeatBooked($showtimeId, $seatId)) {
            return 'booked';
        }

        // 2. Kiểm tra đang giữ (Tiếng Việt)
        $hold = SeatHold::where('id_suat_chieu', $showtimeId)
            ->where('id_ghe', $seatId)
            ->where('thoi_gian_het_han', '>', Carbon::now())
            ->whereIn('trang_thai', ['dang_giu', 'holding'])
            ->first();

        if ($hold) {
            if ($currentUserId && $hold->id_nguoi_dung == $currentUserId) {
                return 'held_by_me';
            }
            return 'held_by_other';
        }

        return 'available';
    }

    /**
     * Confirm booking: Release holds (called after payment success)
     */
    public function confirmBooking(int $showtimeId, array $seatIds, int $userId): bool
    {
        try {
            return $this->releaseSeats($showtimeId, $seatIds, $userId) > 0;
        } catch (\Exception $e) {
            Log::error('Error confirming booking', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Helper: Check if seat is booked (paid booking in chi_tiet_dat_ve)
     */
    private function isSeatBooked(int $showtimeId, int $seatId): bool
    {
        try {
            return \App\Models\ChiTietDatVe::whereHas('datVe', function($query) use ($showtimeId) {
                    $query->where('id_suat_chieu', $showtimeId)
                          ->where('trang_thai', 1); // 1 = Đã thanh toán
                })
                ->where('id_ghe', $seatId)
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    // --- CÁC HÀM BỔ SUNG (Đã map sang cột Tiếng Việt) ---

    /**
     * Get all holds for a showtime
     */
    public function getHoldsForShowtime(int $showtimeId)
    {
        return SeatHold::where('id_suat_chieu', $showtimeId)
            ->where('thoi_gian_het_han', '>', Carbon::now())
            ->whereIn('trang_thai', ['dang_giu', 'holding'])
            ->with(['seat', 'user'])
            ->get();
    }

    /**
     * Get holds for a user
     */
    public function getHoldsForUser(int $userId, ?int $showtimeId = null)
    {
        $query = SeatHold::where('id_nguoi_dung', $userId)
            ->where('thoi_gian_het_han', '>', Carbon::now())
            ->whereIn('trang_thai', ['dang_giu', 'holding']);
        
        if ($showtimeId) {
            $query->where('id_suat_chieu', $showtimeId);
        }
        
        return $query->with(['seat', 'showtime'])->get();
    }

    /**
     * Cleanup expired holds (called by cron job)
     */
    public function cleanupExpiredHolds(): int
    {
        return SeatHold::where('thoi_gian_het_han', '<=', Carbon::now())->delete();
    }
}