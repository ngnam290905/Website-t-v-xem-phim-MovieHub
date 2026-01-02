<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DatVe;
use App\Models\ThanhToan;
use App\Models\ChiTietDatVe;
use App\Models\ChiTietCombo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
     * Xử lý kết quả trả về từ VNPAY
     */
    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET');
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
        
        $txnRef = $request->vnp_TxnRef;
        $parts = explode('_', $txnRef);
        $bookingId = $parts[0];

        $booking = DatVe::find($bookingId);

        // Trường hợp vé không tồn tại (đã bị xóa trước đó)
        if (!$booking) {
            return redirect()->route('home')->with('error', 'Đơn hàng không tồn tại hoặc đã bị hủy.');
        }

        // 1. Kiểm tra chữ ký bảo mật
        if ($secureHash == $vnp_SecureHash) {
            // 2. Kiểm tra mã lỗi
            if ($request->vnp_ResponseCode == '00') {
                // --- THANH TOÁN THÀNH CÔNG ---
                DB::transaction(function () use ($booking) {
                    if ($booking->trang_thai == 0) {
                        $booking->update([
                            'trang_thai' => 1, 
                            'expires_at' => null
                        ]);
                        
                        $thanhToan = ThanhToan::where('id_dat_ve', $booking->id)->first();
                        if ($thanhToan) {
                            $thanhToan->update([
                                'trang_thai' => 1, 
                                'ma_giao_dich' => request()->vnp_TransactionNo ?? null
                            ]);
                        }
                        
                        try {
                            $seatIds = $booking->chiTietDatVe->pluck('id_ghe')->toArray();
                            app(SeatHoldService::class)->confirmBooking(
                                $booking->id_suat_chieu, 
                                $seatIds, 
                                $booking->id_nguoi_dung
                            );
                        } catch (\Exception $e) {
                            Log::error("Confirm booking error: " . $e->getMessage());
                        }
                    }
                });

                return redirect()->route('booking.ticket.detail', ['id' => $booking->id])
                    ->with('success', 'Thanh toán thành công! Vé của bạn đã được xác nhận.');

            } else {
                // --- THANH TOÁN THẤT BẠI / HỦY BỎ ---
                // Code 24: Khách hàng hủy giao dịch
                
                $showtimeId = $booking->id_suat_chieu;
                
                // GỌI HÀM XÓA VÉ NGAY LẬP TỨC
                $this->cancelAndCleanupBooking($booking);

                $message = 'Giao dịch không thành công.';
                if ($request->vnp_ResponseCode == '24') {
                    $message = 'Bạn đã hủy giao dịch. Vé chưa được lưu.';
                }

                // Quay về trang chọn ghế để khách đặt lại
                return redirect()->route('booking.seats', ['showId' => $showtimeId])
                    ->with('error', $message);
            }
        } else {
            return redirect()->route('home')->with('error', 'Chữ ký bảo mật không hợp lệ!');
        }
    }

    /**
     * Hàm xóa sạch dữ liệu vé và nhả ghế
     */
    private function cancelAndCleanupBooking($booking)
    {
        try {
            DB::beginTransaction();

            if (!$booking) return;

            $seatIds = ChiTietDatVe::where('id_dat_ve', $booking->id)->pluck('id_ghe')->toArray();
            $userId = $booking->id_nguoi_dung;
            $showtimeId = $booking->id_suat_chieu;

            // Xóa các bảng chi tiết trước
            ChiTietDatVe::where('id_dat_ve', $booking->id)->delete();
            ChiTietCombo::where('id_dat_ve', $booking->id)->delete();
            ThanhToan::where('id_dat_ve', $booking->id)->delete();

            // Xóa vé chính
            $booking->delete();

            // Nhả ghế trong Redis/Cache (Quan trọng để ghế chuyển màu trắng lại ngay)
            if (!empty($seatIds)) {
                app(SeatHoldService::class)->releaseSeats($showtimeId, $seatIds, $userId);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi hủy vé VNPAY: ' . $e->getMessage());
        }
    }
}