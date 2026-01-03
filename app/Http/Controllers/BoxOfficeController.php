<?php

namespace App\Http\Controllers;

use App\Models\Phim;
use App\Models\SuatChieu;
use App\Models\Ghe;
use App\Models\Combo;
use App\Models\Food;
use App\Models\KhuyenMai;
use App\Models\DatVe;
use App\Models\ChiTietDatVe;
use App\Models\ChiTietCombo;
use App\Models\ChiTietFood;
use App\Models\ThanhToan;
use App\Models\NguoiDung;
use App\Models\ShowtimeSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class BoxOfficeController extends Controller
{
    private const BASE_TICKET_PRICE = 100000;
    private const SEAT_HOLD_DURATION = 10; // phút

    /**
     * Bước 1-2: Trang chính box office
     * GET /admin/box-office
     */
    public function index()
    {
        // Kiểm tra quyền: staff hoặc admin
        $user = Auth::user();
        if (!$user || !in_array(optional($user->vaiTro)->ten, ['admin', 'staff'])) {
            abort(403, 'Bạn không có quyền truy cập.');
        }

        return view('admin.box-office.index');
    }

    /**
     * Bước 3: API lấy danh sách phim
     * GET /admin/box-office/movies
     */
    public function getMovies(Request $request)
    {
        $status = $request->get('status', 'showing'); // showing, upcoming
        
        $query = Phim::query();
        
        if ($status === 'showing') {
            // Phim đang chiếu
            $query->where('trang_thai', 'dang_chieu');
        } elseif ($status === 'upcoming') {
            // Phim sắp chiếu (có suất)
            $query->whereIn('trang_thai', ['sap_chieu', 'dang_chieu'])
                ->whereHas('suatChieu', function($q) {
                    $q->where('thoi_gian_bat_dau', '>', now())
                        ->where('trang_thai', 1);
                });
        }
        
        $movies = $query->orderBy('ngay_khoi_chieu', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $movies->map(function($movie) {
                return [
                    'id' => $movie->id,
                    'ten_phim' => $movie->ten_phim,
                    'thoi_luong' => $movie->thoi_luong,
                    'do_tuoi' => $movie->do_tuoi,
                    'poster' => $movie->poster,
                    'trailer' => $movie->trailer,
                    'mo_ta' => $movie->mo_ta,
                ];
            })
        ]);
    }

    /**
     * Bước 4: API lấy suất chiếu theo phim
     * GET /admin/box-office/showtimes?movie_id=1
     */
    public function getShowtimes(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|integer|exists:phim,id',
        ]);

        $movieId = $request->movie_id;
        $now = now();

        // Chỉ lấy suất chưa kết thúc, chưa bị khóa
        $showtimes = SuatChieu::where('id_phim', $movieId)
            ->where('trang_thai', 1) // Chưa khóa
            ->where('thoi_gian_ket_thuc', '>', $now) // Chưa kết thúc
            ->with(['phongChieu'])
            ->orderBy('thoi_gian_bat_dau')
            ->get()
            ->map(function($showtime) {
                return [
                    'id' => $showtime->id,
                    'phong_chieu' => $showtime->phongChieu->ten_phong ?? 'N/A',
                    'thoi_gian_bat_dau' => $showtime->thoi_gian_bat_dau->format('H:i'),
                    'thoi_gian_ket_thuc' => $showtime->thoi_gian_ket_thuc->format('H:i'),
                    'ngay_chieu' => $showtime->thoi_gian_bat_dau->format('d/m/Y'),
                    'gia_ve_co_ban' => self::BASE_TICKET_PRICE,
                    'is_ended' => $showtime->thoi_gian_ket_thuc->lt(now()),
                    'can_lock' => $this->canLockShowtime($showtime), // Còn < X phút
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $showtimes
        ]);
    }

    /**
     * Bước 5: API lấy sơ đồ ghế
     * GET /admin/box-office/showtimes/{id}/seats
     */
    public function getSeats($showtimeId)
    {
        try {
            $showtime = SuatChieu::with(['phongChieu', 'phongChieu.seats.loaiGhe'])->findOrFail($showtimeId);
            
            // Giải phóng ghế hết hạn
            $this->releaseExpiredHolds($showtime);
            
            $room = $showtime->phongChieu;
            $seats = $room->seats()->where('trang_thai', 1)->orderBy('so_hang')->orderBy('so_ghe')->get();
            
            // Lấy ghế đã bán
            $bookedSeatIds = ChiTietDatVe::whereHas('datVe', function($q) use ($showtimeId) {
                $q->where('id_suat_chieu', $showtimeId)
                    ->whereIn('trang_thai', [0, 1]); // Pending hoặc Paid
            })->pluck('id_ghe')->toArray();
            
            // Lấy ghế đang giữ chỗ
            $heldSeatIds = [];
            if (DB::getSchemaBuilder()->hasTable('suat_chieu_ghe')) {
                $heldSeatIds = ShowtimeSeat::where('id_suat_chieu', $showtimeId)
                    ->where('trang_thai', 'holding')
                    ->where('thoi_gian_het_han', '>', now())
                    ->pluck('id_ghe')
                    ->toArray();
            }
            
            $seatsData = $seats->map(function($seat) use ($bookedSeatIds, $heldSeatIds) {
                $status = 'available'; // Trống
                if (in_array($seat->id, $bookedSeatIds)) {
                    $status = 'sold'; // Đã bán
                } elseif (in_array($seat->id, $heldSeatIds)) {
                    $status = 'holding'; // Đang giữ chỗ
                }
                
                // Tính giá ghế
                $heSoGia = $seat->loaiGhe->he_so_gia ?? 1;
                $price = self::BASE_TICKET_PRICE * $heSoGia;
                
                return [
                    'id' => $seat->id,
                    'code' => $seat->so_ghe,
                    'row' => $seat->so_hang,
                    'type' => $seat->id_loai ?? 1, // 1: Thường, 2: VIP, 3: Đôi
                    'status' => $status,
                    'price' => $price,
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'showtime_id' => $showtimeId,
                    'room_name' => $room->ten_phong ?? 'N/A',
                    'seats' => $seatsData,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('BoxOffice getSeats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải sơ đồ ghế'
            ], 500);
        }
    }

    /**
     * Bước 5: Giữ ghế tạm thời (5-10 phút)
     * POST /admin/box-office/seat-hold
     */
    public function holdSeat(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|integer|exists:suat_chieu,id',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer|exists:ghe,id',
        ]);

        try {
            DB::beginTransaction();
            
            $showtime = SuatChieu::findOrFail($request->showtime_id);
            $seatIds = $request->seat_ids;
            
            // Kiểm tra ghế có sẵn không
            $bookedSeatIds = ChiTietDatVe::whereHas('datVe', function($q) use ($showtime) {
                $q->where('id_suat_chieu', $showtime->id)
                    ->whereIn('trang_thai', [0, 1]);
            })->pluck('id_ghe')->toArray();
            
            $conflictedSeats = [];
            foreach ($seatIds as $seatId) {
                if (in_array($seatId, $bookedSeatIds)) {
                    $seat = Ghe::find($seatId);
                    $conflictedSeats[] = $seat->so_ghe ?? "ID: {$seatId}";
                }
            }
            
            if (!empty($conflictedSeats)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Một hoặc nhiều ghế đã được đặt: ' . implode(', ', $conflictedSeats)
                ], 400);
            }
            
            // Giữ ghế trong bảng suat_chieu_ghe (nếu có)
            $holds = [];
            if (DB::getSchemaBuilder()->hasTable('suat_chieu_ghe')) {
                foreach ($seatIds as $seatId) {
                    $hold = ShowtimeSeat::updateOrCreate(
                        [
                            'id_suat_chieu' => $showtime->id,
                            'id_ghe' => $seatId,
                        ],
                        [
                            'trang_thai' => 'holding',
                            'thoi_gian_het_han' => now()->addMinutes(self::SEAT_HOLD_DURATION),
                        ]
                    );
                    $holds[] = $hold;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Đã giữ ghế thành công',
                'hold_expires_at' => now()->addMinutes(self::SEAT_HOLD_DURATION)->toIso8601String(),
                'expires_in_minutes' => self::SEAT_HOLD_DURATION,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BoxOffice holdSeat error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi giữ ghế: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bước 6-7: API lấy combo và đồ ăn
     * GET /admin/box-office/foods
     */
    public function getFoods()
    {
        $combos = Combo::where('trang_thai', 1)->get();
        $foods = Food::where('is_active', 1)->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'combos' => $combos->map(function($combo) {
                    return [
                        'id' => $combo->id,
                        'ten' => $combo->ten,
                        'gia' => $combo->gia,
                        'mo_ta' => $combo->mo_ta ?? '',
                        'anh' => $combo->anh ?? '',
                    ];
                }),
                'foods' => $foods->map(function($food) {
                    return [
                        'id' => $food->id,
                        'name' => $food->name,
                        'price' => $food->price,
                        'stock' => $food->stock ?? 0,
                        'image' => $food->image ?? '',
                    ];
                }),
            ]
        ]);
    }

    /**
     * Bước 8: Tạo đơn hàng
     * POST /admin/box-office/orders
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|integer|exists:suat_chieu,id',
            'seats' => 'required|array|min:1',
            'seats.*' => 'string', // Mã ghế như "A1", "A2"
            'foods' => 'nullable|array',
            'foods.*.id' => 'required_with:foods|integer',
            'foods.*.qty' => 'required_with:foods|integer|min:1',
            'payment_method' => 'required|in:cash,transfer,e_wallet,pos',
            'customer_phone' => 'nullable|string|max:20',
            'customer_id' => 'nullable|integer|exists:nguoi_dung,id',
            'type' => 'required|in:OFFLINE',
        ]);

        try {
            DB::beginTransaction();
            
            $showtime = SuatChieu::with(['phim', 'phongChieu'])->findOrFail($request->showtime_id);
            
            // Kiểm tra suất chiếu
            if ($showtime->trang_thai != 1) {
                throw new \Exception('Suất chiếu không khả dụng.');
            }
            
            if ($showtime->thoi_gian_ket_thuc < now()) {
                throw new \Exception('Suất chiếu đã kết thúc.');
            }
            
            // Xác định khách hàng
            $customerId = $request->customer_id;
            if ($request->customer_phone && !$customerId) {
                // Tìm khách hàng theo số điện thoại
                $customer = NguoiDung::where('sdt', $request->customer_phone)->first();
                if ($customer) {
                    $customerId = $customer->id;
                } else {
                    // Tạo khách hàng mới nếu không tìm thấy
                    $customerId = NguoiDung::create([
                        'ho_ten' => 'Khách hàng ' . $request->customer_phone,
                        'sdt' => $request->customer_phone,
                        'id_vai_tro' => 4, // Khách hàng
                        'trang_thai' => 1,
                    ])->id;
                }
            }
            
            if (!$customerId) {
                throw new \Exception('Vui lòng cung cấp thông tin khách hàng.');
            }
            
            // Chuyển đổi mã ghế thành ID ghế
            $seatCodes = $request->seats;
            $seatIds = [];
            foreach ($seatCodes as $code) {
                $seat = Ghe::where('id_phong', $showtime->id_phong)
                    ->where('so_ghe', trim($code))
                    ->first();
                if ($seat) {
                    $seatIds[] = $seat->id;
                }
            }
            
            if (empty($seatIds)) {
                throw new \Exception('Không tìm thấy ghế được chọn.');
            }
            
            // Kiểm tra ghế có sẵn không
            $bookedSeatIds = ChiTietDatVe::whereHas('datVe', function($q) use ($showtime) {
                $q->where('id_suat_chieu', $showtime->id)
                    ->whereIn('trang_thai', [0, 1]);
            })->pluck('id_ghe')->toArray();
            
            $conflictedSeats = [];
            foreach ($seatIds as $seatId) {
                if (in_array($seatId, $bookedSeatIds)) {
                    $seat = Ghe::find($seatId);
                    $conflictedSeats[] = $seat->so_ghe ?? "ID: {$seatId}";
                }
            }
            
            if (!empty($conflictedSeats)) {
                throw new \Exception('Một hoặc nhiều ghế đã được đặt: ' . implode(', ', $conflictedSeats));
            }
            
            // Tính giá ghế
            $tongGhe = 0;
            $seatDetails = [];
            foreach ($seatIds as $seatId) {
                $ghe = Ghe::with('loaiGhe')->findOrFail($seatId);
                $heSoGia = $ghe->loaiGhe->he_so_gia ?? 1;
                $gia = self::BASE_TICKET_PRICE * $heSoGia;
                $tongGhe += $gia;
                $seatDetails[] = ['id' => $ghe->id, 'gia' => $gia];
            }
            
            // Tính giá combo/food
            $tongCombo = 0;
            $tongFood = 0;
            $comboDetails = [];
            $foodDetails = [];
            
            if ($request->foods) {
                foreach ($request->foods as $food) {
                    if (isset($food['id']) && isset($food['qty']) && $food['qty'] > 0) {
                        // Kiểm tra là combo hay food
                        $combo = Combo::find($food['id']);
                        if ($combo) {
                            $tongCombo += ($combo->gia * $food['qty']);
                            $comboDetails[] = [
                                'id' => $combo->id,
                                'so_luong' => $food['qty'],
                                'gia' => $combo->gia,
                            ];
                        } else {
                            $foodItem = Food::find($food['id']);
                            if ($foodItem) {
                                $tongFood += ($foodItem->price * $food['qty']);
                                $foodDetails[] = [
                                    'id' => $foodItem->id,
                                    'quantity' => $food['qty'],
                                    'price' => $foodItem->price,
                                ];
                            }
                        }
                    }
                }
            }
            
            // Tính tổng tiền
            $tongTien = $tongGhe + $tongCombo + $tongFood;
            
            // Xác định trạng thái booking và thanh toán
            // Nếu là chuyển khoản (QR), tạo booking pending để chờ xác nhận
            $isQrPayment = $request->payment_method === 'transfer';
            $bookingStatus = $isQrPayment ? 0 : 1; // 0: Pending, 1: SOLD
            $paymentStatus = $isQrPayment ? 0 : 1; // 0: Chưa thanh toán, 1: Đã thanh toán
            
            // Tạo đặt vé
            $booking = DatVe::create([
                'id_nguoi_dung' => $customerId,
                'id_suat_chieu' => $showtime->id,
                'trang_thai' => $bookingStatus,
                'tong_tien' => $tongTien,
                'phuong_thuc_thanh_toan' => $this->mapPaymentMethod($request->payment_method),
                'ghi_chu_noi_bo' => 'Đặt vé tại quầy bởi ' . Auth::user()->ho_ten,
            ]);
            
            // Tạo chi tiết ghế
            foreach ($seatDetails as $detail) {
                ChiTietDatVe::create([
                    'id_dat_ve' => $booking->id,
                    'id_ghe' => $detail['id'],
                    'gia' => $detail['gia'],
                ]);
            }
            
            // Tạo chi tiết combo
            foreach ($comboDetails as $detail) {
                ChiTietCombo::create([
                    'id_dat_ve' => $booking->id,
                    'id_combo' => $detail['id'],
                    'so_luong' => $detail['so_luong'],
                    'gia_ap_dung' => $detail['gia'],
                ]);
            }
            
            // Tạo chi tiết food
            foreach ($foodDetails as $detail) {
                ChiTietFood::create([
                    'id_dat_ve' => $booking->id,
                    'food_id' => $detail['id'],
                    'quantity' => $detail['quantity'],
                    'price' => $detail['price'],
                ]);
                
                // Chỉ trừ kho đồ ăn khi đã thanh toán
                if (!$isQrPayment) {
                    Food::where('id', $detail['id'])->decrement('stock', $detail['quantity']);
                }
            }
            
            // Tạo thanh toán
            ThanhToan::create([
                'id_dat_ve' => $booking->id,
                'so_tien' => $tongTien,
                'phuong_thuc' => $this->getPaymentMethodName($request->payment_method),
                'trang_thai' => $paymentStatus,
                'thoi_gian' => $paymentStatus === 1 ? now() : null,
            ]);
            
            // Cập nhật ShowtimeSeat (chỉ khi đã thanh toán)
            if (!$isQrPayment && DB::getSchemaBuilder()->hasTable('suat_chieu_ghe')) {
                foreach ($seatIds as $seatId) {
                    ShowtimeSeat::updateOrCreate(
                        [
                            'id_suat_chieu' => $showtime->id,
                            'id_ghe' => $seatId,
                        ],
                        [
                            'trang_thai' => 'booked',
                            'thoi_gian_het_han' => null,
                        ]
                    );
                }
            }
            
            DB::commit();
            
            // Nếu là QR payment, redirect đến trang QR
            if ($isQrPayment) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã tạo đơn hàng, vui lòng thanh toán QR',
                    'data' => [
                        'booking_id' => $booking->id,
                        'total' => $tongTien,
                        'qr_payment' => true,
                        'qr_url' => route('admin.box-office.qr-payment', $booking->id),
                    ]
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Đặt vé thành công',
                'data' => [
                    'booking_id' => $booking->id,
                    'total' => $tongTien,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BoxOffice createOrder error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Hiển thị trang thanh toán QR
     * GET /admin/box-office/qr-payment/{bookingId}
     */
    public function showQrPayment($bookingId)
    {
        try {
            $booking = DatVe::with([
                'nguoiDung',
                'suatChieu.phim',
                'suatChieu.phongChieu',
                'chiTietDatVe.ghe',
                'thanhToan'
            ])->findOrFail($bookingId);
            
            // Kiểm tra quyền
            $user = Auth::user();
            if (!$user || !in_array(optional($user->vaiTro)->ten, ['admin', 'staff'])) {
                abort(403, 'Bạn không có quyền truy cập.');
            }
            
            // Kiểm tra đã thanh toán chưa
            if ($booking->trang_thai == 1) {
                return redirect()->route('admin.box-office.tickets.print', $bookingId)
                    ->with('info', 'Vé đã được thanh toán.');
            }
            
            // Tạo mã QR fake (mã đơn hàng + timestamp)
            $qrCode = 'QR' . str_pad($bookingId, 6, '0', STR_PAD_LEFT) . '-' . time();
            
            return view('admin.box-office.qr-payment', compact('booking', 'qrCode'));
        } catch (\Exception $e) {
            Log::error('BoxOffice showQrPayment error: ' . $e->getMessage());
            return redirect()->route('admin.box-office.index')
                ->with('error', 'Không tìm thấy đơn hàng.');
        }
    }

    /**
     * Bước 9: Xác nhận thanh toán (nếu cần)
     * POST /admin/box-office/payments/confirm
     */
    public function confirmPayment(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:dat_ve,id',
        ]);

        try {
            DB::beginTransaction();
            
            $booking = DatVe::with(['chiTietFood', 'chiTietDatVe'])->findOrFail($request->booking_id);
            
            if ($booking->trang_thai == 1) {
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Vé đã được thanh toán',
                ]);
            }
            
            // Cập nhật trạng thái booking
            $booking->update(['trang_thai' => 1]); // SOLD
            
            // Cập nhật thanh toán
            $payment = ThanhToan::where('id_dat_ve', $booking->id)->first();
            if ($payment) {
                $payment->update([
                    'trang_thai' => 1,
                    'thoi_gian' => now(),
                ]);
            }
            
            // Trừ kho đồ ăn (nếu chưa trừ)
            foreach ($booking->chiTietFood as $foodDetail) {
                Food::where('id', $foodDetail->food_id)
                    ->decrement('stock', $foodDetail->quantity);
            }
            
            // Cập nhật ShowtimeSeat
            if (DB::getSchemaBuilder()->hasTable('suat_chieu_ghe')) {
                $seatIds = $booking->chiTietDatVe->pluck('id_ghe')->toArray();
                $showtime = $booking->suatChieu;
                $thoiGianKetThuc = $showtime ? $showtime->thoi_gian_ket_thuc : now()->addDays(1);
                
                foreach ($seatIds as $seatId) {
                    // Kiểm tra record đã tồn tại chưa
                    $existing = ShowtimeSeat::where('id_suat_chieu', $booking->id_suat_chieu)
                        ->where('id_ghe', $seatId)
                        ->first();
                    
                    if ($existing) {
                        // Nếu đã tồn tại, chỉ cập nhật trạng thái (không động đến thoi_gian_het_han)
                        $existing->update(['trang_thai' => 'booked']);
                    } else {
                        // Nếu chưa tồn tại, tạo mới với thoi_gian_het_han hợp lệ
                        ShowtimeSeat::create([
                            'id_suat_chieu' => $booking->id_suat_chieu,
                            'id_ghe' => $seatId,
                            'trang_thai' => 'booked',
                            'thoi_gian_giu' => now(),
                            'thoi_gian_het_han' => $thoiGianKetThuc,
                            'gia_giu' => 0,
                        ]);
                    }
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Xác nhận thanh toán thành công',
                'redirect_url' => route('admin.box-office.tickets.print', $booking->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('BoxOffice confirmPayment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xác nhận thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bước 10: In vé
     * GET /admin/box-office/tickets/{bookingId}/print
     */
    public function printTicket($bookingId)
    {
        $booking = DatVe::with([
            'nguoiDung',
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe',
            'chiTietCombo.combo',
            'chiTietFood.food',
        ])->findOrFail($bookingId);
        
        return view('admin.box-office.ticket-print', compact('booking'));
    }

    /**
     * Bước 10: Gửi vé (SMS/Email)
     * POST /admin/box-office/tickets/{bookingId}/send
     */
    public function sendTicket(Request $request, $bookingId)
    {
        $request->validate([
            'method' => 'required|in:sms,email',
        ]);

        try {
            $booking = DatVe::with(['nguoiDung', 'suatChieu.phim'])->findOrFail($bookingId);
            $customer = $booking->nguoiDung;
            
            if ($request->method === 'email' && $customer->email) {
                Mail::to($customer->email)->send(new \App\Mail\TicketMail($booking));
                return response()->json([
                    'success' => true,
                    'message' => 'Đã gửi vé qua email',
                ]);
            } elseif ($request->method === 'sms' && $customer->sdt) {
                // TODO: Implement SMS sending
                return response()->json([
                    'success' => true,
                    'message' => 'Đã gửi vé qua SMS',
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Không có thông tin liên lạc của khách hàng',
            ], 400);
        } catch (\Exception $e) {
            Log::error('BoxOffice sendTicket error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi gửi vé'
            ], 500);
        }
    }

    // --- Helper Methods ---
    
    private function canLockShowtime($showtime)
    {
        $minutesUntilStart = now()->diffInMinutes($showtime->thoi_gian_bat_dau, false);
        return $minutesUntilStart < 30; // Còn < 30 phút
    }
    
    private function releaseExpiredHolds($showtime)
    {
        if (DB::getSchemaBuilder()->hasTable('suat_chieu_ghe')) {
            ShowtimeSeat::where('id_suat_chieu', $showtime->id)
                ->where('trang_thai', 'holding')
                ->where('thoi_gian_het_han', '<=', now())
                ->update([
                    'trang_thai' => 'available',
                    'thoi_gian_het_han' => null,
                ]);
        }
    }
    
    private function mapPaymentMethod($method)
    {
        $map = [
            'cash' => 2,      // Tiền mặt
            'transfer' => 3,   // Chuyển khoản
            'e_wallet' => 4,  // Ví điện tử
            'pos' => 5,        // POS
        ];
        return $map[$method] ?? 2;
    }
    
    private function getPaymentMethodName($method)
    {
        $map = [
            'cash' => 'Tiền mặt',
            'transfer' => 'Chuyển khoản',
            'e_wallet' => 'Ví điện tử',
            'pos' => 'POS',
        ];
        return $map[$method] ?? 'Tiền mặt';
    }
}
