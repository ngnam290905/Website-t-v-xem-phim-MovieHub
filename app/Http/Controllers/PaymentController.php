<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DatVe;
use App\Models\ThanhToan;
use App\Models\ChiTietDatVe;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\SeatHoldService;

class PaymentController extends Controller
{
    /**
     * Tạo URL thanh toán VNPAY
     */
    public function createVnpayUrl($orderId, $amount)
    {
        $vnp_Url = env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $vnp_ReturnUrl = env('VNP_RETURN_URL', route('payment.vnpay_return'));
        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');

        $vnp_TxnRef = $orderId . "_" . time(); 
        $vnp_OrderInfo = "Thanh toan ve xem phim #" . $orderId;
        $vnp_OrderType = "billpayment";
        $vnp_Amount = $amount * 100;
        $vnp_Locale = "vn";
        $vnp_IpAddr = request()->ip();

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

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

    /**
     * Xử lý kết quả trả về từ VNPAY (User được redirect về đây)
     */
    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $inputData = array();
        
        // Lấy toàn bộ tham số trả về
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
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
        $txnRef = $request->vnp_TxnRef;
        $parts = explode('_', $txnRef);
        $bookingId = $parts[0];

        // Tìm đơn hàng
        $booking = DatVe::find($bookingId);

        if (!$booking) {
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
            return redirect()->route('home')->with('error', 'Chữ ký bảo mật không hợp lệ!');
        }
    }
}