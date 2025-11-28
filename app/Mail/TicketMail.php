<?php

namespace App\Mail;

use App\Models\DatVe;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(DatVe $booking)
    {
        $this->booking = $booking->load([
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe',
            'chiTietCombo.combo',
            'thanhToan',
            'nguoiDung'
        ]);
    }

    public function build()
    {
        $movieName = $this->booking->suatChieu->phim->ten_phim ?? 'Vé xem phim';
        return $this->subject("Vé xem phim - {$movieName}")
                    ->view('emails.ticket');
    }
}
