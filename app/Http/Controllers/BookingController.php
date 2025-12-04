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
            $comboTotal = (int) $selectedCombos->sum(function ($c) {
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
                $userId = Auth::id();
                $conflictedSeats = [];
                
                // Check for seat conflicts BEFORE creating booking
                foreach ($selectedSeatCodes as $code) {
                    $seat = $seats->get($code);
                    if (!$seat) continue;
                    
                    $seatId = $seat->id;
                    
                    // Lock the seat to prevent race condition
                    $ghe = \App\Models\Ghe::where('id', $seatId)->lockForUpdate()->first();
                    if (!$ghe) {
                        $conflictedSeats[] = $code;
                        continue;
                    }
                    
                    // Check if seat is already booked (paid)
                    $isSold = \App\Models\ChiTietDatVe::whereHas('datVe', function($query) use ($showtimeId) {
                            $query->where('id_suat_chieu', $showtimeId)
                                  ->where('trang_thai', 1); // Only PAID
                        })
                        ->where('id_ghe', $seatId)
                        ->exists();
                    
                    if ($isSold) {
                        $conflictedSeats[] = $code;
                        continue;
                    }
                    
                    // Check if seat is already in pending booking by another user
                    $hasPendingBooking = \App\Models\ChiTietDatVe::whereHas('datVe', function($query) use ($showtimeId, $userId) {
                            $query->where('id_suat_chieu', $showtimeId)
                                  ->where('trang_thai', 0) // Pending
                                  ->where('id_nguoi_dung', '!=', $userId); // Different user
                        })
                        ->where('id_ghe', $seatId)
                        ->exists();
                    
                    if ($hasPendingBooking) {
                        $conflictedSeats[] = $code;
                        continue;
                    }
                    
                    // Check ShowtimeSeat if exists
                    try {
                        $showtimeSeat = \App\Models\ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                            ->where('id_ghe', $seatId)
                            ->lockForUpdate()
                            ->first();
                        
                        if ($showtimeSeat) {
                            // Check if booked
                            if ($showtimeSeat->trang_thai === 'booked') {
                                $conflictedSeats[] = $code;
                                continue;
                            }
                            
                            // Check if held by another user
                            if ($showtimeSeat->trang_thai === 'holding' && 
                                $showtimeSeat->id_nguoi_dung && 
                                $showtimeSeat->id_nguoi_dung != $userId &&
                                $showtimeSeat->thoi_gian_het_han && 
                                $showtimeSeat->thoi_gian_het_han->isFuture()) {
                                $conflictedSeats[] = $code;
                                continue;
                            }
                        }
                    } catch (\Exception $e) {
                        // ShowtimeSeat table might not exist, continue
                    }
                }
                
                // If any seats are conflicted, throw error
                if (!empty($conflictedSeats)) {
                    throw new \Exception('Một hoặc nhiều ghế đã được đặt: ' . implode(', ', $conflictedSeats));
                }
                
                // 1) Create booking (pending) with expiration time
                // Set expires_at to 15 minutes from now for online payment
                $expiresAt = \Carbon\Carbon::now()->addMinutes(15);
                $booking = \App\Models\DatVe::create([
                    'id_nguoi_dung' => $userId,
                    'id_suat_chieu' => $showtimeId,
                    'trang_thai' => 0, // pending
                    'expires_at' => $expiresAt,
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

        // Clean up expired pending bookings for this user
        $expiredBookings = DatVe::where('id_nguoi_dung', $userId)
            ->where('trang_thai', 0) // Pending
            ->where(function($query) {
                $query->whereNotNull('expires_at')
                      ->where('expires_at', '<=', now());
            })
            ->get();
        
        if ($expiredBookings->count() > 0) {
            foreach ($expiredBookings as $expiredBooking) {
                // Delete seat details
                ChiTietDatVe::where('id_dat_ve', $expiredBooking->id)->delete();
                // Delete combo details
                ChiTietCombo::where('id_dat_ve', $expiredBooking->id)->delete();
                // Delete payment record
                ThanhToan::where('id_dat_ve', $expiredBooking->id)->delete();
                // Delete booking
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
        
        // Fix bookings that have been paid but status is still pending
        $paidButPending = DatVe::where('id_nguoi_dung', $userId)
            ->where('trang_thai', 0) // Pending
            ->whereHas('thanhToan', function($query) {
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
            'booking.selected_combos' => collect($validated['combos'] ?? [])->filter(function ($c) {
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
            // 1. Lấy danh sách ghế ĐÃ BÁN từ Database (Status 1: Đã thanh toán, 0: Đang chờ thanh toán)
            $soldSeats = \DB::table('chi_tiet_dat_ve')
                ->join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->where('dat_ve.id_suat_chieu', $showId)
                ->whereIn('dat_ve.trang_thai', [0, 1]) // 0: Pending, 1: Thành công
                ->pluck('chi_tiet_dat_ve.id_ghe')
                ->toArray();

            // Gán trạng thái 'sold' cho các ghế này
            foreach ($soldSeats as $seatId) {
                $statuses[$seatId] = 'booked'; // JS bên view check 'booked' hoặc 'sold'
            }

            // 2. Lấy danh sách ghế ĐANG GIỮ (từ Service/Redis)
            $seatHoldService = app(\App\Services\SeatHoldService::class);
            $showtime = SuatChieu::find($showId);

            if ($showtime) {
                $seats = Ghe::where('id_phong', $showtime->id_phong)->get();
                foreach ($seats as $seat) {
                    // Nếu ghế đã bán rồi thì bỏ qua, không cần check giữ chỗ nữa
                    if (isset($statuses[$seat->id])) continue;

                    $status = $seatHoldService->getSeatStatus($showId, $seat->id, Auth::id());

                    // Nếu status là 'hold' (người khác giữ) hoặc 'sold' (redis báo bán)
                    if ($status === 'hold' || $status === 'sold') {
                        $statuses[$seat->id] = ($status === 'hold') ? 'locked_by_other' : 'booked';
                    }
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
        // Only show showtimes that haven't ended yet
        // First try to get showtimes from now to 7 days ahead
        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movie->id)
            ->where('thoi_gian_ket_thuc', '>', now()) // Only showtimes that haven't ended
            ->where('thoi_gian_bat_dau', '<=', now()->addDays(7))
            ->get();

    // Khởi tạo các biến khác để tránh lỗi undefined
    $roomInfo = null;
    $seats = collect();
    $vipSeats = [];
    $vipRows = [];
    $coupleSeats = [];

    // 2. Lấy danh sách suất chiếu từ DB
    // Thử lấy suất chiếu từ nay đến 7 ngày tới
    $showtimes = SuatChieu::with('phongChieu')
        ->where('id_phim', $movie->id)
        ->where('thoi_gian_bat_dau', '>=', now())
        ->where('thoi_gian_bat_dau', '<=', now()->addDays(7))
        ->where('trang_thai', 1)
        ->orderBy('thoi_gian_bat_dau')
        ->get();

    // Nếu không có, lấy bất kỳ suất chiếu nào trong tương lai
    if ($showtimes->isEmpty()) {
        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movie->id)
            ->where('thoi_gian_bat_dau', '>=', now())

            ->where('trang_thai', 1)
            ->orderBy('thoi_gian_bat_dau')
            ->get();
    }


        // If no showtimes in next 7 days, get any future showtimes that haven't ended
        if ($showtimes->isEmpty()) {
            $showtimes = SuatChieu::with('phongChieu')
                ->where('id_phim', $movie->id)
                ->where('thoi_gian_ket_thuc', '>', now()) // Only showtimes that haven't ended
                ->where('trang_thai', 1)
                ->orderBy('thoi_gian_bat_dau')
                ->get();
        }

    // Nếu vẫn không có, thử bỏ check trang_thai
    if ($showtimes->isEmpty()) {
        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movie->id)
            ->where('thoi_gian_bat_dau', '>=', now())
            ->orderBy('thoi_gian_bat_dau')
            ->get();
    }

    // Nếu vẫn không có, lấy các suất chiếu gần đây nhất (để test)
    if ($showtimes->isEmpty()) {
        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movie->id)
            ->where('trang_thai', 1)
            ->orderBy('thoi_gian_bat_dau', 'desc')
            ->limit(10)
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

    // 3. Lấy thông tin ghế và phòng của suất chiếu ĐẦU TIÊN (mặc định)
    if ($showtimes->isNotEmpty()) {
        $firstShowtime = $showtimes->first();
        
        // Tìm suất chiếu thực tế từ DB
        $suatChieu = SuatChieu::with('phongChieu')->find($firstShowtime->id);
        
        // [QUAN TRỌNG] Gán biến $showtime để truyền sang View
        $showtime = $suatChieu; 

        if ($suatChieu && $suatChieu->phongChieu) {
            $roomInfo = $suatChieu->phongChieu;
            $seats = Ghe::where('id_phong', $suatChieu->id_phong)
                ->with('loaiGhe')

                ->get();
        }

        // If still no showtimes, try without trang_thai check (maybe trang_thai is 0 or null)
        if ($showtimes->isEmpty()) {
            $showtimes = SuatChieu::with('phongChieu')
                ->where('id_phim', $movie->id)
                ->where('thoi_gian_ket_thuc', '>', now()) // Only showtimes that haven't ended
                ->orderBy('thoi_gian_bat_dau')
                ->get();
        }

            // Lọc ghế VIP
            $vipSeatData = $seats->filter(function ($seat) {
                return $this->isVipSeat($seat);
            });
            $vipSeats = $vipSeatData->pluck('so_ghe')->toArray();


            // Lấy danh sách hàng ghế VIP (A, B...)
            $vipRows = $vipSeatData->map(function ($seat) {
                return substr($seat->so_ghe, 0, 1); 
            })->unique()->values()->toArray();

            // Lọc ghế đôi
            $coupleSeatData = $seats->filter(function ($seat) {
                return $this->isCoupleSeat($seat);
            });

            // Gom nhóm ghế đôi theo hàng
            $coupleSeatGroups = $coupleSeatData->groupBy(function ($seat) {
                return substr($seat->so_ghe, 0, 1);
            });

            $coupleSeats = [];
            foreach ($coupleSeatGroups as $row => $seatsInRow) {
                $seatNumbers = $seatsInRow->pluck('so_ghe')->toArray();
                sort($seatNumbers);

                // Tìm các cặp ghế liền kề (ví dụ: A1-A2)
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

    // Fallback dữ liệu nếu không tìm thấy ghế hoặc phòng
    if ($seats->isEmpty() || !$roomInfo) {
        $coupleSeats = [];
        $vipSeats = [];
        $vipRows = [];
        $roomInfo = (object) [
            'so_cot' => 15,
            'so_hang' => 10
        ];
    }

    // Ghi đè lại biến showtimes bằng biến đã map (nếu muốn dùng cấu trúc mảng cũ) 
    // hoặc giữ nguyên Collection gốc tùy thuộc vào view của bạn.
    // Ở đoạn code gốc bạn gán lại $showtimes = $showtimesMapped; 
    // nhưng cần chú ý $firstShowtime ở trên đang dùng Collection gốc.
    // Để an toàn, ta truyền $showtimesMapped sang view với tên 'showtimes'.
    
    return view('booking', [
        'movie' => $movie,
        'showtimes' => $showtimesMapped, // Sử dụng biến đã map format
        'coupleSeats' => $coupleSeats,
        'vipSeats' => $vipSeats,
        'vipRows' => $vipRows,
        'roomInfo' => $roomInfo,
        'showtime' => $showtime // [QUAN TRỌNG] Truyền biến này để sửa lỗi Undefined variable
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
                ->whereIn('dat_ve.trang_thai', [0, 1]) // 0: Pending, 1: Success
                ->pluck('ghe.so_ghe')
                ->toArray();

            // Lấy danh sách mã ghế đang giữ (Redis/Table phụ)
            $holdingSeats = [];
            try {
                if (Schema::hasTable('suat_chieu_ghe')) {
                    $holdingSeats = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                        ->where('status', 'holding')
                        ->where('hold_expires_at', '>', Carbon::now())
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

            // --- [FIX BẮT ĐẦU] ---
            // Lấy danh sách ID ghế đã bán thật sự trong Database (Trạng thái 0: Chờ, 1: Đã mua)
            $soldSeatIds = DB::table('chi_tiet_dat_ve')
                ->join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->where('dat_ve.id_suat_chieu', $showtimeId)
                ->whereIn('dat_ve.trang_thai', [0, 1])
                ->pluck('chi_tiet_dat_ve.id_ghe')
                ->toArray();
            // --- [FIX KẾT THÚC] ---

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
                    if ($redisStatus === 'hold') {
                        $seatStatus = 'hold';
                        // Lấy thời gian hết hạn giữ nếu cần
                        $hold = $seatHoldService->getSeatHold($showtimeId, $seat->id);
                        if ($hold && isset($hold['hold_expires_at'])) {
                            $holdExpiresAt = Carbon::parse($hold['hold_expires_at'])->toIso8601String();
                        }
                    } elseif ($redisStatus === 'sold') {
                        // Fallback nếu Redis báo sold
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

            // Validate seat pattern
            $patternValidator = app(\App\Services\SeatPatternValidator::class);
            $patternValidation = $patternValidator->validatePattern($data['seats']);

            if (!$patternValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $patternValidation['message']
                ], 400);
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
            
            // Calculate total amount


            // [QUAN TRỌNG - FIX RACE CONDITION] 
            // Khóa các dòng trong bảng GHE để chặn người khác truy cập cùng lúc
            // Dữ liệu bảng Ghe luôn tồn tại nên lock được. 
            // Người đến sau sẽ phải CHỜ ở dòng này cho đến khi Transaction này xong.
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

            // Calculate total amount for Booking Header
            // Logic tính tổng tiền theo yêu cầu của bạn: Ghế đôi +200k, VIP +120k, Thường +80k

            $seatTotal = 0;
            foreach ($data['seats'] as $seat) {
                if (strpos($seat, '-') !== false) {
                    $seatTotal += 200000;
                } else {
                    $row = substr($seat, 0, 1);
                    $col = substr($seat, 1);
                    $seatObj = Ghe::where('id_phong', $showtime->id_phong)->where('so_ghe', $row . $col)->first();
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
                $expiresAt = \Carbon\Carbon::now()->addMinutes(5);
            } else if ($paymentMethod === 'online') {
                $expiresAt = \Carbon\Carbon::now()->addMinutes(15);
            }


            // Create booking WITH conflict checking in a single transaction
            // This ensures atomicity and prevents race conditions when 2 users book simultaneously
            try {
                $booking = DB::transaction(function () use ($data, $existingBooking, $promotionId, $totalAmount, $bookingStatus, $methodCode, $expiresAt, $showtimeId, $allSeatIds, $userId, $selectedCombo, $paymentMethod, $showtime) {
                    // Lock all seats first to prevent concurrent access
                    $lockedSeats = [];
                    $conflictedSeats = [];
                    
                    foreach ($allSeatIds as $seatId) {
                        // Lock the seat row to prevent race condition
                        $ghe = Ghe::where('id', $seatId)->lockForUpdate()->first();
                        if (!$ghe) {
                            $conflictedSeats[] = $seatId;
                            continue;
                        }
                        
                        // Check if seat is already booked (paid)
                        $isSold = ChiTietDatVe::whereHas('datVe', function($query) use ($showtimeId) {
                                $query->where('id_suat_chieu', $showtimeId)
                                      ->where('trang_thai', 1); // Only PAID
                            })
                            ->where('id_ghe', $seatId)
                            ->lockForUpdate()
                            ->exists();
                        
                        if ($isSold) {
                            $conflictedSeats[] = $seatId;
                            continue;
                        }
                        
                        // Check if seat is already in pending booking by another user
                        $hasPendingBooking = ChiTietDatVe::whereHas('datVe', function($query) use ($showtimeId, $userId) {
                                $query->where('id_suat_chieu', $showtimeId)
                                      ->where('trang_thai', 0) // Pending
                                      ->where('id_nguoi_dung', '!=', $userId); // Different user
                            })
                            ->where('id_ghe', $seatId)
                            ->lockForUpdate()
                            ->exists();
                        
                        if ($hasPendingBooking) {
                            $conflictedSeats[] = $seatId;
                            continue;
                        }
                        
                        // Check ShowtimeSeat if exists
                        try {
                            $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                                ->where('id_ghe', $seatId)
                                ->lockForUpdate()
                                ->first();
                            
                            if ($showtimeSeat) {
                                // Check if booked
                                if ($showtimeSeat->trang_thai === 'booked') {
                                    $conflictedSeats[] = $seatId;
                                    continue;
                                }
                                
                                // Check if held by another user
                                if ($showtimeSeat->trang_thai === 'holding' && 
                                    $showtimeSeat->id_nguoi_dung && 
                                    $showtimeSeat->id_nguoi_dung != $userId &&
                                    $showtimeSeat->thoi_gian_het_han && 
                                    $showtimeSeat->thoi_gian_het_han->isFuture()) {
                                    $conflictedSeats[] = $seatId;
                                    continue;
                                }
                            }
                        } catch (\Exception $e) {
                            // ShowtimeSeat table might not exist, continue
                        }
                        
                        $lockedSeats[] = $seatId;
                    }
                    
                    // If any seats are conflicted, throw error
                    if (!empty($conflictedSeats)) {
                        $conflictedSeatCodes = [];
                        foreach ($conflictedSeats as $seatId) {
                            $ghe = Ghe::find($seatId);
                            if ($ghe) {
                                $conflictedSeatCodes[] = $ghe->so_ghe;
                            }
                        }
                        
                        throw new \Exception('Một hoặc nhiều ghế đã được đặt: ' . implode(', ', array_unique($conflictedSeatCodes)));
                    }
                    
                    // Now create/update booking (all seats are locked and available)
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

                    // Save seat details
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
                
                return $booking;
                });
                
                // Beta standard: Store booking_hold_id if available (from selectSeats)
                // This allows us to release holds if payment fails
                if (isset($data['booking_hold_id'])) {
                    // Store booking_hold_id in session or booking metadata for later use
                    session(['booking_hold_id_' . $booking->id => $data['booking_hold_id']]);
                }
            } catch (\Exception $e) {
                // Handle conflict errors
                if (strpos($e->getMessage(), 'đã được đặt') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => $e->getMessage()
                    ], 400);
                }
                throw $e;

            }

            // [Removed duplicated post-transaction block]
        } catch (\Throwable $e) {
            // Rollback Transaction nếu có lỗi
            DB::rollBack();

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
}
