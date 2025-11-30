<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use App\Services\PaymentService;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private PaymentService $paymentService
    ) {}

    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'show_id' => 'required|exists:shows,id',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'exists:seats,id',
            'combo_items' => 'nullable|array',
            'combo_items.*.combo_id' => 'exists:combos,id',
            'combo_items.*.qty' => 'integer|min:1',
            'discount_rules' => 'nullable|array',
            'payment_provider' => 'required|in:momo,zalopay,vnpay',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $userId = auth()->id();
            $booking = $this->bookingService->createBooking(
                $request->show_id,
                $request->seat_ids,
                $userId,
                $request->combo_items ?? [],
                $request->discount_rules ?? []
            );

            $paymentUrl = $this->paymentService->createPaymentUrl($booking, $request->payment_provider);

            return response()->json([
                'success' => true,
                'data' => [
                    'booking' => [
                        'id' => $booking->id,
                        'status' => $booking->status,
                        'total' => $booking->total,
                        'lock_expires_at' => $booking->lock_expires_at->toIso8601String(),
                    ],
                    'payment_url' => $paymentUrl,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $booking = Booking::with([
            'show.movie',
            'show.room',
            'bookingSeats.seat',
            'bookingCombos.combo',
            'payment',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $booking->id,
                'status' => $booking->status,
                'show' => [
                    'id' => $booking->show->id,
                    'movie' => $booking->show->movie->title,
                    'room' => $booking->show->room->name,
                    'start_at' => $booking->show->start_at->toIso8601String(),
                ],
                'seats' => $booking->bookingSeats->map(function ($bs) {
                    return [
                        'id' => $bs->seat->id,
                        'row' => $bs->seat->row,
                        'number' => $bs->seat->number,
                        'type' => $bs->seat->type,
                        'price' => $bs->price,
                        'status' => $bs->status,
                    ];
                }),
                'combos' => $booking->bookingCombos->map(function ($bc) {
                    return [
                        'id' => $bc->combo->id,
                        'name' => $bc->combo->name,
                        'qty' => $bc->qty,
                        'unit_price' => $bc->unit_price,
                        'total_price' => $bc->total_price,
                    ];
                }),
                'pricing' => [
                    'subtotal' => $booking->subtotal,
                    'discount' => $booking->discount,
                    'total' => $booking->total,
                ],
                'payment' => $booking->payment ? [
                    'status' => $booking->payment->status,
                    'provider' => $booking->payment->provider,
                    'transaction_id' => $booking->payment->transaction_id,
                ] : null,
            ],
        ]);
    }
}

