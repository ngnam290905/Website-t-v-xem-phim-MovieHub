<?php

namespace App\Services;

use App\Models\SuatChieu;
use App\Models\Ghe;
use App\Models\DatVe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class LegacySeatLockService
{
    private const LOCK_DURATION_MINUTES = 5;
    private const CACHE_PREFIX = 'seat_lock';

    /**
     * Cleanup expired locks from both database and cache
     */
    public function cleanupExpiredLocks(): int
    {
        $deleted = 0;
        
        // Cleanup from database (if using seat_locks table)
        try {
            $deleted = DB::table('seat_locks')
                ->where('expires_at', '<', now())
                ->delete();
        } catch (\Exception $e) {
            // Table might not exist, ignore
        }
        
        // Cleanup from cache
        // Note: Cache will auto-expire, but we can manually clean if needed
        
        return $deleted;
    }

    /**
     * Lock seats for a showtime
     * Uses both database (if available) and cache for reliability
     */
    public function lockSeats(int $showId, array $seatIds, ?int $userId = null, ?int $bookingId = null): array
    {
        $this->cleanupExpiredLocks();
        
        $locks = [];
        $expiresAt = Carbon::now()->addMinutes(self::LOCK_DURATION_MINUTES);
        $expiresAtTimestamp = $expiresAt->timestamp;
        
        DB::beginTransaction();
        try {
            foreach ($seatIds as $seatId) {
                // Check if seat is already locked
                if ($this->isSeatLocked($showId, $seatId, $userId)) {
                    throw new \Exception("Seat {$seatId} is already locked");
                }
                
                // Lock in cache
                $cacheKey = $this->getCacheKey($showId, $seatId);
                Cache::put($cacheKey, [
                    'user_id' => $userId,
                    'booking_id' => $bookingId,
                    'locked_at' => now()->timestamp,
                    'expires_at' => $expiresAtTimestamp
                ], self::LOCK_DURATION_MINUTES * 60);
                
                // Try to lock in database (if seat_locks table exists and compatible)
                try {
                    // Map to new system IDs if needed
                    // For now, we'll use cache as primary and DB as backup
                    DB::table('seat_locks')->insert([
                        'show_id' => $showId,
                        'seat_id' => $seatId,
                        'booking_id' => $bookingId,
                        'expires_at' => $expiresAt,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    // Table might not exist or have different structure, continue with cache only
                }
                
                $locks[] = [
                    'show_id' => $showId,
                    'seat_id' => $seatId,
                    'user_id' => $userId,
                    'booking_id' => $bookingId,
                    'expires_at' => $expiresAtTimestamp
                ];
            }
            
            DB::commit();
            return $locks;
        } catch (\Exception $e) {
            DB::rollBack();
            // Release any locks that were created
            foreach ($seatIds as $seatId) {
                $this->unlockSeat($showId, $seatId, $userId);
            }
            throw $e;
        }
    }

    /**
     * Check if a seat is locked
     */
    public function isSeatLocked(int $showId, int $seatId, ?int $userId = null): bool
    {
        // Check cache first (faster)
        $cacheKey = $this->getCacheKey($showId, $seatId);
        $lock = Cache::get($cacheKey);
        
        if ($lock && isset($lock['expires_at']) && $lock['expires_at'] > now()->timestamp) {
            // If userId provided, only consider it locked if locked by someone else
            if ($userId !== null && isset($lock['user_id']) && $lock['user_id'] == $userId) {
                return false; // Not locked for this user
            }
            return true;
        }
        
        // Cleanup expired cache
        if ($lock) {
            Cache::forget($cacheKey);
        }
        
        // Check database as backup
        try {
            return DB::table('seat_locks')
                ->where('show_id', $showId)
                ->where('seat_id', $seatId)
                ->where('expires_at', '>', now())
                ->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get seat status for a showtime
     */
    public function getSeatStatus(int $showId, int $seatId, ?int $userId = null): string
    {
        // Check if seat is sold (booked and paid)
        // trang_thai: 0 = DRAFT, 1 = PAID/CONFIRMED, 2 = CANCELLED
        $sold = DB::table('chi_tiet_dat_ve as ctdv')
            ->join('dat_ve as dv', 'ctdv.id_dat_ve', '=', 'dv.id')
            ->where('dv.id_suat_chieu', $showId)
            ->where('ctdv.id_ghe', $seatId)
            ->where('dv.trang_thai', 1) // 1 = PAID/CONFIRMED
            ->exists();
        
        if ($sold) {
            return 'SOLD';
        }
        
        // Check if locked
        if ($this->isSeatLocked($showId, $seatId, $userId)) {
            $cacheKey = $this->getCacheKey($showId, $seatId);
            $lock = Cache::get($cacheKey);
            
            if ($lock && isset($lock['user_id'])) {
                if ($userId !== null && $lock['user_id'] == $userId) {
                    return 'LOCKED_BY_ME';
                }
                return 'LOCKED_BY_OTHER';
            }
            return 'LOCKED';
        }
        
        return 'AVAILABLE';
    }

    /**
     * Unlock a seat
     */
    public function unlockSeat(int $showId, int $seatId, ?int $userId = null): bool
    {
        $cacheKey = $this->getCacheKey($showId, $seatId);
        $lock = Cache::get($cacheKey);
        
        // Only unlock if it's locked by this user or no user specified
        if ($lock) {
            if ($userId === null || (isset($lock['user_id']) && $lock['user_id'] == $userId)) {
                Cache::forget($cacheKey);
                
                // Also remove from database
                try {
                    DB::table('seat_locks')
                        ->where('show_id', $showId)
                        ->where('seat_id', $seatId)
                        ->delete();
                } catch (\Exception $e) {
                    // Ignore if table doesn't exist
                }
                
                return true;
            }
        }
        
        return false;
    }

    /**
     * Unlock multiple seats
     */
    public function unlockSeats(int $showId, array $seatIds, ?int $userId = null): int
    {
        $unlocked = 0;
        foreach ($seatIds as $seatId) {
            if ($this->unlockSeat($showId, $seatId, $userId)) {
                $unlocked++;
            }
        }
        return $unlocked;
    }

    /**
     * Release all locks for a booking
     */
    public function releaseLocksForBooking(int $bookingId): int
    {
        $released = 0;
        
        // Get all seat IDs for this booking
        $seatIds = DB::table('chi_tiet_dat_ve')
            ->where('id_dat_ve', $bookingId)
            ->pluck('id_ghe')
            ->toArray();
        
        if (empty($seatIds)) {
            return 0;
        }
        
        // Get showtime ID
        $showId = DB::table('dat_ve')
            ->where('id', $bookingId)
            ->value('id_suat_chieu');
        
        if (!$showId) {
            return 0;
        }
        
        // Release from cache
        foreach ($seatIds as $seatId) {
            $cacheKey = $this->getCacheKey($showId, $seatId);
            if (Cache::has($cacheKey)) {
                Cache::forget($cacheKey);
                $released++;
            }
        }
        
        // Release from database
        try {
            $deleted = DB::table('seat_locks')
                ->where('booking_id', $bookingId)
                ->delete();
            $released += $deleted;
        } catch (\Exception $e) {
            // Ignore if table doesn't exist
        }
        
        return $released;
    }

    /**
     * Get all locked seats for a showtime
     */
    public function getLockedSeats(int $showId): array
    {
        $locked = [];
        
        // Get from cache
        // Note: We can't easily iterate all cache keys, so we'll rely on database or
        // get seat IDs from the showtime's room
        
        // Get from database as source of truth
        try {
            $locks = DB::table('seat_locks')
                ->where('show_id', $showId)
                ->where('expires_at', '>', now())
                ->get();
            
            foreach ($locks as $lock) {
                $locked[$lock->seat_id] = [
                    'user_id' => null, // Database doesn't store user_id
                    'booking_id' => $lock->booking_id,
                    'expires_at' => Carbon::parse($lock->expires_at)->timestamp
                ];
            }
        } catch (\Exception $e) {
            // Table might not exist
        }
        
        return $locked;
    }

    /**
     * Update locks with booking ID
     */
    public function updateLocksWithBookingId(int $showId, array $seatIds, int $bookingId): void
    {
        // Update cache
        foreach ($seatIds as $seatId) {
            $cacheKey = $this->getCacheKey($showId, $seatId);
            $lock = Cache::get($cacheKey);
            
            if ($lock) {
                $lock['booking_id'] = $bookingId;
                $expiresIn = isset($lock['expires_at']) ? max(0, $lock['expires_at'] - now()->timestamp) : self::LOCK_DURATION_MINUTES * 60;
                Cache::put($cacheKey, $lock, $expiresIn);
            }
        }
        
        // Update database
        try {
            DB::table('seat_locks')
                ->where('show_id', $showId)
                ->whereIn('seat_id', $seatIds)
                ->update(['booking_id' => $bookingId]);
        } catch (\Exception $e) {
            // Ignore if table doesn't exist
        }
    }

    /**
     * Get cache key for a seat lock
     */
    private function getCacheKey(int $showId, int $seatId): string
    {
        return self::CACHE_PREFIX . ":{$showId}:{$seatId}";
    }
}

