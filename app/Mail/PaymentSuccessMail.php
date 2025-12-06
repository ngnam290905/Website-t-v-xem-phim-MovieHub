<?php

namespace App\Mail;

use App\Models\DatVe;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $paymentData;

    public function __construct(DatVe $booking, array $paymentData = [])
    {
        $this->booking = $booking;
        $this->paymentData = $paymentData;
    }

    public function build()
    {
        $movieName = $this->booking->suatChieu->phim->ten_phim ?? 'Vé xem phim';
        return $this->subject("✅ Thanh toán thành công - {$movieName}")
                    ->view('emails.payment-success');
    }
}

