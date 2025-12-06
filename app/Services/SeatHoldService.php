<?php

namespace App\Services;

use App\Models\SeatHold;
use App\Models\Ghe;
use App\Models\SuatChieu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SeatHoldService
{
    private const HOLD_DURATION_MINUTES = 10;

    /**
     * Hold a seat for a user
     * 
     * @param int $showtimeId
     * @param int $seatId
     * @param int|null $userId
     * @param string|null $sessionId
     * @return array ['success' => bool, 'hold' => SeatHold|null, 'message' => string]
     */
    public function holdSeat(int $showtimeId, int $seatId, ?int $userId = null, ?string $sessionId = null): array
    {
        try {
            return DB::transaction(function () use ($showtimeId, $seatId, $userId, $sessionId) {
                // 1. Kiểm tra ghế có tồn tại và available không
                $seat = Ghe::find($seatId);
                if (!$seat) {
                    return [
                        'success' => false,
                        'hold' => null,
                        'message' => 'Ghế không tồn tại'
                    ];
                }

                // 2. Kiểm tra ghế đã được đặt (booked) chưa
                if ($this->isSeatBooked($showtimeId, $seatId)) {
                    return [
                        'success' => false,
                        'hold' => null,
                        'message' => 'Ghế đã được đặt'
                    ];
                }

                // 3. Kiểm tra ghế đang được giữ bởi người khác
                $existingHold = SeatHold::forShowtime($showtimeId)
                    ->forSeat($seatId)
                    ->active()
                    ->first();

                if ($existingHold) {
                    // Nếu cùng user/session → auto-extend
                    if (($userId && $existingHold->user_id == $userId) || 
                        ($sessionId && $existingHold->session_id == $sessionId)) {
                        // Gia hạn thời gian giữ
                        $existingHold->expires_at = Carbon::now()->addMinutes(self::HOLD_DURATION_MINUTES);
                        $existingHold->save();

                        Log::info('Seat hold extended', [
                            'showtime_id' => $showtimeId,
                            'seat_id' => $seatId,
                            'user_id' => $userId,
                            'expires_at' => $existingHold->expires_at
                        ]);

                        return [
                            'success' => true,
                            'hold' => $existingHold,
                            'message' => 'Ghế đã được gia hạn'
                        ];
                    } else {
                        // Người khác đang giữ
                        return [
                            'success' => false,
                            'hold' => null,
                            'message' => 'Ghế đang được người khác chọn'
                        ];
                    }
                }

                // 4. Tạo hold mới
                $hold = SeatHold::create([
                    'showtime_id' => $showtimeId,
                    'seat_id' => $seatId,
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                    'expires_at' => Carbon::now()->addMinutes(self::HOLD_DURATION_MINUTES),
                ]);

                Log::info('Seat held', [
                    'showtime_id' => $showtimeId,
                    'seat_id' => $seatId,
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                    'expires_at' => $hold->expires_at
                ]);

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

            return [
                'success' => false,
                'hold' => null,
                'message' => 'Có lỗi xảy ra khi giữ ghế: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Hold multiple seats at once
     * 
     * @param int $showtimeId
     * @param array $seatIds
     * @param int|null $userId
     * @param string|null $sessionId
     * @return array ['success' => bool, 'holds' => array, 'failed_seats' => array, 'message' => string]
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
                    'message' => $result['message']
                ];
            }
        }

        if (empty($holds)) {
            return [
                'success' => false,
                'holds' => [],
                'failed_seats' => $failedSeats,
                'message' => 'Không thể giữ bất kỳ ghế nào'
            ];
        }

        if (!empty($failedSeats)) {
            // Nếu một số ghế thất bại, release các ghế đã hold
            foreach ($holds as $hold) {
                $this->releaseSeat($showtimeId, $hold->seat_id, $userId, $sessionId);
            }

            return [
                'success' => false,
                'holds' => [],
                'failed_seats' => $failedSeats,
                'message' => 'Một số ghế không thể giữ được'
            ];
        }

        return [
            'success' => true,
            'holds' => $holds,
            'failed_seats' => [],
            'message' => 'Tất cả ghế đã được giữ thành công'
        ];
    }

    /**
     * Release a seat hold
     * 
     * @param int $showtimeId
     * @param int $seatId
     * @param int|null $userId Only release if held by this user
     * @param string|null $sessionId Only release if held by this session
     * @return bool
     */
    public function releaseSeat(int $showtimeId, int $seatId, ?int $userId = null, ?string $sessionId = null): bool
    {
        try {
            $query = SeatHold::forShowtime($showtimeId)
                ->forSeat($seatId)
                ->active();

            // Chỉ release nếu là của user/session này
            if ($userId) {
                $query->forUser($userId);
            } elseif ($sessionId) {
                $query->forSession($sessionId);
            }

            $deleted = $query->delete();

            if ($deleted > 0) {
                Log::info('Seat hold released', [
                    'showtime_id' => $showtimeId,
                    'seat_id' => $seatId,
                    'user_id' => $userId,
                    'session_id' => $sessionId
                ]);
            }

            return $deleted > 0;
        } catch (\Exception $e) {
            Log::error('Error releasing seat hold', [
                'showtime_id' => $showtimeId,
                'seat_id' => $seatId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Release multiple seats
     * 
     * @param int $showtimeId
     * @param array $seatIds
     * @param int|null $userId
     * @param string|null $sessionId
     * @return int Number of seats released
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
     * 
     * @param int $showtimeId
     * @param int $seatId
     * @param int|null $currentUserId
     * @return string 'available'|'held_by_me'|'held_by_other'|'booked'
     */
    public function getSeatStatus(int $showtimeId, int $seatId, ?int $currentUserId = null): string
    {
        // 1. Kiểm tra booked trước
        if ($this->isSeatBooked($showtimeId, $seatId)) {
            return 'booked';
        }

        // 2. Kiểm tra hold
        $hold = SeatHold::forShowtime($showtimeId)
            ->forSeat($seatId)
            ->active()
            ->first();

        if ($hold) {
            if ($currentUserId && $hold->user_id == $currentUserId) {
                return 'held_by_me';
            }
            return 'held_by_other';
        }

        return 'available';
    }

    /**
     * Get all holds for a showtime
     * 
     * @param int $showtimeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHoldsForShowtime(int $showtimeId)
    {
        return SeatHold::forShowtime($showtimeId)
            ->active()
            ->with(['seat', 'user'])
            ->get();
    }

    /**
     * Get holds for a user
     * 
     * @param int $userId
     * @param int|null $showtimeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getHoldsForUser(int $userId, ?int $showtimeId = null)
    {
        $query = SeatHold::forUser($userId)->active();
        
        if ($showtimeId) {
            $query->forShowtime($showtimeId);
        }
        
        return $query->with(['seat', 'showtime'])->get();
    }

    /**
     * Confirm booking: Convert holds to booked status
     * This should be called when payment succeeds
     * 
     * @param int $showtimeId
     * @param array $seatIds
     * @param int $userId
     * @return bool
     */
    public function confirmBooking(int $showtimeId, array $seatIds, int $userId): bool
    {
        try {
            // Release all holds for these seats (they will be marked as booked in booking_seats table)
            return $this->releaseSeats($showtimeId, $seatIds, $userId) > 0;
        } catch (\Exception $e) {
            Log::error('Error confirming booking', [
                'showtime_id' => $showtimeId,
                'seat_ids' => $seatIds,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Cleanup expired holds (called by cron job)
     * 
     * @return int Number of expired holds cleaned up
     */
    public function cleanupExpiredHolds(): int
    {
        return SeatHold::releaseExpired();
    }

    /**
     * Check if seat is booked (paid booking)
     * 
     * @param int $showtimeId
     * @param int $seatId
     * @return bool
     */
    private function isSeatBooked(int $showtimeId, int $seatId): bool
    {
        // Check in ChiTietDatVe - only PAID bookings (trang_thai = 1)
        try {
            $hasPaidBooking = \App\Models\ChiTietDatVe::whereHas('datVe', function($query) use ($showtimeId) {
                    $query->where('id_suat_chieu', $showtimeId)
                          ->where('trang_thai', 1); // Only PAID
                })
                ->where('id_ghe', $seatId)
                ->exists();
            
            return $hasPaidBooking;
        } catch (\Exception $e) {
            Log::warning('Error checking if seat is booked', [
                'showtime_id' => $showtimeId,
                'seat_id' => $seatId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
