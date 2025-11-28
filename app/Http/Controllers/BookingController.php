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
        $seatList = $booking->chiTietDatVe->map(function($ct){ return optional($ct->ghe)->so_ghe; })->filter()->values()->all();

        // Calculate totals
        $comboItems = $booking->chiTietCombo ?? collect();
        $promo = $booking->khuyenMai;
        $comboTotal = $comboItems->sum(function($i){ return (float)$i->gia_ap_dung * max(1, (int)$i->so_luong); });
        $seatTotal = (float) $booking->chiTietDatVe->sum('gia');
        $subtotal = $seatTotal + $comboTotal;
        $promoDiscount = 0;
        
        if ($promo) {
            $type = strtolower($promo->loai_giam);
            $val = (float)$promo->gia_tri_giam;
            $min = 0;
            if ($subtotal >= $min) {
                if ($type === 'phantram') {
                    $promoDiscount = round($subtotal * ($val/100));
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

        return view('user.ticket-detail', compact(
            'booking', 
            'showtime', 
            'movie', 
            'room', 
            'seatList', 
            'comboItems', 
            'promo', 
            'promoDiscount', 
            'computedTotal',
            'pt'
        ));
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
            
            // Release expired seats (lazy check) - skip if table doesn't exist
            try {
                if (Schema::hasTable('suat_chieu_ghe')) {
                    ShowtimeSeat::releaseExpiredSeats($showtimeId);
                }
            } catch (\Throwable $e) {
                \Log::warning('Skip releaseExpiredSeats: '.$e->getMessage());
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
                \Log::warning('Skip get booked showtime seats: '.$e->getMessage());
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
                \Log::warning('Skip get holding seats: '.$e->getMessage());
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
            \Log::error('Error loading showtime seats: ' . $e->getMessage());
            return response()->json(['seats' => []]);
        }
    }

    public function store(Request $request)
    {
        try {
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
            
            // Release expired seats first (if mapping table exists)
            try {
                if (Schema::hasTable('suat_chieu_ghe')) {
                    ShowtimeSeat::releaseExpiredSeats($data['showtime']);
                }
            } catch (\Throwable $e) {
                \Log::warning('Skip releaseExpiredSeats@store: '.$e->getMessage());
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
                    \Log::warning('Skip release existing booking seats@store: '.$e->getMessage());
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
                    \Log::warning('Skip get existing booking seats@store: '.$e->getMessage());
                }
            }

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
                        // Skip check if this seat is already in existing booking (from chi_tiet_dat_ve)
                        if (in_array($ghe->id, $existingBookingSeats)) {
                            continue;
                        }
                        
                        // Skip check if this seat is being held by existing booking (from suat_chieu_ghe)
                        if (in_array($ghe->id, $existingBookingHoldingSeats)) {
                            continue;
                        }
                        
                        // Check showtime_seats table (if exists) - only check for booked seats, ignore holding
                        $showtimeSeat = null;
                        try {
                            if (Schema::hasTable('suat_chieu_ghe')) {
                                $showtimeSeat = ShowtimeSeat::where('id_suat_chieu', $data['showtime'])
                                    ->where('id_ghe', $ghe->id)
                                    ->first();
                            }
                        } catch (\Throwable $e) {
                            \Log::warning('Skip check showtimeSeat@store: '.$e->getMessage());
                        }
                        
                        if ($showtimeSeat) {
                            // Only check if seat is booked (permanently), ignore holding status
                            // Holding seats will be converted to booked during payment processing
                            if ($showtimeSeat->isBooked()) {
                                $unavailableSeats[] = $code;
                                continue;
                            }
                            // Skip checking holding status - allow all holding seats
                        }
                        
                        // Also check old booking system for backward compatibility (exclude existing booking)
                        $exists = ChiTietDatVe::join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                            ->where('dat_ve.id_suat_chieu', $data['showtime'])
                            ->where('chi_tiet_dat_ve.id_ghe', $ghe->id)
                            ->whereIn('dat_ve.trang_thai', [0, 1])
                            ->when(isset($data['booking_id']) && $data['booking_id'], function($query) use ($data) {
                                $query->where('dat_ve.id', '!=', $data['booking_id']);
                            })
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
            
            // Release expired seats first (only if mapping table exists)
            try {
                if (Schema::hasTable('suat_chieu_ghe')) {
                    ShowtimeSeat::releaseExpiredSeats($data['showtime']);
                }
            } catch (\Throwable $e) {
                \Log::warning('Skip releaseExpiredSeats before saving seats: '.$e->getMessage());
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
                        
                        // Update showtime_seats: convert holding to booked, or create new booked entry (if table exists)
                        try {
                            if (Schema::hasTable('suat_chieu_ghe')) {
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
                            }
                        } catch (\Throwable $e) {
                            \Log::warning('Skip update showtime_seat@store: '.$e->getMessage());
                        }
                        
                        // Lock seat (legacy behavior)
                        $seat->trang_thai = 0;
                        $seat->save();
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

            // Create payment record
            ThanhToan::create([
                'id_dat_ve'    => $booking->id,
                'phuong_thuc'  => ($paymentMethod === 'online') ? 'VNPAY' : 'Tiền mặt',
                'so_tien'      => $totalAmount,
                'trang_thai'   => 0, // Chưa thanh toán
                'thoi_gian'    => now()
            ]);

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
            
        } catch (\Exception $e) {
            Log::error('Booking error: ' . $e->getMessage());
            Log::error('Booking error trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại!',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Booking fatal error: ' . $e->getMessage());
            Log::error('Booking fatal error trace: ' . $e->getTraceAsString());
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

            // Check if seats are available (skip if mapping table doesn't exist)
            $unavailableSeats = [];
            foreach ($seatIds as $index => $seatId) {
                try {
                    if (Schema::hasTable('suat_chieu_ghe')) {
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
                } catch (\Throwable $e) {
                    \Log::warning('Skip availability check (suat_chieu_ghe missing?): '.$e->getMessage());
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
                    'id_suat_chieu' => $showtimeId,
                    'tong_tien' => 0,
                    'trang_thai' => 0, // pending
                ];
                // Only set user id if authenticated (allow guest holds)
                try {
                    if (Schema::hasColumn('dat_ve', 'id_nguoi_dung')) {
                        $uid = Auth::id();
                        if ($uid) {
                            $bookingData['id_nguoi_dung'] = $uid;
                        }
                    }
                } catch (\Throwable $e) {
                    // Schema check failed; ignore and proceed without user id
                }
                
                // Optional columns
                if (Schema::hasColumn('dat_ve', 'phuong_thuc_thanh_toan')) {
                    $bookingData['phuong_thuc_thanh_toan'] = null;
                }
                if (Schema::hasColumn('dat_ve', 'tong_tien_hien_thi')) {
                    $bookingData['tong_tien_hien_thi'] = 0;
                }
                
                // Create booking while handling missing timestamps columns gracefully
                if (Schema::hasColumn('dat_ve', 'created_at') && Schema::hasColumn('dat_ve', 'updated_at')) {
                    $booking = DatVe::create($bookingData);
                } else {
                    $booking = DatVe::withoutTimestamps(function () use ($bookingData) {
                        return DatVe::create($bookingData);
                    });
                }
                $bookingId = $booking->id;

                // Set seats to holding status (skip if mapping table doesn't exist)
                try {
                    if (Schema::hasTable('suat_chieu_ghe')) {
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
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Skip holding seats (suat_chieu_ghe missing?): '.$e->getMessage());
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
                    'message' => 'Có lỗi xảy ra khi giữ ghế: ' . $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Select seats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
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

    /**
     * VNPAY Return Handler
     * Handle payment return from VNPAY gateway
     */
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

            // Load booking with details
            $booking = DatVe::with(['chiTietDatVe.ghe', 'chiTietCombo', 'thanhToan'])->find($bookingId);

            if ($request->vnp_ResponseCode == '00') {
                // Payment successful
                if ($booking && $booking->trang_thai == 0) {
                    DB::transaction(function () use ($booking, $request) {
                        // 1. Update booking status to confirmed
                        $booking->update(['trang_thai' => 1]);

                        // 2. Update payment record
                        if ($booking->thanhToan) {
                            $booking->thanhToan()->update([
                                'trang_thai' => 1,
                                'ma_giao_dich' => $request->vnp_TransactionNo,
                                'thoi_gian' => now()
                            ]);
                        } else {
                            // Fallback: create new payment record if not exists
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
                    return redirect()->route('booking.tickets')->with('success', 'Thanh toán thành công!');
                }
            } else {
                // Payment failed or cancelled
                if ($booking && $booking->trang_thai == 0) {
                    DB::transaction(function () use ($booking) {
                        // 1. Release seats
                        foreach ($booking->chiTietDatVe as $detail) {
                            if ($detail->ghe) {
                                $detail->ghe->update(['trang_thai' => 1]); // 1 = Available
                            }
                        }

                        // 2. Delete related records
                        $booking->chiTietDatVe()->delete();
                        $booking->chiTietCombo()->delete();
                        if ($booking->thanhToan) {
                            $booking->thanhToan()->delete();
                        }

                        // 3. Delete booking
                        $booking->delete();
                    });
                }
                return redirect()->route('home')->with('error', 'Giao dịch đã bị hủy. Vui lòng đặt lại vé.');
            }
        } else {
            return redirect()->route('home')->with('error', 'Chữ ký bảo mật không hợp lệ!');
        }
        return redirect()->route('home');
    }

    /**
     * Helper method to create VNPAY payment URL
     */
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
            "vnp_ReturnUrl" => env('VNP_RETURN_URL', route('payment.vnpay_return')),
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
}
