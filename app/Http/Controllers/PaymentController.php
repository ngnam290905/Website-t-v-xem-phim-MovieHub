<?php

namespace App\Http\Controllers;

use App\Models\DatVe;
use App\Models\ThanhToan;
use App\Models\ShowtimeSeat;
use App\Models\ChiTietDatVe;
use App\Mail\TicketMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    /**
     * Create VNPAY payment URL
     */
    public function createVnpayUrl($bookingId, $amount)
    {
        try {
            // Prefer new VNPAY_* keys, fallback to legacy VNP_*
            $vnp_TmnCode = trim((string) env('VNPAY_TMN_CODE', ''));
            $vnp_HashSecret = trim((string) env('VNPAY_HASH_SECRET', ''));
            $vnp_Url = rtrim(trim((string) env('VNPAY_URL', '')), '/');

            if ($vnp_TmnCode === '') {
                $vnp_TmnCode = trim((string) env('VNP_TMN_CODE', ''));
            }
            if ($vnp_HashSecret === '') {
                $vnp_HashSecret = trim((string) env('VNP_HASH_SECRET', ''));
            }
            if ($vnp_Url === '') {
                $vnp_Url = rtrim('https://sandbox.vnpayment.vn/paymentv2/vpcpay.html', '/');
                $legacyUrl = trim((string) env('VNP_URL', ''));
                if ($legacyUrl !== '') $vnp_Url = rtrim($legacyUrl, '/');
            }

            // Validate required config
            if (empty($vnp_TmnCode)) {
                Log::error('VNPAY: VNP_TMN_CODE is not configured');
                throw new \Exception('VNP_TMN_CODE chưa được cấu hình trong file .env');
            }
            
            if (empty($vnp_HashSecret)) {
                Log::error('VNPAY: VNP_HASH_SECRET is not configured');
                throw new \Exception('VNP_HASH_SECRET chưa được cấu hình trong file .env');
            }
            
            // Validate TMN Code format (should be 8 characters for sandbox)
            if (strlen($vnp_TmnCode) < 6 || strlen($vnp_TmnCode) > 10) {
                Log::warning('VNPAY: VNP_TMN_CODE length seems incorrect', [
                    'length' => strlen($vnp_TmnCode),
                    'code' => substr($vnp_TmnCode, 0, 3) . '...' // Partial for security
                ]);
            }

            // Ensure amount is integer and positive
            $vnp_Amount = (int)($amount * 100);
            if ($vnp_Amount <= 0) {
                throw new \Exception('Số tiền thanh toán không hợp lệ!');
            }

            // Get IP address
            $vnp_IpAddr = request()->ip();
            if (empty($vnp_IpAddr) || $vnp_IpAddr === '::1') {
                $vnp_IpAddr = '127.0.0.1';
            }

            // Create date strings (format: YmdHis - 8 digits date + 6 digits time)
            $vnp_CreateDate = date('YmdHis');
            
            // Get return URL (must be absolute URL)
            $vnp_ReturnUrl = trim((string) env('VNPAY_RETURN_URL', ''));
            if ($vnp_ReturnUrl === '') {
                $vnp_ReturnUrl = route('payment.vnpay_return');
            }
            if (!filter_var($vnp_ReturnUrl, FILTER_VALIDATE_URL)) {
                // If route returns relative URL, make it absolute
                $vnp_ReturnUrl = url($vnp_ReturnUrl);
            }
            
            // Clean order info (remove special characters that might cause issues)
            // VNPAY requires ASCII characters only for vnp_OrderInfo
            $vnp_OrderInfo = "Thanh toan ve xem phim #{$bookingId}";
            // Remove Vietnamese characters and special chars, keep only ASCII
            $vnp_OrderInfo = preg_replace('/[^\x20-\x7E]/', '', $vnp_OrderInfo);
            $vnp_OrderInfo = mb_substr($vnp_OrderInfo, 0, 255); // Max 255 characters
            
            // Generate unique transaction reference (max 100 characters)
            $vnp_TxnRef = $bookingId . "_" . time();
            if (strlen($vnp_TxnRef) > 100) {
                $vnp_TxnRef = substr($vnp_TxnRef, 0, 100);
            }

            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => $vnp_CreateDate,
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => "vn",
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => "billpayment",
                "vnp_ReturnUrl" => $vnp_ReturnUrl,
                "vnp_TxnRef" => $vnp_TxnRef
            ];
            
            // Note: vnp_ExpireDate is optional, only add if needed
            // Some VNPAY configurations don't require it

            // Remove empty values but keep 0 values
            $inputData = array_filter($inputData, function($value) {
                return $value !== null && $value !== '';
            }, ARRAY_FILTER_USE_BOTH);

            // Sort by key (alphabetically)
            ksort($inputData);
            
            // Build query string and hash data
            $query = "";
            $hashdata = "";
            $i = 0;
            foreach ($inputData as $key => $value) {
                // Convert value to string and ensure proper encoding
                $value = (string)$value;
                
                // Build hashdata (for signature)
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                
                // Build query string (for URL)
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }

            // Remove trailing &
            $query = rtrim($query, '&');

            // Create secure hash using hashdata (without vnp_SecureHash)
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            
            // Build final URL - add vnp_SecureHash at the end
            $vnp_Url = $vnp_Url . "?" . $query . '&vnp_SecureHash=' . $vnpSecureHash;
            
            // Debug: Log the hashdata and secure hash for troubleshooting
            Log::info('VNPAY payment URL created', [
                'booking_id' => $bookingId,
                'amount' => $amount,
                'vnp_Amount' => $vnp_Amount,
                'tmn_code' => $vnp_TmnCode,
                'tmn_code_length' => strlen($vnp_TmnCode),
                'hash_secret_length' => strlen($vnp_HashSecret),
                'url_length' => strlen($vnp_Url),
                'return_url' => $vnp_ReturnUrl
            ]);
            
            // Log hashdata only in debug mode for security
            if (config('app.debug')) {
                Log::debug('VNPAY hash data (DEBUG ONLY)', [
                    'hashdata' => $hashdata,
                    'secure_hash' => $vnpSecureHash
                ]);
            }
            
            // Log for debugging (remove in production)
            Log::info('VNPAY URL created', [
                'booking_id' => $bookingId,
                'amount' => $amount,
                'vnp_Amount' => $vnp_Amount,
                'url_length' => strlen($vnp_Url)
            ]);

            return $vnp_Url;
        } catch (\Exception $e) {
            Log::error('VNPAY URL creation error: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'amount' => $amount,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Create MOMO payment URL
     */
    public function createMomopayUrl($bookingId, $amount)
    {
        $endpoint = env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = env('MOMO_PARTNER_CODE');
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');
        $returnUrl = route('payment.momo_return');
        $notifyUrl = route('payment.momo_ipn');
        $orderId = $bookingId . "_" . time();
        $orderInfo = "Thanh toan ve xem phim #{$bookingId}";
        $requestId = time() . "";
        $extraData = "";

        // Create signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $notifyUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $returnUrl . "&requestId=" . $requestId . "&requestType=captureWallet";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => "MovieHub",
            'storeId' => "MovieHub",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $returnUrl,
            'ipnUrl' => $notifyUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => 'captureWallet',
            'signature' => $signature
        ];

        // Call MOMO API to create payment
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $result = json_decode($response, true);
            if (isset($result['payUrl'])) {
                return $result['payUrl'];
            }
        }

        Log::error('MOMO payment URL creation failed', [
            'response' => $response,
            'http_code' => $httpCode
        ]);

        throw new \Exception('Không thể tạo link thanh toán MOMO. Vui lòng thử lại!');
    }

    /**
     * VNPAY Return Handler
     * Handle payment return from VNPAY gateway
     */
    public function vnpayReturn(Request $request)
    {
        // Ưu tiên dùng VNPAY_HASH_SECRET, fallback sang VNP_HASH_SECRET cho tương thích cũ
        $vnp_HashSecret = trim((string) env('VNPAY_HASH_SECRET', ''));
        if ($vnp_HashSecret === '') {
            $vnp_HashSecret = trim((string) env('VNP_HASH_SECRET', ''));
        }
        $inputData = array();
        
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            $parts = explode("_", $request->vnp_TxnRef);
            $bookingId = $parts[0];

            // Load booking with details
            $booking = DatVe::with([
                'chiTietDatVe.ghe.loaiGhe',
                'chiTietCombo.combo',
                'thanhToan',
                'suatChieu.phim',
                'suatChieu.phongChieu',
                'nguoiDung'
            ])->find($bookingId);
            
            if (!$booking) {
                Log::error('VNPAY Return: Booking not found', ['booking_id' => $bookingId]);
                return redirect()->route('home')->with('error', 'Không tìm thấy đơn đặt vé!');
            }

            if ($request->vnp_ResponseCode == '00') {
                // Payment successful
                return $this->handlePaymentSuccess($booking, [
                    'provider' => 'VNPAY',
                    'transaction_id' => $request->vnp_TransactionNo,
                    'amount' => $request->vnp_Amount / 100,
                    'response_code' => $request->vnp_ResponseCode
                ]);
            } else {
                // Payment failed or cancelled
                return $this->handlePaymentFailure($booking, [
                    'provider' => 'VNPAY',
                    'response_code' => $request->vnp_ResponseCode,
                    'message' => $request->vnp_ResponseCode ?? 'Giao dịch thất bại'
                ]);
            }
        } else {
            Log::warning('VNPAY: Invalid secure hash', [
                'request_data' => $request->all()
            ]);
            return redirect()->route('home')->with('error', 'Chữ ký bảo mật không hợp lệ!');
        }
    }

    /**
     * MOMO Return Handler
     * Handle payment return from MOMO gateway
     */
    public function momoReturn(Request $request)
    {
        $partnerCode = $request->partnerCode;
        $orderId = $request->orderId;
        $resultCode = $request->resultCode;
        $amount = $request->amount;
        $orderInfo = $request->orderInfo;
        $transId = $request->transId ?? null;

        // Extract booking ID from orderId (format: bookingId_timestamp)
        $parts = explode("_", $orderId);
        $bookingId = $parts[0];

        $booking = DatVe::with(['chiTietDatVe.ghe', 'chiTietCombo', 'thanhToan', 'suatChieu'])->find($bookingId);

        if ($resultCode == 0) {
            // Payment successful
            return $this->handlePaymentSuccess($booking, [
                'provider' => 'MOMO',
                'transaction_id' => $transId,
                'amount' => $amount,
                'result_code' => $resultCode
            ]);
        } else {
            // Payment failed
            return $this->handlePaymentFailure($booking, [
                'provider' => 'MOMO',
                'result_code' => $resultCode,
                'message' => $request->message ?? 'Giao dịch thất bại'
            ]);
        }
    }

    /**
     * MOMO IPN (Instant Payment Notification) Handler
     * Handle server-to-server callback from MOMO
     */
    public function momoIpn(Request $request)
    {
        $partnerCode = $request->partnerCode;
        $orderId = $request->orderId;
        $resultCode = $request->resultCode;
        $amount = $request->amount;
        $transId = $request->transId ?? null;
        $accessKey = env('MOMO_ACCESS_KEY');
        $secretKey = env('MOMO_SECRET_KEY');

        // Verify signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . ($request->extraData ?? "") . "&message=" . ($request->message ?? "") . "&orderId=" . $orderId . "&orderInfo=" . ($request->orderInfo ?? "") . "&orderType=" . ($request->orderType ?? "") . "&partnerCode=" . $partnerCode . "&payType=" . ($request->payType ?? "") . "&requestId=" . ($request->requestId ?? "") . "&responseTime=" . ($request->responseTime ?? "") . "&resultCode=" . $resultCode . "&transId=" . $transId;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        if ($signature != $request->signature) {
            Log::warning('MOMO IPN: Invalid signature', [
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Extract booking ID
        $parts = explode("_", $orderId);
        $bookingId = $parts[0];

        $booking = DatVe::with(['chiTietDatVe.ghe', 'chiTietCombo', 'thanhToan', 'suatChieu'])->find($bookingId);

        if ($resultCode == 0) {
            // Payment successful
            $this->handlePaymentSuccess($booking, [
                'provider' => 'MOMO',
                'transaction_id' => $transId,
                'amount' => $amount,
                'result_code' => $resultCode
            ], false); // Don't redirect for IPN

            return response()->json(['status' => 'success'], 200);
        } else {
            // Payment failed
            $this->handlePaymentFailure($booking, [
                'provider' => 'MOMO',
                'result_code' => $resultCode,
                'message' => $request->message ?? 'Giao dịch thất bại'
            ], false); // Don't redirect for IPN

            return response()->json(['status' => 'failed'], 200);
        }
    }

    /**
     * Handle successful payment
     */
    private function handlePaymentSuccess($booking, $paymentData, $shouldRedirect = true)
    {
        if (!$booking) {
            Log::error('Payment success: Booking not found', $paymentData);
            if ($shouldRedirect) {
                return redirect()->route('home')->with('error', 'Không tìm thấy đơn đặt vé!');
            }
            return;
        }

        try {
            DB::transaction(function () use ($booking, $paymentData) {
                Log::info('Starting payment success transaction', [
                    'booking_id' => $booking->id,
                    'provider' => $paymentData['provider'] ?? 'Unknown'
                ]);

                // 1. Generate ticket_code if not exists
                if (!$booking->ticket_code) {
                    try {
                        Log::info('Generating ticket code', ['booking_id' => $booking->id]);
                        $ticketCode = $this->generateTicketCode($booking->id);
                        $booking->update([
                            'ticket_code' => $ticketCode
                        ]);
                        Log::info('Ticket code generated successfully', ['booking_id' => $booking->id]);
                    } catch (\Exception $e) {
                        Log::error('Failed to generate ticket code', [
                            'booking_id' => $booking->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        $ticketCode = $this->generateTicketCode($booking->id);
                        $booking->update([
                            'ticket_code' => $ticketCode
                        ]);
                    }
                }

                // 2. Update booking status to confirmed (paid)
                try {
                    Log::info('Updating booking status to paid', [
                        'booking_id' => $booking->id,
                        'current_status' => $booking->trang_thai
                    ]);
                    $booking->update([
                        'trang_thai' => 1, // Always set to paid when payment succeeds
                        'expires_at' => null // Clear expiration when paid
                    ]);
                    Log::info('Booking status updated successfully', [
                        'booking_id' => $booking->id,
                        'new_status' => 1
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to update booking status', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }

                // 3. Update payment record
                try {
                    Log::info('Processing payment record', [
                        'booking_id' => $booking->id,
                        'has_thanh_toan' => $booking->thanhToan ? 'yes' : 'no'
                    ]);
                    
                    if ($booking->thanhToan) {
                        $booking->thanhToan()->update([
                            'trang_thai' => 1, // Paid
                            'ma_giao_dich' => $paymentData['transaction_id'] ?? null,
                            'thoi_gian' => now()
                        ]);
                        Log::info('Payment record updated', ['booking_id' => $booking->id]);
                    } else {
                        // Create new payment record if not exists
                        ThanhToan::create([
                            'id_dat_ve' => $booking->id,
                            'phuong_thuc' => $paymentData['provider'] ?? 'VNPAY',
                            'so_tien' => $paymentData['amount'] ?? 0,
                            'ma_giao_dich' => $paymentData['transaction_id'] ?? null,
                            'trang_thai' => 1, // Paid
                            'thoi_gian' => now()
                        ]);
                        Log::info('Payment record created', ['booking_id' => $booking->id]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to update/create payment record', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }

                // 4. Update seats status to "paid" (booked)
                try {
                    if ($booking->suatChieu && $booking->chiTietDatVe) {
                        Log::info('Updating seats status', [
                            'booking_id' => $booking->id,
                            'showtime_id' => $booking->suatChieu->id,
                            'seats_count' => $booking->chiTietDatVe->count()
                        ]);
                        
                        foreach ($booking->chiTietDatVe as $detail) {
                            if ($detail->ghe) {
                                // Update ShowtimeSeat status to "booked"
                                ShowtimeSeat::updateOrCreate(
                                    [
                                        'id_suat_chieu' => $booking->suatChieu->id,
                                        'id_ghe' => $detail->ghe->id
                                    ],
                                    [
                                        'trang_thai' => 'booked',
                                        'thoi_gian_het_han' => null
                                    ]
                                );
                            }
                        }
                        Log::info('Seats status updated successfully', ['booking_id' => $booking->id]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to update seats status', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    throw $e;
                }

                Log::info('Payment transaction completed successfully', [
                    'booking_id' => $booking->id,
                    'provider' => $paymentData['provider'] ?? 'Unknown',
                    'transaction_id' => $paymentData['transaction_id'] ?? null
                ]);
            });

            // Send email outside transaction to avoid rollback if email fails
            // Refresh booking to get latest data with all relationships
            try {
                $booking->refresh();
                $booking->load([
                    'suatChieu.phim',
                    'suatChieu.phongChieu',
                    'chiTietDatVe.ghe.loaiGhe',
                    'chiTietCombo.combo',
                    'thanhToan',
                    'nguoiDung'
                ]);
                $this->sendTicketEmail($booking);
            } catch (\Exception $e) {
                // Log email error but don't fail the payment
                Log::error('Failed to send ticket email after payment', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage()
                ]);
            }

            if ($shouldRedirect) {
                return redirect()->route('user.bookings')->with('success', 'Thanh toán thành công!');
            }
        } catch (\Exception $e) {
            Log::error('Payment success handling error', [
                'booking_id' => $booking->id ?? 'N/A',
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'payment_data' => $paymentData ?? []
            ]);

            if ($shouldRedirect) {
                // Show more specific error message in development
                $errorMessage = config('app.debug') 
                    ? 'Lỗi: ' . $e->getMessage() . ' (File: ' . basename($e->getFile()) . ':' . $e->getLine() . ')'
                    : 'Có lỗi xảy ra khi xử lý thanh toán. Vui lòng liên hệ hỗ trợ với mã lỗi: ' . substr(md5($e->getMessage()), 0, 8);
                
                return redirect()->route('home')->with('error', $errorMessage);
            }
        }
    }

    /**
     * Handle failed payment
     */
    private function handlePaymentFailure($booking, $paymentData, $shouldRedirect = true)
    {
        if (!$booking) {
            Log::error('Payment failure: Booking not found', $paymentData);
            if ($shouldRedirect) {
                return redirect()->route('home')->with('error', 'Không tìm thấy đơn đặt vé!');
            }
            return;
        }

        try {
            DB::transaction(function () use ($booking) {
                // 1. Release seats (mở lại ghế)
                if ($booking->suatChieu) {
                    foreach ($booking->chiTietDatVe as $detail) {
                        if ($detail->ghe) {
                            // Release seat in ShowtimeSeat
                            ShowtimeSeat::where('id_suat_chieu', $booking->suatChieu->id)
                                ->where('id_ghe', $detail->ghe->id)
                                ->update([
                                    'status' => 'available',
                                    'hold_expires_at' => null
                                ]);

                            // Also update seat status in Ghe table if needed
                            // $detail->ghe->update(['trang_thai' => 1]); // 1 = Available
                        }
                    }
                }

                // 2. Delete related records
                $booking->chiTietDatVe()->delete();
                $booking->chiTietCombo()->delete();
                if ($booking->thanhToan) {
                    $booking->thanhToan()->delete();
                }

                // 3. Delete booking
                $booking->delete();

                Log::info('Payment failed: Booking deleted and seats released', [
                    'booking_id' => $booking->id,
                    'provider' => $paymentData['provider'] ?? 'Unknown'
                ]);
            });

            if ($shouldRedirect) {
                return redirect()->route('booking', ['id' => $booking->suatChieu->id_phim ?? null])
                    ->with('error', 'Giao dịch đã bị hủy. Vui lòng đặt lại vé.');
            }
        } catch (\Exception $e) {
            Log::error('Payment failure handling error: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'error' => $e->getTraceAsString()
            ]);

            if ($shouldRedirect) {
                return redirect()->route('home')->with('error', 'Có lỗi xảy ra khi xử lý thanh toán. Vui lòng liên hệ hỗ trợ!');
            }
        }
    }

    /**
     * Generate unique ticket code
     */
    private function generateTicketCode($bookingId)
    {
        $prefix = 'TKT';
        $timestamp = now()->format('Ymd');
        $random = strtoupper(substr(md5($bookingId . time() . rand()), 0, 6));
        return $prefix . $timestamp . $random;
    }

    /**
     * Send ticket email to customer
     */
    private function sendTicketEmail($booking)
    {
        try {
            // Get customer email
            $email = $booking->email;
            if (!$email && $booking->nguoiDung) {
                $email = $booking->nguoiDung->email;
            }

            if (!$email) {
                Log::warning('Cannot send ticket email: No email address', [
                    'booking_id' => $booking->id
                ]);
                return;
            }

            // Send email
            Mail::to($email)->send(new TicketMail($booking));

            Log::info('Ticket email sent successfully', [
                'booking_id' => $booking->id,
                'email' => $email
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send ticket email', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

