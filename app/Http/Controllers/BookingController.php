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

    /**
     * Start VNPAY payment for current hold booking
     */
    public function processPayment(\Illuminate\Http\Request $request, $bookingId)
    {
        // bookingId here is the hold id stored in session
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

        // Compute amount (VND) from seat selection with couple seat pairing
        $amount = 0;
        $showtime = SuatChieu::find($showtimeId);
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
            while ($i < $selected->count()) {
                $cur = $selected[$i];
                $next = $selected[$i + 1] ?? null;
                if ($cur['isCouple'] && $next && $next['isCouple'] && $cur['row'] === $next['row'] && $next['num'] === $cur['num'] + 1) {
                    $amount += 200000;
                    $i += 2;
                } else {
                    if ($cur['isCouple']) {
                        $amount += 200000;
                    } elseif ($cur['isVip']) {
                        $amount += 120000;
                    } else {
                        $amount += 80000;
                    }
                    $i += 1;
                }
            }
        }
        if ($amount <= 0) $amount = 80000; // fallback

        // Add combo total from session
        $selectedCombos = collect(session('booking.selected_combos', []));
        if ($selectedCombos->isNotEmpty()) {
            // Optionally validate price by DB; for now sum by provided gia * so_luong
            $comboTotal = (int) $selectedCombos->sum(function($c){
                $price = (float) ($c['gia'] ?? 0);
                $qty = (int) ($c['so_luong'] ?? 0);
                return (int) round($price) * max(0, $qty);
            });
            $amount += $comboTotal;
        }

        // Apply promotion if selected and valid
        $promoId = $request->input('promo_id');
        if ($promoId) {
            $promo = KhuyenMai::where('trang_thai', 1)
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

        // Persist a pending booking BEFORE redirecting to VNPAY, so return can update it
        try {
            $booking = \DB::transaction(function () use ($showtimeId, $selectedSeatCodes, $seats, $amount) {
                // 1) Create booking (pending)
                $booking = \App\Models\DatVe::create([
                    'id_nguoi_dung' => Auth::id(),
                    'id_suat_chieu' => $showtimeId,
                    'trang_thai' => 0, // pending
                ]);

                // 2) Create seat details
                foreach ($selectedSeatCodes as $code) {
                    $seat = $seats->get($code);
                    if (!$seat) continue;
                    $price = $this->isCoupleSeat($seat) ? 200000 : ($this->isVipSeat($seat) ? 120000 : 80000);
                    \App\Models\ChiTietDatVe::create([
                        'id_dat_ve' => $booking->id,
                        'id_ghe' => $seat->id,
                        'gia' => $price,
                    ]);
                }

                // 3) Create a payment record (unpaid)
                \App\Models\ThanhToan::create([
                    'id_dat_ve' => $booking->id,
                    'phuong_thuc' => 'VNPAY',
                    'so_tien' => $amount,
                    'trang_thai' => 0,
                    'thoi_gian' => now(),
                ]);

                return $booking;
            });

            // Store mapping hold_id -> booking_id (optional for debugging)
            if ($holdId && $booking) {
                session(['booking.mapped.' . $holdId => $booking->id]);
            }

            // Create VNPAY URL using REAL booking ID
            $vnp_Url = app(\App\Http\Controllers\PaymentController::class)->createVnpayUrl($booking->id, $amount);
            return redirect()->away($vnp_Url);
        } catch (\Throwable $e) {
            Log::error('Failed to create pending booking before VNPAY redirect', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', 'Không thể tạo đơn đặt vé. Vui lòng thử lại.');
        }
    }

    /**
     * VNPAY return URL handler
     */
    public function vnpayReturn(\Illuminate\Http\Request $request)
    {
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $data = $request->all();
        $vnp_SecureHash = $data['vnp_SecureHash'] ?? '';
        // Remove secure hash params before verify
        foreach (['vnp_SecureHash', 'vnp_SecureHashType'] as $k) unset($data[$k]);
        ksort($data);
        $hashdata = urldecode(http_build_query($data));
        $verified = $vnp_HashSecret ? hash_hmac('sha512', $hashdata, $vnp_HashSecret) === ($vnp_SecureHash) : false;

        $success = $verified && ($request->input('vnp_ResponseCode') === '00');

        // You can persist DatVe here. For demo, just show result.
        return view('booking.payment-result', [
            'success' => $success,
            'txnRef' => $request->input('vnp_TxnRef'),
            'amount' => (int) (($request->input('vnp_Amount') ?? 0) / 100),
            'message' => $success ? 'Thanh toán thành công' : 'Thanh toán thất bại hoặc bị hủy',
        ]);
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

        // Get booking data for user with related showtime, movie, room, and seat details
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
            ->orderByRaw('COALESCE(created_at, id) DESC')
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
        $promo = $booking->khuyenMai;
        $comboTotal = $comboItems->sum(function ($i) {
            return (float)$i->gia_ap_dung * max(1, (int)$i->so_luong);
        });
        $seatTotal = (float) $booking->chiTietDatVe->sum('gia');
        $subtotal = $seatTotal + $comboTotal;
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

        return view($viewName, compact(
            'booking',
            'showtime',
            'movie',
            'room',
            'seatList',
            'comboItems',
            'promo',
            'promoDiscount',
            'computedTotal',
            'pt',
            'qrCodeData'
        ));
    }
    
    /**
     * Display seat selection page for a specific showtime
     * This is the new flow: user selects showtime first, then comes here to select seats
     */
    public function showSeatsPage($showtimeId)
    {
        try {
            $showtime = SuatChieu::with(['phim', 'phongChieu'])->findOrFail($showtimeId);
            
            if ($showtime->trang_thai != 1) {
                return redirect()->route('booking.index')
                    ->with('error', 'Suất chiếu không khả dụng.');
            }
            
            if ($showtime->thoi_gian_bat_dau < now()) {
                return redirect()->route('booking.index')
                    ->with('error', 'Suất chiếu đã bắt đầu.');
            }
            
            $movie = $showtime->phim;
            $room = $showtime->phongChieu;
            
            if (!$movie || !$room) {
                return redirect()->route('booking.index')
                    ->with('error', 'Thông tin suất chiếu không hợp lệ.');
            }
            
            // Get combos and promotions
            $combos = Combo::where('trang_thai', 1)->get();
            $khuyenmais = KhuyenMai::where('trang_thai', 1)
                ->where('ngay_bat_dau', '<=', now())
                ->where('ngay_ket_thuc', '>=', now())
                ->get();

            // Build seats collection required by the seats view
            $seats = Ghe::where('id_phong', $showtime->id_phong)
                ->with('loaiGhe')
                ->get()
                ->map(function ($seat) {
                    // Derive properties used by the Blade view
                    $seat->seatType = $seat->loaiGhe; // alias expected by view
                    $seat->booking_status = 'available'; // default status; can be updated later
                    // Derive row label (so_hang) from seat code e.g., A10 -> A
                    $seat->so_hang = is_string($seat->so_ghe) && strlen($seat->so_ghe) > 0
                        ? substr($seat->so_ghe, 0, 1)
                        : null;
                    return $seat;
                });

            // Selected combos placeholder for view compatibility
            $selectedCombos = collect();
            
            $existingBooking = null; // default to avoid undefined in view
            return view('booking.seats', compact('showtime', 'movie', 'room', 'combos', 'khuyenmais', 'existingBooking', 'seats', 'selectedCombos'));
        } catch (\Exception $e) {
            Log::error('Error loading seat selection page', [
                'showtime_id' => $showtimeId,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('booking.index')
                ->with('error', 'Không thể tải trang chọn ghế. Vui lòng thử lại.');
        }
    }

    /**
     * Backward-compatible alias for routes that call showSeats
     */
    public function showSeats($showtimeId)
    {
        return $this->showSeatsPage($showtimeId);
    }
    
    /**
     * Lock seats for current user (hold for a short time)
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
        $conflicts = [];
        $expiresAt = null;

        try {
            // Prefer SeatHoldService if available
            $seatHoldService = app(\App\Services\SeatHoldService::class);

            foreach ($seatIds as $seatId) {
                // Check current status first
                $status = $seatHoldService->getSeatStatus($showId, (int)$seatId, $userId);
                if ($status !== 'available' && $status !== 'hold') {
                    $conflicts[] = ['seat_id' => (int)$seatId, 'status' => $status];
                    continue;
                }

                $hold = $seatHoldService->holdSeat($showId, (int)$seatId, $userId, 5 * 60);
                if (!$hold) {
                    $conflicts[] = ['seat_id' => (int)$seatId, 'status' => 'conflict'];
                } else {
                    $expiresAt = $hold['hold_expires_at'] ?? $expiresAt;
                }
            }

            if (count($conflicts) === count($seatIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Một số ghế đã bị giữ hoặc bán',
                    'conflicts' => $conflicts,
                ]);
            }

            // Create a transient booking hold id in session for next step
            $bookingHoldId = session('booking.hold_id');
            if (!$bookingHoldId) {
                $bookingHoldId = 'hold_' . uniqid();
            }
            session([
                'booking.hold_id' => $bookingHoldId,
                'booking.showtime_id' => $showId,
                'booking.seat_ids' => $seatIds,
                'booking.hold_expires_at' => $expiresAt ? \Carbon\Carbon::parse($expiresAt)->timestamp : (time() + 5 * 60),
            ]);

            return response()->json([
                'success' => true,
                'expires_at' => $expiresAt ? \Carbon\Carbon::parse($expiresAt)->timestamp : (time() + 5 * 60),
                'booking_id' => $bookingHoldId,
            ]);
        } catch (\Throwable $e) {
            // Fallback: if service is not available, return success so UI can proceed in demo mode
            \Log::warning('lockSeats fallback: ' . $e->getMessage());
            $bookingHoldId = session('booking.hold_id');
            if (!$bookingHoldId) {
                $bookingHoldId = 'hold_' . uniqid();
            }
            session([
                'booking.hold_id' => $bookingHoldId,
                'booking.showtime_id' => $showId,
                'booking.seat_ids' => $seatIds,
                'booking.hold_expires_at' => time() + 5 * 60,
            ]);
            return response()->json([
                'success' => true,
                'expires_at' => time() + 5 * 60,
                'booking_id' => $bookingHoldId,
            ]);
        }
    }

    /**
     * Unlock seats for current user
     */
    public function unlockSeats(Request $request, $showId)
    {
        $request->validate([
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer'
        ]);

        $userId = Auth::id();
        $seatIds = $request->input('seat_ids', []);

        try {
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            foreach ($seatIds as $seatId) {
                $seatHoldService->releaseSeat($showId, (int)$seatId, $userId);
            }
        } catch (\Throwable $e) {
            \Log::warning('unlockSeats fallback: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
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
            'combos.*.gia' => 'required_with:combos|numeric|min:0'
        ]);

        $holdId = session('booking.hold_id');
        if (!$holdId) {
            return response()->json(['success' => false, 'message' => 'Phiên giữ ghế đã hết hạn. Vui lòng chọn lại.'], 410);
        }

        // Save selected seat codes, combos and showtime for the next step
        session([
            'booking.selected_seat_codes' => $validated['seats'],
            'booking.showtime_id' => $validated['showtime_id'],
            'booking.selected_combos' => collect($validated['combos'] ?? [])->filter(function($c){
                return isset($c['id_combo']) && ($c['so_luong'] ?? 0) > 0; 
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

        // Compute simple seat pricing for display with couple seat pairing
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
            while ($i < $selected->count()) {
                $cur = $selected[$i];
                $next = $selected[$i + 1] ?? null;
                if ($cur['isCouple'] && $next && $next['isCouple'] && $cur['row'] === $next['row'] && $next['num'] === $cur['num'] + 1) {
                    $seatDetails[] = ['code' => $cur['code'], 'type' => $cur['type'], 'price' => 100000];
                    $seatDetails[] = ['code' => $next['code'], 'type' => $next['type'], 'price' => 100000];
                    $totalSeatPrice += 200000;
                    $i += 2;
                } else {
                    if ($cur['isCouple']) {
                        $price = 200000;
                    } elseif ($cur['isVip']) {
                        $price = 120000;
                    } else {
                        $price = 80000;
                    }
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

        // Read VNPAY env for on-screen debug (with legacy fallbacks)
        $dbg_vnp_TmnCode = trim((string) env('VNPAY_TMN_CODE', ''));
        $dbg_vnp_HashSecret = trim((string) env('VNPAY_HASH_SECRET', ''));
        $dbg_vnp_Url = rtrim(trim((string) env('VNPAY_URL', '')), '/');
        $dbg_vnp_ReturnUrl = trim((string) env('VNPAY_RETURN_URL', ''));
        if ($dbg_vnp_TmnCode === '') { $dbg_vnp_TmnCode = trim((string) env('VNP_TMN_CODE', '')); }
        if ($dbg_vnp_HashSecret === '') { $dbg_vnp_HashSecret = trim((string) env('VNP_HASH_SECRET', '')); }
        if ($dbg_vnp_Url === '') { $dbg_vnp_Url = rtrim(trim((string) env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html')), '/'); }
        if ($dbg_vnp_ReturnUrl === '') { $dbg_vnp_ReturnUrl = trim((string) (env('VNP_RETURN_URL', url('/payment/vnpay-return')))); }

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

        // Load active promotions for display on payment page
        $khuyenmais = KhuyenMai::where('trang_thai', 1)
            ->where('ngay_bat_dau', '<=', now())
            ->where('ngay_ket_thuc', '>=', now())
            ->get();

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
     * Refresh seat statuses for a showtime
     */
    public function refreshSeats($showId)
    {
        $statuses = [];
        try {
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            // Load all seats in the room
            $showtime = SuatChieu::find($showId);
            if ($showtime) {
                $seats = Ghe::where('id_phong', $showtime->id_phong)->get();
                foreach ($seats as $seat) {
                    $status = $seatHoldService->getSeatStatus($showId, $seat->id, Auth::id());
                    if ($status === 'sold' || $status === 'hold' || $status === 'reserved' || $status === 'blocked') {
                        $statuses[$seat->id] = $status === 'hold' ? 'locked_by_other' : $status;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::warning('refreshSeats fallback: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'seats' => $statuses,
        ]);
    }
    
    public function create($id = null)
    {
        // Get movie data
        $movie = null;
        if ($id) {
            $movie = Phim::find($id);
        }

        // If no movie found, get first movie or create demo data
        if (!$movie) {
            $movie = Phim::first() ?? (object)[
                'id' => 1,
                'ten_phim' => 'Demo Movie',
                'thoi_luong' => 120,
                'poster' => 'images/default-poster.jpg'
            ];
        }

        // Initialize variables to avoid undefined variable errors
        $roomInfo = null;
        $seats = collect();
        $vipSeats = [];
        $vipRows = [];
        $coupleSeats = [];

        // Get real showtimes from database for this movie
        // First try to get showtimes from now to 7 days ahead
        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movie->id)
            ->where('thoi_gian_bat_dau', '>=', now())
            ->where('thoi_gian_bat_dau', '<=', now()->addDays(7))
            ->where('trang_thai', 1)
            ->orderBy('thoi_gian_bat_dau')
            ->get();

        // If no showtimes in next 7 days, get any future showtimes
        if ($showtimes->isEmpty()) {
            $showtimes = SuatChieu::with('phongChieu')
                ->where('id_phim', $movie->id)
                ->where('thoi_gian_bat_dau', '>=', now())
                ->where('trang_thai', 1)
                ->orderBy('thoi_gian_bat_dau')
                ->get();
        }

        // If still no showtimes, try without trang_thai check (maybe trang_thai is 0 or null)
        if ($showtimes->isEmpty()) {
            $showtimes = SuatChieu::with('phongChieu')
                ->where('id_phim', $movie->id)
                ->where('thoi_gian_bat_dau', '>=', now())
                ->orderBy('thoi_gian_bat_dau')
                ->get();
        }

        // If still no showtimes, get recent active showtimes (for testing/debugging)
        if ($showtimes->isEmpty()) {
            $showtimes = SuatChieu::with('phongChieu')
                ->where('id_phim', $movie->id)
                ->where('trang_thai', 1)
                ->orderBy('thoi_gian_bat_dau', 'desc')
                ->limit(10)
                ->get();
        }

        $showtimes = $showtimes->map(function ($suat) {
            return [
                'id' => $suat->id,
                'label' => date('H:i - d/m/Y', strtotime($suat->thoi_gian_bat_dau)) . ' - ' . ($suat->phongChieu->ten_phong ?? 'Phòng 1'),
                'time' => date('H:i', strtotime($suat->thoi_gian_bat_dau)),
                'date' => date('d/m/Y', strtotime($suat->thoi_gian_bat_dau)),
                'room' => $suat->phongChieu->ten_phong ?? 'Phòng 1'
            ];
        });

        // If no showtimes found, create demo data


        // Get real seats from database for the first showtime's room


        if ($showtimes->isNotEmpty()) {
            $firstShowtime = $showtimes->first();
            $suatChieu = SuatChieu::with('phongChieu')->find($firstShowtime['id']);
            if ($suatChieu && $suatChieu->phongChieu) {
                $roomInfo = $suatChieu->phongChieu;
                $seats = Ghe::where('id_phong', $suatChieu->id_phong)
                    ->with('loaiGhe')
                    ->get();

                // Get VIP seats - find seats with id_loai = 2 (VIP) or name contains "VIP"
                $vipSeatData = $seats->filter(function ($seat) {
                    return $this->isVipSeat($seat);
                });
                $vipSeats = $vipSeatData->pluck('so_ghe')->toArray();

                // Get VIP rows (extract row letter from seat code)
                $vipRows = $vipSeatData->map(function ($seat) {
                    return substr($seat->so_ghe, 0, 1); // Get first character (A, B, C, etc.)
                })->unique()->values()->toArray();

                // Get couple seats - find seats with id_loai = 3 (Couple) or name contains "đôi"
                $coupleSeatData = $seats->filter(function ($seat) {
                    return $this->isCoupleSeat($seat);
                });

                // Group couple seats by row
                $coupleSeatGroups = $coupleSeatData->groupBy(function ($seat) {
                    return substr($seat->so_ghe, 0, 1); // Group by row letter
                });

                $coupleSeats = [];
                foreach ($coupleSeatGroups as $row => $seatsInRow) {
                    $seatNumbers = $seatsInRow->pluck('so_ghe')->toArray();
                    sort($seatNumbers); // Sort to ensure correct order

                    // Look for pairs (like 11-12, 13-14, etc.)
                    for ($i = 0; $i < count($seatNumbers) - 1; $i++) {
                        $num1 = intval(substr($seatNumbers[$i], 1)); // Extract number part
                        $num2 = intval(substr($seatNumbers[$i + 1], 1));

                        // Check if seats are adjacent (like 11 and 12)
                        if ($num2 == $num1 + 1) {
                            $coupleSeats[] = $row . $num1 . '-' . $num2;
                            $i++; // Skip the next seat as it's already paired
                        }
                    }
                }
            }
        }

        // Fallback to hardcoded data if no seats found or no room info
        if ($seats->isEmpty() || !$roomInfo) {
            $coupleSeats = [];
            $vipSeats = [];
            $vipRows = [];
            // Also set fallback room info
            $roomInfo = (object) [
                'so_cot' => 15,
                'so_hang' => 10
            ];
        }

        return view('booking', compact('movie', 'showtimes', 'coupleSeats', 'vipSeats', 'vipRows', 'roomInfo'));
    }

    public function getBookedSeats($showtimeId)
    {
        try {
            // Validate showtime exists
            $showtime = SuatChieu::find($showtimeId);
            if (!$showtime) {
                return response()->json(['seats' => []]);
            }

            // Release expired seats (lazy check) - skip if table doesn't exist
            try {
                if (Schema::hasTable('suat_chieu_ghe')) {
                    ShowtimeSeat::releaseExpiredSeats($showtimeId);
                }
            } catch (\Throwable $e) {
                \Log::warning('Skip releaseExpiredSeats: ' . $e->getMessage());
            }

            // Get booked seats from showtime_seats table - guard for missing table
            $bookedSeats = collect();
            try {
                if (Schema::hasTable('suat_chieu_ghe')) {
                    $bookedSeats = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                        ->where('status', 'booked')
                        ->with('ghe')
                        ->get()
                        ->map(function ($showtimeSeat) {
                            return $showtimeSeat->ghe->so_ghe ?? null;
                        })
                        ->filter();
                }
            } catch (\Throwable $e) {
                \Log::warning('Skip get booked showtime seats: ' . $e->getMessage());
                $bookedSeats = collect();
            }

            // Also get from old booking system for backward compatibility
            $oldBookedSeats = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->join('ghe', 'chi_tiet_dat_ve.id_ghe', '=', 'ghe.id')
                ->where('dat_ve.id_suat_chieu', $showtimeId)
                ->whereIn('dat_ve.trang_thai', [0, 1]) // 0=chờ xác nhận, 1=đã xác nhận
                ->select('ghe.so_ghe')
                ->get()
                ->map(function ($seat) {
                    return $seat->so_ghe;
                });

            // Merge and get unique seats
            $allBookedSeats = $bookedSeats->merge($oldBookedSeats)->unique()->values();

            // Get holding seats (for display purposes) - guard for missing table
            $holdingSeats = collect();
            try {
                if (Schema::hasTable('suat_chieu_ghe')) {
                    $holdingSeats = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                        ->where('status', 'holding')
                        ->where('hold_expires_at', '>', Carbon::now())
                        ->with('ghe')
                        ->get()
                        ->map(function ($showtimeSeat) {
                            return $showtimeSeat->ghe->so_ghe ?? null;
                        })
                        ->filter();
                }
            } catch (\Throwable $e) {
                \Log::warning('Skip get holding seats: ' . $e->getMessage());
                $holdingSeats = collect();
            }

            return response()->json([
                'seats' => $allBookedSeats,
                'holding' => $holdingSeats
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading booked seats: ' . $e->getMessage());
            return response()->json(['seats' => [], 'holding' => []]);
        }
    }

    public function getShowtimeSeats($showtimeId)
    {
        try {
            Log::info('getShowtimeSeats called with showtimeId: ' . $showtimeId);

            // Validate showtime exists
            $showtime = SuatChieu::find($showtimeId);
            if (!$showtime) {
                Log::warning('Showtime not found: ' . $showtimeId);
                return response()->json(['seats' => []]);
            }

            Log::info('Showtime found, room id: ' . $showtime->id_phong);

            // Release expired seats (lazy check) - skip if table doesn't exist
            try {
                ShowtimeSeat::releaseExpiredSeats($showtimeId);
            } catch (\Exception $e) {
                \Log::warning('Could not release expired seats (table may not exist): ' . $e->getMessage());
            }
            
            // Get seat statuses from Redis (Beta standard) and DB
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            $currentUserId = Auth::id();
            
            // Get seat statuses from DB (for sold/reserved seats)
            $showtimeSeats = collect();
            try {
                $showtimeSeats = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                    ->with('ghe')
                    ->get()
                    ->keyBy(function ($showtimeSeat) {
                        return $showtimeSeat->ghe->so_ghe ?? null;
                    })
                    ->filter();
            } catch (\Exception $e) {
                \Log::warning('Could not load showtime seats (table may not exist): ' . $e->getMessage());
                $showtimeSeats = collect();
            }
            
            // Get room for layout service
            $room = $showtime->phongChieu;
            if (!$room) {
                Log::warning('Room not found for showtime: ' . $showtimeId);
                return response()->json(['seats' => []]);
            }
            
            // Use SeatLayoutService to get matrix with null positions for special layouts
            $layoutService = app(\App\Services\SeatLayoutService::class);
            $seatMatrix = $layoutService->getSeatMatrix($room);
            
            // Get all seats for this showtime's room (for status checking)
            $allSeats = Ghe::where('id_phong', $showtime->id_phong)
                ->with('loaiGhe')
                ->get()
                ->keyBy('so_ghe');
            
            \Log::info('Total seats found in room: ' . $allSeats->count());
            
            // Convert matrix to flat array with status
            $seats = [];
            foreach ($seatMatrix as $rowLabel => $columns) {
                foreach ($columns as $col => $seatData) {
                    if ($seatData === null) {
                        // Empty position - mark as null (frontend will skip rendering)
                        $positionKey = $rowLabel . $col;
                        $seats[$positionKey] = null;
                    } else {
                        // Real seat - get status
                        $seat = $allSeats->get($seatData['code']);
                        if (!$seat) {
                            continue; // Skip if seat not found in DB
                        }
                        
                        $typeText = strtolower($seat->loaiGhe->ten_loai ?? 'thường');
                        
                        // Get status from Redis (Beta standard) - checks hold, sold, reserved
                        $seatStatus = $seatHoldService->getSeatStatus($showtimeId, $seat->id, $currentUserId);
                        $isAvailable = ($seatStatus === 'available');
                        
                        // Get hold expiration if held
                        $holdExpiresAt = null;
                        if ($seatStatus === 'hold') {
                            $hold = $seatHoldService->getSeatHold($showtimeId, $seat->id);
                            if ($hold && isset($hold['hold_expires_at'])) {
                                $holdExpiresAt = Carbon::parse($hold['hold_expires_at']);
                            }
                        }
                        
                        // Fallback: Check DB for sold/reserved if Redis doesn't have it
                        if ($seatStatus === 'available') {
                            $showtimeSeat = $showtimeSeats->get($seat->so_ghe);
                            if ($showtimeSeat) {
                                if ($showtimeSeat->isBooked() || $showtimeSeat->status === 'booked') {
                                    $seatStatus = 'sold';
                                    $isAvailable = false;
                                } elseif ($showtimeSeat->status === 'reserved') {
                                    $seatStatus = 'reserved';
                                    $isAvailable = false;
                                }
                            } else {
                                // Check seat's own status
                                $isAvailable = (int)($seat->trang_thai ?? 0) === 1;
                                if (!$isAvailable) {
                                    $seatStatus = 'blocked';
                                }
                            }
                        }
                        
                        // Determine price based on seat type
                        if ($isAvailable) {
                            if (str_contains($typeText, 'vip')) {
                                $price = 120000;
                            } elseif (str_contains($typeText, 'đôi') || str_contains($typeText, 'doi') || str_contains($typeText, 'couple')) {
                                $price = 200000;
                            } else {
                                $price = 80000;
                            }
                        } else {
                            $price = 0;
                        }
                        
                        $seats[$seat->so_ghe] = [
                            'id' => $seat->id,
                            'code' => $seat->so_ghe,
                            'type' => $seat->loaiGhe->ten_loai ?? 'Thường',
                            'available' => $isAvailable,
                            'status' => $seatStatus, // 'available', 'hold', 'sold', 'reserved', 'blocked'
                            'price' => $price,
                            'hold_expires_at' => $holdExpiresAt ? $holdExpiresAt->toIso8601String() : null,
                        ];
                    }
                }
            }
            
            // Old code (kept for reference, but replaced above)
            /*
            $seats = $allSeats->mapWithKeys(function ($seat) use ($showtimeSeats, $seatHoldService, $showtimeId, $currentUserId) {
                    $typeText = strtolower($seat->loaiGhe->ten_loai ?? 'thường');
                    
                    // Get status from Redis (Beta standard) - checks hold, sold, reserved
                    $seatStatus = $seatHoldService->getSeatStatus($showtimeId, $seat->id, $currentUserId);
                    $isAvailable = ($seatStatus === 'available');
                    
                    // Get hold expiration if held
                    $holdExpiresAt = null;
                    if ($seatStatus === 'hold') {
                        $hold = $seatHoldService->getSeatHold($showtimeId, $seat->id);
                        if ($hold && isset($hold['hold_expires_at'])) {
                            $holdExpiresAt = Carbon::parse($hold['hold_expires_at']);
                        }
                    }
                    
                    // Fallback: Check DB for sold/reserved if Redis doesn't have it
                    if ($seatStatus === 'available') {
                        $showtimeSeat = $showtimeSeats->get($seat->so_ghe);
                        if ($showtimeSeat) {
                            if ($showtimeSeat->isBooked() || $showtimeSeat->status === 'booked') {
                                $seatStatus = 'sold';
                                $isAvailable = false;
                            } elseif ($showtimeSeat->status === 'reserved') {
                                $seatStatus = 'reserved';
                                $isAvailable = false;
                            }
                        } else {
                            // Check seat's own status
                            $isAvailable = (int)($seat->trang_thai ?? 0) === 1;
                            if (!$isAvailable) {
                                $seatStatus = 'blocked';
                            }
                        }
                    }
                } else {
                    // Fallback to seat's own status
                    $isAvailable = (int)($seat->trang_thai ?? 0) === 1;
                    if (!$isAvailable) {
                        $seatStatus = 'blocked';
                    }
                }

                // Debug logging for VIP seats
                if (str_contains($typeText, 'vip') || str_contains(strtolower($seat->loaiGhe->ten_loai ?? ''), 'vip')) {
                    Log::info('VIP seat found: ' . $seat->so_ghe . ', type: ' . ($seat->loaiGhe->ten_loai ?? 'N/A'));
                }

                // Determine price based on seat type
                if ($isAvailable) {
                    if (str_contains($typeText, 'vip')) {
                        $price = 120000;
                    } elseif (str_contains($typeText, 'đôi') || str_contains($typeText, 'doi') || str_contains($typeText, 'couple')) {
                        $price = 200000;
                    } else {
                        $price = 80000;
                    }
                    
                    return [$seat->so_ghe => [
                        'id' => $seat->id,
                        'code' => $seat->so_ghe,
                        'type' => $seat->loaiGhe->ten_loai ?? 'Thường',
                        'available' => $isAvailable,
                        'status' => $seatStatus, // 'available', 'hold', 'sold', 'reserved', 'blocked'
                        'price' => $price,
                        'hold_expires_at' => $holdExpiresAt ? $holdExpiresAt->toIso8601String() : null,
                    ]];
                });
            */
            
            \Log::info('Seats data prepared, count: ' . count($seats));
            
            // Convert collection to array to ensure proper JSON encoding
            // mapWithKeys returns a collection, convert to array properly
            $seatsArray = [];
            foreach ($seats as $key => $value) {
                $seatsArray[$key] = $value;
            }

            \Log::info('Seats array count: ' . count($seatsArray));
            \Log::info('Sample seat keys: ' . implode(', ', array_slice(array_keys($seatsArray), 0, 5)));

            return response()->json(['seats' => $seatsArray]);
        } catch (\Exception $e) {
            \Log::error('Error loading showtime seats: ' . $e->getMessage());
            return response()->json(['seats' => []]);
        }
    }

    public function store(Request $request)
    {
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
                'content_type' => $request->header('Content-Type'),
                'is_json' => $request->isJson(),
                'user_id' => Auth::id(),
                'showtime' => $requestData['showtime'] ?? null,
                'seats' => $requestData['seats'] ?? [],
                'booking_hold_id' => $requestData['booking_hold_id'] ?? null,
                'booking_id' => $requestData['booking_id'] ?? null
            ]);
            
            // Check authentication
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để đặt vé!'
                ], 401);
            }

            // Check if user is admin (prevent admin from booking tickets)
            $user = Auth::user();
            if ($user && $user->id_vai_tro == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin không được phép đặt vé trực tiếp!'
                ]);
            }

            // Parse JSON data - support both JSON and form data
            $data = $request->all();
            if ($request->isJson() || $request->header('Content-Type') === 'application/json') {
                $jsonData = json_decode($request->getContent(), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($jsonData)) {
                    $data = $jsonData;
                }
            }

            // Validate required fields
            if (!isset($data['seats']) || empty($data['seats'])) {
                Log::warning('Booking store: No seats selected', ['data' => $data]);
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ghế!'
                ], 400);
            }
            
            // Validate seat pattern (chặn tam giác, hình thoi, ziczac, etc.)
            $patternValidator = app(\App\Services\SeatPatternValidator::class);
            $patternValidation = $patternValidator->validatePattern($data['seats']);
            
            if (!$patternValidation['valid']) {
                Log::warning('Booking store: Invalid seat pattern', [
                    'seats' => $data['seats'],
                    'user_id' => Auth::id(),
                    'message' => $patternValidation['message']
                ]);
                return response()->json([
                    'success' => false,
                    'message' => $patternValidation['message']
                ], 400);
            }

            // Validate showtime exists
            if (!isset($data['showtime']) || !$data['showtime']) {
                Log::warning('Booking store: No showtime selected', ['data' => $data]);
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn suất chiếu!'
                ], 400);
            }

            $showtime = SuatChieu::find($data['showtime']);
            if (!$showtime) {
                Log::warning('Booking store: Showtime not found', [
                    'showtime_id' => $data['showtime'] ?? null
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Suất chiếu không tồn tại!'
                ], 400);
            }
            
            // Beta standard: Validate seats are still held before creating booking
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            $bookingHoldId = $data['booking_hold_id'] ?? null;
            
            Log::info('Booking store request', [
                'user_id' => Auth::id(),
                'showtime_id' => $data['showtime'] ?? null,
                'seats' => $data['seats'] ?? [],
                'booking_hold_id' => $bookingHoldId
            ]);
            
            if ($bookingHoldId) {
                // Check booking hold (for logging only, don't reject if expired)
                $bookingHold = $seatHoldService->getBookingHold($bookingHoldId);
                if ($bookingHold) {
                    Log::info('Booking hold found', [
                        'booking_hold_id' => $bookingHoldId,
                        'hold_seats' => $bookingHold['seat_ids'] ?? [],
                        'hold_expires_at' => $bookingHold['hold_expires_at'] ?? null
                    ]);
                } else {
                    Log::info('Booking hold not found (may have expired, but allowing to continue)', [
                        'booking_hold_id' => $bookingHoldId,
                        'user_id' => Auth::id(),
                        'showtime_id' => $data['showtime'] ?? null
                    ]);
                }
            } else {
                // No booking_hold_id provided - this is okay for backward compatibility
                // But we should log it for debugging
                Log::info('Booking store: No booking_hold_id provided', [
                    'user_id' => Auth::id(),
                    'showtime_id' => $data['showtime'] ?? null,
                    'seats' => $data['seats'] ?? []
                ]);
            }

            // Release expired seats first (if mapping table exists)
            try {
                if (Schema::hasTable('suat_chieu_ghe')) {
                    ShowtimeSeat::releaseExpiredSeats($data['showtime']);
                }
            } catch (\Throwable $e) {
                \Log::warning('Skip releaseExpiredSeats@store: ' . $e->getMessage());
            }

            // If there's an existing booking, release its seats first
            if (isset($data['booking_id']) && $data['booking_id']) {
                try {
                    $existingBooking = DatVe::where('id', $data['booking_id'])
                        ->where('id_nguoi_dung', Auth::id())
                        ->where('id_suat_chieu', $data['showtime'])
                        ->where('trang_thai', 0)
                        ->first();

                    if ($existingBooking && Schema::hasTable('suat_chieu_ghe')) {
                        // Release seats from existing booking
                        $existingSeats = ChiTietDatVe::where('id_dat_ve', $existingBooking->id)
                            ->with('ghe')
                            ->get();

                        foreach ($existingSeats as $seatDetail) {
                            if ($seatDetail->ghe) {
                                ShowtimeSeat::where('id_suat_chieu', $data['showtime'])
                                    ->where('id_ghe', $seatDetail->ghe->id)
                                    ->where('status', 'holding')
                                    ->update([
                                        'status' => 'available',
                                        'hold_expires_at' => null
                                    ]);
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Skip release existing booking seats@store: ' . $e->getMessage());
                }
            }

            // Get existing booking seats if booking_id is provided
            $existingBookingSeats = [];
            $existingBookingHoldingSeats = []; // Seats held by this booking in suat_chieu_ghe
            if (isset($data['booking_id']) && $data['booking_id']) {
                try {
                    $existingBooking = DatVe::where('id', $data['booking_id'])
                        ->where('id_nguoi_dung', Auth::id())
                        ->where('id_suat_chieu', $data['showtime'])
                        ->where('trang_thai', 0)
                        ->first();

                    if ($existingBooking) {
                        // Get seats from chi_tiet_dat_ve (if any)
                        $existingBookingSeats = ChiTietDatVe::where('id_dat_ve', $existingBooking->id)
                            ->with('ghe')
                            ->get()
                            ->pluck('ghe.id')
                            ->toArray();

                        // Also get seats that are holding for this booking from suat_chieu_ghe
                        if (Schema::hasTable('suat_chieu_ghe')) {
                            $holdingSeats = ShowtimeSeat::where('id_suat_chieu', $data['showtime'])
                                ->where('status', 'holding')
                                ->with('ghe')
                                ->get();

                            // Check if these holding seats belong to our booking
                            // Since selectSeats doesn't create chi_tiet_dat_ve, we need to check by showtime
                            // All holding seats for this showtime by this user should be considered as from this booking
                            foreach ($holdingSeats as $holdingSeat) {
                                if ($holdingSeat->ghe) {
                                    $existingBookingHoldingSeats[] = $holdingSeat->ghe->id;
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Skip get existing booking seats@store: ' . $e->getMessage());
                }
            }

            // Check if seats are valid (null positions in special layouts)
            // Support for special layouts: validate seats are not null positions
            $layoutService = app(\App\Services\SeatLayoutService::class);
            $seatMatrix = $layoutService->getSeatMatrix($showtime->phongChieu);
            
            $invalidSeats = []; // Seats that don't exist (null positions)
            
            foreach ($data['seats'] as $seat) {
                $seat = trim($seat);
                if ($seat === '') continue;

                $pairs = [];
                if (strpos($seat, '-') !== false) {
                    // Format: R11-12
                    if (preg_match('/^([A-Z])(?:\s*)(\d+)-(\d+)$/i', $seat, $m)) {
                        $rowLetter = strtoupper($m[1]);
                        $start = (int)$m[2];
                        $end = (int)$m[3];
                        for ($c = $start; $c <= $end; $c++) {
                            $pairs[] = $rowLetter . $c;
                        }
                    }
                } elseif (strpos($seat, ',') !== false) {
                    // Format: R11,R12
                    $parts = array_filter(array_map('trim', explode(',', $seat)));
                    foreach ($parts as $code) {
                        $pairs[] = strtoupper($code);
                    }
                } else {
                    // Single seat like R11
                    $pairs[] = strtoupper($seat);
                }

                foreach ($pairs as $code) {
                    // Extract row and col from seat code (e.g., "A1" -> row="A", col=1)
                    if (preg_match('/^([A-Z])(\d+)$/i', $code, $matches)) {
                        $rowLabel = strtoupper($matches[1]);
                        $col = (int)$matches[2];
                        
                        // Check if this is a valid seat (not null position in special layouts)
                        if (!$layoutService->isValidSeat($seatMatrix, $rowLabel, $col)) {
                            $invalidSeats[] = $code;
                            continue; // Skip null positions
                        }
                    }
                    
                    $ghe = Ghe::where('id_phong', $showtime->id_phong)
                        ->where('so_ghe', $code)
                        ->first();
                    
                    if (!$ghe) {
                        // Seat code doesn't exist in database (might be null position)
                        $invalidSeats[] = $code;
                        continue;
                    }
                }
            }
            
            // Check for invalid seats (null positions)
            if (!empty($invalidSeats)) {
                Log::warning('Invalid seats (null positions) selected', [
                    'invalid_seats' => $invalidSeats,
                    'showtime_id' => $data['showtime'] ?? null
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Một hoặc nhiều ghế không hợp lệ (vị trí trống): ' . implode(', ', array_unique($invalidSeats))
                ], 400);
            }
            
            // Note: Removed unavailable seats validation - seats availability is checked by SeatHoldService
            // and payment callback will handle final seat status updates
            
            // Calculate total amount
            $seatTotal = 0;

            // Get VIP rows from database for the showtime's room
            $vipRows = [];
            $showtime = SuatChieu::find($data['showtime']);
            if ($showtime) {
                $vipSeats = Ghe::where('id_phong', $showtime->id_phong)
                    ->with('loaiGhe')
                    ->get()
                    ->filter(function ($seat) {
                        return $this->isVipSeat($seat);
                    });

                $vipRows = $vipSeats->map(function ($seat) {
                    return substr($seat->so_ghe, 0, 1); // Get row letter
                })->unique()->toArray();
            }

            // Fallback to hardcoded if no VIP rows found
            if (empty($vipRows)) {
                $vipRows = ['C', 'D', 'E', 'F'];
            }

            foreach ($data['seats'] as $seat) {
                // Handle couple seats (contains dash)
                if (strpos($seat, '-') !== false) {
                    $seatTotal += 200000; // Couple seat price
                } else {
                    // Get actual seat from database to check type
                    $row = substr($seat, 0, 1);
                    $col = substr($seat, 1);
                    $seatObj = Ghe::where('id_phong', $showtime->id_phong)
                        ->where('so_ghe', $row . $col)
                        ->with('loaiGhe')
                        ->first();

                    if ($seatObj && $this->isVipSeat($seatObj)) {
                        $seatTotal += 120000;
                    } else {
                        $seatTotal += 80000;
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
                    // Parse min condition from dieu_kien (e.g., "...500000")
                    $min = 0;
                    if (!empty($promotion->dieu_kien)) {
                        $minDigits = preg_replace('/\D+/', '', (string)$promotion->dieu_kien);
                        if ($minDigits !== '') $min = (float)$minDigits;
                    }
                    if ($subtotal >= $min) {
                        if ($promotion->loai_giam === 'phantram') {
                            $discount = round($subtotal * ((float)$promotion->gia_tri_giam / 100));
                        } else { // codinh
                            $val = (float)$promotion->gia_tri_giam;
                            $fixed = $val >= 1000 ? $val : $val * 1000; // treat as thousands if small
                            $discount = round($fixed);
                        }
                        if ($discount > $subtotal) $discount = $subtotal;
                    }
                }
            }

            $totalAmount = max(0, $seatTotal + $comboTotal - $discount);

            // Check if there's a pending booking from selectSeats
            $existingBooking = null;
            if (isset($data['booking_id']) && $data['booking_id']) {
                $existingBooking = DatVe::where('id', $data['booking_id'])
                    ->where('id_nguoi_dung', Auth::id())
                    ->where('id_suat_chieu', $data['showtime'])
                    ->where('trang_thai', 0) // pending
                    ->first();
            }

            // If no existing booking found, try to find any pending booking for this user and showtime
            // Remove tong_tien = 0 condition to avoid duplicate bookings
            if (!$existingBooking) {
                $existingBooking = DatVe::where('id_nguoi_dung', Auth::id())
                    ->where('id_suat_chieu', $data['showtime'])
                    ->where('trang_thai', 0) // pending
                    ->orderBy('created_at', 'desc')
                    ->first();
            }

            // Create or update booking
            // Beta standard: Booking status is ALWAYS 0 (pending) until payment succeeds
            $paymentMethod = $data['payment_method'] ?? 'offline';
            $bookingStatus = 0; // ALWAYS pending - only change to 1 when payment succeeds
            $methodCode = ($paymentMethod === 'online') ? 1 : 2; // 1=online, 2=tai quay

            // Set expires_at for offline bookings (5 minutes from now)
            $expiresAt = null;
            if ($paymentMethod === 'offline') {
                $expiresAt = \Carbon\Carbon::now()->addMinutes(5);
            } else if ($paymentMethod === 'online') {
                $expiresAt = \Carbon\Carbon::now()->addMinutes(15);
            }

            if ($existingBooking) {
                // Delete old seat details if any
                ChiTietDatVe::where('id_dat_ve', $existingBooking->id)->delete();

                // Update existing booking
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
                // Create new booking
                $createData = [
                    'id_nguoi_dung'   => Auth::id(),
                    'id_suat_chieu'   => $data['showtime'] ?? null,
                    'id_khuyen_mai'   => $promotionId,
                    'tong_tien'       => $totalAmount,
                    'trang_thai'      => $bookingStatus,
                ];
                if ($expiresAt) {
                    $createData['expires_at'] = $expiresAt;
                }
                $booking = DatVe::create($createData);
            }

            // Release expired seats first (only if mapping table exists)
            try {
                if (Schema::hasTable('suat_chieu_ghe')) {
                    ShowtimeSeat::releaseExpiredSeats($data['showtime']);
                }
            } catch (\Throwable $e) {
                \Log::warning('Skip releaseExpiredSeats before saving seats: ' . $e->getMessage());
            }

            // Save seat details and update showtime_seats status
            foreach ($data['seats'] as $seatCode) {
                $seatCode = trim($seatCode);
                if ($seatCode === '') continue;

                $codesToSave = [];
                if (strpos($seatCode, '-') !== false) {
                    if (preg_match('/^([A-Z])(?:\s*)(\d+)-(\d+)$/i', $seatCode, $matches)) {
                        $row = strtoupper($matches[1]);
                        $col1 = (int)$matches[2];
                        $col2 = (int)$matches[3];
                        for ($c = $col1; $c <= $col2; $c++) {
                            $codesToSave[] = $row . $c;
                        }
                    }
                } elseif (strpos($seatCode, ',') !== false) {
                    $parts = array_filter(array_map('trim', explode(',', $seatCode)));
                    foreach ($parts as $code) {
                        $codesToSave[] = strtoupper($code);
                    }
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
                        // Determine price
                        $price = $this->isCoupleSeat($seat) ? 100000 : ($this->isVipSeat($seat) ? 120000 : 80000);
                        ChiTietDatVe::create([
                            'id_dat_ve' => $booking->id,
                            'id_ghe' => $seat->id,
                            'gia' => $price
                        ]);
                        
                        // Beta standard: DO NOT update seat status to "booked" here
                        // Seats remain in HOLD status (in Redis) until payment succeeds
                        // Only update to "booked" when payment callback confirms success
                        // This prevents seats from being locked if payment fails
                        
                        // Note: Seat hold is managed in Redis via SeatHoldService
                        // We only create booking record here, seats stay in hold state
                    }
                }
            }

            // Save combo detail if chosen
            if ($selectedCombo) {
                ChiTietCombo::create([
                    'id_dat_ve'   => $booking->id,
                    'id_combo'    => $selectedCombo->id,
                    'so_luong'    => 1,
                    'gia_ap_dung' => (float)$selectedCombo->gia,
                ]);
            }

            // Create payment record (Beta standard: status = 0 until payment succeeds)
            ThanhToan::create([
                'id_dat_ve'    => $booking->id,
                'phuong_thuc'  => ($paymentMethod === 'online') ? 'VNPAY' : 'Tiền mặt',
                'so_tien'      => $totalAmount,
                'trang_thai'   => 0, // Chưa thanh toán - only change to 1 when payment succeeds
                'thoi_gian'    => now()
            ]);
            
            // Beta standard: Store booking_hold_id if available (from selectSeats)
            // This allows us to release holds if payment fails
            if (isset($data['booking_hold_id'])) {
                // Store booking_hold_id in session or booking metadata for later use
                session(['booking_hold_id_' . $booking->id => $data['booking_hold_id']]);
            }

            // Return result
            if ($paymentMethod === 'online') {
                $vnp_Url = app(PaymentController::class)->createVnpayUrl($booking->id, $totalAmount);
                return response()->json([
                    'success' => true,
                    'message' => 'Đang chuyển hướng thanh toán...',
                    'payment_url' => $vnp_Url,
                    'is_redirect' => true
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Đặt vé thành công! Vui lòng thanh toán tại quầy trong 5 phút.',
                    'booking_id' => $booking->id,
                    'is_redirect' => false
                ]);
            }
            
        } catch (\Throwable $e) {
            Log::error('Booking error: ' . $e->getMessage());
            Log::error('Booking error trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại!',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
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
        usort($seats, function($a, $b) {
            if ($a['row'] !== $b['row']) {
                return strcmp($a['row'], $b['row']);
            }
            return $a['number'] - $b['number'];
        });

        usort($expectedCorners, function($a, $b) {
            if ($a['row'] !== $b['row']) {
                return strcmp($a['row'], $b['row']);
            }
            return $a['number'] - $b['number'];
        });

        // Check if seats match expected corners
        for ($i = 0; $i < 4; $i++) {
            if ($seats[$i]['row'] !== $expectedCorners[$i]['row'] || 
                $seats[$i]['number'] !== $expectedCorners[$i]['number']) {
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
}
