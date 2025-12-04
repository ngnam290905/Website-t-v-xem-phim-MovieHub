<?php

namespace App\Services;

use App\Models\SuatChieu;
use App\Models\Ghe;
use App\Models\ShowtimeSeat;
use App\Models\ChiTietDatVe;
use App\Models\DatVe;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShowtimeSeatService
{
    private const HOLD_DURATION_MINUTES = 5;

    /**
     * Load seat layout for a showtime
     * Creates ShowtimeSeat records if they don't exist
     */
    public function loadSeatLayout(SuatChieu $showtime)
    {
        $room = $showtime->phongChieu;
        if (!$room) {
            throw new \Exception('Room not found for showtime');
        }

        $seats = $room->seats()->orderBy('so_hang')->orderBy('so_ghe')->get();
        
        DB::beginTransaction();
        try {
            foreach ($seats as $seat) {
                ShowtimeSeat::firstOrCreate(
                    [
                        'id_suat_chieu' => $showtime->id,
                        'id_ghe' => $seat->id,
                    ],
                    [
                        'trang_thai' => $this->getInitialSeatStatus($seat),
                        'thoi_gian_giu' => Carbon::now(),
                    ]
                );
            }
            
            DB::commit();
            return $seats;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get initial seat status based on seat condition
     */
    private function getInitialSeatStatus(Ghe $seat)
    {
        // If seat is broken (trang_thai = 0), mark as blocked
        if ($seat->trang_thai == 0) {
            return 'blocked';
        }
        
        // Check if seat is already booked for this showtime
        // Note: This method is called during loadSeatLayout, so we need showtime context
        // For now, we'll check globally - actual showtime-specific check is in getSeatStatus
        $isBooked = false; // Will be checked properly in getSeatStatus with showtime context
        
        if ($isBooked) {
            return 'booked';
        }
        
        return 'available';
    }

    /**
     * Get seat status for a showtime
     */
    public function getSeatStatus(SuatChieu $showtime, Ghe $seat)
    {
        $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $showtime->id)
            ->where('id_ghe', $seat->id)
            ->first();
        
        if (!$showtimeSeat) {
            // Load layout if not exists
            $this->loadSeatLayout($showtime);
            $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $showtime->id)
                ->where('id_ghe', $seat->id)
                ->first();
        }
        
        if (!$showtimeSeat) {
            return 'unavailable';
        }
        
        // Check if hold expired
        if ($showtimeSeat->trang_thai === 'holding' && $showtimeSeat->thoi_gian_het_han) {
            if ($showtimeSeat->thoi_gian_het_han->isPast()) {
                $showtimeSeat->update([
                    'trang_thai' => 'available',
                    'thoi_gian_het_han' => null
                ]);
                return 'available';
            }
        }
        
        // Check if actually booked (double check with booking table)
        $isBooked = ChiTietDatVe::whereHas('datVe', function($query) use ($showtime) {
                $query->where('id_suat_chieu', $showtime->id)
                      ->where('trang_thai', 1); // Only paid
            })
            ->where('id_ghe', $seat->id)
            ->exists();
        
        if ($isBooked && $showtimeSeat->trang_thai !== 'booked') {
            $showtimeSeat->update(['trang_thai' => 'booked']);
            return 'booked';
        }
        
        return $showtimeSeat->trang_thai;
    }

    /**
     * Hold seats temporarily (3-5 minutes)
     */
    public function holdSeats(SuatChieu $showtime, array $seatIds, $userId = null)
    {
        $holdExpiresAt = Carbon::now()->addMinutes(self::HOLD_DURATION_MINUTES);
        $unavailableSeats = [];
        
        return DB::transaction(function () use ($showtime, $seatIds, $userId, $holdExpiresAt, &$unavailableSeats) {
            foreach ($seatIds as $seatId) {
                // Lock the seat row to prevent race condition
                $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $showtime->id)
                    ->where('id_ghe', $seatId)
                    ->lockForUpdate()
                    ->first();
                
                $seat = Ghe::findOrFail($seatId);
                
                // Check if seat is already booked
                if ($showtimeSeat && $showtimeSeat->isBooked()) {
                    $unavailableSeats[] = $seatId;
                    continue;
                }
                
                // Check if seat is held by another user (not expired)
                if ($showtimeSeat && $showtimeSeat->isHolding()) {
                    // Check if it's held by current user (allow refresh)
                    if ($userId && $showtimeSeat->id_nguoi_dung && $showtimeSeat->id_nguoi_dung != $userId) {
                        $unavailableSeats[] = $seatId;
                        continue;
                    }
                }
                
                // Check if seat is available
                $status = $this->getSeatStatus($showtime, $seat);
                
                if ($status !== 'available') {
                    $unavailableSeats[] = $seatId;
                    continue;
                }
                
                // Validate seat selection rules
                if (!$this->validateSeatSelection($showtime, $seat, $seatIds)) {
                    $unavailableSeats[] = $seatId;
                    continue;
                }
                
                // Hold the seat immediately with user tracking
                ShowtimeSeat::updateOrCreate(
                    [
                        'id_suat_chieu' => $showtime->id,
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
                return [
                    'success' => false,
                    'message' => 'Một số ghế không khả dụng',
                    'unavailable_seats' => $unavailableSeats
                ];
            }
            
            return [
                'success' => true,
                'hold_expires_at' => $holdExpiresAt,
                'expires_in_seconds' => self::HOLD_DURATION_MINUTES * 60
            ];
        }, 3); // Retry 3 times on deadlock
    }

    /**
     * Validate seat selection rules
     */
    private function validateSeatSelection(SuatChieu $showtime, Ghe $seat, array $allSelectedSeatIds)
    {
        // Rule 1: Check if seat is broken
        if ($seat->trang_thai == 0) {
            return false;
        }
        
        // Rule 2: Check if seat is already booked
        $isBooked = ChiTietDatVe::whereHas('datVe', function($query) use ($showtime) {
                $query->where('id_suat_chieu', $showtime->id)
                      ->where('trang_thai', 1);
            })
            ->where('id_ghe', $seat->id)
            ->exists();
        
        if ($isBooked) {
            return false;
        }
        
        // Rule 3: Check if seat is VIP and price validation (handled in booking flow)
        // This is a placeholder - actual price validation should be in booking controller
        
        // Rule 4: Check for isolated seat (don't allow leaving single empty seat between booked seats)
        // This is complex and may be optional - skipping for now
        
        return true;
    }

    /**
     * Book seats (after payment)
     */
    public function bookSeats(SuatChieu $showtime, array $seatIds, DatVe $booking)
    {
        DB::beginTransaction();
        try {
            foreach ($seatIds as $seatId) {
                ShowtimeSeat::updateOrCreate(
                    [
                        'id_suat_chieu' => $showtime->id,
                        'id_ghe' => $seatId,
                    ],
                    [
                        'trang_thai' => 'booked',
                        'thoi_gian_het_han' => null,
                    ]
                );
            }
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Release expired holds
     */
    public function releaseExpiredHolds(SuatChieu $showtime = null)
    {
        $query = ShowtimeSeat::where('trang_thai', 'holding')
            ->whereNotNull('thoi_gian_het_han')
            ->where('thoi_gian_het_han', '<', Carbon::now());
        
        if ($showtime) {
            $query->where('id_suat_chieu', $showtime->id);
        }
        
        return $query->update([
            'trang_thai' => 'available',
            'thoi_gian_het_han' => null
        ]);
    }

    /**
     * Get all seats with status for a showtime
     */
    public function getSeatsWithStatus(SuatChieu $showtime)
    {
        // Ensure layout is loaded
        $this->loadSeatLayout($showtime);
        
        // Release expired holds
        $this->releaseExpiredHolds($showtime);
        
        $room = $showtime->phongChieu;
        $seats = $room->seats()->orderBy('so_hang')->orderBy('so_ghe')->get();
        
        $seatsWithStatus = [];
        foreach ($seats as $seat) {
            $status = $this->getSeatStatus($showtime, $seat);
            $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $showtime->id)
                ->where('id_ghe', $seat->id)
                ->first();
            
            $seatsWithStatus[] = [
                'id' => $seat->id,
                'seat_code' => $seat->so_ghe,
                'row' => $seat->so_hang,
                'seat_type' => $seat->seatType ? $seat->seatType->ten_loai : 'normal',
                'status' => $status,
                'hold_expires_at' => $showtimeSeat ? $showtimeSeat->thoi_gian_het_han : null,
                'is_broken' => $seat->trang_thai == 0,
            ];
        }
        
        return $seatsWithStatus;
    }

    /**
     * Admin: Manually book seat (for counter booking)
     */
    public function adminBookSeat(SuatChieu $showtime, Ghe $seat)
    {
        $status = $this->getSeatStatus($showtime, $seat);
        
        if ($status === 'booked') {
            throw new \Exception('Seat is already booked');
        }
        
        ShowtimeSeat::updateOrCreate(
            [
                'id_suat_chieu' => $showtime->id,
                'id_ghe' => $seat->id,
            ],
            [
                'trang_thai' => 'booked',
                'thoi_gian_het_han' => null,
            ]
        );
        
        return true;
    }

    /**
     * Admin: Cancel seat booking
     */
    public function adminCancelSeat(SuatChieu $showtime, Ghe $seat)
    {
        $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $showtime->id)
            ->where('id_ghe', $seat->id)
            ->first();
        
        if ($showtimeSeat) {
            // Check if there's an actual booking
            $hasBooking = ChiTietDatVe::whereHas('datVe', function($query) use ($showtime) {
                    $query->where('id_suat_chieu', $showtime->id);
                })
                ->where('id_ghe', $seat->id)
                ->exists();
            
            if ($hasBooking) {
                throw new \Exception('Cannot cancel seat with active booking. Cancel booking first.');
            }
            
            $showtimeSeat->update([
                'trang_thai' => 'available',
                'thoi_gian_het_han' => null,
            ]);
        }
        
        return true;
    }

    /**
     * Admin: Transfer seat to another showtime
     */
    public function adminTransferSeat(SuatChieu $fromShowtime, SuatChieu $toShowtime, Ghe $seat)
    {
        // Check if seat is available in target showtime
        $targetStatus = $this->getSeatStatus($toShowtime, $seat);
        
        if ($targetStatus !== 'available') {
            throw new \Exception('Seat is not available in target showtime');
        }
        
        // Check if seat has booking in source showtime
        $hasBooking = ChiTietDatVe::whereHas('datVe', function($query) use ($fromShowtime) {
                $query->where('id_suat_chieu', $fromShowtime->id);
            })
            ->where('id_ghe', $seat->id)
            ->exists();
        
        if ($hasBooking) {
            throw new \Exception('Cannot transfer seat with active booking');
        }
        
        DB::beginTransaction();
        try {
            // Remove from source showtime
            ShowtimeSeat::where('id_suat_chieu', $fromShowtime->id)
                ->where('id_ghe', $seat->id)
                ->delete();
            
            // Add to target showtime
            ShowtimeSeat::updateOrCreate(
                [
                    'id_suat_chieu' => $toShowtime->id,
                    'id_ghe' => $seat->id,
                ],
                [
                    'trang_thai' => 'available',
                    'thoi_gian_giu' => Carbon::now(),
                ]
            );
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

