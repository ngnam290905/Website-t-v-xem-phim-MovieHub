<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function webhook(Request $request): JsonResponse
    {
        $validator = \Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'status' => 'required|in:SUCCESS,FAIL',
            'provider' => 'required|in:momo,zalopay,vnpay',
            'payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payment = $this->paymentService->processWebhook(
                $request->transaction_id,
                $request->status,
                $request->provider,
                $request->payload ?? []
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_id' => $payment->id,
                    'booking_id' => $payment->booking_id,
                    'status' => $payment->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function return(Request $request): JsonResponse
    {
        $bookingId = $request->query('booking_id');
        $provider = $request->query('provider');

        if (!$bookingId || !$provider) {
            return response()->json([
                'success' => false,
                'message' => 'Missing booking_id or provider',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment processed. Please check booking status.',
            'booking_id' => $bookingId,
        ]);
    }
}

