<?php

namespace App\Services;

use App\Models\DatVe;
use App\Mail\PaymentSuccessMail;
use App\Mail\InvoiceMail;
use App\Mail\TicketMail;
use App\Mail\BookingConfirmationMail;
use App\Mail\PaymentReminderMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send payment success email
     * Gửi email xác nhận thanh toán thành công
     * 
     * @param DatVe $booking
     * @param array $paymentData Additional payment information
     * @return bool
     */
    public function sendPaymentSuccessEmail(DatVe $booking, array $paymentData = []): bool
    {
        try {
            $email = $this->getBookingEmail($booking);
            if (!$email) {
                Log::warning('Cannot send payment success email: No email address', [
                    'booking_id' => $booking->id
                ]);
                return false;
            }

            // Load all necessary relationships
            $booking->load([
                'suatChieu.phim',
                'suatChieu.phongChieu',
                'chiTietDatVe.ghe.loaiGhe',
                'chiTietCombo.combo',
                'thanhToan',
                'nguoiDung'
            ]);

            Mail::to($email)->send(new PaymentSuccessMail($booking, $paymentData));

            Log::info('Payment success email sent successfully', [
                'booking_id' => $booking->id,
                'email' => $email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send payment success email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send invoice email
     * Gửi email hóa đơn
     * 
     * @param DatVe $booking
     * @return bool
     */
    public function sendInvoiceEmail(DatVe $booking): bool
    {
        try {
            $email = $this->getBookingEmail($booking);
            if (!$email) {
                Log::warning('Cannot send invoice email: No email address', [
                    'booking_id' => $booking->id
                ]);
                return false;
            }

            // Load all necessary relationships
            $booking->load([
                'suatChieu.phim',
                'suatChieu.phongChieu',
                'chiTietDatVe.ghe.loaiGhe',
                'chiTietCombo.combo',
                'thanhToan',
                'nguoiDung',
                'khuyenMai'
            ]);

            Mail::to($email)->send(new InvoiceMail($booking));

            Log::info('Invoice email sent successfully', [
                'booking_id' => $booking->id,
                'email' => $email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send invoice email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send ticket email
     * Gửi email thông tin vé
     * 
     * @param DatVe $booking
     * @return bool
     */
    public function sendTicketEmail(DatVe $booking): bool
    {
        try {
            $email = $this->getBookingEmail($booking);
            if (!$email) {
                Log::warning('Cannot send ticket email: No email address', [
                    'booking_id' => $booking->id
                ]);
                return false;
            }

            // Load all necessary relationships
            $booking->load([
                'suatChieu.phim',
                'suatChieu.phongChieu',
                'chiTietDatVe.ghe.loaiGhe',
                'chiTietCombo.combo',
                'thanhToan',
                'nguoiDung'
            ]);

            Mail::to($email)->send(new TicketMail($booking));

            Log::info('Ticket email sent successfully', [
                'booking_id' => $booking->id,
                'email' => $email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send ticket email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send booking confirmation email
     * Gửi email xác nhận đặt vé (khi đặt vé thành công nhưng chưa thanh toán)
     * 
     * @param DatVe $booking
     * @return bool
     */
    public function sendBookingConfirmationEmail(DatVe $booking): bool
    {
        try {
            $email = $this->getBookingEmail($booking);
            if (!$email) {
                Log::warning('Cannot send booking confirmation email: No email address', [
                    'booking_id' => $booking->id
                ]);
                return false;
            }

            // Load all necessary relationships
            $booking->load([
                'suatChieu.phim',
                'suatChieu.phongChieu',
                'chiTietDatVe.ghe.loaiGhe',
                'chiTietCombo.combo',
                'nguoiDung'
            ]);

            Mail::to($email)->send(new BookingConfirmationMail($booking));

            Log::info('Booking confirmation email sent successfully', [
                'booking_id' => $booking->id,
                'email' => $email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send payment reminder email
     * Gửi email nhắc nhở thanh toán (cho đơn chưa thanh toán)
     * 
     * @param DatVe $booking
     * @param int $minutesRemaining Số phút còn lại để thanh toán
     * @return bool
     */
    public function sendPaymentReminderEmail(DatVe $booking, int $minutesRemaining = 5): bool
    {
        try {
            $email = $this->getBookingEmail($booking);
            if (!$email) {
                Log::warning('Cannot send payment reminder email: No email address', [
                    'booking_id' => $booking->id
                ]);
                return false;
            }

            // Load all necessary relationships
            $booking->load([
                'suatChieu.phim',
                'suatChieu.phongChieu',
                'chiTietDatVe.ghe.loaiGhe',
                'chiTietCombo.combo',
                'nguoiDung'
            ]);

            Mail::to($email)->send(new PaymentReminderMail($booking, $minutesRemaining));

            Log::info('Payment reminder email sent successfully', [
                'booking_id' => $booking->id,
                'email' => $email,
                'minutes_remaining' => $minutesRemaining
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send payment reminder email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send showtime cancellation notification
     * Gửi email thông báo hủy suất chiếu
     * 
     * @param DatVe $booking
     * @param \App\Models\SuatChieu $showtime
     * @param string|null $reason
     * @return bool
     */
    public static function sendShowtimeCancellationNotification(DatVe $booking, \App\Models\SuatChieu $showtime, ?string $reason = null): bool
    {
        try {
            $emailService = new self();
            $email = $emailService->getBookingEmail($booking);
            
            if (!$email) {
                Log::warning('Cannot send cancellation email: No email address', [
                    'booking_id' => $booking->id
                ]);
                return false;
            }

            // Load all necessary relationships
            $booking->load([
                'suatChieu.phim',
                'suatChieu.phongChieu',
                'chiTietDatVe.ghe.loaiGhe',
                'chiTietCombo.combo',
                'thanhToan',
                'nguoiDung'
            ]);

            // Use PaymentSuccessMail template with cancellation flag, or create a new mail class
            // For now, we'll use a simple notification
            $subject = 'Thông báo hủy suất chiếu - ' . ($showtime->phim ? $showtime->phim->ten_phim : 'Suất chiếu');
            
            Mail::send('emails.showtime-cancellation', [
                'booking' => $booking,
                'showtime' => $showtime,
                'reason' => $reason
            ], function($message) use ($email, $subject) {
                $message->to($email)
                        ->subject($subject);
            });

            Log::info('Showtime cancellation email sent successfully', [
                'booking_id' => $booking->id,
                'showtime_id' => $showtime->id,
                'email' => $email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send showtime cancellation email', [
                'booking_id' => $booking->id,
                'showtime_id' => $showtime->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get booking email address
     * Lấy địa chỉ email từ booking hoặc user
     * 
     * @param DatVe $booking
     * @return string|null
     */
    private function getBookingEmail(DatVe $booking): ?string
    {
        // Try to get email from booking first
        if ($booking->email) {
            return $booking->email;
        }

        // Fallback to user email
        if ($booking->nguoiDung && $booking->nguoiDung->email) {
            return $booking->nguoiDung->email;
        }

        return null;
    }
}

