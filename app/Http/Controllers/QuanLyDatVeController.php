<?php

namespace App\Http\Controllers;

use App\Models\DatVe;
use App\Models\Combo;
use App\Models\Ghe;
use App\Models\SuatChieu;
use App\Models\KhuyenMai;
use App\Models\HangThanhVien;
use App\Models\DiemThanhVien;
use App\Models\ChiTietDatVe;
use App\Models\ChiTietCombo;
use App\Models\Phim;
use App\Models\NguoiDung;
use App\Models\ThanhToan;
use App\Mail\TicketMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Models\ShowtimeSeat; // Model mapping to 'tam_giu_ghe'
use Carbon\Carbon;

class QuanLyDatVeController extends Controller
{
    private const BASE_TICKET_PRICE = 100000;

    public function index(Request $request)
    {
        // ... (Logic index giữ nguyên)
        $expiredQuery = DatVe::with('thanhToan')->where('trang_thai', 0);

        if (Schema::hasColumn('dat_ve', 'phuong_thuc_thanh_toan')) {
            $expiredQuery->where('phuong_thuc_thanh_toan', 2);
        }

        $expired = $expiredQuery->where(function ($query) {
            if (Schema::hasColumn('dat_ve', 'expires_at')) {
                $query->where('expires_at', '<=', now())
                    ->orWhere(function ($q) {
                        $q->whereNull('expires_at')
                            ->where('created_at', '<=', now()->subMinutes(5));
                    });
            } else {
                $query->where('created_at', '<=', now()->subMinutes(5));
            }
        })->get();

        foreach ($expired as $bk) {
            try {
                DB::transaction(function () use ($bk) {
                    $bk->update(['trang_thai' => 2]);
                    $this->releaseSeats($bk);
                });
            } catch (\Throwable $e) {
                Log::error('Auto-cancel error: ' . $e->getMessage());
                try {
                    $bk->update(['trang_thai' => 2]);
                } catch (\Throwable $e2) {}
            }
        }

        $query = DatVe::with([
            'nguoiDung',
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe',
            'chiTietDatVe.ghe',
            'chiTietCombo.combo',
            'thanhToan',
            'khuyenMai'
        ])->orderBy('created_at', 'desc');

        $this->applyFilters($query, $request);
        $bookings = $query->paginate(10)->appends($request->query());

        $stats = [
            'totalBookings' => DatVe::count(),
            'pendingCount' => DatVe::where('trang_thai', 0)->count(),
            'confirmedCount' => DatVe::where('trang_thai', 1)->count(),
            'canceledCount' => DatVe::where('trang_thai', 2)->count(),
            'requestCancelCount' => DatVe::where('trang_thai', 3)->count(),
            'expiredCount' => DatVe::where('trang_thai', '!=', 2)
                ->whereHas('suatChieu', fn($q) => $q->where('thoi_gian_bat_dau', '<', now()))
                ->count(),
            'revenueToday' => DatVe::where('trang_thai', 1)
                ->whereDate('created_at', now()->toDateString())
                ->get()
                ->sum(fn($b) => (float) ($b->tong_tien ?? $b->tong_tien_hien_thi ?? 0)),
        ];

        return view('admin.bookings.index', array_merge(['bookings' => $bookings], $stats));
    }

    public function myBookings(Request $request)
    {
        $this->authorizeAction('xem vé của mình');
        $userId = Auth::id();
        if (!$userId) return redirect()->route('admin.dashboard')->with('error', 'Bạn cần đăng nhập.');

        // Logic check expired cho user (giữ nguyên)
        $expiredBookings = DatVe::where('id_nguoi_dung', $userId)
            ->where('trang_thai', 0)
            ->where(function ($query) {
                if (Schema::hasColumn('dat_ve', 'expires_at')) {
                    $query->where(function ($q) {
                        $q->whereNotNull('expires_at')->where('expires_at', '<=', now());
                    })->orWhere(function ($q) {
                        $q->whereNull('expires_at')->where('created_at', '<=', now()->subMinutes(15));
                    });
                } else {
                    $query->where('created_at', '<=', now()->subMinutes(15));
                }
            })->get();

        foreach ($expiredBookings as $expiredBooking) {
            try {
                DB::transaction(function () use ($expiredBooking) {
                    $expiredBooking->update(['trang_thai' => 2]);
                    $this->releaseSeats($expiredBooking);
                });
            } catch (\Throwable $e) {}
        }

        $query = DatVe::with([
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe',
            'chiTietDatVe.ghe',
            'chiTietCombo.combo',
            'thanhToan',
            'khuyenMai'
        ])->where('id_nguoi_dung', $userId)->orderBy('created_at', 'desc');

        if ($request->filled('status')) $query->where('trang_thai', $request->status);
        if ($request->filled('phim')) $query->whereHas('suatChieu.phim', fn($q) => $q->where('ten_phim', 'like', '%' . $request->phim . '%'));
        if ($request->filled('booking_date')) $query->whereDate('created_at', $request->booking_date);
        if ($request->filled('show_date')) $query->whereHas('suatChieu', fn($q) => $q->whereDate('thoi_gian_bat_dau', $request->show_date));

        $bookings = $query->paginate(10)->appends($request->query());

        $stats = [
            'totalBookings' => DatVe::where('id_nguoi_dung', $userId)->count(),
            'pendingCount' => DatVe::where('id_nguoi_dung', $userId)->where('trang_thai', 0)->count(),
            'confirmedCount' => DatVe::where('id_nguoi_dung', $userId)->where('trang_thai', 1)->count(),
            'canceledCount' => DatVe::where('id_nguoi_dung', $userId)->where('trang_thai', 2)->count(),
        ];

        return view('admin.bookings.my-bookings', array_merge(['bookings' => $bookings], $stats));
    }

    public function show($id)
    {
        $booking = DatVe::with([
            'nguoiDung.diemThanhVien',
            'nguoiDung.hangThanhVien',
            'nguoiDung',
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe',
            'chiTietDatVe.ghe',
            'chiTietCombo.combo',
            'thanhToan',
            'khuyenMai',
        ])->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
    }

    public function cancel($id)
    {
        $this->authorizeAction('hủy vé');
        $booking = DatVe::with(['chiTietDatVe.ghe'])->findOrFail($id);

        if (!in_array($booking->trang_thai, [0, 3])) {
            return back()->with('error', 'Chỉ có thể hủy vé đang chờ hoặc có yêu cầu hủy.');
        }

        try {
            DB::transaction(function () use ($booking) {
                $booking->update(['trang_thai' => 2]);
                $this->releaseSeats($booking);
            });
            
            $this->updateMemberStats($booking->id_nguoi_dung);

            return back()->with('success', 'Đã hủy vé và giải phóng ghế.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi hủy vé: ' . $e->getMessage());
        }
    }

    public function confirm($id)
    {
        $this->authorizeAction('xác nhận vé');
        $booking = DatVe::with('thanhToan')->findOrFail($id);
        if ($booking->trang_thai == 2) return back()->with('error', 'Vé này đã hủy.');
        if ($booking->trang_thai != 0) return back()->with('error', 'Chỉ xác nhận vé đang chờ.');

        try {
            DB::transaction(function () use ($booking) {
                $booking->update(['trang_thai' => 1]);
                if ($booking->thanhToan) {
                    $booking->thanhToan()->update(['trang_thai' => 1, 'thoi_gian' => now()]);
                }
            });
            $this->updateMemberStats($booking->id_nguoi_dung);
            $this->sendTicketEmail($booking);

            return redirect()->route('admin.bookings.index')->with('success', 'Đã xác nhận vé và gửi email.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAction('cập nhật vé');
        $booking = DatVe::with('thanhToan')->findOrFail($id);

        $request->validate([
            'ghe_ids' => 'nullable|string',
            'suat_chieu_id' => 'nullable|integer|exists:suat_chieu,id',
            'trang_thai' => 'nullable|in:0,1,2,3',
            'combo_ids' => 'nullable|array',
            'combo_quantities' => 'nullable|array'
        ]);

        try {
            DB::transaction(function () use ($request, $booking) {
                if ($request->filled('suat_chieu_id')) {
                    $booking->id_suat_chieu = (int) $request->input('suat_chieu_id');
                }

                if ($request->has('ghe_ids')) {
                    $this->releaseSeats($booking);
                    $booking->chiTietDatVe()->delete();
                    $seatIds = array_filter(array_unique(explode(',', $request->input('ghe_ids'))), 'is_numeric');
                    foreach ($seatIds as $gheId) {
                        $ghe = Ghe::with('loaiGhe')->find($gheId);
                        if (!$ghe || ($ghe->trang_thai != 1)) continue;
                        $gia = ($ghe->loaiGhe->he_so_gia ?? 1) * self::BASE_TICKET_PRICE;
                        $booking->chiTietDatVe()->create(['id_ghe' => $gheId, 'gia' => $gia]);
                        $ghe->update(['trang_thai' => 0]);
                    }
                }

                if ($request->has('combo_ids')) {
                    $booking->chiTietCombo()->delete();
                    $comboIds = $request->input('combo_ids', []);
                    $quantities = $request->input('combo_quantities', []);
                    $validCombos = Combo::whereIn('id', $comboIds)->where('trang_thai', 1)->get();
                    foreach ($validCombos as $cb) {
                        $qty = max(1, (int)($quantities[$cb->id] ?? 1));
                        $booking->chiTietCombo()->create([
                            'id_combo' => $cb->id,
                            'so_luong' => $qty,
                            'gia_ap_dung' => $cb->gia
                        ]);
                    }
                }

                if ($request->has('ghi_chu_noi_bo') && Schema::hasColumn('dat_ve', 'ghi_chu_noi_bo')) {
                    $booking->ghi_chu_noi_bo = $request->input('ghi_chu_noi_bo');
                }

                $this->recomputeBookingTotal($booking);

                if ($request->has('trang_thai') && $booking->trang_thai != $request->trang_thai) {
                    $newStatus = (int)$request->trang_thai;
                    if ($newStatus == 2) $this->releaseSeats($booking);
                    $booking->trang_thai = $newStatus;
                    if ($newStatus == 1 && $booking->thanhToan) {
                        $booking->thanhToan()->update(['trang_thai' => 1, 'thoi_gian' => now()]);
                    }
                }
                $booking->save();
            });

            if ($booking->id_nguoi_dung) $this->updateMemberStats($booking->id_nguoi_dung);
            return redirect()->route('admin.bookings.index')->with('success', 'Cập nhật thành công.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $this->authorizeAction('tạo đặt vé');

        $request->validate([
            'showtime_id' => 'required|integer|exists:suat_chieu,id',
            'seat_ids' => 'required|array|min:1',
            'seat_ids.*' => 'integer|exists:ghe,id',
            'combo_ids' => 'nullable|array',
            'combo_quantities' => 'nullable|array',
            'promotion_id' => 'nullable|integer|exists:khuyen_mai,id',
            'payment_method' => 'required|in:online,offline,cash,transfer,card',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $showtime = SuatChieu::with(['phim', 'phongChieu'])->findOrFail($request->showtime_id);
            if ($showtime->trang_thai != 1) throw new \Exception('Suất chiếu không khả dụng.');

            $userId = Auth::id();
            if (!$userId) throw new \Exception('Bạn cần đăng nhập để đặt vé.');

            // Check ghế trùng
            $selectedSeatIds = array_map('intval', $request->seat_ids);
            $conflictedSeats = [];
            foreach ($selectedSeatIds as $seatId) {
                // Kiểm tra trong bảng đặt vé
                $isTaken = ChiTietDatVe::whereHas('datVe', function ($query) use ($showtime) {
                    $query->where('id_suat_chieu', $showtime->id)->where('trang_thai', '!=', 2);
                })->where('id_ghe', $seatId)->exists();
                
                if ($isTaken) {
                    $seat = Ghe::find($seatId);
                    $conflictedSeats[] = $seat->so_ghe ?? "ID: {$seatId}";
                }
            }
            if (!empty($conflictedSeats)) throw new \Exception('Ghế đã được bán: ' . implode(', ', $conflictedSeats));

            // Tính tiền ghế
            $tongGhe = 0;
            $seatDetails = [];
            foreach ($selectedSeatIds as $seatId) {
                $ghe = Ghe::with('loaiGhe')->findOrFail($seatId);
                if ($ghe->id_phong != $showtime->id_phong) throw new \Exception("Ghế {$ghe->so_ghe} sai phòng.");
                $gia = (float)(($ghe->loaiGhe->he_so_gia ?? 1) * self::BASE_TICKET_PRICE);
                $tongGhe += $gia;
                $seatDetails[] = ['id' => $ghe->id, 'gia' => $gia];
            }

            // Tính tiền combo
            $tongCombo = 0;
            $comboDetails = [];
            $comboQuantities = $request->combo_quantities ?? [];
            if (is_array($comboQuantities)) {
                foreach ($comboQuantities as $comboId => $qty) {
                    $qty = (int)$qty;
                    if ($qty > 0) {
                        $combo = Combo::find($comboId);
                        if ($combo && $combo->trang_thai == 1) {
                            $giaCombo = (float)$combo->gia;
                            $tongCombo += ($giaCombo * $qty);
                            $comboDetails[] = ['id' => $combo->id, 'so_luong' => $qty, 'gia' => $giaCombo];
                        }
                    }
                }
            }

            // Khuyến mãi
            $discount = 0;
            $promotionId = null;
            if ($request->promotion_id) {
                $promo = KhuyenMai::find($request->promotion_id);
                if ($promo && $promo->trang_thai == 1 && $promo->ngay_bat_dau <= now() && $promo->ngay_ket_thuc >= now()) {
                    $promotionId = $promo->id;
                    $subtotal = $tongGhe + $tongCombo;
                    $discount = $promo->loai_giam === 'phantram'
                        ? round($subtotal * ((float)$promo->gia_tri_giam / 100))
                        : (float)$promo->gia_tri_giam;
                }
            }

            $tongTien = max(0, ($tongGhe + $tongCombo) - $discount);

            // Xử lý phương thức thanh toán
            $paymentMethod = $request->payment_method;
            if ($paymentMethod === 'online') {
                $phuongThucDB = 1;
                $trangThai = 0; // Chờ thanh toán
                $expiresAt = Carbon::now()->addMinutes(15);
                $isPaid = false;
            } else {
                $phuongThucDB = 2;
                $trangThai = 1; // Đã thanh toán
                $expiresAt = null;
                $isPaid = true;
            }

            $paymentMethodName = match ($paymentMethod) {
                'online' => 'VNPAY',
                'cash' => 'Tiền mặt',
                'transfer' => 'Chuyển khoản',
                'card' => 'Thẻ ngân hàng',
                default => 'Offline'
            };

            $booking = DatVe::create([
                'id_nguoi_dung' => $userId,
                'id_suat_chieu' => $showtime->id,
                'trang_thai' => $trangThai,
                'tong_tien' => $tongTien,
                'id_khuyen_mai' => $promotionId,
                'phuong_thuc_thanh_toan' => $phuongThucDB,
                'expires_at' => $expiresAt,
                'ghi_chu_noi_bo' => $request->notes,
            ]);

            foreach ($seatDetails as $detail) {
                ChiTietDatVe::create(['id_dat_ve' => $booking->id, 'id_ghe' => $detail['id'], 'gia' => $detail['gia']]);
            }
            foreach ($comboDetails as $detail) {
                ChiTietCombo::create(['id_dat_ve' => $booking->id, 'id_combo' => $detail['id'], 'so_luong' => $detail['so_luong'], 'gia_ap_dung' => $detail['gia']]);
            }

            ThanhToan::create([
                'id_dat_ve' => $booking->id,
                'so_tien' => $tongTien,
                'phuong_thuc' => $paymentMethodName,
                'trang_thai' => $isPaid ? 1 : 0,
                'thoi_gian' => $isPaid ? now() : null,
            ]);

            // [FIXED] Logic giữ chỗ trong bảng tam_giu_ghe
            // Lỗi cũ: thiếu thoi_gian_giu, thoi_gian_het_han và sai tên cột
            if (Schema::hasTable('tam_giu_ghe')) {
                foreach ($selectedSeatIds as $seatId) {
                    // Nếu đã thanh toán thì trạng thái là 'da_ban' (hoặc 'booked'), 
                    // nếu chưa thì là 'dang_giu'
                    $status = $isPaid ? 'da_ban' : 'dang_giu';
                    
                    ShowtimeSeat::updateOrCreate(
                        ['id_suat_chieu' => $showtime->id, 'id_ghe' => $seatId],
                        [
                            'trang_thai' => $status,
                            'thoi_gian_giu' => now(), // [FIX] Required column
                            'thoi_gian_het_han' => now()->addMinutes(15), // [FIX] Required column
                            'id_nguoi_dung' => $userId,
                            'session_id' => session()->getId(),
                        ]
                    );
                }
            }

            if ($isPaid && $userId) {
                $this->updateMemberStats($userId);
            }

            DB::commit();

            if ($paymentMethod === 'online') {
                $vnpUrl = app(\App\Http\Controllers\PaymentController::class)->createVnpayUrl($booking->id, $tongTien);
                return redirect()->away($vnpUrl);
            }

            return redirect()->route('admin.bookings.index')
                ->with('success', "Xuất vé thành công! Mã: #{$booking->id}. Thu: " . number_format($tongTien) . "đ");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Staff booking error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    // Các hàm phụ trợ
    public function seatsByShowtime($suatChieuId, Request $request)
    {
        try {
            $suat = SuatChieu::with('phongChieu')->findOrFail($suatChieuId);
            $seatQuery = Ghe::where('id_phong', $suat->id_phong)->orderBy('so_hang');
            if (Schema::hasColumn('ghe', 'pos_x')) {
                $seatQuery->orderByRaw('COALESCE(pos_x, 0) ASC');
            }
            $seats = $seatQuery->orderBy('so_ghe')->get();

            $bookedSeatIds = [];
            try {
                $bookedQuery = DB::table('chi_tiet_dat_ve as c')
                    ->join('dat_ve as d', 'd.id', '=', 'c.id_dat_ve')
                    ->where('d.id_suat_chieu', $suatChieuId)
                    ->where('d.trang_thai', '!=', 2);

                if ($request->filled('exclude_booking_id')) {
                    $bookedQuery->where('d.id', '!=', $request->exclude_booking_id);
                }
                $bookedSeatIds = $bookedQuery->pluck('c.id_ghe')->toArray();
            } catch (\Throwable $e) {
                $bookedSeatIds = [];
            }

            return response()->json([
                'room' => [
                    'id' => $suat->id_phong,
                    'ten_phong' => $suat->phongChieu->ten_phong ?? 'N/A',
                ],
                'seats' => $seats->map(function ($g) use ($bookedSeatIds) {
                    $label = (string) $g->so_ghe;
                    $num = (int) preg_replace('/\D+/', '', $label);
                    $seatType = (int) ($g->id_loai ?? 1);
                    if ($seatType === 0) $seatType = 1;

                    return [
                        'id' => $g->id,
                        'label' => $label,
                        'row' => (int) $g->so_hang,
                        'col' => is_null($g->pos_x) ? $num : (int) $g->pos_x,
                        'type' => $seatType,
                        'booked' => in_array($g->id, $bookedSeatIds),
                    ];
                }),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Internal error loading seats'], 500);
        }
    }

    public function getShowtimesForStaff(Request $request, $movieId)
    {
        $request->validate(['date' => 'required|date']);
        $movie = Phim::findOrFail($movieId);
        $date = Carbon::parse($request->date)->format('Y-m-d');
        $now = now();
        $showtimes = SuatChieu::where('id_phim', $movieId)->where('trang_thai', 1)
            ->whereDate('thoi_gian_bat_dau', $date)->where('thoi_gian_ket_thuc', '>', $now)
            ->whereHas('phongChieu', fn($q) => $q->where('trang_thai', 1))
            ->with(['phongChieu'])->orderBy('thoi_gian_bat_dau')->get()
            ->map(function ($showtime) {
                $totalSeats = $showtime->phongChieu->seats()->where('trang_thai', 1)->count();
                $bookedSeats = DB::table('chi_tiet_dat_ve')->join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                    ->where('dat_ve.id_suat_chieu', $showtime->id)->where('dat_ve.trang_thai', 1)->count();
                return [
                    'id' => $showtime->id,
                    'time' => $showtime->thoi_gian_bat_dau->format('H:i'),
                    'end_time' => $showtime->thoi_gian_ket_thuc->format('H:i'),
                    'room_name' => $showtime->phongChieu->ten_phong ?? 'Phòng chiếu',
                    'available_seats' => max(0, $totalSeats - $bookedSeats),
                ];
            });
        return response()->json(['success' => true, 'data' => $showtimes]);
    }

    public function create()
    {
        $this->authorizeAction('đặt vé');
        $user = Auth::user();
        if (!$user || !in_array(optional($user->vaiTro)->ten, ['admin', 'staff'])) {
            abort(403, 'Bạn không có quyền đặt vé.');
        }
        $movies = Phim::where('trang_thai', 'dang_chieu')->orderBy('ngay_khoi_chieu', 'desc')->get();
        $combos = Combo::where('trang_thai', 1)->get();
        $promotions = KhuyenMai::where('trang_thai', 1)
            ->where('ngay_bat_dau', '<=', now())
            ->where('ngay_ket_thuc', '>=', now())
            ->get();
        return view('admin.bookings.create', compact('movies', 'combos', 'promotions', 'user'));
    }

    private function authorizeAction($actionName)
    {
        $role = optional(Auth::user()->vaiTro)->ten;
        if (!in_array($role, ['admin', 'staff'])) {
            abort(403, "Bạn không có quyền $actionName.");
        }
    }

    private function applyFilters($query, $request)
    {
        if ($request->filled('status')) $query->where('trang_thai', $request->status);
        if ($request->filled('phim')) $query->whereHas('suatChieu.phim', fn($q) => $q->where('ten_phim', 'like', '%' . $request->phim . '%'));
        if ($request->filled('booking_date')) $query->whereDate('created_at', $request->booking_date);
        if ($request->filled('show_date')) $query->whereHas('suatChieu', fn($q) => $q->whereDate('thoi_gian_bat_dau', $request->show_date));
    }

    private function releaseSeats(DatVe $booking): void
    {
        try {
            if (Schema::hasTable('tam_giu_ghe')) { // [FIX] Tên bảng trong SQL dump
                foreach ($booking->chiTietDatVe as $detail) {
                    if ($detail->id_ghe) {
                        // [FIX] Sử dụng delete() để giải phóng ghế khỏi bảng tạm giữ
                        // Hoặc cập nhật trạng thái nếu muốn giữ lịch sử
                        ShowtimeSeat::where('id_suat_chieu', $booking->id_suat_chieu)
                            ->where('id_ghe', $detail->id_ghe)
                            ->delete(); 
                    }
                }
            }
            foreach ($booking->chiTietDatVe as $detail) {
                if ($detail->ghe) $detail->ghe->update(['trang_thai' => 1]);
            }
        } catch (\Throwable $e) {
            Log::error('Error releasing seats: ' . $e->getMessage());
        }
    }

    private function sendTicketEmail(DatVe $booking): void
    {
        $email = $booking->email ?? ($booking->nguoiDung->email ?? null);
        if (!$email) return;
        try {
            Mail::to($email)->send(new TicketMail($booking));
        } catch (\Throwable $e) {
            Log::error("Mail error: " . $e->getMessage());
        }
    }

    private function updateMemberStats(?int $userId): void
    {
        if (!$userId) return;
        $bookings = DatVe::with(['chiTietDatVe', 'chiTietCombo'])
            ->where('id_nguoi_dung', $userId)
            ->where('trang_thai', 1)
            ->get();

        $totalSpent = 0.0;
        foreach ($bookings as $bk) {
            $seatSum = $bk->chiTietDatVe->sum(fn($item) => (float)($item->gia ?? 0));
            $comboSum = $bk->chiTietCombo->sum(function($c) {
                return (float)($c->gia_ap_dung ?? 0) * (int)($c->so_luong ?? 1);
            });
            $totalSpent += ($seatSum + $comboSum);
        }

        if (Schema::hasColumn('nguoi_dung', 'tong_chi_tieu')) {
            NguoiDung::where('id', $userId)->update(['tong_chi_tieu' => $totalSpent]);
        }

        $points = (int) floor((float)$totalSpent / 1000);
        DiemThanhVien::updateOrCreate(['id_nguoi_dung' => $userId], ['tong_diem' => $points]);

        $tier = match (true) {
            $totalSpent >= 4000000 => 'Kim cương',
            $totalSpent >= 2000000 => 'Vàng',
            $totalSpent >= 1000000 => 'Bạc',
            $totalSpent >= 100000  => 'Đồng',
            default => null,
        };

        if ($tier) {
            HangThanhVien::updateOrCreate(['id_nguoi_dung' => $userId], ['ten_hang' => $tier]);
        } else {
            HangThanhVien::where('id_nguoi_dung', $userId)->delete();
        }
    }

    private function recomputeBookingTotal(DatVe $booking): void
    {
        $booking->loadMissing(['chiTietDatVe', 'chiTietCombo', 'khuyenMai']);
        $seatTotal = $booking->chiTietDatVe->sum(fn($item) => (float)($item->gia ?? 0));
        $comboTotal = $booking->chiTietCombo->sum(function ($c) {
            return (float)($c->gia_ap_dung ?? 0) * (int)($c->so_luong ?? 1);
        });
        $subtotal = $seatTotal + $comboTotal;
        $discount = 0;
        if ($booking->khuyenMai) {
            $discount = $booking->khuyenMai->loai_giam === 'phantram'
                ? round($subtotal * ($booking->khuyenMai->gia_tri_giam / 100))
                : (float)$booking->khuyenMai->gia_tri_giam;
        }
        $final = max(0, $subtotal - $discount);
        DatVe::where('id', $booking->id)->update([
            'tong_tien' => $final,
            'tong_tien_hien_thi' => $final
        ]);
    }
}