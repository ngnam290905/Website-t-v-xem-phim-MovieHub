<?php

namespace App\Mail;

use App\Models\DatVe;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(DatVe $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        $invoiceNumber = 'INV-' . str_pad($this->booking->id, 8, '0', STR_PAD_LEFT);
        return $this->subject("Hóa đơn điện tử #{$invoiceNumber}")
                    ->view('emails.invoice');
    }
}

