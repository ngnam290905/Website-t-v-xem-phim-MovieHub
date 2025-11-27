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

class BookingController extends Controller
{
    //Helper: Check VIP
    private function isVipSeat($seat)
    {
        if ($seat->id_loai == 2) return true;
        if ($seat->loaiGhe && stripos($seat->loaiGhe->ten_loai, 'vip') !== false) return true;
        return false;
    }

    //Helper: Check Couple
    private function isCoupleSeat($seat)
    {
        if ($seat->id_loai == 3) return true;
        if ($seat->loaiGhe && (stripos($seat->loaiGhe->ten_loai, 'đôi') !== false || stripos($seat->loaiGhe->ten_loai, 'doi') !== false)) return true;
        return false;
    }

    public function index()
    {
        $bookings = DatVe::with(['suatChieu.phim', 'suatChieu.phongChieu', 'chiTietDatVe.ghe', 'khuyenMai', 'chiTietCombo.combo', 'thanhToan'])
            ->where('id_nguoi_dung', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('user.bookings', compact('bookings'));
    }

    public function show($id)
    {
        $booking = DatVe::with(['suatChieu.phim', 'suatChieu.phongChieu', 'chiTietDatVe.ghe', 'khuyenMai', 'chiTietCombo.combo', 'thanhToan', 'nguoiDung'])
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
                if ($type === 'phantram') $promoDiscount = round($subtotal * ($val / 100));
                else $promoDiscount = ($val >= 1000) ? $val : $val * 1000;
            }
        }

        $computedTotal = max(0, $subtotal - $promoDiscount);

        $pt = $booking->phuong_thuc_thanh_toan;
        if (!$pt) {
            $map = optional($booking->thanhToan)->phuong_thuc ?? null;
            $pt = $map === 'VNPAY' ? 1 : ($map === 'Tiền mặt' ? 2 : null);
        }

        return view('user.ticket-detail', compact('booking', 'showtime', 'movie', 'room', 'seatList', 'comboItems', 'promo', 'promoDiscount', 'computedTotal', 'pt'));
    }

    public function create($id = null)
    {
        $movie = $id ? Phim::find($id) : Phim::first();
        if (!$movie) {
            $movie = (object)['id' => 1, 'ten_phim' => 'Demo Movie', 'thoi_luong' => 120, 'poster' => 'images/default-poster.jpg'];
        }

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
        
        // If still no showtimes, get the most recent active showtimes (for testing)
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
                'id' => $suat->id,
                'label' => date('H:i - d/m/Y', strtotime($suat->thoi_gian_bat_dau)) . ' - ' . ($suat->phongChieu->ten_phong ?? 'Phòng 1'),
                'time' => date('H:i', strtotime($suat->thoi_gian_bat_dau)),
                'date' => date('d/m/Y', strtotime($suat->thoi_gian_bat_dau)),
                'room' => $suat->phongChieu->ten_phong ?? 'Phòng 1'
            ];
        });
        if ($showtimes->isNotEmpty()) {
            $firstShowtime = $showtimes->first();
            $suatChieu = SuatChieu::with('phongChieu')->find($firstShowtime['id']);
            if ($suatChieu && $suatChieu->phongChieu) {
                $roomInfo = $suatChieu->phongChieu;
                $seats = Ghe::where('id_phong', $suatChieu->id_phong)->with('loaiGhe')->get();

                $vipSeatData = $seats->filter(fn($s) => $this->isVipSeat($s));
                $vipSeats = $vipSeatData->pluck('so_ghe')->toArray();
                $vipRows = $vipSeatData->map(fn($s) => substr($s->so_ghe, 0, 1))->unique()->values()->toArray();

                $coupleSeatData = $seats->filter(fn($s) => $this->isCoupleSeat($s));
                $coupleSeatGroups = $coupleSeatData->groupBy(fn($s) => substr($s->so_ghe, 0, 1));

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
        }

        if ($seats->isEmpty() || !$roomInfo) {
            $roomInfo = (object) ['so_cot' => 15, 'so_hang' => 10];
        }

        return view('booking', compact('movie', 'showtimes', 'coupleSeats', 'vipSeats', 'vipRows', 'roomInfo'));
    }

    public function getBookedSeats($showtimeId)
    {
        try {
            $showtime = SuatChieu::find($showtimeId);
<<<<<<< HEAD
            if (!$showtime) {
                return response()->json(['seats' => []]);
            }
            
            // Release expired seats (lazy check)
            ShowtimeSeat::releaseExpiredSeats($showtimeId);
            
            // Get booked seats from showtime_seats table
            $bookedSeats = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                ->where('status', 'booked')
                ->with('ghe')
                ->get()
                ->map(function ($showtimeSeat) {
                    return $showtimeSeat->ghe->so_ghe ?? null;
                })
                ->filter();
            
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
            
            // Get holding seats (for display purposes)
            $holdingSeats = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                ->where('status', 'holding')
                ->where('hold_expires_at', '>', Carbon::now())
                ->with('ghe')
                ->get()
                ->map(function ($showtimeSeat) {
                    return $showtimeSeat->ghe->so_ghe ?? null;
                })
                ->filter();
            
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
            $showtime = SuatChieu::find($showtimeId);
<<<<<<< HEAD
            if (!$showtime) {
                \Log::warning('Showtime not found: ' . $showtimeId);
                return response()->json(['seats' => []]);
            }
            
            \Log::info('Showtime found, room id: ' . $showtime->id_phong);
            
            // Release expired seats (lazy check) - skip if table doesn't exist
            try {
                ShowtimeSeat::releaseExpiredSeats($showtimeId);
            } catch (\Exception $e) {
                \Log::warning('Could not release expired seats (table may not exist): ' . $e->getMessage());
            }
            
            // Get seat statuses from showtime_seats table - skip if table doesn't exist
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
            
            // Get all seats for this showtime's room
            $allSeats = Ghe::where('id_phong', $showtime->id_phong)
                ->with('loaiGhe')
                ->get();
            
            \Log::info('Total seats found in room: ' . $allSeats->count());
            
            $seats = $allSeats->mapWithKeys(function ($seat) use ($showtimeSeats) {
                    $typeText = strtolower($seat->loaiGhe->ten_loai ?? 'thường');
                    
                    // Check status from showtime_seats table
                    $showtimeSeat = $showtimeSeats->get($seat->so_ghe);
                    $seatStatus = 'available';
                    $isAvailable = true;
                    
                    if ($showtimeSeat) {
                        if ($showtimeSeat->isBooked()) {
                            $seatStatus = 'booked';
                            $isAvailable = false;
                        } elseif ($showtimeSeat->isHolding()) {
                            $seatStatus = 'holding';
                            $isAvailable = false;
                        } else {
                            $seatStatus = $showtimeSeat->status;
                            $isAvailable = $showtimeSeat->isAvailable();
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
                        \Log::info('VIP seat found: ' . $seat->so_ghe . ', type: ' . ($seat->loaiGhe->ten_loai ?? 'N/A'));
                    }
                    
                    // Determine price based on seat type
                    if ($isAvailable) {
                        if ($this->isVipSeat($seat)) $price = 120000;
                        elseif ($this->isCoupleSeat($seat)) $price = 200000;
                        else $price = 80000;
                    } else {
                        $price = 0;
                    }

                    return [$seat->so_ghe => [
                        'id' => $seat->id,
                        'code' => $seat->so_ghe,
                        'type' => $seat->loaiGhe->ten_loai ?? 'Thường',
                        'available' => $isAvailable,
                        'status' => $seatStatus,
                        'price' => $price,
                        'hold_expires_at' => $showtimeSeat && $showtimeSeat->isHolding() ? $showtimeSeat->hold_expires_at->toIso8601String() : null,
                    ]];
                });
            
            \Log::info('Seats data prepared, count: ' . $seats->count());
            
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
            return response()->json(['seats' => []]);
        }
    }

    // =========================================================================
    // === HÀM STORE (QUAN TRỌNG: ĐÃ SỬA LỖI LOGIC) ===
    // =========================================================================
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user && $user->id_vai_tro == 1) {
                return response()->json(['success' => false, 'message' => 'Admin không được phép đặt vé!']);
            }

            $data = json_decode($request->getContent(), true);

            if (empty($data['seats'])) return response()->json(['success' => false, 'message' => 'Vui lòng chọn ghế!']);
            if (empty($data['showtime'])) return response()->json(['success' => false, 'message' => 'Vui lòng chọn suất chiếu!']);

            $showtime = SuatChieu::find($data['showtime']);
            if (!$showtime) return response()->json(['success' => false, 'message' => 'Suất chiếu không tồn tại!']);

            // Release expired seats first
            try {
                ShowtimeSeat::releaseExpiredSeats($data['showtime']);
            } catch (\Exception $e) {
                \Log::warning('Could not release expired seats: ' . $e->getMessage());
            }

            // Sử dụng Transaction để đảm bảo toàn vẹn dữ liệu
            return DB::transaction(function () use ($data, $showtime, $user) {

                // 1. Parse danh sách ghế từ request (A1, A2...)
                $seatsToBook = [];
                foreach ($data['seats'] as $seatCode) {
                    $seatCode = trim($seatCode);
                    if ($seatCode === '') continue;

                    if (strpos($seatCode, '-') !== false) { // Ghế đôi: A1-A2
                        if (preg_match('/^([A-Z])(?:\s*)(\d+)-(\d+)$/i', $seatCode, $matches)) {
                            $row = strtoupper($matches[1]);
                            for ($c = (int)$matches[2]; $c <= (int)$matches[3]; $c++) $seatsToBook[] = $row . $c;
                        }
                    } elseif (strpos($seatCode, ',') !== false) { // Ghế lẻ ghép: A1,A2
                        foreach (explode(',', $seatCode) as $part) $seatsToBook[] = strtoupper(trim($part));
                    } else { // Ghế đơn: A1
                        $seatsToBook[] = strtoupper($seatCode);
                    }
                }

                // 2. Kiểm tra ghế trùng & Tính tiền
                $seatTotal = 0;
                $seatIds = []; // Lưu lại ID để insert sau

                foreach ($seatsToBook as $code) {
                    // Tìm ghế trong DB
                    $seat = Ghe::where('id_phong', $showtime->id_phong)
                        ->where('so_ghe', $code)
                        ->with('loaiGhe')
                        ->first();

                    if (!$seat) throw new \Exception("Ghế $code không tồn tại trong phòng này.");

                    // Kiểm tra ghế đã bị đặt chưa (trừ vé đã hủy)
                    $isBooked = ChiTietDatVe::whereHas('datVe', function ($q) use ($showtime) {
                        $q->where('id_suat_chieu', $showtime->id)->whereIn('trang_thai', [0, 1]);
                    })->where('id_ghe', $seat->id)->exists();

                    if ($isBooked) throw new \Exception("Ghế $code đã có người đặt.");

                    // Tính giá
                    $price = 80000;
                    if ($this->isCoupleSeat($seat)) $price = 100000; // Giá 1 ghế trong cặp (Tổng cặp = 200k)
                    elseif ($this->isVipSeat($seat)) $price = 120000;

                    $seatTotal += $price;
                    $seatIds[] = ['seat' => $seat, 'price' => $price];
                }

                // 3. Tính Combo & Khuyến mãi
                $comboTotal = 0;
                $selectedCombo = null;
                if (isset($data['combo']) && $data['combo']) {
                    $selectedCombo = Combo::find($data['combo']['id'] ?? null);
                    if ($selectedCombo) $comboTotal = (float)$selectedCombo->gia;
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
                            $min = (float)preg_replace('/\D+/', '', (string)$promotion->dieu_kien);
                        }
                        if ($subtotal >= $min) {
                            if ($promotion->loai_giam === 'phantram') {
                                $discount = round($subtotal * ((float)$promotion->gia_tri_giam / 100));
                            } else {
                                $val = (float)$promotion->gia_tri_giam;
                                $discount = ($val >= 1000) ? $val : $val * 1000;
                            }
                            if ($discount > $subtotal) $discount = $subtotal;
                        }
                    }
                }

                $totalAmount = max(0, $seatTotal + $comboTotal - $discount);

                // 4. Tạo Vé (QUAN TRỌNG: trang_thai = 0)
                $paymentMethod = $data['payment_method'] ?? 'offline';

                $booking = DatVe::create([
                    'id_nguoi_dung'   => Auth::id(),
                    'id_suat_chieu'   => $data['showtime'],
                    'id_khuyen_mai'   => $promotionId,
                    'tong_tien'       => $totalAmount,
                    'trang_thai'      => 0, // <--- LUÔN LÀ 0 KHI MỚI TẠO
                    'thoi_gian'       => now()
                ]);

                // 5. Lưu Chi tiết ghế
                foreach ($seatIds as $item) {
                    ChiTietDatVe::create([
                        'id_dat_ve' => $booking->id,
                        'id_ghe'    => $item['seat']->id,
                        'gia'       => $item['price']
                    ]);
                    // Cập nhật trạng thái ghế
                    $item['seat']->update(['trang_thai' => 0]);
                    
                    // Update showtime_seats: convert holding to booked, or create new booked entry
                    try {
                        $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $showtime->id)
                            ->where('id_ghe', $item['seat']->id)
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
                                'id_suat_chieu' => $showtime->id,
                                'id_ghe' => $item['seat']->id,
                                'status' => 'booked',
                                'hold_expires_at' => null,
                            ]);
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Could not update showtime_seats: ' . $e->getMessage());
                    }
                }

                // 6. Lưu Chi tiết Combo
                if ($selectedCombo) {
                    ChiTietCombo::create([
                        'id_dat_ve'   => $booking->id,
                        'id_combo'    => $selectedCombo->id,
                        'so_luong'    => 1,
                        'gia_ap_dung' => (float)$selectedCombo->gia,
                    ]);
                }

                // 7. Tạo Thanh Toán (QUAN TRỌNG ĐỂ ADMIN BIẾT MÀ HỦY VÉ)
                ThanhToan::create([
                    'id_dat_ve'    => $booking->id,
                    'phuong_thuc'  => ($paymentMethod === 'online') ? 'VNPAY' : 'Tiền mặt',
                    'so_tien'      => $totalAmount,
                    'trang_thai'   => 0, // Chưa thanh toán
                    'thoi_gian'    => now()
                ]);

                // 8. Trả về kết quả
                if ($paymentMethod === 'online') {
                    $vnp_Url = $this->createVnpayUrl($booking->id, $totalAmount);
                    return response()->json([
                        'success' => true,
                        'message' => 'Đang chuyển hướng thanh toán...',
                        'payment_url' => $vnp_Url,
                        'is_redirect' => true
                    ]);
                } else {
                    // Offline
                    return response()->json([
                        'success' => true,
                        'message' => 'Đặt vé thành công! Vui lòng thanh toán tại quầy trong 5 phút.',
                        'booking_id' => $booking->id,
                        'is_redirect' => false
                    ]);
                }
            });
        } catch (\Exception $e) {
            Log::error('Booking error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    // =========================================================================
    // === HÀM VNPAY RETURN (QUAN TRỌNG: ĐÃ SỬA UPDATE) ===
    // =========================================================================
    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = trim(env('VNP_HASH_SECRET'));
        $inputData = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") $inputData[$key] = $value;
        }
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            $parts = explode("_", $request->vnp_TxnRef);
            $bookingId = $parts[0];

            // Load booking kèm chi tiết để xử lý ghế
            $booking = DatVe::with(['chiTietDatVe.ghe', 'chiTietCombo', 'thanhToan'])->find($bookingId);

            if ($request->vnp_ResponseCode == '00') {
                // --- THANH TOÁN THÀNH CÔNG ---
                if ($booking && $booking->trang_thai == 0) {
                    DB::transaction(function () use ($booking, $request) {
                        // 1. Cập nhật vé thành công
                        $booking->update(['trang_thai' => 1]);

                        // 2. Cập nhật bản ghi thanh toán
                        if ($booking->thanhToan) {
                            $booking->thanhToan()->update([
                                'trang_thai' => 1,
                                'ma_giao_dich' => $request->vnp_TransactionNo,
                                'thoi_gian' => now()
                            ]);
                        } else {
                            // Fallback an toàn (tạo mới nếu chưa có)
                            ThanhToan::create([
                                'id_dat_ve' => $booking->id,
                                'phuong_thuc' => 'VNPAY',
                                'so_tien' => $request->vnp_Amount / 100,
                                'ma_giao_dich' => $request->vnp_TransactionNo,
                                'trang_thai' => 1,
                                'thoi_gian' => now()
                            ]);
                        }
                    });
                    return redirect()->route('user.bookings')->with('success', 'Thanh toán thành công!');
                }
            } else {
                // --- [SỬA] THANH TOÁN THẤT BẠI / HỦY ---
                // Thay vì lưu trạng thái hủy, ta XÓA LUÔN bản ghi
                if ($booking && $booking->trang_thai == 0) {
                    DB::transaction(function () use ($booking) {
                        // 1. Nhả ghế trước (Quan trọng: Trả lại trạng thái ghế trống)
                        foreach ($booking->chiTietDatVe as $detail) {
                            if ($detail->ghe) {
                                $detail->ghe->update(['trang_thai' => 1]); // 1 = Trống
                            }
                        }

                        // 2. Xóa các bảng chi tiết liên quan (Laravel Cascade có thể tự làm, nhưng làm thủ công cho chắc)
                        $booking->chiTietDatVe()->delete();
                        $booking->chiTietCombo()->delete();
                        if ($booking->thanhToan) {
                            $booking->thanhToan()->delete();
                        }

                        // 3. Xóa vé chính (Xóa vĩnh viễn khỏi DB)
                        $booking->delete();
                    });
                }
                // Chuyển hướng về trang đặt vé (hoặc trang chủ) thay vì trang lịch sử (vì vé đã mất)
                return redirect()->route('home')->with('error', 'Giao dịch đã bị hủy. Vui lòng đặt lại vé.');
            }
        } else {
            return redirect()->route('home')->with('error', 'Chữ ký bảo mật không hợp lệ!');
        }
        return redirect()->route('home');
    }

    // Helper tạo URL VNPAY
    private function createVnpayUrl($orderId, $amount)
    {
        $vnp_Url = env('VNP_URL');
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $vnp_TmnCode = env('VNP_TMN_CODE');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $amount * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => request()->ip(),
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Thanh toan ve #$orderId",
            "vnp_OrderType" => "billpayment",
            "vnp_ReturnUrl" => env('VNP_RETURN_URL'),
            "vnp_TxnRef" => $orderId . "_" . time()
        ];

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
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return $vnp_Url;
    }

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
                    'payment_method' => $method,
                    'qr' => [
                        'data' => $payloadUrl,
                        'image' => 'https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=' . urlencode($payloadUrl)
                    ],
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
