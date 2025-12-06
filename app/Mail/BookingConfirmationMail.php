<?php

namespace App\Mail;

use App\Models\DatVe;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(DatVe $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        $movieName = $this->booking->suatChieu->phim->ten_phim ?? 'Vé xem phim';
        return $this->subject("Xác nhận đặt vé - {$movieName}")
                    ->view('emails.booking-confirmation');
    }
}

