<?php

namespace App\Services;

use App\Models\SeatLock;
use App\Models\Show;
use App\Models\Seat;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SeatLockService
{
    private const LOCK_DURATION_MINUTES = 10;

    public function cleanupExpiredLocks(): int
    {
        return SeatLock::where('expires_at', '<', now())->delete();
    }

    public function lockSeats(Show $show, array $seatIds, ?int $bookingId = null): array
    {
        $this->cleanupExpiredLocks();
        
        $locks = [];
        $expiresAt = Carbon::now()->addMinutes(self::LOCK_DURATION_MINUTES);
        
        DB::beginTransaction();
        try {
            foreach ($seatIds as $seatId) {
                $seat = Seat::findOrFail($seatId);
                
                if ($this->isSeatLocked($show->id, $seatId)) {
                    throw new \Exception("Seat {$seatId} is already locked");
                }
                
                $lock = SeatLock::create([
                    'show_id' => $show->id,
                    'seat_id' => $seatId,
                    'booking_id' => $bookingId,
                    'expires_at' => $expiresAt,
                ]);
                
                $locks[] = $lock;
            }
            
            DB::commit();
            return $locks;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function isSeatLocked(int $showId, int $seatId): bool
    {
        return SeatLock::where('show_id', $showId)
            ->where('seat_id', $seatId)
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function getSeatStatus(Show $show, Seat $seat): string
    {
        $soldBookingSeat = DB::table('booking_seats')
            ->join('bookings', 'booking_seats.booking_id', '=', 'bookings.id')
            ->where('bookings.show_id', $show->id)
            ->where('booking_seats.seat_id', $seat->id)
            ->where('booking_seats.status', 'SOLD')
            ->where('bookings.status', 'PAID')
            ->exists();
        
        if ($soldBookingSeat) {
            return 'SOLD';
        }
        
        if ($this->isSeatLocked($show->id, $seat->id)) {
            return 'LOCKED';
        }
        
        return 'AVAILABLE';
    }

    public function releaseLocksForBooking(Booking $booking): void
    {
        SeatLock::where('booking_id', $booking->id)->delete();
    }

    public function updateLocksWithBookingId(array $lockIds, int $bookingId): void
    {
        SeatLock::whereIn('id', $lockIds)
            ->update(['booking_id' => $bookingId]);
    }
}

