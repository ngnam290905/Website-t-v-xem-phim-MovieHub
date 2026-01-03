<?php

namespace App\Http\Controllers;

use App\Models\DatVe;
use App\Models\Combo;
use App\Models\KhuyenMai;
use App\Models\Phim;
use App\Models\SuatChieu;
use App\Models\ChiTietDatVe;
use App\Models\ChiTietCombo;
use App\Models\Ghe;
use App\Models\LoaiGhe;
use App\Models\ShowtimeSeat;
use App\Models\ThanhToan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Http\Controllers\PaymentController;

class BookingController extends Controller
{
    /**
     * Helper method to check if seat is VIP
     */
    private function isVipSeat($seat)
    {
        // Database uses id_loai = 2 for VIP seats
        if ($seat->id_loai == 2) {
            return true;
        }
        // Or check by name if id_loai has different values
        if ($seat->loaiGhe && stripos($seat->loaiGhe->ten_loai, 'vip') !== false) {
            return true;
        }
        return false;
    }

    private function buildRowStates(int $showtimeId, array $selectedSeatCodes): array
    {
        $showtime = \App\Models\SuatChieu::find($showtimeId);
        if (!$showtime) return [];

        // Load all seats of the room ordered by row then number
        $seats = \App\Models\Ghe::where('id_phong', $showtime->id_phong)
            ->with('loaiGhe')
            ->get()
            ->sortBy(function ($g) {
                $row = is_string($g->so_ghe) ? substr($g->so_ghe, 0, 1) : 'A';
                $num = (int)preg_replace('/[^0-9]/', '', (string)$g->so_ghe);
                return $row . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
            })
            ->values();

        // Map booked seats for this showtime (treat pending/paid as occupied)
        $occupiedIds = \DB::table('chi_tiet_dat_ve as c')
            ->join('dat_ve as d', 'd.id', '=', 'c.id_dat_ve')
            ->where('d.id_suat_chieu', $showtimeId)
            ->whereIn('d.trang_thai', [0, 1])
            ->pluck('c.id_ghe')
            ->toArray();

        $selectedSet = collect($selectedSeatCodes)->map(function ($code) {
            return strtoupper(trim($code));
        })->filter()->values()->all();

        $rows = [];
        foreach ($seats as $g) {
            $label = (string)$g->so_ghe;
            $row = substr($label, 0, 1);
            if ($row === '' || !ctype_alpha($row)) continue;
            $rows[$row] = $rows[$row] ?? [];
            $state = 0;
            if (in_array($g->id, $occupiedIds)) {
                $state = 1;
            }
            if (in_array(strtoupper($label), $selectedSet, true)) {
                $state = 2; // selection overrides
            }
            $rows[$row][] = $state;
        }

        return $rows;
    }

    /**
     * Start VNPAY payment for current hold booking
     */
    /**
     * Start VNPAY payment or Offline booking
     */
    public function processPayment(\Illuminate\Http\Request $request, $bookingId)
    {
        // 1. Lấy phương thức thanh toán từ form (mặc định là online nếu không có)
        $paymentMethod = $request->input('payment_method', 'online');

        // bookingId ở đây là hold id lưu trong session
        $holdId = session('booking.hold_id');
        if (!$holdId || $holdId !== $bookingId) {
            return redirect()->route('booking.seats', ['showId' => session('booking.showtime_id')])
                ->with('error', 'Phiên giữ ghế đã hết hạn hoặc không hợp lệ.');
        }

        $showtimeId = session('booking.showtime_id');
        $selectedSeatCodes = session('booking.selected_seat_codes', []);
        if (!$showtimeId || empty($selectedSeatCodes)) {
            return redirect()->back()->with('error', 'Thiếu thông tin ghế để thanh toán.');
        }

        // --- BẮT ĐẦU TÍNH TOÁN TIỀN ---
        $amount = 0;
        $showtime = \App\Models\SuatChieu::find($showtimeId);
        $seats = collect();

        if ($showtime) {
            $seats = \App\Models\Ghe::where('id_phong', $showtime->id_phong)
                ->whereIn('so_ghe', $selectedSeatCodes)
                ->with('loaiGhe')
                ->get()
                ->keyBy('so_ghe');

            $selected = collect($selectedSeatCodes)
                ->map(function ($code) use ($seats) {
                    $seat = $seats->get($code);
                    $row = is_string($code) && strlen($code) > 0 ? substr($code, 0, 1) : null;
                    $num = (int) preg_replace('/[^0-9]/', '', (string) $code);
                    return [
                        'code' => $code,
                        'row' => $row,
                        'num' => $num,
                        'isCouple' => $seat ? $this->isCoupleSeat($seat) : false,
                        'isVip' => $seat ? $this->isVipSeat($seat) : false,
                    ];
                })
                ->sortBy(['row', 'num'])
                ->values();

            $i = 0;
            // CẬP NHẬT GIÁ CƠ BẢN: 100.000đ
            $base = 100000;

            while ($i < $selected->count()) {
                $cur = $selected[$i];
                $next = $selected[$i + 1] ?? null;
                // Logic tính giá ghế đôi
                if ($cur['isCouple'] && $next && $next['isCouple'] && $cur['row'] === $next['row'] && $next['num'] === $cur['num'] + 1) {
                    // Ghế đôi: Giả sử hệ số giá đôi ~ 2.0 hoặc lấy giá cứng 200k
                    // Ở đây dùng logic cộng dồn như code cũ nhưng với giá trị cập nhật
                    // Giá trị hiển thị mong muốn: 200k/cặp
                    $s1 = $seats->get($cur['code']);
                    $s2 = $seats->get($next['code']);

                    // Nếu DB chưa set hệ số, ta hardcode giá trị tiền luôn cho chính xác
                    // Mỗi ghế đơn trong cặp đôi = 100k -> Tổng 200k
                    $amount += 200000;

                    $i += 2;
                } else {
                    $s = $seats->get($cur['code']);
                    // Giá VIP: 150k, Thường: 100k
                    if ($this->isVipSeat($s)) {
                        $amount += 150000;
                    } else {
                        $amount += 100000;
                    }
                    $i += 1;
                }
            }
        }
        if ($amount <= 0) $amount = 100000; // Giá fallback

        // Cộng tiền Combo
        $selectedCombos = collect(session('booking.selected_combos', []));
        if ($selectedCombos->isNotEmpty()) {
            $comboTotal = 0;
            foreach ($selectedCombos as $c) {
                $price = isset($c['gia']) ? (float) $c['gia'] : 0;
                $qty = isset($c['so_luong']) ? (int) $c['so_luong'] : 0;
                if ($price > 0 && $qty > 0) {
                    $comboTotal += (int) round($price * $qty);
                }
            }
            $amount += $comboTotal;
        }

        // Cộng tiền Foods
        $selectedFoods = collect(session('booking.selected_foods', []));
        if ($selectedFoods->isNotEmpty()) {
            $foodTotal = 0;
            foreach ($selectedFoods as $f) {
                $price = isset($f['price']) ? (float) $f['price'] : 0;
                $qty = isset($f['quantity']) ? (int) $f['quantity'] : 0;
                if ($price > 0 && $qty > 0) {
                    $foodTotal += (int) round($price * $qty);
                }
            }
            $amount += $foodTotal;
        }

        // Áp dụng khuyến mãi
        $promoId = $request->input('promo_id');
        $promoCode = strtoupper(trim((string)$request->input('promo_code', '')));
        if (!$promoId && $promoCode !== '') {
            $promoLookup = \App\Models\KhuyenMai::where('trang_thai', 1)
                ->where('ngay_bat_dau', '<=', now())
                ->where('ngay_ket_thuc', '>=', now())
                ->where(function ($q) use ($promoCode) {
                    $q->where('ma_km', $promoCode);
                })
                ->first();
            if ($promoLookup) {
                $promoId = $promoLookup->id;
            }
        }

        if ($promoId) {
            $promo = \App\Models\KhuyenMai::where('trang_thai', 1)
                ->where('ngay_bat_dau', '<=', now())
                ->where('ngay_ket_thuc', '>=', now())
                ->find($promoId);
            if ($promo) {
                $type = strtolower($promo->loai_giam);
                $val = (float)$promo->gia_tri_giam;
                $discount = 0;
                if ($type === 'phantram') {
                    $discount = round($amount * ($val / 100));
                } else {
                    $discount = ($val >= 1000) ? $val : $val * 1000;
                }
                $amount = max(0, $amount - (int)$discount);
            }
        }
        // --- KẾT THÚC TÍNH TOÁN ---

        // Bắt đầu Transaction tạo đơn hàng
        try {
            $selectedFoods = collect(session('booking.selected_foods', []));
            $booking = \DB::transaction(function () use ($showtimeId, $selectedSeatCodes, $seats, $amount, $paymentMethod, $promoId, $selectedCombos, $selectedFoods) {
                $userId = Auth::id();
                $conflictedSeats = [];

                // Kiểm tra ghế đã bị đặt chưa (Concurrency Check)
                foreach ($selectedSeatCodes as $code) {
                    $seat = $seats->get($code);
                    if (!$seat) continue;

                    $seatId = $seat->id;
                    $ghe = \App\Models\Ghe::where('id', $seatId)->lockForUpdate()->first();

                    if (!$ghe) {
                        $conflictedSeats[] = $code;
                        continue;
                    }

                    // Check bảng chi_tiet_dat_ve (Ghế đã bán hoặc đang chờ thanh toán của người khác)
                    $isTaken = \App\Models\ChiTietDatVe::whereHas('datVe', function ($query) use ($showtimeId, $userId) {
                        $query->where('id_suat_chieu', $showtimeId)
                            ->whereIn('trang_thai', [0, 1]) // 0: Pending, 1: Paid
                            ->where('id_nguoi_dung', '!=', $userId);
                    })
                        ->where('id_ghe', $seatId)
                        ->exists();

                    if ($isTaken) {
                        $conflictedSeats[] = $code;
                        continue;
                    }
                }

                if (!empty($conflictedSeats)) {
                    throw new \Exception('Một hoặc nhiều ghế đã được đặt: ' . implode(', ', $conflictedSeats));
                }

                // --- XỬ LÝ LOGIC ONLINE / OFFLINE ---
                $expiresAt = null;
                $phuongThucDB = 1; // Mặc định 1: Online

                if ($paymentMethod === 'offline') {
                    // Tại quầy: Hạn là trước giờ chiếu 30 phút
                    $showtimeObj = \App\Models\SuatChieu::find($showtimeId);
                    $start = \Carbon\Carbon::parse($showtimeObj->thoi_gian_bat_dau);
                    if (now()->diffInMinutes($start, false) < 30) {
                        throw new \Exception('Đã quá trễ để đặt vé giữ chỗ (phải trước 30 phút). Vui lòng thanh toán Online.');
                    }
                    $expiresAt = $start->subMinutes(30);
                    $phuongThucDB = 2; // 2: Offline
                } else {
                    // Online: Hạn 15 phút
                    $expiresAt = \Carbon\Carbon::now()->addMinutes(15);
                    $phuongThucDB = 1;
                }

                // 1) Tạo Booking
                $booking = \App\Models\DatVe::create([
                    'id_nguoi_dung' => $userId,
                    'id_suat_chieu' => $showtimeId,
                    'trang_thai' => 0, // Trạng thái ban đầu luôn là Pending
                    'expires_at' => $expiresAt,
                    'tong_tien' => $amount,
                    'id_khuyen_mai' => $promoId,
                    'phuong_thuc_thanh_toan' => $phuongThucDB
                ]);

                // 2) Tạo chi tiết vé
                foreach ($selectedSeatCodes as $code) {
                    $seat = $seats->get($code);
                    if (!$seat) continue;
                    // Cập nhật giá lưu vào DB theo giá mới
                    $price = $this->isCoupleSeat($seat) ? 200000 : ($this->isVipSeat($seat) ? 150000 : 100000);

                    \App\Models\ChiTietDatVe::create([
                        'id_dat_ve' => $booking->id,
                        'id_ghe' => $seat->id,
                        'gia' => $price,
                    ]);
                }

                // Lưu Combo
                foreach ($selectedCombos as $c) {
                    if (($c['so_luong'] ?? 0) > 0) {
                        \App\Models\ChiTietCombo::create([
                            'id_dat_ve' => $booking->id,
                            'id_combo' => $c['id_combo'],
                            'so_luong' => $c['so_luong'],
                            'gia_ap_dung' => $c['gia']
                        ]);
                    }
                }

                // Lưu Foods
                foreach ($selectedFoods as $f) {
                    if (($f['quantity'] ?? 0) > 0) {
                        $food = \App\Models\Food::find($f['food_id'] ?? null);
                        if ($food && $food->stock >= ($f['quantity'] ?? 0)) {
                            \App\Models\ChiTietFood::create([
                                'id_dat_ve' => $booking->id,
                                'food_id' => $f['food_id'],
                                'quantity' => $f['quantity'],
                                'price' => $f['price']
                            ]);
                        }
                    }
                }

                // 3) Tạo bản ghi thanh toán
                \App\Models\ThanhToan::create([
                    'id_dat_ve' => $booking->id,
                    'phuong_thuc' => ($paymentMethod === 'offline') ? 'Tiền mặt' : 'VNPAY',
                    'so_tien' => $amount,
                    'trang_thai' => 0, // Chưa thanh toán
                    'thoi_gian' => now(),
                ]);

                // 4) Nhả ghế đang giữ trong Service (Vì giờ ghế đã được lưu an toàn trong dat_ve)
                if ($paymentMethod === 'offline') {
                    $seatIds = $seats->whereIn('so_ghe', $selectedSeatCodes)->pluck('id')->toArray();
                    app(\App\Services\SeatHoldService::class)->releaseSeats($showtimeId, $seatIds, $userId);
                }

                return $booking;
            });

            // Store session map
            if ($holdId && $booking) {
                session(['booking.mapped.' . $holdId => $booking->id]);
            }

            // Lưu combo và food vào session để lần sau đặt lại vẫn còn
            if ($booking) {
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
                
                // Lưu vào session (không ghi đè nếu đã có)
                if (!empty($savedCombos)) {
                    session(['booking.selected_combos' => $savedCombos]);
                }
                if (!empty($savedFoods)) {
                    session(['booking.selected_foods' => $savedFoods]);
                }
            }

            // --- RẼ NHÁNH CHUYỂN HƯỚNG ---
            if ($paymentMethod === 'offline') {
                // Nếu chọn Tại quầy -> Chuyển đến trang chi tiết vé
                return redirect()->route('booking.ticket.detail', ['id' => $booking->id])
                    ->with('success', 'Đặt vé giữ chỗ thành công! Vui lòng thanh toán tại quầy trước giờ chiếu 30 phút.');
            } else {
                // Nếu chọn Online -> Tạo URL VNPAY và chuyển hướng
                $vnp_Url = app(\App\Http\Controllers\PaymentController::class)->createVnpayUrl($booking->id, $amount);
                return redirect()->away($vnp_Url);
            }
        } catch (\Throwable $e) {
            Log::error('Booking failed', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Lỗi đặt vé: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to check if seat is Couple
     */
    private function isCoupleSeat($seat)
    {
        // Database uses id_loai = 3 for Couple seats
        if ($seat->id_loai == 3) {
            return true;
        }

        // Or check by name
        if ($seat->loaiGhe && (stripos($seat->loaiGhe->ten_loai, 'đôi') !== false || stripos($seat->loaiGhe->ten_loai, 'doi') !== false)) {
            return true;
        }

        return false;
    }

    /**
     * Helper method to get seat price
     */
    private function getSeatPrice($roomId, $row, $col)
    {
        // Find seat in database
        $seat = Ghe::where('id_phong', $roomId)
            ->where('so_ghe', $row . $col)
            ->with('loaiGhe')
            ->first();

        if (!$seat) {
            return 80000; // Default price
        }

        // Check if couple seat
        if ($this->isCoupleSeat($seat)) {
            return 200000;
        }

        // Check if VIP seat
        if ($this->isVipSeat($seat)) {
            return 120000;
        }

        // Regular seat
        return 80000;
    }

    public function index()
    {
        // Get current user ID
        $userId = Auth::id();

        // Debug: Check if user is authenticated
        if (!$userId) {
            Log::warning('User not authenticated when accessing bookings');
            return redirect()->route('login.form')->with('error', 'Vui lòng đăng nhập để xem lịch sử đặt vé');
        }

        // Clean up expired pending bookings for this user
        $expiredBookings = DatVe::where('id_nguoi_dung', $userId)
            ->where('trang_thai', 0) // Pending
            ->where(function ($query) {
                $query->whereNotNull('expires_at')
                    ->where('expires_at', '<=', now());
            })
            ->with('thanhToan')
            ->get();

        if ($expiredBookings->count() > 0) {
            foreach ($expiredBookings as $expiredBooking) {
                // Skip cleanup if payment was completed (avoid deleting tickets paid via late IPN)
                $hasPaid = $expiredBooking->thanhToan && (int)$expiredBooking->thanhToan->trang_thai === 1;
                if ($hasPaid) {
                    // Ensure booking is marked paid and expiration cleared
                    $expiredBooking->update(['trang_thai' => 1, 'expires_at' => null]);
                    continue;
                }

                // Safe to cleanup unpaid expired pending bookings
                ChiTietDatVe::where('id_dat_ve', $expiredBooking->id)->delete();
                ChiTietCombo::where('id_dat_ve', $expiredBooking->id)->delete();
                \App\Models\ChiTietFood::where('id_dat_ve', $expiredBooking->id)->delete();
                ThanhToan::where('id_dat_ve', $expiredBooking->id)->delete();
                $expiredBooking->delete();
            }

            Log::info('Cleaned up expired pending bookings', [
                'user_id' => $userId,
                'count' => $expiredBookings->count()
            ]);
        }

        // Get booking data for user with related showtime, movie, room, and seat details
        // Only show paid bookings (trang_thai = 1) or cancelled bookings (for history)
        // Do NOT show pending bookings - they are only for payment processing
        $bookings = DatVe::with([
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe',
            'khuyenMai',
            'chiTietCombo.combo',
            'thanhToan',
            'nguoiDung'
        ])
            ->where('id_nguoi_dung', $userId)
            ->whereIn('trang_thai', [1, 2]) // Only paid (1) or cancelled (2)
            // Show newest first. Do not use updated_at because model disables it
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        // Debug log
        Log::info('User Bookings Retrieved', [
            'user_id' => $userId,
            'user_email' => Auth::user()->email ?? 'N/A',
            'bookings_count' => $bookings->count(),
            'total' => $bookings->total(),
            'booking_ids' => $bookings->pluck('id')->toArray()
        ]);

        // Additional debug: Check if there are bookings in DB for this user
        $totalBookings = DatVe::where('id_nguoi_dung', $userId)->count();
        if ($totalBookings === 0) {
            Log::info('No bookings found in database for user', ['user_id' => $userId]);
        }

        // Fix bookings that have been paid but status is still pending
        $paidButPending = DatVe::where('id_nguoi_dung', $userId)
            ->where('trang_thai', 0) // Pending
            ->whereHas('thanhToan', function ($query) {
                $query->where('trang_thai', 1); // Payment is paid
            })
            ->get();

        if ($paidButPending->count() > 0) {
            Log::warning('Found bookings with paid payment but pending status', [
                'user_id' => $userId,
                'count' => $paidButPending->count(),
                'booking_ids' => $paidButPending->pluck('id')->toArray()
            ]);

            foreach ($paidButPending as $booking) {
                $booking->update([
                    'trang_thai' => 1, // Update to paid
                    'expires_at' => null // Clear expiration
                ]);
                Log::info('Fixed booking status', [
                    'booking_id' => $booking->id,
                    'user_id' => $userId
                ]);
            }
        }

        return view('user.bookings', compact('bookings'));
    }

    /**
     * Show detailed ticket view with QR code (alias for ticketDetail)
     */
    public function ticketDetail($id)
    {
        return $this->show($id);
    }

    /**
     * Show detailed ticket view with QR code
     */
    public function show($id)
    {
        $booking = DatVe::with([
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe',
            'khuyenMai',
            'chiTietCombo.combo',
            'chiTietFood.food',
            'thanhToan',
            'nguoiDung'
        ])
            ->where('id', $id)
            ->where('id_nguoi_dung', Auth::id())
            ->firstOrFail();

        $showtime = optional($booking->suatChieu);
        $movie = optional($showtime->phim);
        $room = optional($showtime->phongChieu);
        $seatList = $booking->chiTietDatVe->map(function ($ct) {
            return optional($ct->ghe)->so_ghe;
        })->filter()->values()->all();

        // Calculate totals
        $comboItems = $booking->chiTietCombo ?? collect();
        $foodItems = $booking->chiTietFood ?? collect();
        $promo = $booking->khuyenMai;
        $comboTotal = $comboItems->sum(function ($i) {
            return (float)$i->gia_ap_dung * max(1, (int)$i->so_luong);
        });
        $foodTotal = $foodItems->sum(function ($f) {
            return (float)$f->price * max(1, (int)$f->quantity);
        });
        $seatTotal = (float) $booking->chiTietDatVe->sum('gia');
        $subtotal = $seatTotal + $comboTotal + $foodTotal;
        $promoDiscount = 0;

        if ($promo) {
            $type = strtolower($promo->loai_giam);
            $val = (float)$promo->gia_tri_giam;
            $min = 0;
            if ($subtotal >= $min) {
                if ($type === 'phantram') {
                    $promoDiscount = round($subtotal * ($val / 100));
                } else {
                    $promoDiscount = ($val >= 1000) ? $val : $val * 1000;
                }
            }
        }

        $computedTotal = max(0, $subtotal - $promoDiscount);

        // Payment method
        $pt = $booking->phuong_thuc_thanh_toan;
        if (!$pt) {
            $map = optional($booking->thanhToan)->phuong_thuc ?? null;
            $pt = $map === 'online' ? 1 : ($map === 'offline' ? 2 : null);
        }

        // Generate QR code data for confirmed tickets
        $qrCodeData = null;
        if ($booking->trang_thai == 1) {
            // QR code contains ticket_id for scanning
            $qrCodeData = 'ticket_id=' . $booking->id;
            // If ticket_code exists, use it instead
            if ($booking->ticket_code) {
                $qrCodeData = 'ticket_id=' . $booking->ticket_code;
            }
        }

        // Check which view exists and use it
        $viewName = view()->exists('booking.ticket-detail') ? 'booking.ticket-detail' : 'user.ticket-detail';

        $isPaid = $booking->trang_thai == 1;
        $isPrinted = $booking->da_in ?? false;

        return view($viewName, compact(
            'booking',
            'showtime',
            'movie',
            'room',
            'seatList',
            'comboItems',
            'foodItems',
            'promo',
            'promoDiscount',
            'computedTotal',
            'pt',
            'qrCodeData',
            'isPaid',
            'isPrinted'
        ));
    }

    /**
     * Mark ticket as printed (only once)
     */
    public function markAsPrinted($id)
    {
        $booking = DatVe::where('id', $id)
            ->where('id_nguoi_dung', Auth::id())
            ->firstOrFail();

        // Chỉ đánh dấu nếu chưa in
        if (!$booking->da_in) {
            $booking->update([
                'da_in' => true,
                'thoi_gian_in' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vé đã được đánh dấu là đã in',
                'printed_at' => $booking->thoi_gian_in->format('d/m/Y H:i:s')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Vé này đã được in rồi',
            'printed_at' => $booking->thoi_gian_in ? $booking->thoi_gian_in->format('d/m/Y H:i:s') : null
        ], 400);
    }

    /**
     * Display seat selection page for a specific showtime
     * This is the new flow: user selects showtime first, then comes here to select seats
     */
    public function showSeatsPage($showtimeId)
    {
        try {
            \Log::info('showSeatsPage called', ['showtimeId' => $showtimeId]);
            
            $showtime = SuatChieu::with(['phim', 'phongChieu'])->findOrFail($showtimeId);
            
            \Log::info('Showtime found', [
                'id' => $showtime->id,
                'trang_thai' => $showtime->trang_thai,
                'thoi_gian_bat_dau' => $showtime->thoi_gian_bat_dau,
                'has_movie' => $showtime->phim ? true : false,
                'has_room' => $showtime->phongChieu ? true : false,
            ]);

            if ($showtime->trang_thai != 1) {
                \Log::warning('Showtime not active', ['showtimeId' => $showtimeId, 'trang_thai' => $showtime->trang_thai]);
                return redirect()->route('booking.index')
                    ->with('error', 'Suất chiếu không khả dụng.');
            }

            if ($showtime->thoi_gian_bat_dau < now()) {
                \Log::warning('Showtime already started', [
                    'showtimeId' => $showtimeId, 
                    'thoi_gian_bat_dau' => $showtime->thoi_gian_bat_dau,
                    'now' => now()
                ]);
                return redirect()->route('booking.index')
                    ->with('error', 'Suất chiếu đã bắt đầu.');
            }

            $movie = $showtime->phim;
            $room = $showtime->phongChieu;

            if (!$movie || !$room) {
                \Log::error('Showtime missing movie or room', [
                    'showtimeId' => $showtimeId,
                    'has_movie' => $movie ? true : false,
                    'has_room' => $room ? true : false,
                ]);
                return redirect()->route('booking.index')
                    ->with('error', 'Thông tin suất chiếu không hợp lệ.');
            }

            // Get combos, foods and promotions
            $combos = Combo::where('trang_thai', 1)->get();
            try {
                $foods = \App\Models\Food::where('is_active', true)->where('stock', '>', 0)->get();
            } catch (\Exception $e) {
                \Log::warning('Foods table not available', ['error' => $e->getMessage()]);
                $foods = collect(); // Empty collection if foods table doesn't exist
            }
            $khuyenmais = KhuyenMai::where('trang_thai', 1)
                ->where('ngay_bat_dau', '<=', now())
                ->where('ngay_ket_thuc', '>=', now())
                ->get();

            // Build seats collection required by the seats view
            $seats = Ghe::where('id_phong', $showtime->id_phong)
                ->with('loaiGhe')
                ->get()
                ->map(function ($seat) use ($showtime) {
                    $seat->seatType = $seat->loaiGhe;
                    $seat->booking_status = 'available';
                    $seat->so_hang = is_string($seat->so_ghe) && strlen($seat->so_ghe) > 0
                        ? substr($seat->so_ghe, 0, 1)
                        : null;
                    return $seat;
                });

            // Preload booked/held statuses so UI disables them immediately
            try {
                $seatHoldService = app(\App\Services\SeatHoldService::class);
                $currentUserId = \Illuminate\Support\Facades\Auth::id();
                foreach ($seats as $s) {
                    $st = $seatHoldService->getSeatStatus($showtime->id, $s->id, $currentUserId);
                    if ($st === 'booked') {
                        $s->booking_status = 'booked';
                    } elseif ($st === 'held_by_other') {
                        $s->booking_status = 'locked_by_other';
                    } elseif ($st === 'held_by_me') {
                        $s->booking_status = 'locked_by_me';
                    }
                }
            } catch (\Throwable $e) {
                // ignore preload errors; frontend refresh will handle
            }

            // Không tự động load combo/food - để người dùng tự chọn
            $selectedCombos = collect();
            $selectedFoods = collect();
            $existingBooking = null;
            
            // Xóa combo/food cũ khỏi session khi vào showtime mới để đảm bảo người dùng tự chọn
            $sessionShowtimeId = session('booking.showtime_id');
            if ($sessionShowtimeId != $showtimeId) {
                session()->forget(['booking.selected_combos', 'booking.selected_foods']);
            }
            
            return view('booking.seats', compact('showtime', 'movie', 'room', 'combos', 'foods', 'khuyenmais', 'existingBooking', 'seats', 'selectedCombos', 'selectedFoods'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Showtime not found', [
                'showtime_id' => $showtimeId,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('booking.index')
                ->with('error', 'Không tìm thấy suất chiếu.');
        } catch (\Exception $e) {
            \Log::error('Error loading seat selection page', [
                'showtime_id' => $showtimeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('booking.index')
                ->with('error', 'Không thể tải trang chọn ghế. Vui lòng thử lại.');
        }
    }

    /**
     * Backward-compatible alias for routes that call showSeats
     */
    public function showSeats($showId)
    {
        \Log::info('showSeats called', ['showId' => $showId, 'showtimeId param' => $showId]);
        return $this->showSeatsPage($showId);
    }

    /**
     * Hold seat(s) for current user (NEW LOGIC - 10 minutes, DB-based)
     */
    public function holdSeat(Request $request, $showId)
    {
        $request->validate([
            'seat_id' => 'sometimes|integer',
            'seat_ids' => 'sometimes|array|min:1',
            'seat_ids.*' => 'integer',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        // Validate showtime exists
        $showtime = \App\Models\SuatChieu::find($showId);
        if (!$showtime) {
            return response()->json([
                'success' => false,
                'message' => 'Suất chiếu không tồn tại'
            ], 404);
        }

        $seatId = $request->input('seat_id');
        $seatIds = $request->input('seat_ids');
        $sessionId = $request->session()->getId();

        // Validate that at least one seat is provided
        if (empty($seatIds) && empty($seatId)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn ít nhất một ghế'
            ], 400);
        }

        try {
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            // Batch mode
            if (is_array($seatIds) && count($seatIds) > 0) {
                $result = $seatHoldService->holdSeats($showId, array_map('intval', $seatIds), $userId, $sessionId);

                if (!$result['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['message'] ?? 'Không thể giữ ghế',
                        'failed_seats' => $result['failed_seats'] ?? []
                    ], 400);
                }

                // Track temporary hold in session for continueToPayment
                if (!session('booking.hold_id')) {
                    $generatedHoldId = 'hold_' . $showId . '_' . uniqid('', true);
                    session([
                        'booking.hold_id' => $generatedHoldId,
                        'booking.showtime_id' => (int)$showId,
                    ]);
                }

                $expiresAt = !empty($result['holds']) ? $result['holds'][0]->expires_at->timestamp : (time() + 10 * 60);

                return response()->json([
                    'success' => true,
                    'holds' => collect($result['holds'])->map(function ($hold) {
                        return [
                            'id' => $hold->id,
                            'seat_id' => $hold->seat_id,
                            'expires_at' => $hold->expires_at->timestamp,
                        ];
                    })->toArray(),
                    'expires_at' => $expiresAt,
                    'message' => $result['message']
                ]);
            }

            // Single mode
            if (empty($seatId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ít nhất một ghế'
                ], 400);
            }

            $result = $seatHoldService->holdSeat($showId, intval($seatId), $userId, $sessionId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Không thể giữ ghế'
                ], 400);
            }

            // Track temporary hold in session for continueToPayment
            if (!session('booking.hold_id')) {
                $generatedHoldId = 'hold_' . $showId . '_' . uniqid('', true);
                session([
                    'booking.hold_id' => $generatedHoldId,
                    'booking.showtime_id' => (int)$showId,
                ]);
            }

            return response()->json([
                'success' => true,
                'hold' => [
                    'id' => $result['hold']->id,
                    'seat_id' => $result['hold']->seat_id,
                    'expires_at' => $result['hold']->expires_at->timestamp,
                    'expires_at_iso' => $result['hold']->expires_at->toIso8601String(),
                ],
                'message' => $result['message']
            ]);
        } catch (\Throwable $e) {
            \Log::error('holdSeat error: ' . $e->getMessage(), [
                'show_id' => $showId,
                'seat_id' => $seatId,
                'seat_ids' => $seatIds,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi giữ ghế: ' . ($e->getMessage() ?: 'Lỗi không xác định')
            ], 500);
        }
    }

    /**
     * Hold multiple seats at once (NEW LOGIC)
     */
    public function lockSeats(Request $request, $showId)
    {
        $request->validate([
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer'
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $seatIds = $request->input('seat_ids', []);
        $sessionId = $request->session()->getId();

        try {
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            $result = $seatHoldService->holdSeats($showId, array_map('intval', $seatIds), $userId, $sessionId);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Không thể giữ ghế',
                    'failed_seats' => $result['failed_seats'] ?? []
                ], 400);
            }

            // Track temporary hold in session for continueToPayment
            if (!session('booking.hold_id')) {
                $generatedHoldId = 'hold_' . $showId . '_' . uniqid('', true);
                session([
                    'booking.hold_id' => $generatedHoldId,
                    'booking.showtime_id' => (int)$showId,
                ]);
            }

            // Get expiration time from first hold
            $expiresAt = !empty($result['holds']) ? $result['holds'][0]->expires_at->timestamp : (time() + 10 * 60);

            return response()->json([
                'success' => true,
                'holds' => collect($result['holds'])->map(function ($hold) {
                    return [
                        'id' => $hold->id,
                        'seat_id' => $hold->seat_id,
                        'expires_at' => $hold->expires_at->timestamp,
                    ];
                })->toArray(),
                'expires_at' => $expiresAt,
                'message' => $result['message']
            ]);
        } catch (\Throwable $e) {
            \Log::error('lockSeats error: ' . $e->getMessage(), [
                'show_id' => $showId,
                'seat_ids' => $seatIds,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi giữ ghế: ' . ($e->getMessage() ?: 'Lỗi không xác định')
            ], 500);
        }
    }

    /**
     * Release seat hold (NEW LOGIC)
     */
    public function releaseSeat(Request $request, $showId)
    {
        $request->validate([
            'seat_id' => 'sometimes|integer',
            'seat_ids' => 'sometimes|array|min:1',
            'seat_ids.*' => 'integer',
        ]);

        $userId = Auth::id();
        $seatId = $request->input('seat_id');
        $seatIds = $request->input('seat_ids', []);
        $sessionId = $request->session()->getId();

        try {
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            if (is_array($seatIds) && count($seatIds) > 0) {
                $count = $seatHoldService->releaseSeats($showId, array_map('intval', $seatIds), $userId, $sessionId);
                return response()->json([
                    'success' => true,
                    'released_count' => $count,
                    'message' => "Đã nhả {$count} ghế"
                ]);
            }

            $released = $seatHoldService->releaseSeat($showId, intval($seatId), $userId, $sessionId);

            return response()->json([
                'success' => $released,
                'message' => $released ? 'Đã nhả ghế' : 'Không tìm thấy ghế đang giữ'
            ]);
        } catch (\Throwable $e) {
            \Log::error('releaseSeat error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi nhả ghế'
            ], 500);
        }
    }

    /**
     * Unlock seats for current user (NEW LOGIC)
     */
    public function unlockSeats(Request $request, $showId)
    {
        $request->validate([
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer'
        ]);

        $userId = Auth::id();
        $seatIds = $request->input('seat_ids', []);
        $sessionId = $request->session()->getId();

        try {
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            $count = $seatHoldService->releaseSeats($showId, array_map('intval', $seatIds), $userId, $sessionId);

            return response()->json([
                'success' => true,
                'released_count' => $count,
                'message' => "Đã nhả {$count} ghế"
            ]);
        } catch (\Throwable $e) {
            \Log::error('unlockSeats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi nhả ghế'
            ], 500);
        }
    }

    /**
     * Continue to payment: persist selected seats and move to checkout
     */
    public function continueToPayment(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'showtime_id' => 'required|integer',
            'seats' => 'required|array|min:1',
            'seats.*' => 'string',
            'booking_hold_id' => 'nullable|string',
            'combos' => 'nullable|array',
            'combos.*.id_combo' => 'required_with:combos|integer',
            'combos.*.so_luong' => 'required_with:combos|integer|min:0',
            'combos.*.gia' => 'required_with:combos|numeric|min:0',
            'foods' => 'nullable|array',
            'foods.*.food_id' => 'required_with:foods|integer',
            'foods.*.quantity' => 'required_with:foods|integer|min:0',
            'foods.*.price' => 'required_with:foods|numeric|min:0'
        ]);

        $holdId = session('booking.hold_id');
        if (!$holdId) {
            return response()->json(['success' => false, 'message' => 'Phiên giữ ghế đã hết hạn. Vui lòng chọn lại.'], 410);
        }

        // Validate No Single Seat Rule before saving
        $rowStates = $this->buildRowStates((int)$validated['showtime_id'], (array)$validated['seats']);
        $nsr = \App\Services\SeatPatternValidator::validateNoSingleSeatRule($rowStates);
        if (!$nsr['valid']) {
            return response()->json(['success' => false, 'message' => $nsr['message'] ?? 'Không được để ghế trống lẻ.'], 422);
        }

        // Save selected seat codes, combos, foods and showtime for the next step
        session([
            'booking.selected_seat_codes' => $validated['seats'],
            'booking.showtime_id' => $validated['showtime_id'],
            'booking.selected_combos' => collect($validated['combos'] ?? [])->filter(function ($c) {
                return isset($c['id_combo']) && ($c['so_luong'] ?? 0) > 0;
            })->values()->all(),
            'booking.selected_foods' => collect($validated['foods'] ?? [])->filter(function ($f) {
                return isset($f['food_id']) && ($f['quantity'] ?? 0) > 0;
            })->values()->all(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Redirect to existing checkout route with the hold id
     */
    public function showPaymentPage()
    {
        $holdId = session('booking.hold_id');
        if (!$holdId) {
            return redirect()->back()->with('error', 'Phiên giữ ghế đã hết hạn. Vui lòng chọn lại ghế.');
        }

        $showtimeId = session('booking.showtime_id');
        $selectedSeatCodes = session('booking.selected_seat_codes', []);
        $showtime = $showtimeId ? SuatChieu::with(['phim', 'phongChieu'])->find($showtimeId) : null;
        $movie = $showtime ? $showtime->phim : null;
        $room = $showtime ? $showtime->phongChieu : null;

        // Compute seat pricing for display with Admin rule (base 100000 * he_so_gia), couple = sum of two seats
        $seats = collect();
        $seatDetails = [];
        $totalSeatPrice = 0;
        if ($showtime) {
            $seats = Ghe::where('id_phong', $showtime->id_phong)
                ->whereIn('so_ghe', $selectedSeatCodes)
                ->with('loaiGhe')
                ->get()
                ->keyBy('so_ghe');

            $selected = collect($selectedSeatCodes)
                ->map(function ($code) use ($seats) {
                    $seat = $seats->get($code);
                    $row = is_string($code) && strlen($code) > 0 ? substr($code, 0, 1) : null;
                    $num = (int) preg_replace('/[^0-9]/', '', (string) $code);
                    $type = $seat ? (optional($seat->loaiGhe)->ten_loai ?? 'Thường') : 'Thường';
                    return [
                        'code' => $code,
                        'row' => $row,
                        'num' => $num,
                        'isCouple' => $seat ? $this->isCoupleSeat($seat) : false,
                        'isVip' => $seat ? $this->isVipSeat($seat) : false,
                        'type' => $type,
                    ];
                })
                ->sortBy(['row', 'num'])
                ->values();

            $i = 0;
            $base = 100000; // Admin base ticket price
            while ($i < $selected->count()) {
                $cur = $selected[$i];
                $next = $selected[$i + 1] ?? null;
                if ($cur['isCouple'] && $next && $next['isCouple'] && $cur['row'] === $next['row'] && $next['num'] === $cur['num'] + 1) {
                    $s1 = $seats->get($cur['code']);
                    $s2 = $seats->get($next['code']);
                    $m1 = (float) optional(optional($s1)->loaiGhe)->he_so_gia ?: 1.0;
                    $m2 = (float) optional(optional($s2)->loaiGhe)->he_so_gia ?: 1.0;
                    $p1 = (int) round($base * $m1);
                    $p2 = (int) round($base * $m2);
                    $seatDetails[] = ['code' => $cur['code'], 'type' => $cur['type'], 'price' => $p1];
                    $seatDetails[] = ['code' => $next['code'], 'type' => $next['type'], 'price' => $p2];
                    $totalSeatPrice += ($p1 + $p2);
                    $i += 2;
                } else {
                    $s = $seats->get($cur['code']);
                    $m = (float) optional(optional($s)->loaiGhe)->he_so_gia ?: 1.0;
                    $price = (int) round($base * $m);
                    $seatDetails[] = ['code' => $cur['code'], 'type' => $cur['type'], 'price' => $price];
                    $totalSeatPrice += $price;
                    $i += 1;
                }
            }
        }

        // Load selected combos from session and compute totals for display
        $comboDetails = [];
        $comboTotal = 0;
        $selectedCombosSession = collect(session('booking.selected_combos', []));
        if ($selectedCombosSession->isNotEmpty()) {
            $comboIds = $selectedCombosSession->pluck('id_combo')->unique()->values();
            $comboMap = Combo::whereIn('id', $comboIds)->get()->keyBy('id');
            foreach ($selectedCombosSession as $c) {
                $id = (int) ($c['id_combo'] ?? 0);
                $qty = (int) ($c['so_luong'] ?? 0);
                $price = (float) ($c['gia'] ?? 0);
                if ($qty <= 0) continue;
                $name = optional($comboMap->get($id))->ten ?? ('Combo #' . $id);
                $line = (int) round($price) * $qty;
                $comboDetails[] = [
                    'id' => $id,
                    'name' => $name,
                    'price' => (int) round($price),
                    'qty' => $qty,
                    'total' => $line,
                ];
                $comboTotal += $line;
            }
        }

        // Load selected foods from session and compute totals for display
        $foodDetails = [];
        $foodTotal = 0;
        $selectedFoodsSession = collect(session('booking.selected_foods', []));
        if ($selectedFoodsSession->isNotEmpty()) {
            $foodIds = $selectedFoodsSession->pluck('food_id')->unique()->values();
            $foodMap = \App\Models\Food::whereIn('id', $foodIds)->get()->keyBy('id');
            foreach ($selectedFoodsSession as $f) {
                $id = (int) ($f['food_id'] ?? 0);
                $qty = (int) ($f['quantity'] ?? 0);
                $price = (float) ($f['price'] ?? 0);
                if ($qty <= 0) continue;
                $name = optional($foodMap->get($id))->name ?? ('Đồ ăn #' . $id);
                $line = (int) round($price * $qty);
                $foodDetails[] = [
                    'id' => $id,
                    'name' => $name,
                    'price' => (int) round($price),
                    'qty' => $qty,
                    'total' => $line,
                ];
                $foodTotal += $line;
            }
        }

        // Read VNPAY env for on-screen debug (with legacy fallbacks)
        $dbg_vnp_TmnCode = trim((string) env('VNPAY_TMN_CODE', ''));
        $dbg_vnp_HashSecret = trim((string) env('VNPAY_HASH_SECRET', ''));
        $dbg_vnp_Url = rtrim(trim((string) env('VNPAY_URL', '')), '/');
        $dbg_vnp_ReturnUrl = trim((string) env('VNPAY_RETURN_URL', ''));
        if ($dbg_vnp_TmnCode === '') {
            $dbg_vnp_TmnCode = trim((string) env('VNP_TMN_CODE', ''));
        }
        if ($dbg_vnp_HashSecret === '') {
            $dbg_vnp_HashSecret = trim((string) env('VNP_HASH_SECRET', ''));
        }
        if ($dbg_vnp_Url === '') {
            $dbg_vnp_Url = rtrim(trim((string) env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html')), '/');
        }
        if ($dbg_vnp_ReturnUrl === '') {
            $dbg_vnp_ReturnUrl = trim((string) (env('VNP_RETURN_URL', url('/payment/vnpay-return'))));
        }

        // VNPAY debug info (masked) for troubleshooting on the payment page
        $vnpDebug = [
            'tmn_code' => $dbg_vnp_TmnCode,
            'return_url' => $dbg_vnp_ReturnUrl,
            'url' => $dbg_vnp_Url,
            'hash_present' => $dbg_vnp_HashSecret !== '',
            'source_keys' => [
                'tmn' => env('VNPAY_TMN_CODE') ? 'VNPAY_TMN_CODE' : (env('VNP_TMN_CODE') ? 'VNP_TMN_CODE' : 'none'),
                'hash' => env('VNPAY_HASH_SECRET') ? 'VNPAY_HASH_SECRET' : (env('VNP_HASH_SECRET') ? 'VNP_HASH_SECRET' : 'none'),
                'url'  => env('VNPAY_URL') ? 'VNPAY_URL' : (env('VNP_URL') ? 'VNP_URL' : 'default'),
                'ret'  => env('VNPAY_RETURN_URL') ? 'VNPAY_RETURN_URL' : (env('VNP_RETURN_URL') ? 'VNP_RETURN_URL' : 'default'),
            ]
        ];

        // Load all promotions for display; view will disable those not applicable
        $khuyenmais = KhuyenMai::orderBy('ngay_ket_thuc', 'desc')->get();

        return view('booking.payment', [
            'holdId' => $holdId,
            'showtime' => $showtime,
            'movie' => $movie,
            'room' => $room,
            'selectedSeatCodes' => $selectedSeatCodes,
            'seatDetails' => $seatDetails,
            'totalSeatPrice' => $totalSeatPrice,
            'comboDetails' => $comboDetails,
            'comboTotal' => $comboTotal,
            'foodDetails' => $foodDetails,
            'foodTotal' => $foodTotal,
            'khuyenmais' => $khuyenmais,
            'vnpDebug' => $vnpDebug,
        ]);
    }

    /**
     * Backward-compatible checkout route handler
     * Simply forward to showPaymentPage using session hold
     */
    public function checkout($bookingId)
    {
        // Ensure the booking id matches current hold, otherwise redirect back
        $holdId = session('booking.hold_id');
        if (!$holdId || $holdId !== $bookingId) {
            return redirect()->route('booking.seats', ['showId' => session('booking.showtime_id')])
                ->with('error', 'Phiên giữ ghế đã hết hạn hoặc không hợp lệ.');
        }
        return $this->showPaymentPage();
    }

    /**
     * Refresh seat statuses for a showtime (NEW LOGIC)
     */
    public function refreshSeats($showId)
    {
        $statuses = [];
        try {
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            $showtime = SuatChieu::find($showId);
            $currentUserId = Auth::id();

            if ($showtime) {
                $seats = Ghe::where('id_phong', $showtime->id_phong)->get();
                foreach ($seats as $seat) {
                    $status = $seatHoldService->getSeatStatus($showId, $seat->id, $currentUserId);

                    // Map status to frontend format
                    if ($status === 'booked') {
                        $statuses[$seat->id] = 'booked';
                    } elseif ($status === 'held_by_other') {
                        $statuses[$seat->id] = 'locked_by_other';
                    } elseif ($status === 'held_by_me') {
                        // Don't mark as locked if it's held by current user
                        // Frontend will show it as selected
                    }
                    // 'available' doesn't need to be in the response
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('refreshSeats error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'seats' => $statuses,
        ]);
    }

    /**
     * Confirm booking: Convert holds to booked (called after payment success)
     */
    public function confirmBooking(Request $request, $showId)
    {
        $request->validate([
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer'
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        $seatIds = $request->input('seat_ids', []);

        try {
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            $result = $seatHoldService->confirmBooking($showId, array_map('intval', $seatIds), $userId);

            return response()->json([
                'success' => $result,
                'message' => $result ? 'Đã xác nhận đặt vé' : 'Không thể xác nhận đặt vé'
            ]);
        } catch (\Throwable $e) {
            \Log::error('confirmBooking error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xác nhận đặt vé'
            ], 500);
        }
    }

    public function create($id = null)
    {
        // 1. Lấy thông tin phim
        $movie = null;
        if ($id) {
            $movie = Phim::find($id);
        }

        // Nếu không tìm thấy phim, lấy phim đầu tiên hoặc tạo dữ liệu giả
        if (!$movie) {
            $movie = Phim::first() ?? (object)[
                'id' => 1,
                'ten_phim' => 'Demo Movie',
                'thoi_luong' => 120,
                'poster' => 'images/default-poster.jpg'
            ];
        }

        // [QUAN TRỌNG] Khởi tạo biến $showtime mặc định là null để tránh lỗi Undefined variable
        $showtime = null;

        // Get real showtimes from database for this movie
        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movie->id)
            ->where('thoi_gian_ket_thuc', '>', now())
            ->where('thoi_gian_bat_dau', '<=', now()->addDays(7))
            ->get();

        $roomInfo = null;
        $seats = collect();
        $vipSeats = [];
        $vipRows = [];
        $coupleSeats = [];

        // Lọc showtime (giữ nguyên logic cũ của bạn)
        if ($showtimes->isEmpty()) {
            $showtimes = SuatChieu::with('phongChieu')
                ->where('id_phim', $movie->id)
                ->where('thoi_gian_bat_dau', '>=', now())
                ->orderBy('thoi_gian_bat_dau')
                ->get();
        }

        // Format dữ liệu suất chiếu cho frontend
        $showtimesMapped = $showtimes->map(function ($suat) {
            return [
                'id' => $suat->id,
                'label' => date('H:i - d/m/Y', strtotime($suat->thoi_gian_bat_dau)) . ' - ' . ($suat->phongChieu->ten_phong ?? 'Phòng 1'),
                'time' => date('H:i', strtotime($suat->thoi_gian_bat_dau)),
                'date' => date('d/m/Y', strtotime($suat->thoi_gian_bat_dau)),
                'room' => $suat->phongChieu->ten_phong ?? 'Phòng 1'
            ];
        });

        // 3. Lấy thông tin ghế và phòng của suất chiếu ĐẦU TIÊN
        if ($showtimes->isNotEmpty()) {
            $firstShowtime = $showtimes->first();
            $suatChieu = SuatChieu::with('phongChieu')->find($firstShowtime->id);
            $showtime = $suatChieu; // Gán biến để view dùng

            if ($suatChieu && $suatChieu->phongChieu) {
                $roomInfo = $suatChieu->phongChieu;
                $seats = Ghe::where('id_phong', $suatChieu->id_phong)->with('loaiGhe')->get();
            }

            // Lọc ghế VIP
            $vipSeatData = $seats->filter(function ($seat) {
                return $this->isVipSeat($seat);
            });
            $vipSeats = $vipSeatData->pluck('so_ghe')->toArray();

            $vipRows = $vipSeatData->map(function ($seat) {
                return substr($seat->so_ghe, 0, 1);
            })->unique()->values()->toArray();

            // Lọc ghế đôi
            $coupleSeatData = $seats->filter(function ($seat) {
                return $this->isCoupleSeat($seat);
            });

            $coupleSeatGroups = $coupleSeatData->groupBy(function ($seat) {
                return substr($seat->so_ghe, 0, 1);
            });

            $coupleSeats = [];
            foreach ($coupleSeatGroups as $row => $seatsInRow) {
                $seatNumbers = $seatsInRow->pluck('so_ghe')->toArray();
                sort($seatNumbers);
                for ($i = 0; $i < count($seatNumbers) - 1; $i++) {
                    $num1 = intval(substr($seatNumbers[$i], 1));
                    $num2 = intval(substr($seatNumbers[$i + 1], 1));
                    if ($num2 == $num1 + 1) {
                        $coupleSeats[] = $row . $num1 . '-' . $num2;
                        $i++;
                    }
                }
            }
        }

        // Fallback dữ liệu
        if ($seats->isEmpty() || !$roomInfo) {
            $roomInfo = (object) ['so_cot' => 15, 'so_hang' => 10];
        }

        return view('booking', [
            'movie' => $movie,
            'showtimes' => $showtimesMapped,
            'coupleSeats' => $coupleSeats,
            'vipSeats' => $vipSeats,
            'vipRows' => $vipRows,
            'roomInfo' => $roomInfo,
            'showtime' => $showtime // Truyền biến đã fix lỗi
        ]);
    }

    public function getBookedSeats($showtimeId)
    {
        try {
            // Lấy danh sách mã ghế (A1, A2...) đã bán
            $bookedSeatCodes = DB::table('chi_tiet_dat_ve')
                ->join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->join('ghe', 'chi_tiet_dat_ve.id_ghe', '=', 'ghe.id')
                ->where('dat_ve.id_suat_chieu', $showtimeId)
                ->where('dat_ve.trang_thai', 1) // Chỉ ghế đã thanh toán
                ->pluck('ghe.so_ghe')
                ->toArray();

            // Lấy danh sách mã ghế đang giữ (bảng tam_giu_ghe)
            $holdingSeats = [];
            try {
                if (Schema::hasTable('tam_giu_ghe')) {
                    $holdingSeats = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                        ->where('trang_thai', 'holding')
                        ->where('thoi_gian_het_han', '>', Carbon::now())
                        ->with('ghe')
                        ->get()
                        ->pluck('ghe.so_ghe')
                        ->filter()
                        ->values()
                        ->toArray();
                }
            } catch (\Exception $e) {
            }

            // Merge lại để đảm bảo không sót
            return response()->json([
                'seats' => array_unique($bookedSeatCodes), // Đây là mảng các ghế ĐÃ BÁN -> Sẽ tô đỏ
                'holding' => $holdingSeats
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading booked seats: ' . $e->getMessage());
            return response()->json(['seats' => [], 'holding' => []]);
        }
    }

    public function getShowtimeSeats($showtimeId)
    {
        try {
            // 1. Validate suất chiếu
            $showtime = SuatChieu::find($showtimeId);
            if (!$showtime) {
                return response()->json(['seats' => []]);
            }

            // Lấy danh sách ID ghế đã bán thật sự trong Database (chỉ trạng thái 1: Đã mua)
            $soldSeatIds = DB::table('chi_tiet_dat_ve')
                ->join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->where('dat_ve.id_suat_chieu', $showtimeId)
                ->where('dat_ve.trang_thai', 1)
                ->pluck('chi_tiet_dat_ve.id_ghe')
                ->toArray();

            // 2. Lấy dịch vụ giữ ghế (Redis)
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            $currentUserId = Auth::id();

            // 3. Lấy ghế trong phòng
            $allSeats = Ghe::where('id_phong', $showtime->id_phong)
                ->with('loaiGhe')
                ->get();

            $seats = []; // Dùng array thường thay vì collection để đảm bảo JSON đúng format

            foreach ($allSeats as $seat) {
                $typeText = strtolower($seat->loaiGhe->ten_loai ?? 'thường');

                // Logic giá (để hiển thị đúng trên UI)
                $price = 80000;
                if (str_contains($typeText, 'vip')) $price = 120000;
                elseif (str_contains($typeText, 'đôi') || str_contains($typeText, 'couple')) $price = 200000;

                // Mặc định
                $seatStatus = 'available';
                $isAvailable = true;
                $holdExpiresAt = null;

                // --- [LOGIC KIỂM TRA TRẠNG THÁI] ---

                // Ưu tiên 1: Nếu ID ghế nằm trong danh sách đã bán -> CHẶN LUÔN
                if (in_array($seat->id, $soldSeatIds)) {
                    $seatStatus = 'sold'; // Trạng thái quan trọng để Frontend tô đỏ
                    $isAvailable = false;
                }
                // Ưu tiên 2: Nếu ghế đang bị giữ (Redis)
                else {
                    $redisStatus = $seatHoldService->getSeatStatus($showtimeId, $seat->id, $currentUserId);
                    if ($redisStatus === 'held_by_me' || $redisStatus === 'held_by_other') {
                        $seatStatus = 'hold';
                        // Optionally set hold_expires_at by querying SeatHold if needed (skipped for performance)
                    } elseif ($redisStatus === 'booked') {
                        $seatStatus = 'sold';
                        $isAvailable = false;
                    }

                    // Ưu tiên 3: Ghế bảo trì/hỏng
                    if ((int)$seat->trang_thai !== 1) {
                        $seatStatus = 'blocked';
                        $isAvailable = false;
                    }
                }

                $seats[$seat->so_ghe] = [
                    'id' => $seat->id,
                    'code' => $seat->so_ghe,
                    'type' => $seat->loaiGhe->ten_loai ?? 'Thường',
                    'available' => $isAvailable && ($seatStatus !== 'sold' && $seatStatus !== 'blocked'),
                    'status' => $seatStatus,
                    'price' => $price,
                    'hold_expires_at' => $holdExpiresAt,
                ];
            }

            return response()->json(['seats' => $seats]);
        } catch (\Exception $e) {
            Log::error('Error loading showtime seats: ' . $e->getMessage());
            return response()->json(['seats' => []]);
        }
    }

    public function store(Request $request)
    {
        // Sử dụng Transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();
        try {
            // Log incoming request for debugging
            $requestData = $request->all();
            if ($request->isJson() || $request->header('Content-Type') === 'application/json') {
                $jsonData = json_decode($request->getContent(), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                    $requestData = $jsonData;
                }
            }

            Log::info('Booking store: Incoming request', [
                'method' => $request->method(),
                'user_id' => Auth::id(),
                'showtime' => $requestData['showtime'] ?? null,
                'seats' => $requestData['seats'] ?? [],
            ]);

            // Check authentication
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để đặt vé!'
                ], 401);
            }

            // Check if user is admin
            $user = Auth::user();
            if ($user && $user->id_vai_tro == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin không được phép đặt vé trực tiếp!'
                ]);
            }

            $data = $requestData;

            // Validate required fields
            if (!isset($data['seats']) || empty($data['seats'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ghế!'
                ], 400);
            }

            // Validate No Single Seat Rule
            $rowStates = $this->buildRowStates((int)$data['showtime'], (array)$data['seats']);
            $nsr = \App\Services\SeatPatternValidator::validateNoSingleSeatRule($rowStates);
            if (!$nsr['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $nsr['message'] ?? 'Không được để ghế trống lẻ.'
                ], 422);
            }

            // Validate showtime exists
            if (!isset($data['showtime']) || !$data['showtime']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn suất chiếu!'
                ], 400);
            }

            $showtime = SuatChieu::find($data['showtime']);
            if (!$showtime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Suất chiếu không tồn tại!'
                ], 400);
            }

            // --- 1. CLEANUP & PREPARATION ---

            // Release expired seats first
            try {
                if (Schema::hasTable('suat_chieu_ghe')) {
                    ShowtimeSeat::releaseExpiredSeats($data['showtime']);
                }
            } catch (\Throwable $e) {
                Log::warning('Skip releaseExpiredSeats@store: ' . $e->getMessage());
            }

            // Xử lý Booking cũ nếu có (Update case)
            $existingBooking = null;
            if (isset($data['booking_id']) && $data['booking_id']) {
                $existingBooking = DatVe::where('id', $data['booking_id'])
                    ->where('id_nguoi_dung', Auth::id())
                    ->where('id_suat_chieu', $data['showtime'])
                    ->where('trang_thai', 0) // pending
                    ->first();
            }

            if (!$existingBooking) {
                $existingBooking = DatVe::where('id_nguoi_dung', Auth::id())
                    ->where('id_suat_chieu', $data['showtime'])
                    ->where('trang_thai', 0) // pending
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            // Nếu đang update booking cũ, xóa chi tiết ghế cũ đi trước khi check ghế mới
            if ($existingBooking) {
                ChiTietDatVe::where('id_dat_ve', $existingBooking->id)->delete();
            }

            // --- 2. CRITICAL SECTION: CHECK RACE CONDITION ---

            // Phân tích danh sách ghế khách muốn mua để lấy ra ID
            $requestedSeatIds = [];
            foreach ($data['seats'] as $seatCode) {
                $seatCode = trim($seatCode);
                if ($seatCode === '') continue;

                $codesToResolve = [];
                // Xử lý ghế đôi (A1-2) hoặc ghế đơn (A1)
                if (strpos($seatCode, '-') !== false) {
                    if (preg_match('/^([A-Z])(?:\s*)(\d+)-(\d+)$/i', $seatCode, $matches)) {
                        $row = strtoupper($matches[1]);
                        $col1 = (int)$matches[2];
                        $col2 = (int)$matches[3];
                        for ($c = $col1; $c <= $col2; $c++) {
                            $codesToResolve[] = $row . $c;
                        }
                    }
                } elseif (strpos($seatCode, ',') !== false) {
                    $parts = array_filter(array_map('trim', explode(',', $seatCode)));
                    foreach ($parts as $p) $codesToResolve[] = strtoupper($p);
                } else {
                    $codesToResolve[] = strtoupper($seatCode);
                }

                foreach ($codesToResolve as $finalCode) {
                    $row = substr($finalCode, 0, 1);
                    $number = substr($finalCode, 1);
                    // Tìm ID ghế trong DB
                    $seatObj = Ghe::where('id_phong', $showtime->id_phong)
                        ->where('so_ghe', $row . $number)
                        ->first();

                    if ($seatObj) {
                        $requestedSeatIds[] = $seatObj->id;
                    }
                }
            }

            if (empty($requestedSeatIds)) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin ghế hợp lệ trong hệ thống.'
                ], 400);
            }


            // Check for seat conflicts BEFORE creating booking to prevent double booking
            $userId = Auth::id();
            $showtimeId = $data['showtime'];
            $conflictedSeats = [];

            // Get all seat IDs from seat codes
            $allSeatIds = [];
            foreach ($data['seats'] as $seat) {
                $seat = trim($seat);
                if ($seat === '') continue;

                $pairs = [];
                if (strpos($seat, '-') !== false) {
                    if (preg_match('/^([A-Z])(?:\s*)(\d+)-(\d+)$/i', $seat, $m)) {
                        $rowLetter = strtoupper($m[1]);
                        $start = (int)$m[2];
                        $end = (int)$m[3];
                        for ($c = $start; $c <= $end; $c++) {
                            $pairs[] = $rowLetter . $c;
                        }
                    }
                } elseif (strpos($seat, ',') !== false) {
                    $parts = array_filter(array_map('trim', explode(',', $seat)));
                    foreach ($parts as $code) {
                        $pairs[] = strtoupper($code);
                    }
                } else {
                    $pairs[] = strtoupper($seat);
                }

                foreach ($pairs as $code) {
                    $ghe = Ghe::where('id_phong', $showtime->id_phong)
                        ->where('so_ghe', $code)
                        ->first();
                    if ($ghe) {
                        $allSeatIds[] = $ghe->id;
                    }
                }
            }

            // [QUAN TRỌNG - FIX RACE CONDITION] 
            // Khóa các dòng trong bảng GHE để chặn người khác truy cập cùng lúc
            Ghe::whereIn('id', $requestedSeatIds)->lockForUpdate()->get();

            // Sau khi đã lock xong bảng Ghe, ta mới kiểm tra xem ghế đã bị đặt chưa
            $isTaken = ChiTietDatVe::whereIn('id_ghe', $requestedSeatIds)
                ->whereHas('datVe', function ($query) use ($data) {
                    $query->where('id_suat_chieu', $data['showtime'])
                        ->whereIn('trang_thai', [0, 1]); // 0: Đang giữ/chờ thanh toán, 1: Đã mua
                })
                ->exists();

            if ($isTaken) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Rất tiếc, một trong các ghế bạn chọn vừa được người khác đặt. Vui lòng chọn ghế khác.'
                ], 409); // 409 Conflict
            }

            // --- 3. CALCULATE TOTAL & SAVE DATA ---

            $seatTotal = 0;
            foreach ($data['seats'] as $seat) {
                if (strpos($seat, '-') !== false) {
                    $seatTotal += 200000;
                } else {
                    $row = substr($seat, 0, 1);
                    $col = substr($seat, 1);
                    $seatObj = Ghe::where('id_phong', $showtime->id_phong)->where('so_ghe', $row . $col)->first();
                    // CẬP NHẬT GIÁ
                    if ($seatObj && $this->isVipSeat($seatObj)) {
                        $seatTotal += 150000; // VIP
                    } else {
                        $seatTotal += 100000; // Thường
                    }
                }
            }

            $comboTotal = 0;
            $selectedCombo = null;
            if (isset($data['combo']) && $data['combo']) {
                $selectedCombo = Combo::find($data['combo']['id'] ?? null);
                if ($selectedCombo) {
                    $comboTotal = (float) $selectedCombo->gia;
                }
            }

            $discount = 0;
            $promotionId = null;
            if (isset($data['promotion']) && $data['promotion']) {
                $promotion = KhuyenMai::find($data['promotion']);
                if ($promotion) {
                    $promotionId = $promotion->id;
                    $subtotal = $seatTotal + $comboTotal;
                    $min = 0;
                    if (!empty($promotion->dieu_kien)) {
                        $minDigits = preg_replace('/\D+/', '', (string)$promotion->dieu_kien);
                        if ($minDigits !== '') $min = (float)$minDigits;
                    }
                    if ($subtotal >= $min) {
                        if ($promotion->loai_giam === 'phantram') {
                            $discount = round($subtotal * ((float)$promotion->gia_tri_giam / 100));
                        } else {
                            $val = (float)$promotion->gia_tri_giam;
                            $fixed = $val >= 1000 ? $val : $val * 1000;
                            $discount = round($fixed);
                        }
                        if ($discount > $subtotal) $discount = $subtotal;
                    }
                }
            }

            $totalAmount = max(0, $seatTotal + $comboTotal - $discount);

            $paymentMethod = $data['payment_method'] ?? 'offline';
            $bookingStatus = 0;
            $methodCode = ($paymentMethod === 'online') ? 1 : 2;

            $expiresAt = null;
            if ($paymentMethod === 'offline') {
                $showtimeTime = \Carbon\Carbon::parse($showtime->thoi_gian_bat_dau);
                // Kiểm tra sát giờ
                if (now()->diffInMinutes($showtimeTime, false) < 30) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'Đã quá giờ đặt tại quầy.'], 400);
                }
                $expiresAt = $showtimeTime->subMinutes(30);
            } else if ($paymentMethod === 'online') {
                $expiresAt = \Carbon\Carbon::now()->addMinutes(15);
            }


            // Create booking
            try {
                $booking = DB::transaction(function () use ($data, $existingBooking, $promotionId, $totalAmount, $bookingStatus, $methodCode, $expiresAt, $showtimeId, $allSeatIds, $userId, $selectedCombo, $paymentMethod, $showtime) {
                    // Lock all seats first... (Logic lock ghế đã thực hiện ở trên)

                    // Create/Update booking
                    if ($existingBooking) {
                        ChiTietDatVe::where('id_dat_ve', $existingBooking->id)->delete();
                        $updateData = [
                            'id_khuyen_mai' => $promotionId,
                            'tong_tien' => $totalAmount,
                            'trang_thai' => $bookingStatus,
                            'phuong_thuc_thanh_toan' => $methodCode,
                        ];
                        if ($expiresAt) {
                            $updateData['expires_at'] = $expiresAt;
                        }
                        $existingBooking->update($updateData);
                        $booking = $existingBooking;
                    } else {
                        $createData = [
                            'id_nguoi_dung'   => Auth::id(),
                            'id_suat_chieu'   => $data['showtime'] ?? null,
                            'id_khuyen_mai'   => $promotionId,
                            'tong_tien'       => $totalAmount,
                            'trang_thai'      => $bookingStatus,
                            'phuong_thuc_thanh_toan' => $methodCode,
                        ];
                        if ($expiresAt) {
                            $createData['expires_at'] = $expiresAt;
                        }
                        $booking = DatVe::create($createData);
                    }

                    // Release expired seats first
                    try {
                        if (Schema::hasTable('suat_chieu_ghe')) {
                            ShowtimeSeat::releaseExpiredSeats($data['showtime']);
                        }
                    } catch (\Throwable $e) {
                    }

                    // Save seat details
                    foreach ($data['seats'] as $seatCode) {
                        $seatCode = trim($seatCode);
                        if ($seatCode === '') continue;

                        // (Logic phân tích $codesToSave giống ở trên, tóm tắt)
                        $codesToSave = [];
                        if (strpos($seatCode, '-') !== false) {
                            if (preg_match('/^([A-Z])(?:\s*)(\d+)-(\d+)$/i', $seatCode, $matches)) {
                                $row = strtoupper($matches[1]);
                                for ($c = (int)$matches[2]; $c <= (int)$matches[3]; $c++) $codesToSave[] = $row . $c;
                            }
                        } elseif (strpos($seatCode, ',') !== false) {
                            foreach (explode(',', $seatCode) as $p) $codesToSave[] = strtoupper(trim($p));
                        } else {
                            $codesToSave[] = strtoupper($seatCode);
                        }

                        foreach ($codesToSave as $code) {
                            $row = substr($code, 0, 1);
                            $number = substr($code, 1);
                            $seat = Ghe::where('id_phong', $showtime->id_phong)
                                ->where('so_ghe', $row . $number)
                                ->with('loaiGhe')
                                ->first();
                            if ($seat) {
                                // Determine price (CẬP NHẬT GIÁ MỚI)
                                $price = $this->isCoupleSeat($seat) ? 200000 : ($this->isVipSeat($seat) ? 150000 : 100000);
                                ChiTietDatVe::create([
                                    'id_dat_ve' => $booking->id,
                                    'id_ghe' => $seat->id,
                                    'gia' => $price
                                ]);
                            }
                        }
                    }

                    // Save combo detail
                    if ($selectedCombo) {
                        ChiTietCombo::create([
                            'id_dat_ve'   => $booking->id,
                            'id_combo'    => $selectedCombo->id,
                            'so_luong'    => 1,
                            'gia_ap_dung' => (float)$selectedCombo->gia,
                        ]);
                    }

                    // Create payment record
                    ThanhToan::create([
                        'id_dat_ve'    => $booking->id,
                        'phuong_thuc'  => ($paymentMethod === 'online') ? 'VNPAY' : 'Tiền mặt',
                        'so_tien'      => $totalAmount,
                        'trang_thai'   => 0,
                        'thoi_gian'    => now()
                    ]);

                    return $booking;
                });

                // Beta standard: Store booking_hold_id
                if (isset($data['booking_hold_id'])) {
                    session(['booking_hold_id_' . $booking->id => $data['booking_hold_id']]);
                }

                // Nhả ghế giữ nếu Offline
                if ($paymentMethod === 'offline') {
                    $seatIds = $allSeatIds; // Đã lấy ở trên
                    app(\App\Services\SeatHoldService::class)->releaseSeats($showtimeId, $seatIds, $userId);
                }

                // Commit transaction bên ngoài (DB::transaction() đã commit transaction bên trong)
                DB::commit();
                
                // Nếu thanh toán online, tạo URL VNPAY và redirect
                if ($paymentMethod === 'online') {
                    $vnpUrl = app(\App\Http\Controllers\PaymentController::class)->createVnpayUrl($booking->id, $totalAmount);
                    return response()->json([
                        'success' => true,
                        'booking_id' => $booking->id,
                        'is_redirect' => true,
                        'payment_url' => $vnpUrl,
                        'message' => 'Đang chuyển hướng thanh toán...'
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'booking_id' => $booking->id,
                    'message' => 'Đặt vé thành công! Vui lòng thanh toán tại quầy.',
                    'redirect_url' => route('booking.ticket.detail', ['id' => $booking->id])
                ]);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'đã được đặt') !== false) {
                    return response()->json(['success' => false, 'message' => $e->getMessage()], 409);
                }
                throw $e;
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Booking error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra. ' . $e->getMessage()], 500);
        }
    }

    /**
     * Public API: Get ticket details by id for check page
     */
    public function getTicket($id)
    {
        try {
            $booking = DatVe::with([
                'suatChieu.phim:id,ten_phim,poster',
                'suatChieu.phongChieu:id,ten_phong',
                'chiTietDatVe.ghe:id,so_ghe',
                'khuyenMai:id,ma_km',
            ])->find($id);

            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy vé'], 404);
            }

            $seats = $booking->chiTietDatVe->map(function ($ct) {
                return optional($ct->ghe)->so_ghe;
            })->filter()->values();

            $method = $booking->phuong_thuc_thanh_toan;
            if (!$method) {
                $map = optional($booking->thanhToan)->phuong_thuc ?? null;
                $method = $map === 'online' ? 1 : ($map === 'offline' ? 2 : null);
            }

            $payloadUrl = url('/api/ticket/' . $booking->id);

            return response()->json([
                'success' => true,
                'ticket' => [
                    'id' => $booking->id,
                    'code' => sprintf('MV%06d', $booking->id),
                    'customer' => [
                        'name' => $booking->ten_khach_hang ?? optional($booking->nguoiDung)->ten ?? '—',
                        'phone' => $booking->so_dien_thoai ?? optional($booking->nguoiDung)->so_dien_thoai,
                        'email' => $booking->email ?? optional($booking->nguoiDung)->email,
                    ],
                    'showtime' => [
                        'movie' => optional(optional($booking->suatChieu)->phim)->ten_phim,
                        'room' => optional(optional($booking->suatChieu)->phongChieu)->ten_phong,
                        'start' => optional(optional($booking->suatChieu)->thoi_gian_bat_dau)->format('d/m/Y H:i'),
                    ],
                    'seats' => $seats,
                    'price' => (float) ($booking->tong_tien ?? $booking->tong_tien_hien_thi ?? 0),
                    'status' => (int) ($booking->trang_thai ?? 0),
                    'created_at' => optional($booking->created_at)->format('d/m/Y H:i'),
                    'payment_method' => $method, // 1 online, 2 tại quầy
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Get ticket error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống'], 500);
        }
    }

    /**
     * Select seats and hold them for 5 minutes
     * POST /api/showtimes/{id}/select-seats
     */
    public function selectSeats(Request $request, $showtimeId)
    {
        try {
            $showtime = SuatChieu::find($showtimeId);
            if (!$showtime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Suất chiếu không tồn tại!'
                ], 404);
            }

            $data = $request->json()->all();
            $seatCodes = $data['seats'] ?? [];

            if (empty($seatCodes) || !is_array($seatCodes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ít nhất một ghế!'
                ], 400);
            }

            // Validate seat selection rules
            $validationResult = $this->validateSeatSelection($showtime, $seatCodes);
            if (!$validationResult['valid']) {
                \Log::warning('Seat selection validation failed', [
                    'showtime_id' => $showtimeId,
                    'seat_codes' => $seatCodes,
                    'message' => $validationResult['message']
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $validationResult['message']
                ], 400);
            }

            // Get seat IDs from codes
            $seatIds = [];
            $seatsByCode = [];
            foreach ($seatCodes as $code) {
                $code = strtoupper(trim($code));
                $ghe = Ghe::where('id_phong', $showtime->id_phong)
                    ->where('so_ghe', $code)
                    ->first();

                if (!$ghe) {
                    return response()->json([
                        'success' => false,
                        'message' => "Ghế {$code} không tồn tại!"
                    ], 400);
                }

                $seatIds[] = $ghe->id;
                $seatsByCode[$code] = $ghe;
            }

            // Use SeatHoldService to hold seats in Redis (Beta standard)
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            $userId = Auth::id();

            $holdResult = $seatHoldService->holdSeats($showtimeId, $seatIds, $userId);

            if (!$holdResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $holdResult['message'] ?? 'Không thể giữ ghế'
                ], 400);
            }

            // Return booking_hold_id instead of booking_id
            // This is a temporary hold ID, not a DB booking yet
            return response()->json([
                'success' => true,
                'message' => 'Ghế đã được giữ chỗ trong 5 phút',
                'booking_hold_id' => $holdResult['booking_hold_id'],
                'hold_expires_at' => $holdResult['hold_expires_at']->toIso8601String(),
                'expires_in_seconds' => $holdResult['expires_in_seconds'],
            ]);
        } catch (\Exception $e) {
            Log::error('Select seats error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'showtime_id' => $showtimeId ?? null,
                'seat_codes' => $seatCodes ?? []
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi giữ ghế. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    /**
     * Validate seat selection rules (Theater National Standards):
     * 1. Seats may be across multiple rows
     * 2. Seats must be consecutive within each row (except single seat is allowed)
     * 3. No isolated single seat should be created per row (orphan seat rule)
     * 4. Odd number of seats is allowed
     */
    private function validateSeatSelection($showtime, $seatCodes)
    {
        if (empty($seatCodes)) {
            return ['valid' => false, 'message' => 'Vui lòng chọn ít nhất một ghế!'];
        }

        // Validate seat pattern (chặn tam giác, hình thoi, ziczac, etc.)
        $patternValidator = app(\App\Services\SeatPatternValidator::class);
        $patternValidation = $patternValidator->validatePattern($seatCodes);

        if (!$patternValidation['valid']) {
            return $patternValidation;
        }

        // Parse seat codes -> group by row
        $seats = [];
        foreach ($seatCodes as $code) {
            $code = strtoupper(trim($code));
            if (!preg_match('/^([A-Z])(\d+)$/', $code, $matches)) {
                return ['valid' => false, 'message' => "Mã ghế không hợp lệ: {$code}"];
            }
            $seats[] = [
                'code' => $code,
                'row' => $matches[1],
                'number' => (int)$matches[2],
            ];
        }

        // Group selected seats by row
        $byRow = [];
        foreach ($seats as $s) {
            $byRow[$s['row']][] = $s['number'];
        }

        // Check if this is a 4-corner pattern (A1, J1, J15, A15)
        $isFourCornerPattern = $this->isFourCornerPattern($seats);

        // Rule 2: Seats must be consecutive within each row
        // Exception: Allow non-consecutive seats if it's a 4-corner pattern
        if (!$isFourCornerPattern) {
            foreach ($byRow as $row => $numbers) {
                sort($numbers);
                if (count($numbers) > 1) {
                    for ($i = 1; $i < count($numbers); $i++) {
                        if ($numbers[$i] - $numbers[$i - 1] !== 1) {
                            return ['valid' => false, 'message' => "Các ghế trong hàng {$row} phải liền nhau! Ví dụ: {$row}5-{$row}6-{$row}7 (không được {$row}5-{$row}7)."];
                        }
                    }
                }
            }
        }

        // Rule 3: Orphan-seat rule per row
        // Exception: Skip orphan-seat check for 4-corner pattern
        foreach ($byRow as $row => $selectedNumbers) {
            sort($selectedNumbers);

            // Get all seats in this row for this showtime
            $seatsInRow = Ghe::where('id_phong', $showtime->id_phong)
                ->where('so_ghe', 'like', $row . '%')
                ->with('loaiGhe')
                ->get();

            $allSeatsInRow = $seatsInRow
                ->map(function ($ghe) {
                    if (preg_match('/^([A-Z])(\d+)$/', $ghe->so_ghe, $m)) {
                        return (int)$m[2];
                    }
                    return null;
                })
                ->filter()
                ->sort()
                ->values()
                ->toArray();

            // Build number => type map for couple validation
            $seatTypesByNumber = [];
            foreach ($seatsInRow as $seatModel) {
                if (preg_match('/^([A-Z])(\d+)$/', $seatModel->so_ghe, $mm)) {
                    $num = (int)$mm[2];
                    $seatTypesByNumber[$num] = mb_strtolower($seatModel->loaiGhe->ten_loai ?? '');
                }
            }

            if (empty($allSeatsInRow)) {
                return ['valid' => false, 'message' => "Không tìm thấy ghế trong hàng {$row}!"];
            }

            // Couple seat rule: if any selected seat is couple type, its fixed pair (1-2, 3-4, ...) must be selected too
            foreach ($selectedNumbers as $n) {
                $typeText = $seatTypesByNumber[$n] ?? '';
                if (str_contains($typeText, 'đôi') || str_contains($typeText, 'doi') || str_contains($typeText, 'couple')) {
                    $pair = ($n % 2 === 1) ? $n + 1 : $n - 1;
                    if (!in_array($pair, $selectedNumbers, true)) {
                        return ['valid' => false, 'message' => "Ghế đôi phải đặt theo cặp trong hàng {$row} (" . $row . min($n, $pair) . '-' . $row . max($n, $pair) . ")!"];
                    }
                }
            }

            // Skip orphan-seat check for 4-corner pattern
            if ($isFourCornerPattern) {
                continue;
            }

            // Get booked/holding seats in this row
            $bookedNumbers = [];
            foreach ($allSeatsInRow as $seatNum) {
                $seatCode = $row . $seatNum;
                $ghe = Ghe::where('id_phong', $showtime->id_phong)
                    ->where('so_ghe', $seatCode)
                    ->first();
                if ($ghe) {
                    try {
                        $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $showtime->id)
                            ->where('id_ghe', $ghe->id)
                            ->first();
                        if ($showtimeSeat && ($showtimeSeat->isBooked() || $showtimeSeat->isHolding())) {
                            $bookedNumbers[] = $seatNum;
                        }
                    } catch (\Exception $e) {
                        // ignore when pivot table not available
                    }
                }
            }

            // Merge and check gaps that leave exactly 1 isolated seat
            $allBookedAfter = array_unique(array_merge($bookedNumbers, $selectedNumbers));
            sort($allBookedAfter);

            $minNumber = min($selectedNumbers);
            $maxNumber = max($selectedNumbers);
            $minExisting = $allSeatsInRow[0];
            $maxExisting = max($allSeatsInRow);

            // Left side
            if ($minNumber > $minExisting) {
                $leftBooked = array_filter($allBookedAfter, function ($n) use ($minNumber) {
                    return $n < $minNumber;
                });
                if (count($leftBooked) > 0) {
                    $lastLeftBooked = max($leftBooked);
                    $gap = $minNumber - $lastLeftBooked - 1;
                    if ($gap === 1) {
                        return ['valid' => false, 'message' => "Không thể chọn vì sẽ để lại 1 ghế trống lẻ ở bên trái hàng {$row}."];
                    }
                }
            }

            // Right side
            if ($maxNumber < $maxExisting) {
                $rightBooked = array_filter($allBookedAfter, function ($n) use ($maxNumber) {
                    return $n > $maxNumber;
                });
                if (count($rightBooked) > 0) {
                    $firstRightBooked = min($rightBooked);
                    $gap = $firstRightBooked - $maxNumber - 1;
                    if ($gap === 1) {
                        return ['valid' => false, 'message' => "Không thể chọn vì sẽ để lại 1 ghế trống lẻ ở bên phải hàng {$row}."];
                    }
                }
            }

            // Middle gaps
            for ($i = 0; $i < count($allBookedAfter) - 1; $i++) {
                $gap = $allBookedAfter[$i + 1] - $allBookedAfter[$i] - 1;
                if ($gap === 1) {
                    return ['valid' => false, 'message' => "Không thể chọn vì sẽ để lại 1 ghế trống lẻ giữa các ghế đã đặt ở hàng {$row}."];
                }
            }
        }

        return ['valid' => true, 'message' => 'OK'];
    }

    /**
     * Check if seat selection is a 4-corner pattern (A1, J1, J15, A15)
     * 
     * @param array $seats Array of seat data with 'row' and 'number' keys
     * @return bool
     */
    private function isFourCornerPattern(array $seats): bool
    {
        // Must have exactly 4 seats
        if (count($seats) !== 4) {
            return false;
        }

        // Find min/max row and column
        $rows = [];
        $cols = [];
        foreach ($seats as $seat) {
            $rows[] = $seat['row'];
            $cols[] = $seat['number'];
        }

        $minRow = min($rows);
        $maxRow = max($rows);
        $minCol = min($cols);
        $maxCol = max($cols);

        // Check if we have exactly 2 distinct rows and 2 distinct columns
        $uniqueRows = array_unique($rows);
        $uniqueCols = array_unique($cols);

        if (count($uniqueRows) !== 2 || count($uniqueCols) !== 2) {
            return false;
        }

        // Check if the 4 seats are exactly the 4 corners
        $expectedCorners = [
            ['row' => $minRow, 'number' => $minCol],
            ['row' => $minRow, 'number' => $maxCol],
            ['row' => $maxRow, 'number' => $minCol],
            ['row' => $maxRow, 'number' => $maxCol],
        ];

        // Sort both arrays for comparison
        usort($seats, function ($a, $b) {
            if ($a['row'] !== $b['row']) {
                return strcmp($a['row'], $b['row']);
            }
            return $a['number'] - $b['number'];
        });

        usort($expectedCorners, function ($a, $b) {
            if ($a['row'] !== $b['row']) {
                return strcmp($a['row'], $b['row']);
            }
            return $a['number'] - $b['number'];
        });

        // Check if seats match expected corners
        for ($i = 0; $i < 4; $i++) {
            if (
                $seats[$i]['row'] !== $expectedCorners[$i]['row'] ||
                $seats[$i]['number'] !== $expectedCorners[$i]['number']
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * VNPAY Return Handler
     * Handle payment return from VNPAY gateway
     */

    /**
     * Helper method to create VNPAY payment URL
     */
    private function createVnpayUrl($orderId, $amount)
    {
        $vnp_Url = env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $vnp_TmnCode = env('VNP_TMN_CODE');

        // Validate required config
        if (empty($vnp_TmnCode) || empty($vnp_HashSecret)) {
            Log::error('VNPAY: Missing required configuration', [
                'has_tmn_code' => !empty($vnp_TmnCode),
                'has_hash_secret' => !empty($vnp_HashSecret)
            ]);
            throw new \Exception('VNPAY configuration is missing. Please check .env file.');
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

        // Get return URL (must be absolute URL)
        $vnp_ReturnUrl = env('VNP_RETURN_URL', route('payment.vnpay_return'));
        if (!filter_var($vnp_ReturnUrl, FILTER_VALIDATE_URL)) {
            $vnp_ReturnUrl = url($vnp_ReturnUrl);
        }

        // Clean order info (VNPAY requires ASCII characters only)
        $vnp_OrderInfo = "Thanh toan ve xem phim #{$orderId}";
        // Remove Vietnamese characters and special chars, keep only ASCII
        $vnp_OrderInfo = preg_replace('/[^\x20-\x7E]/', '', $vnp_OrderInfo);
        $vnp_OrderInfo = mb_substr($vnp_OrderInfo, 0, 255); // Max 255 characters

        // Generate unique transaction reference (max 100 characters)
        $vnp_TxnRef = $orderId . "_" . time();
        if (strlen($vnp_TxnRef) > 100) {
            $vnp_TxnRef = substr($vnp_TxnRef, 0, 100);
        }

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef
        ];

        // Remove empty values
        $inputData = array_filter($inputData, function ($value) {
            return $value !== null && $value !== '';
        }, ARRAY_FILTER_USE_BOTH);

        // Sort by key (alphabetically)
        ksort($inputData);

        // Build query string and hash data
        $query = "";
        $hashdata = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            // Convert value to string
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

        // Build final URL - add vnp_SecureHash at the end with &
        $vnp_Url = $vnp_Url . "?" . $query . '&vnp_SecureHash=' . $vnpSecureHash;

        Log::info('VNPAY payment URL created', [
            'booking_id' => $orderId,
            'amount' => $amount,
            'vnp_Amount' => $vnp_Amount,
            'url_length' => strlen($vnp_Url)
        ]);

        return $vnp_Url;
    }

    /**
     * Show user tickets list
     */
    public function tickets()
    {
        $userId = Auth::id();

        if (!$userId) {
            return redirect()->route('login.form')->with('error', 'Vui lòng đăng nhập để xem vé');
        }

        $bookings = DatVe::with([
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe',
            'chiTietCombo.combo',
            'thanhToan',
            'nguoiDung'
        ])
            ->where('id_nguoi_dung', $userId)
            ->orderByDesc('created_at')
            ->paginate(10);

        // Statistics
        $stats = [
            'total' => DatVe::where('id_nguoi_dung', $userId)->count(),
            'paid' => DatVe::where('id_nguoi_dung', $userId)->where('trang_thai', 1)->count(),
            'pending' => DatVe::where('id_nguoi_dung', $userId)->where('trang_thai', 0)->count(),
            'cancelled' => DatVe::where('id_nguoi_dung', $userId)->where('trang_thai', 2)->count(),
        ];

        return view('booking.tickets', compact('bookings', 'stats'));
    }

    /**
     * Hủy booking chưa thanh toán khi người dùng back lại
     */
    public function cancelPendingBooking($bookingId)
    {
        try {
            $booking = DatVe::where('id', $bookingId)
                ->where('id_nguoi_dung', Auth::id())
                ->where('trang_thai', 0) // Chỉ hủy booking chưa thanh toán
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy booking hoặc booking đã được thanh toán.'
                ], 404);
            }

            DB::transaction(function () use ($booking) {
                // Xóa chi tiết ghế
                ChiTietDatVe::where('id_dat_ve', $booking->id)->delete();
                // Xóa chi tiết combo
                ChiTietCombo::where('id_dat_ve', $booking->id)->delete();
                // Xóa chi tiết food
                \App\Models\ChiTietFood::where('id_dat_ve', $booking->id)->delete();
                // Xóa thanh toán
                ThanhToan::where('id_dat_ve', $booking->id)->delete();
                // Xóa booking
                $booking->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Đã hủy booking thành công.'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi hủy booking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy booking.'
            ], 500);
        }
    }
}
