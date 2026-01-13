<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DatVe;
use App\Models\ThanhToan;
use App\Models\ChiTietDatVe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Services\SeatHoldService;
use App\Mail\PaymentSuccessMail;

class PaymentController extends Controller
{
    /**
     * Tạo URL thanh toán VNPAY
     */
    public function createVnpayUrl($orderId, $amount)
    {
        $vnp_Url = env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        
        // Validate required config
        if (empty($vnp_TmnCode) || empty($vnp_HashSecret)) {
            Log::error('VNPAY: Missing required configuration', [
                'has_tmn_code' => !empty($vnp_TmnCode),
                'has_hash_secret' => !empty($vnp_HashSecret)
            ]);
            throw new \Exception('VNPAY configuration is missing. Please check .env file.');
        }
        
        // Get return URL - ensure it's absolute URL
        $vnp_ReturnUrl = env('VNP_RETURN_URL');
        if (empty($vnp_ReturnUrl)) {
            $vnp_ReturnUrl = route('payment.vnpay_return');
        }
        // Ensure absolute URL
        if (!filter_var($vnp_ReturnUrl, FILTER_VALIDATE_URL)) {
            $vnp_ReturnUrl = url($vnp_ReturnUrl);
        }

        // Clean order ID and create TxnRef
        $vnp_TxnRef = $orderId . "_" . time(); 
        
        // Clean OrderInfo - remove Vietnamese characters and special chars for VNPay compatibility
        $vnp_OrderInfo = "Thanh toan ve xem phim #" . $orderId;
        $vnp_OrderInfo = preg_replace('/[^\x20-\x7E]/', '', $vnp_OrderInfo); // Only ASCII
        $vnp_OrderInfo = mb_substr($vnp_OrderInfo, 0, 255); // Max 255 chars
        
        $vnp_OrderType = "billpayment";
        
        // Ensure amount is correct by cross-checking with DB (safety net)
        try {
            $booking = DatVe::with(['chiTietDatVe', 'chiTietCombo'])->find($orderId);
            if ($booking) {
                $seatSum = (int) $booking->chiTietDatVe->sum('gia');
                $comboSum = (int) ($booking->chiTietCombo->sum(function($c){ return (int)$c->so_luong * (int)$c->gia_ap_dung; }) ?? 0);
                $detailTotal = $seatSum + $comboSum; // subtotal before discounts
                $dbTotal = (int) ($booking->tong_tien ?? 0); // total after discounts
                $originalAmount = (int) $amount;

                if ($dbTotal > 0) {
                    // Ưu tiên tổng đã áp dụng khuyến mãi lưu trong booking
                    $final = max($originalAmount, $dbTotal);
                    if ($final !== $originalAmount) {
                        Log::warning('VNPay amount adjusted (prefer booking total)', [
                            'booking_id' => $orderId,
                            'from' => $originalAmount,
                            'db_total' => $dbTotal,
                            'detail_total' => $detailTotal,
                            'to' => $final
                        ]);
                    }
                } else {
                    // Khi chưa có tổng trong booking, fallback về tổng chi tiết
                    $final = max($originalAmount, $detailTotal);
                    if ($final !== $originalAmount) {
                        Log::warning('VNPay amount adjusted (fallback detail total)', [
                            'booking_id' => $orderId,
                            'from' => $originalAmount,
                            'db_total' => $dbTotal,
                            'detail_total' => $detailTotal,
                            'to' => $final
                        ]);
                    }
                }
                $amount = $final;
            }
        } catch (\Throwable $e) {
            Log::error('VNPay amount cross-check failed', [
                'booking_id' => $orderId,
                'error' => $e->getMessage()
            ]);
        }

        // Ensure amount is integer and positive
        $vnp_Amount = (int)($amount * 100);
        if ($vnp_Amount <= 0) {
            throw new \Exception('Số tiền thanh toán không hợp lệ!');
        }
        
        $vnp_Locale = "vn";
        
        // Get IP address - handle localhost case
        $vnp_IpAddr = request()->ip();
        if (empty($vnp_IpAddr) || $vnp_IpAddr === '::1' || $vnp_IpAddr === '127.0.0.1') {
            $vnp_IpAddr = '127.0.0.1';
        }

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        // Remove trailing & from query
        $query = rtrim($query, '&');
        
        // Build URL with query string
        $vnp_Url = $vnp_Url . "?" . $query;
        
        // Add secure hash
        if (!empty($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= '&vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    /**
     * Xử lý kết quả trả về từ VNPAY (User được redirect về đây)
     */
    public function vnpayReturn(Request $request)
    {
        try {
            Log::info('VNPay Return Callback', ['request_data' => $request->all()]);
            
            $vnp_HashSecret = env('VNP_HASH_SECRET');
            
            if (empty($vnp_HashSecret)) {
                Log::error('VNPay: Hash secret is missing');
                return redirect()->route('home')->with('error', 'Cấu hình thanh toán không hợp lệ!');
            }
            
            $inputData = array();
            
            // Lấy toàn bộ tham số trả về
            foreach ($request->all() as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }
            
            if (empty($inputData)) {
                Log::error('VNPay: No vnp_ parameters received');
                return redirect()->route('home')->with('error', 'Không nhận được dữ liệu từ cổng thanh toán!');
            }
            
            $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
            unset($inputData['vnp_SecureHash']);
            ksort($inputData);
            
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }

            $secureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            
            // Lấy ID booking từ TxnRef (Ví dụ: 431_1766136978 -> lấy 431)
            $txnRef = $request->vnp_TxnRef ?? '';
            
            if (empty($txnRef)) {
                Log::error('VNPay: TxnRef is missing');
                return redirect()->route('home')->with('error', 'Mã giao dịch không hợp lệ!');
            }
            
            $parts = explode('_', $txnRef);
            $bookingId = $parts[0] ?? null;

            if (empty($bookingId)) {
                Log::error('VNPay: Cannot extract booking ID from TxnRef', ['txn_ref' => $txnRef]);
                return redirect()->route('home')->with('error', 'Mã đơn hàng không hợp lệ!');
            }

            // Tìm đơn hàng
            $booking = DatVe::find($bookingId);

            if (!$booking) {
                Log::error('VNPay: Booking not found', ['booking_id' => $bookingId]);
                return redirect()->route('home')->with('error', 'Đơn hàng không tồn tại');
            }

            // 1. Kiểm tra chữ ký bảo mật
            if ($secureHash == $vnp_SecureHash) {
                // 2. Kiểm tra mã lỗi (00 là thành công)
                if ($request->vnp_ResponseCode == '00') {
                
                // === [LOGIC TỰ ĐỘNG XÁC NHẬN] ===
                DB::transaction(function () use ($booking) {
                    
                    // A. Cập nhật trạng thái vé thành 1 (Đã xác nhận/Đã thanh toán)
                    // Đây chính là dòng code giúp vé tự động "xanh" mà không cần admin duyệt
                    $booking->update([
                        'trang_thai' => 1, 
                        'expires_at' => null // Xóa hạn hủy vì đã mua xong
                    ]);

                    // B. Cập nhật bảng thanh toán thành công
                    $thanhToan = ThanhToan::where('id_dat_ve', $booking->id)->first();
                    if ($thanhToan) {
                        $thanhToan->update([
                            'trang_thai' => 1, 
                            'ma_giao_dich' => request()->vnp_TransactionNo ?? null
                        ]);
                    }

                    // C. Giải phóng ghế khỏi bảng tạm giữ
                    // (Vì ghế đã được lưu cứng vào bảng chi_tiet_dat_ve rồi)
                    try {
                        $seatIds = $booking->chiTietDatVe->pluck('id_ghe')->toArray();
                        app(SeatHoldService::class)->releaseSeats(
                            $booking->id_suat_chieu, 
                            $seatIds, 
                            $booking->id_nguoi_dung
                        );
                    } catch (\Exception $e) {
                        Log::error("Lỗi release ghế sau thanh toán: " . $e->getMessage());
                    }

                    // D. Trừ kho đồ ăn khi thanh toán thành công
                    try {
                        $foodOrders = \App\Models\ChiTietFood::where('id_dat_ve', $booking->id)->get();
                        foreach ($foodOrders as $foodOrder) {
                            $food = \App\Models\Food::find($foodOrder->food_id);
                            if ($food) {
                                $food->decrement('stock', $foodOrder->quantity);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error("Lỗi trừ kho đồ ăn sau thanh toán: " . $e->getMessage());
                    }
                    
                    // Lưu combo và food vào session để lần sau đặt lại vẫn còn
                    $booking->load(['chiTietCombo', 'chiTietFood']);
                    
                    // Lưu combo vào session
                    $savedCombos = [];
                    foreach ($booking->chiTietCombo as $ct) {
                        $savedCombos[] = [
                            'id_combo' => $ct->id_combo,
                            'so_luong' => $ct->so_luong,
                            'gia' => $ct->gia_ap_dung
                        ];
                    }
                    
                    // Lưu food vào session
                    $savedFoods = [];
                    foreach ($booking->chiTietFood as $ct) {
                        $savedFoods[] = [
                            'food_id' => $ct->food_id,
                            'quantity' => $ct->quantity,
                            'price' => $ct->price
                        ];
                    }
                    
                    // Lưu vào session
                    if (!empty($savedCombos)) {
                        session(['booking.selected_combos' => $savedCombos]);
                    }
                    if (!empty($savedFoods)) {
                        session(['booking.selected_foods' => $savedFoods]);
                    }
                });

                // Gửi email xác nhận thanh toán
                try {
                    $booking->refresh();
                    $booking->load(['nguoiDung', 'suatChieu.phim', 'suatChieu.phongChieu', 'chiTietDatVe.ghe', 'chiTietCombo', 'chiTietFood', 'thanhToan']);
                    $userEmail = $booking->nguoiDung->email ?? null;
                    
                    if ($userEmail) {
                        $paymentMethod = $booking->thanhToan->phuong_thuc ?? 'VNPay Online';
                        
                        $paymentData = [
                            'transaction_id' => request()->vnp_TransactionNo ?? null,
                            'payment_method' => $paymentMethod,
                            'paid_at' => now(),
                        ];
                        
                        Mail::to($userEmail)->send(new PaymentSuccessMail($booking, $paymentData));
                        Log::info('Payment success email sent', ['booking_id' => $booking->id, 'email' => $userEmail]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send payment success email', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Không throw exception để không ảnh hưởng đến flow thanh toán
                }

                // Chuyển hướng về trang chi tiết vé
                return redirect()->route('booking.ticket.detail', ['id' => $booking->id])
                    ->with('success', 'Thanh toán thành công! Vé của bạn đã được xác nhận tự động.');

            } else {
                // Thanh toán thất bại hoặc hủy bỏ - XÓA BOOKING CHƯA THANH TOÁN
                try {
                    DB::transaction(function () use ($booking) {
                        // Chỉ xóa nếu booking chưa thanh toán (trang_thai = 0)
                        if ($booking->trang_thai == 0) {
                            // Xóa chi tiết ghế
                            ChiTietDatVe::where('id_dat_ve', $booking->id)->delete();
                            // Xóa chi tiết combo
                            \App\Models\ChiTietCombo::where('id_dat_ve', $booking->id)->delete();
                            // Xóa chi tiết food
                            \App\Models\ChiTietFood::where('id_dat_ve', $booking->id)->delete();
                            // Xóa thanh toán
                            ThanhToan::where('id_dat_ve', $booking->id)->delete();
                            // Xóa booking
                            $booking->delete();
                        }
                    });
                } catch (\Exception $e) {
                    Log::error('Lỗi xóa booking khi thanh toán thất bại: ' . $e->getMessage());
                }
                
                    return redirect()->route('home')
                        ->with('error', 'Giao dịch không thành công hoặc đã bị hủy. Vé đã được hủy tự động.');
                }
            } else {
                Log::warning('VNPay: Invalid secure hash', [
                    'booking_id' => $bookingId,
                    'expected' => $secureHash,
                    'received' => $vnp_SecureHash
                ]);
                return redirect()->route('home')->with('error', 'Chữ ký bảo mật không hợp lệ!');
            }
        } catch (\Exception $e) {
            Log::error('VNPay Return Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return redirect()->route('home')->with('error', 'Đã xảy ra lỗi khi xử lý thanh toán. Vui lòng liên hệ hỗ trợ.');
        }
    }
}