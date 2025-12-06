<?php

namespace App\Mail;

use App\Models\DatVe;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $minutesRemaining;

    public function __construct(DatVe $booking, int $minutesRemaining = 5)
    {
        $this->booking = $booking;
        $this->minutesRemaining = $minutesRemaining;
    }

    public function build()
    {
        $movieName = $this->booking->suatChieu->phim->ten_phim ?? 'Vé xem phim';
        return $this->subject("⏰ Nhắc nhở thanh toán - {$movieName}")
                    ->view('emails.payment-reminder');
    }
}

