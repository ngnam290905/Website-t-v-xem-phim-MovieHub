<?php

namespace App\Http\Controllers;

use App\Models\Phim;
use App\Models\SuatChieu;
use App\Models\DatVe;
use App\Models\Ghe;
use App\Models\Combo;
use App\Models\ChiTietDatVe;
use App\Models\ChiTietCombo;
use App\Models\PhongChieu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Hiển thị trang chọn ghế cho suất chiếu
     */
    public function showSeats($showId)
    {
        $showtime = SuatChieu::with(['phim', 'phongChieu.seats.seatType'])
            ->findOrFail($showId);

        // Lấy danh sách ghế đã đặt cho suất chiếu này
        $bookedSeatIds = DB::table('chi_tiet_dat_ve as ctdv')
            ->join('dat_ve as dv', 'ctdv.id_dat_ve', '=', 'dv.id')
            ->where('dv.id_suat_chieu', $showId)
            ->where('dv.trang_thai', '!=', 'CANCELLED')
            ->whereIn('dv.trang_thai', ['PAID', 'CONFIRMED', 'DRAFT'])
            ->pluck('ctdv.id_ghe')
            ->toArray();

        // Lấy ghế đang bị lock (trong cache)
        $lockedSeats = $this->getLockedSeats($showId);

        // Kiểm tra xem có booking DRAFT đang tồn tại không
        $existingBooking = null;
        $selectedSeatIds = [];
        if (Auth::check()) {
            $existingBooking = DatVe::where('id_nguoi_dung', Auth::id())
                ->where('id_suat_chieu', $showId)
                ->where('trang_thai', 'DRAFT')
                ->first();

            if ($existingBooking) {
                $selectedSeatIds = $existingBooking->chiTietDatVe()->pluck('id_ghe')->toArray();
            }
        }

        // Lấy tất cả ghế trong phòng
        $seats = $showtime->phongChieu->seats()
            ->with('seatType')
            ->orderBy('so_hang')
            ->orderBy('so_ghe')
            ->get();

        // Đánh dấu trạng thái ghế
        foreach ($seats as $seat) {
            if (in_array($seat->id, $bookedSeatIds)) {
                $seat->booking_status = 'booked';
            } elseif (in_array($seat->id, $selectedSeatIds)) {
                $seat->booking_status = 'selected';
            } elseif (isset($lockedSeats[$seat->id]) && $lockedSeats[$seat->id]['user_id'] != Auth::id()) {
                $seat->booking_status = 'locked_by_other';
            } elseif (isset($lockedSeats[$seat->id]) && $lockedSeats[$seat->id]['user_id'] == Auth::id()) {
                $seat->booking_status = 'locked_by_me';
            } elseif ($seat->trang_thai == 0) {
                $seat->booking_status = 'disabled';
            } else {
                $seat->booking_status = 'available';
            }
        }

        // Get available combos
        $combos = Combo::where('trang_thai', true)
            ->where(function($query) {
                $query->whereNull('ngay_bat_dau')
                      ->orWhere('ngay_bat_dau', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('ngay_ket_thuc')
                      ->orWhere('ngay_ket_thuc', '>=', now());
            })
            ->get();

        // Get selected combos if booking exists
        $selectedCombos = collect();
        if ($existingBooking) {
            $selectedCombos = $existingBooking->chiTietCombo()->with('combo')->get();
        }

        return view('booking.seats', compact('showtime', 'seats', 'existingBooking', 'combos', 'selectedCombos'));
    }

    /**
     * Lock ghế (AJAX)
     */
    public function lockSeats(Request $request, $showId)
    {
        $request->validate([
            'seat_ids' => 'required|array',
            'seat_ids.*' => 'exists:ghe,id'
        ]);

        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $seatIds = $request->seat_ids;
        $userId = Auth::id();
        $lockDuration = 300; // 5 phút

        // Kiểm tra xem ghế có bị đặt hoặc bị lock bởi người khác không
        $bookedSeatIds = $this->getBookedSeatIds($showId);
        $lockedSeats = $this->getLockedSeats($showId);

        $conflicts = [];
        foreach ($seatIds as $seatId) {
            if (in_array($seatId, $bookedSeatIds)) {
                $conflicts[] = ['seat_id' => $seatId, 'reason' => 'Ghế đã được đặt'];
            } elseif (isset($lockedSeats[$seatId]) && $lockedSeats[$seatId]['user_id'] != $userId) {
                $conflicts[] = ['seat_id' => $seatId, 'reason' => 'Ghế đang được người khác chọn'];
            }
        }

        if (!empty($conflicts)) {
            return response()->json([
                'success' => false,
                'message' => 'Một số ghế không còn khả dụng',
                'conflicts' => $conflicts
            ], 400);
        }

        // Lock ghế
        foreach ($seatIds as $seatId) {
            $cacheKey = "seat_lock:{$showId}:{$seatId}";
            Cache::put($cacheKey, [
                'user_id' => $userId,
                'locked_at' => now()->timestamp,
                'expires_at' => now()->addSeconds($lockDuration)->timestamp
            ], $lockDuration);
        }

        // Tạo hoặc cập nhật booking DRAFT
        $booking = DatVe::updateOrCreate(
            [
                'id_nguoi_dung' => $userId,
                'id_suat_chieu' => $showId,
                'trang_thai' => 'DRAFT'
            ],
            [
                'tong_tien' => 0,
                'created_at' => now()
            ]
        );

        // Xóa chi tiết cũ
        $booking->chiTietDatVe()->delete();

        // Thêm chi tiết mới
        $basePrice = 50000; // Giá cơ bản
        foreach ($seatIds as $seatId) {
            $seat = Ghe::with('seatType')->find($seatId);
            $coefficient = $seat->seatType->he_so_gia ?? 1;
            $price = $basePrice * $coefficient;
            
            ChiTietDatVe::create([
                'id_dat_ve' => $booking->id,
                'id_ghe' => $seatId,
                'gia' => $price
            ]);
        }

        // Tính lại tổng tiền
        $total = $booking->chiTietDatVe()->sum('gia');
        $booking->update(['tong_tien' => $total]);

        return response()->json([
            'success' => true,
            'booking_id' => $booking->id,
            'locked_seats' => $seatIds,
            'expires_at' => now()->addSeconds($lockDuration)->timestamp
        ]);
    }

    /**
     * Unlock ghế (AJAX)
     */
    public function unlockSeats(Request $request, $showId)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $userId = Auth::id();
        $seatIds = $request->seat_ids ?? [];

        foreach ($seatIds as $seatId) {
            $cacheKey = "seat_lock:{$showId}:{$seatId}";
            $lock = Cache::get($cacheKey);
            
            if ($lock && $lock['user_id'] == $userId) {
                Cache::forget($cacheKey);
            }
        }

        // Xóa ghế khỏi booking DRAFT nếu có
        $booking = DatVe::where('id_nguoi_dung', $userId)
            ->where('id_suat_chieu', $showId)
            ->where('trang_thai', 'DRAFT')
            ->first();

        if ($booking && !empty($seatIds)) {
            $booking->chiTietDatVe()->whereIn('id_ghe', $seatIds)->delete();
            
            // Nếu không còn ghế nào, xóa booking
            if ($booking->chiTietDatVe()->count() == 0) {
                $booking->delete();
            } else {
                $total = $booking->chiTietDatVe()->sum('gia');
                $booking->update(['tong_tien' => $total]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Refresh seat status (AJAX)
     */
    public function refreshSeats(Request $request, $showId)
    {
        $showtime = SuatChieu::findOrFail($showId);
        
        $bookedSeatIds = $this->getBookedSeatIds($showId);
        $lockedSeats = $this->getLockedSeats($showId);

        $seats = $showtime->phongChieu->seats()
            ->with('seatType')
            ->get();

        $status = [];
        foreach ($seats as $seat) {
            if (in_array($seat->id, $bookedSeatIds)) {
                $status[$seat->id] = 'booked';
            } elseif (isset($lockedSeats[$seat->id])) {
                $status[$seat->id] = $lockedSeats[$seat->id]['user_id'] == Auth::id() 
                    ? 'locked_by_me' 
                    : 'locked_by_other';
            } elseif ($seat->trang_thai == 0) {
                $status[$seat->id] = 'disabled';
            } else {
                $status[$seat->id] = 'available';
            }
        }

        return response()->json([
            'success' => true,
            'seats' => $status,
            'timestamp' => now()->timestamp
        ]);
    }

    /**
     * Hiển thị trang chọn combos
     */
    public function addons($bookingId)
    {
        $booking = DatVe::with(['chiTietDatVe.ghe', 'suatChieu.phim'])
            ->findOrFail($bookingId);

        // Kiểm tra quyền truy cập
        if (Auth::id() != $booking->id_nguoi_dung || $booking->trang_thai != 'DRAFT') {
            abort(403);
        }

        // Kiểm tra lock còn hạn không
        $showId = $booking->id_suat_chieu;
        $seatIds = $booking->chiTietDatVe()->pluck('id_ghe')->toArray();
        $allLocksValid = true;

        foreach ($seatIds as $seatId) {
            $cacheKey = "seat_lock:{$showId}:{$seatId}";
            $lock = Cache::get($cacheKey);
            if (!$lock || $lock['user_id'] != Auth::id()) {
                $allLocksValid = false;
                break;
            }
        }

        if (!$allLocksValid) {
            return redirect()->route('booking.seats', $showId)
                ->with('error', 'Thời gian giữ ghế đã hết hạn. Vui lòng chọn lại.');
        }

        $combos = Combo::where('trang_thai', true)
            ->where(function($query) {
                $query->whereNull('ngay_bat_dau')
                      ->orWhere('ngay_bat_dau', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('ngay_ket_thuc')
                      ->orWhere('ngay_ket_thuc', '>=', now());
            })
            ->get();

        $selectedCombos = $booking->chiTietCombo()->with('combo')->get();

        return view('booking.addons', compact('booking', 'combos', 'selectedCombos'));
    }

    /**
     * Cập nhật combos (AJAX)
     */
    public function updateAddons(Request $request, $bookingId)
    {
        $booking = DatVe::findOrFail($bookingId);

        if (Auth::id() != $booking->id_nguoi_dung || $booking->trang_thai != 'DRAFT') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'combos' => 'array',
            'combos.*.id' => 'exists:combo,id',
            'combos.*.quantity' => 'integer|min:1'
        ]);

        // Xóa combos cũ
        $booking->chiTietCombo()->delete();

        // Thêm combos mới
        $comboTotal = 0;
        if ($request->combos) {
            foreach ($request->combos as $comboData) {
                $combo = Combo::find($comboData['id']);
                $quantity = $comboData['quantity'] ?? 1;

                ChiTietCombo::create([
                    'id_dat_ve' => $booking->id,
                    'id_combo' => $combo->id,
                    'so_luong' => $quantity,
                    'gia_ap_dung' => $combo->gia
                ]);

                $comboTotal += $combo->gia * $quantity;
            }
        }

        // Tính lại tổng tiền
        $seatTotal = $booking->chiTietDatVe()->sum('gia');
        $total = $seatTotal + $comboTotal;
        $booking->update(['tong_tien' => $total]);

        return response()->json([
            'success' => true,
            'total' => $total,
            'combo_total' => $comboTotal
        ]);
    }

    /**
     * Hiển thị trang checkout
     */
    public function checkout($bookingId)
    {
        $booking = DatVe::with([
            'chiTietDatVe.ghe.seatType',
            'chiTietCombo.combo',
            'suatChieu.phim',
            'suatChieu.phongChieu'
        ])->findOrFail($bookingId);

        if (Auth::id() != $booking->id_nguoi_dung || $booking->trang_thai != 'DRAFT') {
            abort(403);
        }

        // Tính lại tổng tiền
        $seatTotal = $booking->chiTietDatVe()->sum('gia');
        $comboTotal = $booking->chiTietCombo()->sum(DB::raw('gia_ap_dung * so_luong'));
        $total = $seatTotal + $comboTotal;
        $booking->update(['tong_tien' => $total]);

        return view('booking.checkout', compact('booking'));
    }

    /**
     * Xử lý thanh toán
     */
    public function processPayment(Request $request, $bookingId)
    {
        $booking = DatVe::with('chiTietDatVe')->findOrFail($bookingId);

        if (Auth::id() != $booking->id_nguoi_dung || $booking->trang_thai != 'DRAFT') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'payment_method' => 'required|in:vnpay,momo,credit_card,cash',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email|max:255'
        ]);

        // Cập nhật thông tin khách hàng
        $booking->update([
            'ten_khach_hang' => $request->customer_name,
            'so_dien_thoai' => $request->customer_phone,
            'email' => $request->customer_email
        ]);

        // Kiểm tra lại ghế có còn khả dụng không
        $showId = $booking->id_suat_chieu;
        $seatIds = $booking->chiTietDatVe()->pluck('id_ghe')->toArray();
        $bookedSeatIds = $this->getBookedSeatIds($showId);

        foreach ($seatIds as $seatId) {
            if (in_array($seatId, $bookedSeatIds)) {
                return redirect()->route('booking.seats', $showId)
                    ->with('error', 'Một số ghế đã được đặt bởi người khác. Vui lòng chọn lại.');
            }
        }

        // Nếu là thanh toán online, redirect đến cổng thanh toán
        if (in_array($request->payment_method, ['vnpay', 'momo', 'credit_card'])) {
            // TODO: Implement payment gateway integration
            // Tạm thời giả lập redirect
            $paymentUrl = route('booking.payment.process', [
                'booking_id' => $bookingId,
                'method' => $request->payment_method
            ]);

            return response()->json([
                'success' => true,
                'redirect' => $paymentUrl
            ]);
        }

        // Thanh toán tại quầy
        $booking->update(['trang_thai' => 'PENDING']);

        return redirect()->route('booking.result', ['booking_id' => $bookingId]);
    }

    /**
     * Xử lý callback từ cổng thanh toán
     */
    public function paymentCallback(Request $request)
    {
        $bookingId = $request->booking_id;
        $status = $request->status; // SUCCESS, FAILED, CANCELLED

        $booking = DatVe::findOrFail($bookingId);

        if ($status === 'SUCCESS') {
            // Giải phóng lock
            $showId = $booking->id_suat_chieu;
            $seatIds = $booking->chiTietDatVe()->pluck('id_ghe')->toArray();
            
            foreach ($seatIds as $seatId) {
                $cacheKey = "seat_lock:{$showId}:{$seatId}";
                Cache::forget($cacheKey);
            }

            // Cập nhật trạng thái
            $booking->update(['trang_thai' => 'PAID']);

            // Tạo thanh toán record
            DB::table('thanh_toan')->insert([
                'id_dat_ve' => $booking->id,
                'phuong_thuc' => $request->payment_method ?? 'online',
                'so_tien' => $booking->tong_tien,
                'trang_thai' => 'success',
                'thoi_gian' => now()
            ]);
        } else {
            $booking->update(['trang_thai' => strtoupper($status)]);
        }

        return redirect()->route('booking.result', ['booking_id' => $bookingId]);
    }

    /**
     * Hiển thị kết quả thanh toán
     */
    public function result(Request $request)
    {
        $bookingId = $request->booking_id;
        $booking = DatVe::with([
            'chiTietDatVe.ghe.seatType',
            'chiTietCombo.combo',
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'thanhToan'
        ])->findOrFail($bookingId);

        if (Auth::id() != $booking->id_nguoi_dung) {
            abort(403);
        }

        return view('booking.result', compact('booking'));
    }

    /**
     * Danh sách vé của tôi
     */
    public function tickets(Request $request)
    {
        $user = Auth::user();
        
        $bookings = DatVe::where('id_nguoi_dung', $user->id)
            ->whereIn('trang_thai', ['PAID', 'CONFIRMED'])
            ->with([
                'chiTietDatVe.ghe.seatType',
                'chiTietCombo.combo',
                'suatChieu.phim',
                'suatChieu.phongChieu'
            ])
            ->orderBy('created_at', 'desc');

        // Lọc theo ngày
        if ($request->date) {
            $date = Carbon::parse($request->date);
            $bookings->whereHas('suatChieu', function($query) use ($date) {
                $query->whereDate('thoi_gian_bat_dau', $date);
            });
        }

        $bookings = $bookings->paginate(10);

        return view('booking.tickets', compact('bookings'));
    }

    /**
     * Helper: Lấy danh sách ghế đã đặt
     */
    private function getBookedSeatIds($showId)
    {
        return DB::table('chi_tiet_dat_ve as ctdv')
            ->join('dat_ve as dv', 'ctdv.id_dat_ve', '=', 'dv.id')
            ->where('dv.id_suat_chieu', $showId)
            ->where('dv.trang_thai', '!=', 'CANCELLED')
            ->whereIn('dv.trang_thai', ['PAID', 'CONFIRMED', 'PENDING'])
            ->pluck('ctdv.id_ghe')
            ->toArray();
    }

    /**
     * Helper: Lấy danh sách ghế đang bị lock
     */
    private function getLockedSeats($showId)
    {
        $showtime = SuatChieu::with('phongChieu.seats')->find($showId);
        if (!$showtime || !$showtime->phongChieu) {
            return [];
        }

        $seatIds = $showtime->phongChieu->seats->pluck('id')->toArray();

        $locked = [];
        foreach ($seatIds as $seatId) {
            $cacheKey = "seat_lock:{$showId}:{$seatId}";
            $lock = Cache::get($cacheKey);
            
            if ($lock && isset($lock['expires_at']) && $lock['expires_at'] > now()->timestamp) {
                $locked[$seatId] = $lock;
            } else {
                // Xóa lock hết hạn
                Cache::forget($cacheKey);
            }
        }

        return $locked;
    }
}

