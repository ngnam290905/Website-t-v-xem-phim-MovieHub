<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Show;
use App\Models\Seat;
use App\Models\Combo;
use App\Models\BookingSeat;
use App\Models\BookingCombo;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingService
{
    public function __construct(
        private PricingService $pricingService
    ) {
        // SeatLockService removed - using SeatHoldService instead
    }

    public function createBooking(
        int $showId,
        array $seatIds,
        ?int $userId = null,
        array $comboItems = [],
        array $discountRules = []
    ): Booking {
        $show = Show::findOrFail($showId);
        
        DB::beginTransaction();
        try {
            // NOTE: SeatLockService removed - using SeatHoldService for main booking flow
            // This method may need to be updated if used by API controllers
            // For now, we'll skip seat locking in this legacy method
            
            $seatPrices = [];
            foreach ($seatIds as $seatId) {
                $seat = Seat::findOrFail($seatId);
                $seatPrices[] = $this->pricingService->calculateSeatPrice($show, $seat);
            }
            
            $subtotal = $this->pricingService->calculateBookingSubtotal($seatPrices, $comboItems);
            $discount = $this->pricingService->calculateDiscount($subtotal, $discountRules);
            $total = $this->pricingService->calculateTotal($subtotal, $discount);
            
            $lockExpiresAt = Carbon::now()->addMinutes(10); // 10 minutes like new system
            
            $booking = Booking::create([
                'user_id' => $userId,
                'show_id' => $showId,
                'status' => 'LOCKED',
                'lock_expires_at' => $lockExpiresAt,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
            ]);
            
            // NOTE: Seat lock update removed - this method may need refactoring
            
            foreach ($seatIds as $index => $seatId) {
                BookingSeat::create([
                    'booking_id' => $booking->id,
                    'seat_id' => $seatId,
                    'price' => $seatPrices[$index],
                    'status' => 'LOCKED',
                ]);
            }
            
            foreach ($comboItems as $item) {
                $combo = Combo::find($item['combo_id']);
                if ($combo && $combo->is_active) {
                    $qty = $item['qty'] ?? 1;
                    BookingCombo::create([
                        'booking_id' => $booking->id,
                        'combo_id' => $combo->id,
                        'unit_price' => $combo->price,
                        'qty' => $qty,
                        'total_price' => $combo->price * $qty,
                    ]);
                }
            }
            
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'amount' => $total,
                'status' => 'INIT',
                'provider' => '',
            ]);
            
            DB::commit();
            return $booking->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function getSeatMap(Show $show): array
    {
        $room = $show->room;
        $seats = $room->seats()->orderBy('row')->orderBy('number')->get();
        
        $seatMap = [];
        foreach ($seats as $seat) {
            $status = $this->seatLockService->getSeatStatus($show, $seat);
            $seatMap[] = [
                'id' => $seat->id,
                'row' => $seat->row,
                'number' => $seat->number,
                'type' => $seat->type,
                'status' => $status,
                'price' => $this->pricingService->calculateSeatPrice($show, $seat),
            ];
        }
        
        return $seatMap;
    }

    public function expireOldBookings(): int
    {
        return Booking::where('status', 'LOCKED')
            ->where('lock_expires_at', '<', now())
            ->update(['status' => 'EXPIRED']);
    }
}

