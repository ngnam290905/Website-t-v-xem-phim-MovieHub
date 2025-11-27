<?php

namespace App\Http\Controllers;

use App\Models\DatVe;
use App\Models\Combo;
use App\Models\KhuyenMai;
use App\Models\Phim;
use App\Models\SuatChieu;
use App\Models\ChiTietDatVe;
use App\Models\Ghe;
use App\Models\LoaiGhe;
use App\Models\ShowtimeSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class BookingController extends Controller
{
    private function isVipSeat($seat)
    {
        if ($seat->id_loai == 2) return true;
        if ($seat->loaiGhe && stripos($seat->loaiGhe->ten_loai, 'vip') !== false) return true;
        return false;
    }

    private function isCoupleSeat($seat)
    {
        if ($seat->id_loai == 3) return true;
        if ($seat->loaiGhe && (stripos($seat->loaiGhe->ten_loai, 'đôi') !== false || stripos($seat->loaiGhe->ten_loai, 'couple') !== false)) {
            return true;
        }
        return false;
    }

    // ==================================================================
    // 1. DANH SÁCH VÉ ĐÃ ĐẶT
    // ==================================================================
    public function index()
    {
        $bookings = DatVe::with([
                'suatChieu.phim',
                'suatChieu.phongChieu',
                'chiTietDatVe.ghe',
                'khuyenMai',
                'chiTietCombo.combo',
                'thanhToan'
            ])
            ->where('id_nguoi_dung', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.bookings', compact('bookings'));
    }

    // ==================================================================
    // 2. XEM CHI TIẾT VÉ + QR CODE
    // ==================================================================
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
        $movie    = optional($showtime->phim);
        $room     = optional($showtime->phongChieu);
        $seatList = $booking->chiTietDatVe->map(fn($ct) => optional($ct->ghe)->so_ghe)->filter()->values()->all();

        $comboItems     = $booking->chiTietCombo ?? collect();
        $promo          = $booking->khuyenMai;
        $comboTotal     = $comboItems->sum(fn($i) => (float)$i->gia_ap_dung * max(1, (int)$i->so_luong));
        $seatTotal      = (float) $booking->chiTietDatVe->sum('gia');
        $subtotal       = $seatTotal + $comboTotal;
        $promoDiscount  = 0;

        if ($promo) {
            $type = strtolower($promo->loai_giam);
            $val  = (float)$promo->gia_tri_giam;
            if ($subtotal >= 0) {
                if ($type === 'phantram') {
                    $promoDiscount = round($subtotal * ($val / 100));
                } else {
                    $promoDiscount = ($val >= 1000) ? $val : $val * 1000;
                }
            }
        }

        $computedTotal = max(0, $subtotal - $promoDiscount);
        $pt = $booking->phuong_thuc_thanh_toan ?? optional($booking->thanhToan)->phuong_thuc;
        $pt = !$pt ? null : ($pt === 'online' ? 1 : ($pt === 'offline' ? 2 : null));

        return view('user.ticket-detail', compact(
            'booking','showtime','movie','room','seatList',
            'comboItems','promo','promoDiscount','computedTotal','pt'
        ));
    }

    // ==================================================================
    // 3. TRANG ĐẶT VÉ (CHỌN PHIM → CHỌN SUẤT → CHỌN GHẾ)
    // ==================================================================
    public function create($id = null)
    {
        $movie = null;
        if ($id) {
            $movie = Phim::find($id);
        }

        if (!$movie) {
            $movie = Phim::first() ?? (object)[
                'id' => 1,
                'ten_phim' => 'Demo Movie',
                'thoi_luong' => 120,
                'poster' => 'images/default-poster.jpg'
            ];
        }

        $roomInfo = null;
        $seats = collect();
        $vipSeats = $vipRows = $coupleSeats = [];

        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movie->id)
            ->where('thoi_gian_bat_dau', '>=', now())
            ->where('trang_thai', 1)
            ->orderBy('thoi_gian_bat_dau')
            ->get();

        if ($showtimes->isEmpty()) {
            $showtimes = SuatChieu::with('phongChieu')
                ->where('id_phim', $movie->id)
                ->where('trang_thai', 1)
                ->orderBy('thoi_gian_bat_dau', 'desc')
                ->limit(5)
                ->get();
        }

        $showtimes = $showtimes->map(function ($suat) {
            return [
                'id'    => $suat->id,
                'label' => date('H:i - d/m/Y', strtotime($suat->thoi_gian_bat_dau)) . ' - ' . ($suat->phongChieu->ten_phong ?? 'Phòng 1'),
                'time'  => date('H:i', strtotime($suat->thoi_gian_bat_dau)),
                'date'  => date('d/m/Y', strtotime($suat->thoi_gian_bat_dau)),
                'room'  => $suat->phongChieu->ten_phong ?? 'Phòng 1'
            ];
        });

        if ($showtimes->isNotEmpty()) {
            $firstShowtime = $showtimes->first();
            $suatChieu = SuatChieu::with('phongChieu')->find($firstShowtime['id']);
            if ($suatChieu && $suatChieu->phongChieu) {
                $roomInfo = $suatChieu->phongChieu;
                $seats = Ghe::where('id_phong', $suatChieu->id_phong)
                    ->with('loaiGhe')
                    ->get();

                $vipSeatData = $seats->filter(fn($seat) => $this->isVipSeat($seat));
                $vipSeats    = $vipSeatData->pluck('so_ghe')->toArray();
                $vipRows     = $vipSeatData->map(fn($seat) => substr($seat->so_ghe, 0, 1))->unique()->values()->toArray();

                $coupleSeatData = $seats->filter(fn($seat) => $this->isCoupleSeat($seat));
                $coupleSeatGroups = $coupleSeatData->groupBy(fn($seat) => substr($seat->so_ghe, 0, 1));

                foreach ($coupleSeatGroups as $row => $seatsInRow) {
                    $nums = $seatsInRow->pluck('so_ghe')->toArray();
                    sort($nums);
                    for ($i = 0; $i < count($nums) - 1; $i++) {
                        $n1 = intval(substr($nums[$i], 1));
                        $n2 = intval(substr($nums[$i+1], 1));
                        if ($n2 == $n1 + 1) {
                            $coupleSeats[] = $row . $n1 . '-' . $n2;
                            $i++;
                        }
                    }
                }
            }
        }

        if ($seats->isEmpty() || !$roomInfo) {
            $roomInfo = (object)['so_cot' => 15, 'so_hang' => 10];
        }

        return view('booking', compact('movie','showtimes','coupleSeats','vipSeats','vipRows','roomInfo'));
    }

    // ==================================================================
    // 4. LẤY GHẾ ĐÃ ĐẶT / ĐANG GIỮ
    // ==================================================================
    public function getBookedSeats($showtimeId)
    {
        try {
            $showtime = SuatChieu::find($showtimeId);
            if (!$showtime) return response()->json(['seats' => []]);

            ShowtimeSeat::releaseExpiredSeats($showtimeId);

            $bookedSeats = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                ->where('status', 'booked')
                ->with('ghe')
                ->get()
                ->map(fn($ss) => $ss->ghe->so_ghe ?? null)
                ->filter();

            $oldBookedSeats = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->join('ghe', 'chi_tiet_dat_ve.id_ghe', '=', 'ghe.id')
                ->where('dat_ve.id_suat_chieu', $showtimeId)
                ->whereIn('dat_ve.trang_thai', [0,1])
                ->pluck('ghe.so_ghe');

            $allBooked = $bookedSeats->merge($oldBookedSeats)->unique()->values();

            $holdingSeats = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                ->where('status', 'holding')
                ->where('hold_expires_at', '>', now())
                ->with('ghe')
                ->get()
                ->map(fn($ss) => $ss->ghe->so_ghe ?? null)
                ->filter();

            return response()->json(['seats' => $allBooked, 'holding' => $holdingSeats]);
        } catch (\Exception $e) {
            Log::error('Error getBookedSeats: '.$e->getMessage());
            return response()->json(['seats' => [], 'holding' => []]);
        }
    }

    // ==================================================================
    // 5. LẤY TOÀN BỘ GHẾ + GIÁ ĐỘNG (CHỖ QUAN TRỌNG NHẤT)
    // ==================================================================
    public function getShowtimeSeats($showtimeId)
    {
        try {
            $showtime = SuatChieu::findOrFail($showtimeId);

            try { ShowtimeSeat::releaseExpiredSeats($showtimeId); } catch (\Exception $e) {}

            $showtimeSeats = collect();
            try {
                $showtimeSeats = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                    ->with('ghe')
                    ->get()
                    ->keyBy(fn($ss) => $ss->ghe->so_ghe ?? null);
            } catch (\Exception $e) {}

            $allSeats = Ghe::where('id_phong', $showtime->id_phong)
                ->with('loaiGhe')
                ->get();

            $seats = $allSeats->mapWithKeys(function ($seat) use ($showtimeSeats, $showtimeId) {
                $ss = $showtimeSeats->get($seat->so_ghe);
                $available = $ss ? $ss->isAvailable() : ($seat->trang_thai ?? 1) == 1;

                $giaInfo = $available
                    ? tinhGiaVe($showtimeId, $seat->id_loai_ghe ?? 1)
                    : ['gia_cuoi_cung' => 0];

                return [$seat->so_ghe => [
                    'id'              => $seat->id,
                    'code'            => $seat->so_ghe,
                    'type'            => $seat->loaiGhe->ten_loai ?? 'Thường',
                    'available'       => $available,
                    'status'          => $ss->status ?? 'available',
                    'price'           => $giaInfo['gia_cuoi_cung'],
                    'hold_expires_at' => $ss && $ss->isHolding() ? $ss->hold_expires_at->toIso8601String() : null,
                ]];
            });

            return response()->json(['seats' => $seats->toArray()]);
        } catch (\Exception $e) {
            Log::error('Error getShowtimeSeats: '.$e->getMessage());
            return response()->json(['seats' => []]);
        }
    }

    // ==================================================================
    // 6. API LẤY GIÁ GHẾ THEO LOẠI (dùng khi hover)
    // ==================================================================
    public function getSeatPrice(Request $request)
    {
        $request->validate([
            'suat_chieu_id' => 'required|exists:suat_chieu,id',
            'loai_ghe_id'   => 'required|exists:loai_ghe,id',
        ]);

        $gia = tinhGiaVe($request->suat_chieu_id, $request->loai_ghe_id);

        return response()->json([
            'success' => true,
            'gia'     => number_format($gia['gia_cuoi_cung']) . 'đ',
            'chi_tiet'=> $gia
        ]);
    }

    // ==================================================================
    // 7. GIỮ GHẾ TẠM 10 PHÚT (bảng tam_giu_ghe)
    // ==================================================================
    public function selectSeatsTemp(Request $request, $suatChieuId)
    {
        $request->validate([
            'ghe_id' => 'required|exists:ghe,id',
            'action' => 'required|in:add,remove'
        ]);

        $gheId     = $request->ghe_id;
        $userId    = auth()->id() ?? 0;
        $sessionId = session()->getId();
        $now       = Carbon::now();

        $exists = DB::table('tam_giu_ghe')
            ->where('id_ghe', $gheId)
            ->where('id_suat_chieu', $suatChieuId)
            ->where('thoi_gian_het_han', '>', $now)
            ->first();

        if ($request->action === 'add') {
            if ($exists && $exists->session_id !== $sessionId && $exists->id_nguoi_dung != $userId) {
                return response()->json(['success'=>false,'message'=>'Ghế đã được người khác chọn!'], 409);
            }

            DB::table('tam_giu_ghe')->updateOrInsert(
                ['id_ghe' => $gheId, 'id_suat_chieu' => $suatChieuId],
                [
                    'session_id'        => $sessionId,
                    'id_nguoi_dung'     => $userId > 0 ? $userId : null,
                    'thoi_gian_het_han' => $now->clone()->addMinutes(10),
                    'updated_at'        => $now,
                ]
            );
        } else {
            DB::table('tam_giu_ghe')
                ->where('id_ghe', $gheId)
                ->where('id_suat_chieu', $suatChieuId)
                ->where(fn($q) => $q->where('session_id', $sessionId)->orWhere('id_nguoi_dung', $userId))
                ->delete();
        }

        return response()->json(['success' => true]);
    }
    public function store(Request $request)
    {
        try {
            // Check if user is admin (prevent admin from booking tickets)
            $user = Auth::user();
            if ($user && $user->id_vai_tro == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin không được phép đặt vé trực tiếp!'
                ]);
            }
            
            $data = json_decode($request->getContent(), true);
            
            // Validate required fields
            if (!isset($data['seats']) || empty($data['seats'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ghế!'
                ]);
            }
            
            // Validate showtime exists
            if (!isset($data['showtime']) || !$data['showtime']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn suất chiếu!'
                ]);
            }
            
            $showtime = SuatChieu::find($data['showtime']);
            if (!$showtime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Suất chiếu không tồn tại!'
                ]);
            }
            
            // Release expired seats first
            ShowtimeSeat::releaseExpiredSeats($data['showtime']);

            // Check if seats are already booked or holding
            $unavailableSeats = [];
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
                        for ($c = $start; $c <= $end; $c++) { $pairs[] = $rowLetter.$c; }
                    }
                } elseif (strpos($seat, ',') !== false) {
                    // Format: R11,R12
                    $parts = array_filter(array_map('trim', explode(',', $seat)));
                    foreach ($parts as $code) { $pairs[] = strtoupper($code); }
                } else {
                    // Single seat like R11
                    $pairs[] = strtoupper($seat);
                }

                foreach ($pairs as $code) {
                    $ghe = Ghe::where('id_phong', $showtime->id_phong)
                        ->where('so_ghe', $code)
                        ->first();
                    
                    if ($ghe) {
                        // Check showtime_seats table
                        $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $data['showtime'])
                            ->where('id_ghe', $ghe->id)
                            ->first();
                        
                        if ($showtimeSeat) {
                            // Allow if seat is holding by current user (from selectSeats)
                            $isHoldingByCurrentUser = false;
                            if ($showtimeSeat->isHolding() && isset($data['booking_id']) && $data['booking_id']) {
                                // Check if this holding is from our booking
                                $holdingBooking = DatVe::where('id', $data['booking_id'])
                                    ->where('id_nguoi_dung', Auth::id())
                                    ->where('id_suat_chieu', $data['showtime'])
                                    ->where('trang_thai', 0)
                                    ->first();
                                if ($holdingBooking) {
                                    $isHoldingByCurrentUser = true;
                                }
                            }
                            
                            if ($showtimeSeat->isBooked() || ($showtimeSeat->isHolding() && !$isHoldingByCurrentUser)) {
                                $unavailableSeats[] = $code;
                            }
                        }
                        
                        // Also check old booking system for backward compatibility
                        $exists = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                            ->where('dat_ve.id_suat_chieu', $data['showtime'])
                            ->where('chi_tiet_dat_ve.id_ghe', $ghe->id)
                            ->whereIn('dat_ve.trang_thai', [0, 1])
                            ->exists();
                        
                        if ($exists) {
                            $unavailableSeats[] = $code;
                        }
                    }
                }
            }
            
            if (!empty($unavailableSeats)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Một hoặc nhiều ghế đã được đặt hoặc đang được giữ: ' . implode(', ', array_unique($unavailableSeats))
                ]);
            }
            
            // Calculate total amount
            $seatTotal = 0;
            
            // Get VIP rows from database for the showtime's room
            $vipRows = [];
            $showtime = SuatChieu::find($data['showtime']);
            if ($showtime) {
                $vipSeats = Ghe::where('id_phong', $showtime->id_phong)
                    ->with('loaiGhe')
                    ->get()
                    ->filter(function($seat) {
                        return $this->isVipSeat($seat);
                    });
                    
                $vipRows = $vipSeats->map(function($seat) {
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
            if (!$existingBooking) {
                $existingBooking = DatVe::where('id_nguoi_dung', Auth::id())
                    ->where('id_suat_chieu', $data['showtime'])
                    ->where('trang_thai', 0) // pending
                    ->where('tong_tien', 0) // from selectSeats
                    ->orderBy('created_at', 'desc')
                    ->first();
            }
            
            // Create or update booking
            $paymentMethod = $data['payment_method'] ?? 'offline';
            $bookingStatus = ($paymentMethod === 'online') ? 1 : 0; // 1 = đã thanh toán, 0 = chờ thanh toán tại quầy
            $methodCode = ($paymentMethod === 'online') ? 1 : 2; // 1=online, 2=tai quay
            
            if ($existingBooking) {
                // Delete old seat details if any
                ChiTietDatVe::where('id_dat_ve', $existingBooking->id)->delete();
                
                // Update existing booking
                $existingBooking->update([
                    'id_khuyen_mai' => $promotionId,
                    'tong_tien' => $totalAmount,
                    'trang_thai' => $bookingStatus,
                    'phuong_thuc_thanh_toan' => $methodCode,
                ]);
                $booking = $existingBooking;
            } else {
                // Create new booking
                $booking = DatVe::create([
                    'id_nguoi_dung'   => Auth::id(),
                    'id_suat_chieu'   => $data['showtime'] ?? null,
                    'id_khuyen_mai'   => $promotionId,
                    'tong_tien'       => $totalAmount,
                    'trang_thai'      => $bookingStatus,
                    'phuong_thuc_thanh_toan' => $methodCode,
                ]);
            }
            
            // Release expired seats first
            ShowtimeSeat::releaseExpiredSeats($data['showtime']);

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
                        for ($c = $col1; $c <= $col2; $c++) { $codesToSave[] = $row.$c; }
                    }
                } elseif (strpos($seatCode, ',') !== false) {
                    $parts = array_filter(array_map('trim', explode(',', $seatCode)));
                    foreach ($parts as $code) { $codesToSave[] = strtoupper($code); }
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
                        
                        // Update showtime_seats: convert holding to booked, or create new booked entry
                        $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $data['showtime'])
                            ->where('id_ghe', $seat->id)
                            ->first();
                        
                        if ($showtimeSeat) {
                            // If holding, convert to booked
                            if ($showtimeSeat->isHolding() || $showtimeSeat->status === 'holding') {
                                $showtimeSeat->status = 'booked';
                                $showtimeSeat->hold_expires_at = null;
                                $showtimeSeat->save();
                            } elseif ($showtimeSeat->status !== 'booked') {
                                // If available or other status, mark as booked
                                $showtimeSeat->status = 'booked';
                                $showtimeSeat->hold_expires_at = null;
                                $showtimeSeat->save();
                            }
                        } else {
                            // Create new showtime_seat entry as booked
                            ShowtimeSeat::create([
                                'id_suat_chieu' => $data['showtime'],
                                'id_ghe' => $seat->id,
                                'status' => 'booked',
                                'hold_expires_at' => null,
                            ]);
                        }
                        
                        // Lock seat (legacy behavior)
                        $seat->trang_thai = 0;
                        $seat->save();
                    }
                }
            }
            
            // Save combo detail if chosen
            if ($selectedCombo) {
                \App\Models\ChiTietCombo::create([
                    'id_dat_ve'   => $booking->id,
                    'id_combo'    => $selectedCombo->id,
                    'so_luong'    => 1,
                    'gia_ap_dung' => (float)$selectedCombo->gia,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Đặt vé thành công!',
                'booking_id' => $booking->id
            ]);
            
        } catch (\Exception $e) {
            Log::error('Booking error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại!'
            ]);
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

            $seats = $booking->chiTietDatVe->map(function($ct){ return optional($ct->ghe)->so_ghe; })->filter()->values();

            $method = $booking->phuong_thuc_thanh_toan;
            if (!$method) {
                $map = optional($booking->thanhToan)->phuong_thuc ?? null;
                $method = $map === 'online' ? 1 : ($map === 'offline' ? 2 : null);
            }

            $payloadUrl = url('/api/ticket/'.$booking->id);

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
                    'qr' => [
                        'data' => $payloadUrl,
                        'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data='.urlencode($payloadUrl)
                    ],
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Get ticket error: '.$e->getMessage());
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

            // Release expired seats first (lazy check) - skip if table doesn't exist
            try {
                ShowtimeSeat::releaseExpiredSeats($showtimeId);
            } catch (\Exception $e) {
                \Log::warning('Could not release expired seats: ' . $e->getMessage());
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

            // Check if seats are available
            $unavailableSeats = [];
            foreach ($seatIds as $index => $seatId) {
                $showtimeSeat = ShowtimeSeat::firstOrNew([
                    'id_suat_chieu' => $showtimeId,
                    'id_ghe' => $seatId,
                ]);

                if (!$showtimeSeat->exists) {
                    $showtimeSeat->status = 'available';
                }

                if (!$showtimeSeat->isAvailable()) {
                    $code = $seatCodes[$index];
                    $unavailableSeats[] = $code;
                }
            }

            if (!empty($unavailableSeats)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Một hoặc nhiều ghế đã được đặt hoặc đang được giữ: ' . implode(', ', $unavailableSeats)
                ], 400);
            }

            // Hold seats for 5 minutes
            $holdExpiresAt = Carbon::now()->addMinutes(5);
            $bookingId = null;

            DB::beginTransaction();
            try {
                // Create temporary booking with pending status
                $bookingData = [
                    'id_nguoi_dung' => Auth::id(),
                    'id_suat_chieu' => $showtimeId,
                    'tong_tien' => 0,
                    'trang_thai' => 0, // pending
                ];
                
                // Only add phuong_thuc_thanh_toan if column exists
                if (Schema::hasColumn('dat_ve', 'phuong_thuc_thanh_toan')) {
                    $bookingData['phuong_thuc_thanh_toan'] = null;
                }
                
                $booking = DatVe::create($bookingData);
                $bookingId = $booking->id;

                // Set seats to holding status
                foreach ($seatIds as $seatId) {
                    ShowtimeSeat::updateOrCreate(
                        [
                            'id_suat_chieu' => $showtimeId,
                            'id_ghe' => $seatId,
                        ],
                        [
                            'status' => 'holding',
                            'hold_expires_at' => $holdExpiresAt,
                        ]
                    );
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Ghế đã được giữ chỗ trong 5 phút',
                    'booking_id' => $bookingId,
                    'hold_expires_at' => $holdExpiresAt->toIso8601String(),
                    'expires_in_seconds' => 300,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error holding seats: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi giữ ghế. Vui lòng thử lại!'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Select seats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại!'
            ], 500);
        }
    }

    /**
     * Validate seat selection rules (Theater National Standards):
     * 1. All seats must be in the same row
     * 2. Seats must be consecutive (except single seat is allowed)
     * 3. No isolated single seat should be created (orphan seat rule)
     * 4. Odd number of seats is allowed
     */
    private function validateSeatSelection($showtime, $seatCodes)
    {
        if (empty($seatCodes)) {
            return ['valid' => false, 'message' => 'Vui lòng chọn ít nhất một ghế!'];
        }

        // Parse seat codes to extract row and number
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

        // Rule 1: All seats must be in the same row
        $rows = array_unique(array_column($seats, 'row'));
        if (count($rows) > 1) {
            return ['valid' => false, 'message' => 'Tất cả ghế phải nằm trên cùng một hàng!'];
        }

        // Rule 2: Seats must be consecutive (single seat is allowed)
        $numbers = array_column($seats, 'number');
        sort($numbers);
        if (count($numbers) > 1) {
            for ($i = 1; $i < count($numbers); $i++) {
                if ($numbers[$i] - $numbers[$i - 1] !== 1) {
                    return ['valid' => false, 'message' => 'Các ghế phải liền nhau! Ví dụ: A5-A6-A7 (không được A5-A7).'];
                }
            }
        }

        // Rule 3: Check for isolated seats
        $row = $rows[0];
        $minNumber = min($numbers);
        $maxNumber = max($numbers);

        // Get all seats in this row for this showtime
        $allSeatsInRow = Ghe::where('id_phong', $showtime->id_phong)
            ->where('so_ghe', 'like', $row . '%')
            ->get()
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

        if (empty($allSeatsInRow)) {
            return ['valid' => false, 'message' => 'Không tìm thấy ghế trong hàng này!'];
        }

        // Get booked/holding seats in this row for this showtime
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
                    // Table may not exist, skip
                }
            }
        }

        // Rule 3: Check for isolated single seats (orphan seat rule)
        // Only block if creating exactly 1 isolated seat (2+ seats gap is OK)
        $selectedNumbers = $numbers;
        $allBookedAfter = array_merge($bookedNumbers, $selectedNumbers);
        $allBookedAfter = array_unique($allBookedAfter);
        sort($allBookedAfter);

        // Check left side (before min selected)
        if ($minNumber > $allSeatsInRow[0]) {
            $leftBooked = array_filter($allBookedAfter, function ($n) use ($minNumber) {
                return $n < $minNumber;
            });
            if (count($leftBooked) > 0) {
                $lastLeftBooked = max($leftBooked);
                $gap = $minNumber - $lastLeftBooked - 1;
                // Only block if gap is exactly 1 (single isolated seat)
                if ($gap === 1) {
                    return ['valid' => false, 'message' => 'Không thể chọn ghế này vì sẽ để lại 1 ghế trống lẻ. Vui lòng chọn thêm ghế liền kề hoặc chọn cụm ghế khác.'];
                }
            }
        }

        // Check right side (after max selected)
        $maxRowNumber = max($allSeatsInRow);
        if ($maxNumber < $maxRowNumber) {
            $rightBooked = array_filter($allBookedAfter, function ($n) use ($maxNumber) {
                return $n > $maxNumber;
            });
            if (count($rightBooked) > 0) {
                $firstRightBooked = min($rightBooked);
                $gap = $firstRightBooked - $maxNumber - 1;
                // Only block if gap is exactly 1 (single isolated seat)
                if ($gap === 1) {
                    return ['valid' => false, 'message' => 'Không thể chọn ghế này vì sẽ để lại 1 ghế trống lẻ. Vui lòng chọn thêm ghế liền kề hoặc chọn cụm ghế khác.'];
                }
            }
        }

        // Check middle gaps (between booked seats)
        for ($i = 0; $i < count($allBookedAfter) - 1; $i++) {
            $gap = $allBookedAfter[$i + 1] - $allBookedAfter[$i] - 1;
            // Only block if gap is exactly 1 (single isolated seat)
            if ($gap === 1) {
                return ['valid' => false, 'message' => 'Không thể chọn ghế này vì sẽ để lại 1 ghế trống lẻ giữa các ghế đã đặt. Vui lòng chọn thêm ghế liền kề hoặc chọn cụm ghế khác.'];
            }
        }

        return ['valid' => true, 'message' => 'OK'];
    }
}
