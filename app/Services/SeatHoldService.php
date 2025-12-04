<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SeatHoldService
{
    private const HOLD_PREFIX = 'seat_hold';
    private const HOLD_TTL = 300; // 5 minutes in seconds
    private const BOOKING_HOLD_PREFIX = 'booking_hold';

    /**
     * Hold seats for a user
     * 
     * @param int $showtimeId
     * @param array $seatIds Array of seat IDs
     * @param int|null $userId
     * @return array ['success' => bool, 'hold_expires_at' => Carbon, 'booking_hold_id' => string]
     */
    public function holdSeats(int $showtimeId, array $seatIds, ?int $userId = null): array
    {
        $holdExpiresAt = Carbon::now()->addSeconds(self::HOLD_TTL);
        $bookingHoldId = uniqid('hold_', true);
        
        try {
            // Check Redis connection
            try {
                // Check if Redis class exists (PHP extension installed)
                if (!class_exists('Redis') && !interface_exists('Redis')) {
                    throw new \Exception('Redis PHP extension not installed');
                }
                
                // Try to ping Redis
                $redis = Redis::connection();
                $redis->ping();
            } catch (\Exception $e) {
                Log::warning('Redis unavailable, falling back to database', [
                    'error' => $e->getMessage()
                ]);
                
                // Fallback: Use database temporarily if Redis is not available
                // Note: This is not ideal for Beta standard but allows system to work
                return $this->holdSeatsFallback($showtimeId, $seatIds, $userId, $holdExpiresAt, $bookingHoldId);
            }
            
            // Check if any seat is already held by someone else or sold
            // Allow user to hold their own seats again (refresh hold time)
            $unavailableSeats = [];
            foreach ($seatIds as $seatId) {
                try {
                    $key = $this->getSeatHoldKey($showtimeId, $seatId);
                    $existingHold = Redis::get($key);
                    
                    if ($existingHold) {
                        $holdData = json_decode($existingHold, true);
                        // Check if hold is still valid (not expired)
                        if (isset($holdData['hold_expires_at'])) {
                            $expiresAt = Carbon::parse($holdData['hold_expires_at']);
                            if ($expiresAt->isFuture()) {
                                // Check if this hold belongs to current user
                                // If yes, allow refresh (will be overwritten below)
                                // If no, seat is held by someone else
                                if (!isset($holdData['user_id']) || $holdData['user_id'] != $userId) {
                                    $unavailableSeats[] = $seatId;
                                }
                                // If it's the same user, allow refresh (continue to hold again)
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning('Error checking seat hold in Redis', [
                        'showtime_id' => $showtimeId,
                        'seat_id' => $seatId,
                        'error' => $e->getMessage()
                    ]);
                    // Continue checking other seats
                }
                
                // Also check if seat is sold in DB (only check sold, not holding)
                if ($this->isSeatSold($showtimeId, $seatId)) {
                    $unavailableSeats[] = $seatId;
                }
            }
            
            if (!empty($unavailableSeats)) {
                return [
                    'success' => false,
                    'message' => 'Một hoặc nhiều ghế đã được đặt',
                    'unavailable_seats' => $unavailableSeats
                ];
            }
            
            // Hold all seats
            foreach ($seatIds as $seatId) {
                try {
                    $key = $this->getSeatHoldKey($showtimeId, $seatId);
                    $holdData = [
                        'user_id' => $userId,
                        'showtime_id' => $showtimeId,
                        'seat_id' => $seatId,
                        'hold_expires_at' => $holdExpiresAt->toIso8601String(),
                        'booking_hold_id' => $bookingHoldId,
                        'created_at' => Carbon::now()->toIso8601String()
                    ];
                    
                    Redis::setex($key, self::HOLD_TTL, json_encode($holdData));
                } catch (\Exception $e) {
                    Log::error('Error holding seat in Redis', [
                        'showtime_id' => $showtimeId,
                        'seat_id' => $seatId,
                        'error' => $e->getMessage()
                    ]);
                    throw $e; // Re-throw to rollback
                }
            }
            
            // Store booking hold info
            try {
                $bookingHoldKey = $this->getBookingHoldKey($bookingHoldId);
                $bookingHoldData = [
                    'showtime_id' => $showtimeId,
                    'seat_ids' => $seatIds,
                    'user_id' => $userId,
                    'hold_expires_at' => $holdExpiresAt->toIso8601String(),
                    'created_at' => Carbon::now()->toIso8601String()
                ];
                Redis::setex($bookingHoldKey, self::HOLD_TTL, json_encode($bookingHoldData));
            } catch (\Exception $e) {
                Log::error('Error storing booking hold in Redis', [
                    'booking_hold_id' => $bookingHoldId,
                    'error' => $e->getMessage()
                ]);
                // Release seats that were already held
                foreach ($seatIds as $seatId) {
                    try {
                        $key = $this->getSeatHoldKey($showtimeId, $seatId);
                        Redis::del($key);
                    } catch (\Exception $releaseError) {
                        // Ignore release errors
                    }
                }
                throw $e;
            }
            
            Log::info('Seats held in Redis', [
                'showtime_id' => $showtimeId,
                'seat_ids' => $seatIds,
                'user_id' => $userId,
                'booking_hold_id' => $bookingHoldId,
                'expires_at' => $holdExpiresAt->toIso8601String()
            ]);
            
            return [
                'success' => true,
                'hold_expires_at' => $holdExpiresAt,
                'booking_hold_id' => $bookingHoldId,
                'expires_in_seconds' => self::HOLD_TTL
            ];
        } catch (\Exception $e) {
            Log::error('Error holding seats in Redis', [
                'showtime_id' => $showtimeId,
                'seat_ids' => $seatIds,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi giữ ghế: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get seat hold status
     * 
     * @param int $showtimeId
     * @param int $seatId
     * @return array|null ['user_id' => int, 'hold_expires_at' => string, ...] or null if not held
     */
    public function getSeatHold(int $showtimeId, int $seatId): ?array
    {
        try {
            // Try Redis first (if available)
            if (class_exists('Redis') || interface_exists('Redis')) {
                try {
                    $key = $this->getSeatHoldKey($showtimeId, $seatId);
                    $holdData = Redis::get($key);
                    
                    if ($holdData) {
                        $data = json_decode($holdData, true);
                        
                        // Check if expired
                        if (isset($data['hold_expires_at'])) {
                            $expiresAt = Carbon::parse($data['hold_expires_at']);
                            if ($expiresAt->isPast()) {
                                // Auto cleanup expired hold
                                Redis::del($key);
                                return null;
                            }
                        }
                        
                        return $data;
                    }
                } catch (\Exception $e) {
                    // Redis unavailable, fallback to database
                    Log::debug('Redis unavailable, checking database for seat hold', [
                        'showtime_id' => $showtimeId,
                        'seat_id' => $seatId
                    ]);
                }
            }
            
            // Fallback: Check database
            try {
                $showtimeSeat = \App\Models\ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                    ->where('id_ghe', $seatId)
                    ->where('trang_thai', 'holding')
                    ->first();
                
                if ($showtimeSeat && $showtimeSeat->hold_expires_at && $showtimeSeat->hold_expires_at->isFuture()) {
                    return [
                        'user_id' => null, // Not stored in DB
                        'showtime_id' => $showtimeId,
                        'seat_id' => $seatId,
                        'hold_expires_at' => $showtimeSeat->hold_expires_at->toIso8601String(),
                        'created_at' => $showtimeSeat->created_at->toIso8601String()
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Error checking database for seat hold', [
                    'error' => $e->getMessage()
                ]);
            }
            
            return null;
        } catch (\Exception $e) {
            Log::error('Error getting seat hold', [
                'showtime_id' => $showtimeId,
                'seat_id' => $seatId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Get seat status (available, hold, sold, reserved)
     * 
     * @param int $showtimeId
     * @param int $seatId
     * @param int|null $currentUserId Check if hold belongs to current user
     * @return string 'available'|'hold'|'sold'|'reserved'
     */
    public function getSeatStatus(int $showtimeId, int $seatId, ?int $currentUserId = null): string
    {
        // Check if sold in DB
        if ($this->isSeatSold($showtimeId, $seatId)) {
            return 'sold';
        }
        
        // Check if reserved (staff booking, in DB)
        if ($this->isSeatReserved($showtimeId, $seatId)) {
            return 'reserved';
        }
        
        // Check if held in Redis
        $hold = $this->getSeatHold($showtimeId, $seatId);
        if ($hold) {
            // If current user is checking and it's their hold, still show as hold
            // Otherwise, show as unavailable
            if ($currentUserId && isset($hold['user_id']) && $hold['user_id'] == $currentUserId) {
                return 'hold';
            }
            // Someone else is holding it
            return 'hold';
        }
        
        return 'available';
    }
    
    /**
     * Release seat hold
     * 
     * @param int $showtimeId
     * @param int $seatId
     * @param int|null $userId Only release if held by this user
     * @return bool
     */
    public function releaseSeat(int $showtimeId, int $seatId, ?int $userId = null): bool
    {
        try {
            // Try Redis first (if available)
            if (class_exists('Redis') || interface_exists('Redis')) {
                try {
                    $key = $this->getSeatHoldKey($showtimeId, $seatId);
                    $holdData = Redis::get($key);
                    
                    if ($holdData) {
                        $hold = json_decode($holdData, true);
                        
                        // Check if user has permission to release
                        if ($userId !== null && isset($hold['user_id']) && $hold['user_id'] != $userId) {
                            return false; // Not held by this user
                        }
                        
                        Redis::del($key);
                        Log::info('Seat hold released from Redis', [
                            'showtime_id' => $showtimeId,
                            'seat_id' => $seatId,
                            'user_id' => $userId
                        ]);
                        return true;
                    }
                } catch (\Exception $e) {
                    // Redis unavailable, fallback to database
                    Log::debug('Redis unavailable, releasing from database', [
                        'showtime_id' => $showtimeId,
                        'seat_id' => $seatId
                    ]);
                }
            }
            
            // Fallback: Release from database
            try {
                $showtimeSeat = \App\Models\ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                    ->where('id_ghe', $seatId)
                    ->where('trang_thai', 'holding')
                    ->first();
                
                if ($showtimeSeat) {
                    $showtimeSeat->update([
                        'status' => 'available',
                        'hold_expires_at' => null
                    ]);
                    Log::info('Seat hold released from database', [
                        'showtime_id' => $showtimeId,
                        'seat_id' => $seatId,
                        'user_id' => $userId
                    ]);
                    return true;
                }
            } catch (\Exception $e) {
                Log::warning('Error releasing seat hold from database', [
                    'error' => $e->getMessage()
                ]);
            }
            
            return false;
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
     * Release all seats for a booking hold
     * 
     * @param string $bookingHoldId
     * @return bool
     */
    public function releaseBookingHold(string $bookingHoldId): bool
    {
        try {
            $showtimeId = null;
            $seatIds = [];
            
            // Try Redis first (if available)
            if (class_exists('Redis') || interface_exists('Redis')) {
                try {
                    $bookingHoldKey = $this->getBookingHoldKey($bookingHoldId);
                    $bookingHoldData = Redis::get($bookingHoldKey);
                    
                    if ($bookingHoldData) {
                        $data = json_decode($bookingHoldData, true);
                        $showtimeId = $data['showtime_id'] ?? null;
                        $seatIds = $data['seat_ids'] ?? [];
                        
                        // Delete booking hold from Redis
                        Redis::del($bookingHoldKey);
                    }
                } catch (\Exception $e) {
                    // Redis unavailable, try to find in database
                    Log::debug('Redis unavailable, checking database for booking hold', [
                        'booking_hold_id' => $bookingHoldId
                    ]);
                }
            }
            
            // If not found in Redis, try to find by showtime and release all holding seats
            // (This is a fallback - in ideal case, booking_hold_id should be stored)
            if (empty($seatIds)) {
                // Try to release all holding seats for the showtime
                // Note: This is less precise but works as fallback
                Log::warning('Booking hold ID not found, cannot release specific holds', [
                    'booking_hold_id' => $bookingHoldId
                ]);
                return false;
            }
            
            // Release all seats
            foreach ($seatIds as $seatId) {
                if ($showtimeId) {
                    $this->releaseSeat($showtimeId, $seatId);
                }
            }
            
            Log::info('Booking hold released', [
                'booking_hold_id' => $bookingHoldId,
                'showtime_id' => $showtimeId,
                'seat_ids' => $seatIds
            ]);
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error releasing booking hold', [
                'booking_hold_id' => $bookingHoldId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get booking hold data
     * 
     * @param string $bookingHoldId
     * @return array|null
     */
    public function getBookingHold(string $bookingHoldId): ?array
    {
        try {
            // Try Redis first (if available)
            if (class_exists('Redis') || interface_exists('Redis')) {
                try {
                    $key = $this->getBookingHoldKey($bookingHoldId);
                    $data = Redis::get($key);
                    
                    if ($data) {
                        $holdData = json_decode($data, true);
                        
                        // Check if expired
                        if (isset($holdData['hold_expires_at'])) {
                            $expiresAt = Carbon::parse($holdData['hold_expires_at']);
                            if ($expiresAt->isPast()) {
                                // Auto cleanup
                                Redis::del($key);
                                return null;
                            }
                        }
                        
                        return $holdData;
                    }
                } catch (\Exception $e) {
                    // Redis unavailable
                    Log::debug('Redis unavailable for getting booking hold', [
                        'booking_hold_id' => $bookingHoldId
                    ]);
                }
            }
            
            // Fallback: Cannot get from database as booking_hold_id is not stored there
            // This is a limitation of fallback mode
            return null;
        } catch (\Exception $e) {
            Log::error('Error getting booking hold', [
                'booking_hold_id' => $bookingHoldId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Check if seat is sold (in DB)
     * Only check for PAID bookings (trang_thai = 1), ignore pending (trang_thai = 0)
     * 
     * @param int $showtimeId
     * @param int $seatId
     * @return bool
     */
    private function isSeatSold(int $showtimeId, int $seatId): bool
    {
        try {
            // Check in ShowtimeSeat table - only booked status (sold)
            try {
                $showtimeSeat = \App\Models\ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                    ->where('id_ghe', $seatId)
                    ->where('trang_thai', 'booked')
                    ->first();
                
                if ($showtimeSeat) {
                    return true;
                }
            } catch (\Exception $e) {
                // Table might not exist, continue to next check
                Log::debug('ShowtimeSeat table check skipped', ['error' => $e->getMessage()]);
            }
            
            // Also check in chiTietDatVe - ONLY PAID bookings (trang_thai = 1)
            // Ignore pending bookings (trang_thai = 0)
            try {
                $hasPaidBooking = \App\Models\ChiTietDatVe::whereHas('datVe', function($query) use ($showtimeId) {
                        $query->where('id_suat_chieu', $showtimeId)
                              ->where('trang_thai', 1); // Only PAID, ignore pending (0)
                    })
                    ->where('id_ghe', $seatId)
                    ->exists();
                
                return $hasPaidBooking;
            } catch (\Exception $e) {
                Log::warning('Error checking ChiTietDatVe for sold seat', [
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::warning('Error checking if seat is sold', [
                'showtime_id' => $showtimeId,
                'seat_id' => $seatId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Check if seat is reserved (staff booking, in DB)
     * 
     * @param int $showtimeId
     * @param int $seatId
     * @return bool
     */
    private function isSeatReserved(int $showtimeId, int $seatId): bool
    {
        try {
            // Check in ShowtimeSeat table for reserved status
            $showtimeSeat = \App\Models\ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                ->where('id_ghe', $seatId)
                ->where('status', 'reserved')
                ->first();
            
            return $showtimeSeat !== null;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get Redis key for seat hold
     * 
     * @param int $showtimeId
     * @param int $seatId
     * @return string
     */
    private function getSeatHoldKey(int $showtimeId, int $seatId): string
    {
        return self::HOLD_PREFIX . ":{$showtimeId}:{$seatId}";
    }
    
    /**
     * Get Redis key for booking hold
     * 
     * @param string $bookingHoldId
     * @return string
     */
    private function getBookingHoldKey(string $bookingHoldId): string
    {
        return self::BOOKING_HOLD_PREFIX . ":{$bookingHoldId}";
    }
    
    /**
     * Cleanup expired holds (can be called by cron job)
     * 
     * @return int Number of cleaned holds
     */
    public function cleanupExpiredHolds(): int
    {
        // Redis TTL handles expiration automatically
        // This method is for manual cleanup if needed
        return 0;
    }
    
    /**
     * Fallback method: Hold seats in database if Redis is unavailable
     * This is NOT ideal for Beta standard but allows system to work
     * 
     * @param int $showtimeId
     * @param array $seatIds
     * @param int|null $userId
     * @param Carbon $holdExpiresAt
     * @param string $bookingHoldId
     * @return array
     */
    private function holdSeatsFallback(int $showtimeId, array $seatIds, ?int $userId, Carbon $holdExpiresAt, string $bookingHoldId): array
    {
        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($showtimeId, $seatIds, $userId, $holdExpiresAt, $bookingHoldId) {
            $unavailableSeats = [];
                
                // Check and hold seats with row lock to prevent race condition
            foreach ($seatIds as $seatId) {
                    // Lock the seat row to prevent concurrent access
                    $showtimeSeat = \App\Models\ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                        ->where('id_ghe', $seatId)
                        ->lockForUpdate()
                        ->first();
                    
                    // Check if seat is sold (booked)
                    if ($showtimeSeat && $showtimeSeat->isBooked()) {
                        $unavailableSeats[] = $seatId;
                        continue;
                    }
                    
                    // Check if seat is already held by another user (not expired)
                    if ($showtimeSeat && $showtimeSeat->isHolding()) {
                        // Check if it's held by current user (allow refresh)
                        if ($userId && $showtimeSeat->id_nguoi_dung && $showtimeSeat->id_nguoi_dung != $userId) {
                            $unavailableSeats[] = $seatId;
                            continue;
                        }
                        // If same user or no user tracking, allow refresh
                    }
                    
                    // Also check if seat is sold in ChiTietDatVe
                    if ($this->isSeatSold($showtimeId, $seatId)) {
                        $unavailableSeats[] = $seatId;
                        continue;
            }
            
                    // Hold the seat immediately
                \App\Models\ShowtimeSeat::updateOrCreate(
                    [
                        'id_suat_chieu' => $showtimeId,
                        'id_ghe' => $seatId,
                    ],
                    [
                        'trang_thai' => 'holding',
                            'id_nguoi_dung' => $userId,
                        'thoi_gian_giu' => Carbon::now(),
                        'thoi_gian_het_han' => $holdExpiresAt,
                    ]
                );
            }
                
                if (!empty($unavailableSeats)) {
                    \Illuminate\Support\Facades\DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Một hoặc nhiều ghế đã được đặt',
                        'unavailable_seats' => $unavailableSeats
                    ];
                }
            
            Log::info('Seats held in database (fallback mode)', [
                'showtime_id' => $showtimeId,
                'seat_ids' => $seatIds,
                'user_id' => $userId,
                'booking_hold_id' => $bookingHoldId,
                'expires_at' => $holdExpiresAt->toIso8601String()
            ]);
            
            return [
                'success' => true,
                'hold_expires_at' => $holdExpiresAt,
                'booking_hold_id' => $bookingHoldId,
                'expires_in_seconds' => self::HOLD_TTL,
                'fallback_mode' => true // Indicate this is fallback mode
            ];
            });
        } catch (\Exception $e) {
            Log::error('Error in holdSeatsFallback', [
                'showtime_id' => $showtimeId,
                'seat_ids' => $seatIds,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi giữ ghế: ' . $e->getMessage()
            ];
        }
    }
}

