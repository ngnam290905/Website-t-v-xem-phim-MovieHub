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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
        // Get booking data for user with related showtime, movie, room, and seat details
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
        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movie->id)
            ->where('thoi_gian_bat_dau', '>=', now()->startOfDay()) // Lấy suất chiếu từ hôm nay
            ->where('trang_thai', 1) // Chỉ lấy suất chiếu đang hoạt động
            ->orderBy('thoi_gian_bat_dau')
            ->get()
            ->map(function ($suat) {
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
                $vipSeatData = $seats->filter(function($seat) {
                    return $this->isVipSeat($seat);
                });
                $vipSeats = $vipSeatData->pluck('so_ghe')->toArray();
                
                // Get VIP rows (extract row letter from seat code)
                $vipRows = $vipSeatData->map(function($seat) {
                    return substr($seat->so_ghe, 0, 1); // Get first character (A, B, C, etc.)
                })->unique()->values()->toArray();
                
                // Get couple seats - find seats with id_loai = 3 (Couple) or name contains "đôi"
                $coupleSeatData = $seats->filter(function($seat) {
                    return $this->isCoupleSeat($seat);
                });
                
                // Group couple seats by row
                $coupleSeatGroups = $coupleSeatData->groupBy(function($seat) {
                    return substr($seat->so_ghe, 0, 1); // Group by row letter
                });
                
                $coupleSeats = [];
                foreach ($coupleSeatGroups as $row => $seatsInRow) {
                    $seatNumbers = $seatsInRow->pluck('so_ghe')->toArray();
                    sort($seatNumbers); // Sort to ensure correct order
                    
                    // Look for pairs (like 11-12, 13-14, etc.)
                    for ($i = 0; $i < count($seatNumbers) - 1; $i++) {
                        $num1 = intval(substr($seatNumbers[$i], 1)); // Extract number part
                        $num2 = intval(substr($seatNumbers[$i+1], 1));
                        
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
            
            // Get confirmed bookings for this showtime
            $bookedSeats = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->join('ghe', 'chi_tiet_dat_ve.id_ghe', '=', 'ghe.id')
                ->where('dat_ve.id_suat_chieu', $showtimeId)
                ->whereIn('dat_ve.trang_thai', [0, 1]) // 0=chờ xác nhận, 1=đã xác nhận
                ->select('ghe.so_ghe')
                ->get()
                ->map(function ($seat) {
                    return $seat->so_ghe; // seat code like 'A1'
                });
            
            return response()->json(['seats' => $bookedSeats]);
        } catch (\Exception $e) {
            \Log::error('Error loading booked seats: ' . $e->getMessage());
            return response()->json(['seats' => []]);
        }
    }
    
    public function getShowtimeSeats($showtimeId)
    {
        try {
            \Log::info('getShowtimeSeats called with showtimeId: ' . $showtimeId);
            
            // Validate showtime exists
            $showtime = SuatChieu::find($showtimeId);
            if (!$showtime) {
                \Log::warning('Showtime not found: ' . $showtimeId);
                return response()->json(['seats' => []]);
            }
            
            \Log::info('Showtime found, room id: ' . $showtime->id_phong);
            
            // Get all seats for this showtime's room
            $seats = Ghe::where('id_phong', $showtime->id_phong)
                ->with('loaiGhe')
                ->get()
                ->mapWithKeys(function ($seat) {
                    $typeText = strtolower($seat->loaiGhe->ten_loai ?? 'thường');
                    $isAvailable = (int)($seat->trang_thai ?? 0) === 1;
                    
                    // Debug logging for VIP seats
                    if (str_contains($typeText, 'vip') || str_contains(strtolower($seat->loaiGhe->ten_loai ?? ''), 'vip')) {
                        \Log::info('VIP seat found: ' . $seat->so_ghe . ', type: ' . ($seat->loaiGhe->ten_loai ?? 'N/A'));
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
                    
                    return [$seat->so_ghe => [
                        'id' => $seat->id,
                        'code' => $seat->so_ghe,
                        'type' => $seat->loaiGhe->ten_loai ?? 'Thường',
                        'available' => $isAvailable,
                        'price' => $price
                    ]];
                });
            
            \Log::info('Seats data prepared, count: ' . $seats->count());
            
            return response()->json(['seats' => $seats]);
        } catch (\Exception $e) {
            \Log::error('Error loading showtime seats: ' . $e->getMessage());
            return response()->json(['seats' => []]);
        }
    }
    
    public function store(Request $request)
    {
        try {
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
            
            // Check if seats are already booked
            $existingBookings = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                ->join('ghe', 'chi_tiet_dat_ve.id_ghe', '=', 'ghe.id')
                ->where('dat_ve.id_suat_chieu', $data['showtime'])
                ->whereIn('dat_ve.trang_thai', [0, 1]);
            
            // Check each seat
            $existingBase = $existingBookings; // base query to clone per-check
            foreach ($data['seats'] as $seat) {
                if (strpos($seat, ',') !== false) {
                    // Couple seat - check both individual seats
                    $seatCodes = explode(',', $seat);
                    foreach ($seatCodes as $code) {
                        $row = substr($code, 0, 1);
                        $col = substr($code, 1);
                        $exists = (clone $existingBase)
                            ->where('ghe.so_ghe', $row.$col)
                            ->exists();
                        if ($exists) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ghế đôi đã được đặt. Vui lòng chọn ghế khác!'
                            ]);
                        }
                    }
                } else {
                    // Regular seat
                    $row = substr($seat, 0, 1);
                    $col = substr($seat, 1);
                    $exists = (clone $existingBase)
                        ->where('ghe.so_ghe', $row.$col)
                        ->exists();
                    if ($exists) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Một hoặc nhiều ghế đã được đặt. Vui lòng chọn ghế khác!'
                        ]);
                    }
                }
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
            
            // Create booking
            $paymentMethod = $data['payment_method'] ?? 'offline';
            $bookingStatus = ($paymentMethod === 'online') ? 1 : 0; // 1 = đã thanh toán, 0 = chờ thanh toán tại quầy
            $methodCode = ($paymentMethod === 'online') ? 1 : 2; // 1=online, 2=tai quay
            
            $booking = DatVe::create([
                'id_nguoi_dung'   => Auth::id(),
                'id_suat_chieu'   => $data['showtime'] ?? null,
                'id_khuyen_mai'   => $promotionId,
                // Optional totals by schema (have defaults); we at least set tong_tien
                // 'tong_tien_goc'    => $seatTotal + $comboTotal,
                // 'tien_giam_khuyen_mai' => $discount,
                'tong_tien'       => $totalAmount,
                'trang_thai'      => $bookingStatus,
                'phuong_thuc_thanh_toan' => $methodCode,
            ]);
            
            // Save seat details
            foreach ($data['seats'] as $seatCode) {
                if (strpos($seatCode, '-') !== false) {
                    // Couple seat - save both individual seats (format: G11-12)
                    preg_match('/^([A-Z])(\d+)-(\d+)$/', $seatCode, $matches);
                    if ($matches) {
                        $row = $matches[1];
                        $col1 = $matches[2];
                        $col2 = $matches[3];
                        
                        for ($c = $col1; $c <= $col2; $c++) {
                            $seatFull = Ghe::where('id_phong', $showtime->id_phong)
                                ->where('so_ghe', $row . $c)
                                ->first();
                                
                            if ($seatFull) {
                                ChiTietDatVe::create([
                                    'id_dat_ve' => $booking->id,
                                    'id_ghe' => $seatFull->id,
                                    'gia' => 100000 // Half of couple seat price for each seat
                                ]);
                            }
                        }
                    }
                } else {
                    // Regular seat
                    $row = substr($seatCode, 0, 1);
                    $number = substr($seatCode, 1);
                    
                    $seat = Ghe::where('id_phong', $showtime->id_phong)
                        ->where('so_ghe', $row . $number)
                        ->first();
                        
                    if ($seat) {
                        $price = $this->isVipSeat($seat) ? 120000 : 80000;
                        ChiTietDatVe::create([
                            'id_dat_ve' => $booking->id,
                            'id_ghe' => $seat->id,
                            'gia' => $price
                        ]);
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
}
