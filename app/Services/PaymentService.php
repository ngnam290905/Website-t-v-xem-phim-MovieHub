<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Booking;
use App\Models\BookingSeat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct(
        private SeatLockService $seatLockService
    ) {}

    public function processWebhook(
        string $transactionId,
        string $status,
        string $provider,
        array $payload = []
    ): Payment {
        DB::beginTransaction();
        try {
            $payment = Payment::where(function ($query) use ($transactionId, $provider) {
                $query->where('transaction_id', $transactionId)
                    ->orWhere(function ($q) use ($transactionId, $provider) {
                        $q->whereNull('transaction_id')
                          ->where('provider', $provider)
                          ->whereHas('booking', function ($bq) use ($transactionId) {
                              $bq->where('payment_ref', $transactionId);
                          });
                    });
            })->lockForUpdate()->first();
            
            if ($payment && $payment->status === 'SUCCESS') {
                DB::commit();
                return $payment;
            }
            
            if (!$payment) {
                $bookingId = $payload['booking_id'] ?? null;
                if ($bookingId) {
                    $payment = Payment::where('booking_id', $bookingId)
                        ->where('provider', $provider)
                        ->lockForUpdate()
                        ->first();
                }
            }
            
            if (!$payment) {
                throw new \Exception("Payment not found for transaction_id: {$transactionId}");
            }
            
            $booking = Booking::lockForUpdate()->findOrFail($payment->booking_id);
            
            if ($status === 'SUCCESS') {
                $this->markPaymentSuccess($payment, $booking, $provider, $transactionId, $payload);
            } else {
                $this->markPaymentFailed($payment, $provider, $transactionId, $payload);
            }
            
            DB::commit();
            return $payment->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment webhook error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function markPaymentSuccess(Payment $payment, Booking $booking, string $provider, string $transactionId, array $payload): void
    {
        if ($booking->status !== 'LOCKED') {
            throw new \Exception("Booking {$booking->id} is not in LOCKED status");
        }
        
        $payment->update([
            'status' => 'SUCCESS',
            'provider' => $provider,
            'transaction_id' => $transactionId,
            'paid_at' => now(),
            'payload' => $payload,
        ]);
        
        BookingSeat::where('booking_id', $booking->id)
            ->where('status', 'LOCKED')
            ->update(['status' => 'SOLD']);
        
        $this->seatLockService->releaseLocksForBooking($booking);
        
        $booking->update([
            'status' => 'PAID',
            'payment_provider' => $provider,
            'payment_ref' => $transactionId,
        ]);
    }

    private function markPaymentFailed(Payment $payment, string $provider, string $transactionId, array $payload): void
    {
        $payment->update([
            'status' => 'FAIL',
            'provider' => $provider,
            'transaction_id' => $transactionId,
            'payload' => $payload,
        ]);
    }

    public function createPaymentUrl(Booking $booking, string $provider): string
    {
        $payment = $booking->payment;
        if (!$payment) {
            throw new \Exception("Payment not found for booking {$booking->id}");
        }
        
        $baseUrl = config('app.url');
        $returnUrl = "{$baseUrl}/api/payments/return";
        $webhookUrl = "{$baseUrl}/api/payments/webhook";
        
        return match($provider) {
            'momo' => $this->createMoMoUrl($booking, $payment, $returnUrl, $webhookUrl),
            'zalopay' => $this->createZaloPayUrl($booking, $payment, $returnUrl, $webhookUrl),
            'vnpay' => $this->createVNPayUrl($booking, $payment, $returnUrl, $webhookUrl),
            default => throw new \Exception("Unsupported payment provider: {$provider}"),
        };
    }

    private function createMoMoUrl(Booking $booking, Payment $payment, string $returnUrl, string $webhookUrl): string
    {
        return "{$returnUrl}?provider=momo&booking_id={$booking->id}";
    }

    private function createZaloPayUrl(Booking $booking, Payment $payment, string $returnUrl, string $webhookUrl): string
    {
        return "{$returnUrl}?provider=zalopay&booking_id={$booking->id}";
    }

    private function createVNPayUrl(Booking $booking, Payment $payment, string $returnUrl, string $webhookUrl): string
    {
        return "{$returnUrl}?provider=vnpay&booking_id={$booking->id}";
    }
}

