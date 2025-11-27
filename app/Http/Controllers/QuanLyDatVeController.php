<?php

namespace App\Http\Controllers;

use App\Models\DatVe;
use App\Models\Combo;
use App\Models\Ghe;
use App\Models\SuatChieu;
use App\Models\KhuyenMai;
use App\Models\HangThanhVien;
use App\Models\DiemThanhVien;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
<<<<<<< HEAD
use Illuminate\Support\Facades\Log;

class QuanLyDatVeController extends Controller
{
    private const BASE_TICKET_PRICE = 100000;

    // --- 1. LOGIC DANH SÁCH VÀ TỰ ĐỘNG HỦY ---
=======
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuanLyDatVeController extends Controller
{
    /**
     * Hiển thị danh sách đặt vé
     */
>>>>>>> origin/khanhPH52932
    public function index(Request $request)
    {
        // Lazy Check: Tự động hủy vé quá hạn 5 phút
        $expiredBookings = DatVe::with('thanhToan')
            ->where('trang_thai', 0)
            ->where('created_at', '<', now()->subMinutes(5))
            ->get();

        if ($expiredBookings->count() > 0) {
            foreach ($expiredBookings as $bk) {
                $paymentMethod = optional($bk->thanhToan)->phuong_thuc;
                // Chỉ hủy vé Tiền mặt hoặc chưa chọn PTTT
                if ($paymentMethod == 'Tiền mặt' || empty($paymentMethod)) {
                    try {
                        DB::transaction(function () use ($bk) {
                            $bk->update(['trang_thai' => 2]); // Hủy vé
                            $this->releaseSeats($bk); // Nhả ghế
                        });
                    } catch (\Exception $e) {
                        Log::error("Lỗi hủy vé tự động ID {$bk->id}: " . $e->getMessage());
                        // Update sang 2 để tránh loop, nhưng ghi chú lỗi
                        $bk->update(['trang_thai' => 2, 'ghi_chu_noi_bo' => 'Lỗi hệ thống khi hủy tự động']);
                    }
                }
            }
        }

        $query = DatVe::with(['nguoiDung', 'suatChieu.phim', 'suatChieu.phongChieu', 'chiTietDatVe.ghe', 'chiTietCombo.combo', 'thanhToan', 'khuyenMai'])
            ->orderBy('created_at', 'desc');

<<<<<<< HEAD
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
=======
        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        }

        // Lọc theo tên phim
        if ($request->filled('phim')) {
            $query->whereHas('suatChieu.phim', function ($q) use ($request) {
                $q->where('ten_phim', 'like', '%' . $request->phim . '%');
            });
        }

        // Lọc theo người dùng
        if ($request->filled('nguoi_dung')) {
            $query->whereHas('nguoiDung', function ($q) use ($request) {
                $q->where('ho_ten', 'like', '%' . $request->nguoi_dung . '%');
            });
        }

        $bookings = $query->paginate(10)->appends($request->query());

        // Thống kê nhanh
        $totalBookings = DatVe::count();
        $pendingCount = DatVe::where('trang_thai', 0)->count();
        $confirmedCount = DatVe::where('trang_thai', 1)->count();
        $canceledCount = DatVe::where('trang_thai', 2)->count();
        $requestCancelCount = DatVe::where('trang_thai', 3)->count();

        $todayConfirmed = DatVe::where('trang_thai', 1)
            ->whereDate('created_at', now()->toDateString())
            ->get();

        $revenueToday = $todayConfirmed->sum(fn($b) => $b->tong_tien_hien_thi ?? $b->tong_tien ?? 0);

        return view('admin.bookings.index', compact(
            'bookings', 'totalBookings', 'pendingCount', 'confirmedCount',
            'canceledCount', 'requestCancelCount', 'revenueToday'
        ));
>>>>>>> origin/khanhPH52932
    }

    /**
     * Xem chi tiết đặt vé
     */
    public function show($id)
    {
        $booking = DatVe::with([
            'nguoiDung.diemThanhVien',
            'nguoiDung.hangThanhVien',
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe',
            'chiTietCombo.combo',
            'thanhToan',
            'khuyenMai'
        ])->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
    }

<<<<<<< HEAD
    // --- 2. LOGIC HỦY VÉ ---
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
            // Trừ điểm (thực tế là tính lại tổng chi tiêu)
            $this->updateMemberStats($booking->id_nguoi_dung);

            return back()->with('success', 'Đã hủy vé và giải phóng ghế thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi hủy vé: ' . $e->getMessage());
        }
    }

    // --- 3. LOGIC XÁC NHẬN (ĐÃ CHUẨN) ---
    public function confirm($id)
    {
        $this->authorizeAction('xác nhận vé');
        $booking = DatVe::with('thanhToan')->findOrFail($id);

        if ($booking->trang_thai == 2) return back()->with('error', 'Vé này đã hủy.');
        if ($booking->trang_thai != 0) return back()->with('error', 'Chỉ xác nhận vé đang chờ.');

        try {
            DB::transaction(function () use ($booking) {
                // Vé -> 1
                $booking->update(['trang_thai' => 1]);

                // Thanh toán -> 1 (Thành công)
                if ($booking->thanhToan) {
                    $booking->thanhToan()->update(['trang_thai' => 1, 'thoi_gian' => now()]);
                }
            });

            $this->updateMemberStats($booking->id_nguoi_dung);
            return redirect()->route('admin.bookings.index')->with('success', 'Đã xác nhận vé thành công.');
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }

    // --- 4. LOGIC CHỈNH SỬA (ĐÃ FIX THÊM CẬP NHẬT THANH TOÁN) ---
    public function edit($id)
    {
        $this->authorizeAction('chỉnh sửa vé');
        $booking = DatVe::with(['chiTietDatVe', 'chiTietCombo', 'suatChieu', 'khuyenMai'])->findOrFail($id);

        return view('admin.bookings.edit', [
            'booking' => $booking,
            'combos' => Combo::where('trang_thai', 1)->get(),
            'selectedComboIds' => $booking->chiTietCombo->pluck('id_combo')->toArray(),
            'selectedComboQuantities' => $booking->chiTietCombo->pluck('so_luong', 'id_combo')->toArray(),
            'selectedGheIds' => $booking->chiTietDatVe->pluck('id_ghe')->toArray(),
        ]);
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
                // Cập nhật Suất chiếu
                if ($request->filled('suat_chieu_id')) {
                    $booking->id_suat_chieu = (int) $request->input('suat_chieu_id');
                }

                // Cập nhật Ghế
                $tongGhe = 0;
                if ($request->has('ghe_ids')) {
                    $this->releaseSeats($booking);
                    $booking->chiTietDatVe()->delete();

                    $seatIds = array_filter(array_unique(explode(',', $request->input('ghe_ids'))), 'is_numeric');
                    foreach ($seatIds as $gheId) {
                        $ghe = Ghe::with('loaiGhe')->find($gheId);
                        // Nếu ghế bận (và không phải của chính mình) thì bỏ qua
                        if (!$ghe || ($ghe->trang_thai != 1)) continue;

                        $gia = ($ghe->loaiGhe->he_so_gia ?? 1) * self::BASE_TICKET_PRICE;
                        $booking->chiTietDatVe()->create(['id_ghe' => $gheId, 'gia' => $gia]);
                        $ghe->update(['trang_thai' => 0]);
                        $tongGhe += $gia;
                    }
                } else {
                    $tongGhe = (float) $booking->chiTietDatVe()->sum('gia');
                }

                // Cập nhật Combo
                $tongCombo = 0;
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
                        $tongCombo += ($cb->gia * $qty);
                    }
                } else {
                    $tongCombo = (float) $booking->chiTietCombo()->sum(DB::raw('gia_ap_dung * COALESCE(so_luong,1)'));
                }

                // Ghi chú
                if ($request->has('ghi_chu_noi_bo') && Schema::hasColumn('dat_ve', 'ghi_chu_noi_bo')) {
                    $booking->ghi_chu_noi_bo = $request->input('ghi_chu_noi_bo');
                }

                // Khuyến mãi
                $discount = 0;
                if ($request->filled('ma_km')) {
                    $code = trim($request->input('ma_km'));
                    $promo = KhuyenMai::where('ma_km', $code)->where('trang_thai', 1)
                        ->whereDate('ngay_bat_dau', '<=', now())->whereDate('ngay_ket_thuc', '>=', now())->first();
                    if (!$promo) throw new \Exception('Mã khuyến mãi không hợp lệ.');
                    $discount = ($promo->loai_giam === 'phantram')
                        ? round(($tongGhe + $tongCombo) * ((float)$promo->gia_tri_giam / 100))
                        : (float)$promo->gia_tri_giam;
                    $booking->id_khuyen_mai = $promo->id;
                } elseif ($request->has('ma_km')) {
                    $booking->id_khuyen_mai = null;
                } elseif ($booking->id_khuyen_mai && $booking->khuyenMai) {
                    $promo = $booking->khuyenMai;
                    $discount = ($promo->loai_giam === 'phantram')
                        ? round(($tongGhe + $tongCombo) * ((float)$promo->gia_tri_giam / 100))
                        : (float)$promo->gia_tri_giam;
                }

                // Hạng thành viên
                $memberDiscount = 0;
                if ($booking->id_nguoi_dung) {
                    $tier = optional(HangThanhVien::where('id_nguoi_dung', $booking->id_nguoi_dung)->first())->ten_hang;
                    if ($tier) {
                        $normalized = mb_strtolower($tier);
                        $discounts = ['đồng' => 10000, 'bạc' => 15000, 'vàng' => 20000, 'kim cương' => 25000];
                        // Sửa lỗi key map tiếng việt có dấu
                        $discounts = array_change_key_case($discounts, CASE_LOWER);
                        $memberDiscount = $discounts[$normalized] ?? 0;
                    }
                }

                // Cập nhật Trạng thái
                if ($request->has('trang_thai') && $booking->trang_thai != $request->trang_thai) {
                    $newStatus = (int)$request->trang_thai;
                    if ($newStatus == 2) $this->releaseSeats($booking);
                    $booking->trang_thai = $newStatus;

                    // [FIX QUAN TRỌNG] Nếu sửa trạng thái thành 1 -> Update luôn thanh toán
                    if ($newStatus == 1 && $booking->thanhToan) {
                        $booking->thanhToan()->update(['trang_thai' => 1, 'thoi_gian' => now()]);
                    }
                }

                $booking->tong_tien = max(0, ($tongGhe + $tongCombo) - $discount - $memberDiscount);
                $booking->save();
            });

            if ($booking->id_nguoi_dung) $this->updateMemberStats($booking->id_nguoi_dung);

            return redirect()->route('admin.bookings.index')->with('success', 'Cập nhật thành công.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // --- 5. CÁC HÀM API (ĐÃ ĐIỀN LOGIC) ---
    public function availableShowtimes($id)
    {
        $booking = DatVe::with('suatChieu.phim')->findOrFail($id);
        $movieId = optional($booking->suatChieu)->id_phim;
        if (!$movieId) return response()->json([]);

        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movieId)->where('trang_thai', 1)
            ->where(function ($q) {
                $q->where('thoi_gian_bat_dau', '>', now()); // Chỉ lấy suất tương lai
            })
            ->orderBy('thoi_gian_bat_dau')->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'label' => ($s->thoi_gian_bat_dau ? \Carbon\Carbon::parse($s->thoi_gian_bat_dau)->format('H:i d/m') : '') . ' - ' . optional($s->phongChieu)->ten_phong,
=======
    /**
     * API: Lấy danh sách suất chiếu khả dụng (cùng phim, chưa bắt đầu)
     */
    public function availableShowtimes($id)
    {
        $booking = DatVe::with('suatChieu.phim')->findOrFail($id);
        $movieId = $booking->suatChieu?->id_phim;

        if (!$movieId) {
            return response()->json([]);
        }

        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movieId)
            ->where('trang_thai', 1)
            ->where('thoi_gian_bat_dau', '>=', now()->subMinutes(1))
            ->orderBy('thoi_gian_bat_dau')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'label' => $s->thoi_gian_bat_dau?->format('d/m/Y H:i') . ' • ' . ($s->phongChieu->ten_phong ?? 'N/A'),
>>>>>>> origin/khanhPH52932
                'current' => $s->id === $booking->id_suat_chieu,
            ]);

        return response()->json($showtimes);
    }

<<<<<<< HEAD
    public function seatsByShowtime($suatChieuId, Request $request)
    {
        $suat = SuatChieu::with('phongChieu')->findOrFail($suatChieuId);
        $seats = Ghe::where('id_phong', $suat->id_phong)->orderBy('so_hang')->orderBy('so_ghe')->get();

        // Lấy ghế đã đặt (trừ đơn hàng hiện tại)
        $bookedQuery = DB::table('chi_tiet_dat_ve as c')
            ->join('dat_ve as d', 'd.id', '=', 'c.id_dat_ve')
            ->where('d.id_suat_chieu', $suatChieuId)
            ->where('d.trang_thai', '!=', 2); // Không tính vé hủy

        if ($request->filled('exclude_booking_id')) {
            $bookedQuery->where('d.id', '!=', $request->exclude_booking_id);
=======
    /**
     * API: Lấy sơ đồ ghế theo suất chiếu (đánh dấu ghế đã đặt)
     */
    public function seatsByShowtime($suatChieuId, Request $request)
    {
        $suat = SuatChieu::with('phongChieu')->findOrFail($suatChieuId);
        $excludeBookingId = $request->query('exclude_booking_id');

        $seats = Ghe::where('id_phong', $suat->id_phong)
            ->orderBy('so_hang')
            ->orderBy('so_ghe')
            ->get(['id', 'so_ghe', 'so_hang', 'id_loai']);

        $bookedQuery = DB::table('chi_tiet_dat_ve as c')
            ->join('dat_ve as d', 'd.id', '=', 'c.id_dat_ve')
            ->where('d.id_suat_chieu', $suatChieuId)
            ->where('d.trang_thai', '!=', 2);

        if ($excludeBookingId) {
            $bookedQuery->where('d.id', '!=', $excludeBookingId);
>>>>>>> origin/khanhPH52932
        }

        $bookedSeatIds = $bookedQuery->pluck('c.id_ghe')->toArray();

        return response()->json([
<<<<<<< HEAD
            'room' => ['id' => $suat->id_phong],
            'seats' => $seats->map(fn($g) => [
                'id' => $g->id,
                'label' => $g->so_ghe,
                'booked' => in_array($g->id, $bookedSeatIds)
=======
            'room' => [
                'id' => $suat->id_phong,
                'ten_phong' => $suat->phongChieu->ten_phong ?? 'N/A',
            ],
            'seats' => $seats->map(fn($g) => [
                'id' => $g->id,
                'label' => $g->so_ghe,
                'row' => $g->so_hang,
                'type' => $g->id_loai,
                'booked' => in_array($g->id, $bookedSeatIds),
>>>>>>> origin/khanhPH52932
            ]),
        ]);
    }

<<<<<<< HEAD
    // --- HELPERS ---
    private function authorizeAction($actionName)
=======
    /**
     * Hủy vé (chỉ Admin)
     */
    public function cancel($id)
>>>>>>> origin/khanhPH52932
    {
        if (optional(Auth::user()->vaiTro)->ten !== 'admin') {
            abort(403, "Bạn không có quyền $actionName.");
        }
    }

<<<<<<< HEAD
    private function applyFilters($query, $request)
    {
        if ($request->filled('status')) {
            if ($request->status == 'expired') {
                $query->where('trang_thai', '!=', 2)
                    ->whereHas('suatChieu', fn($q) => $q->where('thoi_gian_bat_dau', '<', now()));
            } else {
                $query->where('trang_thai', $request->status);
            }
        }
        if ($request->filled('phim')) {
            $query->whereHas('suatChieu.phim', fn($q) => $q->where('ten_phim', 'like', '%' . $request->phim . '%'));
        }
        if ($request->filled('nguoi_dung')) {
            $query->whereHas('nguoiDung', fn($q) => $q->where('ho_ten', 'like', '%' . $request->nguoi_dung . '%'));
        }
    }
=======
        $booking = DatVe::findOrFail($id);

        if ($booking->trang_thai !== 0 || $booking->suatChieu->thoi_gian_bat_dau <= now()) {
            return back()->with('error', 'Chỉ có thể hủy vé đang chờ và suất chiếu chưa bắt đầu.');
        }

        DB::transaction(function () use ($booking) {
            $booking->trang_thai = 2;
            $booking->save();
>>>>>>> origin/khanhPH52932

    private function releaseSeats($booking)
    {
        try {
            foreach ($booking->chiTietDatVe as $detail) {
<<<<<<< HEAD
                if ($detail->ghe) $detail->ghe->update(['trang_thai' => 1]);
            }
        } catch (\Exception $e) {
            Log::error("Error releasing seats: " . $e->getMessage());
        }
    }

    private function updateMemberStats($userId)
=======
                $ghe = $detail->ghe;
                if ($ghe) {
                    $ghe->trang_thai = 1;
                    $ghe->save();
                }
            }

            if ($booking->id_nguoi_dung) {
                $this->recalcMembershipTier($booking->id_nguoi_dung);
                $this->recalcMemberPoints($booking->id_nguoi_dung);
            }
        });

        return redirect()->route('admin.bookings.index')->with('success', 'Vé đã được hủy thành công.');
    }

    /**
     * Form chỉnh sửa vé
     */
    public function edit($id)
>>>>>>> origin/khanhPH52932
    {
        if (!$userId) return;
        $stats = DB::table('dat_ve')->where('id_nguoi_dung', $userId)->where('trang_thai', 1)
            ->selectRaw('SUM(tong_tien) as total_spent')->first(); // Dùng tong_tien cho chính xác

        $totalSpent = $stats->total_spent ?? 0;
        $points = (int) floor($totalSpent / 1000);

<<<<<<< HEAD
        DiemThanhVien::updateOrCreate(['id_nguoi_dung' => $userId], ['tong_diem' => $points]);
=======
        $booking = DatVe::with(['chiTietDatVe.ghe', 'chiTietCombo.combo', 'suatChieu.phongChieu'])->findOrFail($id);
        $gheTrong = Ghe::where('id_phong', $booking->suatChieu->id_phong)
            ->where('trang_thai', 1)
            ->orWhereIn('id', $booking->chiTietDatVe->pluck('id_ghe'))
            ->get();

        $combos = Combo::where('trang_thai', 1)->get();
>>>>>>> origin/khanhPH52932

        $tierName = null;
        if ($totalSpent >= 1500000) $tierName = 'Kim cương';
        elseif ($totalSpent >= 1000000) $tierName = 'Vàng';
        elseif ($totalSpent >= 500000) $tierName = 'Bạc';
        elseif ($totalSpent >= 150000) $tierName = 'Đồng';

<<<<<<< HEAD
        if ($tierName) {
            HangThanhVien::updateOrCreate(['id_nguoi_dung' => $userId], ['ten_hang' => $tierName]);
=======
    /**
     * Cập nhật vé (Admin)
     */
    public function update(Request $request, $id)
    {
        $userRole = optional(Auth::user()->vaiTro)->ten;

        if (!in_array($userRole, ['admin', 'staff'])) {
            abort(403, 'Bạn không có quyền cập nhật vé.');
        }

        $request->validate([
            'ghe_ids' => 'nullable|string',
            'suat_chieu_id' => 'nullable|exists:suat_chieu,id',
            'ghi_chu_noi_bo' => 'nullable|string|max:500',
            'trang_thai' => 'nullable|in:0,1,2,3',
            'ma_km' => 'nullable|string|max:20',
            'combo_ids' => 'nullable|array',
            'combo_ids.*' => 'exists:combo,id',
        ]);

        $booking = DatVe::findOrFail($id);

        $result = DB::transaction(function () use ($request, $booking) {
            $replaceSeats = $request->filled('ghe_ids');
            $replaceCombos = $request->filled('combo_ids') && is_array($request->combo_ids);

            // Xử lý ghế
            $seatTotal = $this->updateSeats($booking, $replaceSeats ? $request->ghe_ids : null);

            // Xử lý combo
            $comboTotal = $this->updateCombos($booking, $replaceCombos ? $request->combo_ids : null);

            // Ghi chú nội bộ
            if ($request->filled('ghi_chu_noi_bo') && Schema::hasColumn('dat_ve', 'ghi_chu_noi_bo')) {
                $booking->ghi_chu_noi_bo = $request->ghi_chu_noi_bo;
            }

            // Mã khuyến mãi
            $discount = $this->applyPromoCode($booking, $request->ma_km);

            // Giảm theo hạng thành viên
            $memberDiscount = $this->getMemberDiscount($booking->id_nguoi_dung);

            // Cập nhật trạng thái
            if ($request->filled('trang_thai')) {
                $booking->trang_thai = (int) $request->trang_thai;
            }

            // Đổi suất chiếu
            if ($request->filled('suat_chieu_id')) {
                $newShowtime = SuatChieu::findOrFail($request->suat_chieu_id);
                if ($newShowtime->thoi_gian_bat_dau < now()) {
                    throw new \Exception('Không thể chuyển sang suất chiếu đã bắt đầu.');
                }
                $booking->id_suat_chieu = $newShowtime->id;
            }

            // Tính tổng
            $finalTotal = max(0, $seatTotal + $comboTotal - $discount - $memberDiscount);

            if (Schema::hasColumn('dat_ve', 'tong_tien')) {
                $booking->tong_tien = (float)$finalTotal;
            }
            if (Schema::hasColumn('dat_ve', 'tong_tien_hien_thi')) {
                $booking->tong_tien_hien_thi = (float)$finalTotal;
            }

            $booking->save();

            // Cập nhật hạng + điểm nếu xác nhận
            if ($booking->trang_thai == 1 && $booking->id_nguoi_dung) {
                $this->recalcMembershipTier($booking->id_nguoi_dung);
                $this->recalcMemberPoints($booking->id_nguoi_dung);
            }

            return $finalTotal;
        });

        return redirect()->route('admin.bookings.index')->with('success', 'Vé đã được cập nhật thành công.');
    }

    /**
     * Xác nhận vé
     */
    public function confirm($id)
    {
        $userRole = optional(Auth::user()->vaiTro)->ten;

        if (!in_array($userRole, ['admin', 'staff'])) {
            abort(403, 'Bạn không có quyền xác nhận vé.');
        }

        $booking = DatVe::findOrFail($id);

        if ($booking->trang_thai !== 0) {
            return back()->with('error', 'Chỉ có thể xác nhận vé đang chờ.');
        }

        DB::transaction(function () use ($booking) {
            $booking->trang_thai = 1;
            $booking->save();

            $this->recomputeBookingTotal($booking);

            if ($booking->id_nguoi_dung) {
                $this->recalcMembershipTier($booking->id_nguoi_dung);
                $this->recalcMemberPoints($booking->id_nguoi_dung);
            }
        });

        return redirect()->route('admin.bookings.index')->with('success', 'Vé đã được xác nhận thành công.');
    }

    // ===================================================================
    // PRIVATE HELPER METHODS
    // ===================================================================

    private function authorizeAdmin(): void
    {
        if (optional(Auth::user()->vaiTro)->ten !== 'admin') {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }
    }

    private function updateSeats(DatVe $booking, ?string $gheIdsInput): float
    {
        if (!$gheIdsInput) {
            return (float) $booking->chiTietDatVe()->sum('gia');
        }

        // Giải phóng ghế cũ
        foreach ($booking->chiTietDatVe as $detail) {
            if ($detail->ghe) {
                $detail->ghe->trang_thai = 1;
                $detail->ghe->save();
            }
        }
        $booking->chiTietDatVe()->delete();

        // Thêm ghế mới
        $seatIds = array_filter(array_map('intval', array_filter(explode(',', $gheIdsInput))));
        $total = 0;

        foreach ($seatIds as $gheId) {
            $ghe = Ghe::with('loaiGhe')->find($gheId);
            if (!$ghe || $ghe->trang_thai == 0 && !$booking->chiTietDatVe()->where('id_ghe', $gheId)->exists()) {
                continue;
            }

            $gia = ($ghe->loaiGhe->he_so_gia ?? 1) * 100000;
            ChiTietDatVe::create([
                'id_dat_ve' => $booking->id,
                'id_ghe' => $gheId,
                'gia' => $gia,
            ]);
            $total += $gia;
            $ghe->trang_thai = 0;
            $ghe->save();
        }

        return $total;
    }

    private function updateCombos(DatVe $booking, ?array $comboIds): float
    {
        if (!$comboIds || empty($comboIds)) {
            return (float) $booking->chiTietCombo()->sum(DB::raw('gia_ap_dung * COALESCE(so_luong,1)'));
        }

        $booking->chiTietCombo()->delete();
        $now = now();
        $validCombos = Combo::whereIn('id', $comboIds)
            ->where('trang_thai', 1)
            ->where(function ($q) use ($now) {
                $q->whereNull('ngay_bat_dau')->orWhere('ngay_bat_dau', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ngay_ket_thuc')->orWhere('ngay_ket_thuc', '>=', $now);
            })
            ->get();

        $total = 0;
        foreach ($validCombos as $cb) {
            $price = (float) ($cb->gia ?? 0);
            $booking->chiTietCombo()->create([
                'id_combo' => $cb->id,
                'so_luong' => 1,
                'gia_ap_dung' => $price,
            ]);
            $total += $price;
        }

        return $total;
    }

    private function applyPromoCode(DatVe $booking, ?string $code): float
    {
        if (!$code) {
            $booking->id_khuyen_mai = null;
            return 0;
        }

        $promo = KhuyenMai::where('ma_km', trim($code))
            ->where('trang_thai', 1)
            ->whereDate('ngay_bat_dau', '<=', now())
            ->whereDate('ngay_ket_thuc', '>=', now())
            ->first();

        if (!$promo) {
            throw new \Exception('Mã khuyến mãi không hợp lệ hoặc đã hết hạn.');
        }

        $booking->id_khuyen_mai = $promo->id;
        return $promo->loai_giam === 'phantram'
            ? 0 // sẽ tính sau khi có tổng
            : (float) $promo->gia_tri_giam;
    }

    private function getMemberDiscount(?int $userId): float
    {
        if (!$userId) return 0;

        $tier = HangThanhVien::where('id_nguoi_dung', $userId)->value('ten_hang');
        if (!$tier) return 0;

        return match (mb_strtolower(trim($tier))) {
            'đồng', 'dong' => 10000,
            'bạc', 'bac' => 15000,
            'vàng', 'vang' => 20000,
            'kim cương', 'kim cuong' => 25000,
            default => 0,
        };
    }

    /**
     * TÍNH LẠI TỔNG TIỀN CHO ĐƠN ĐẶT VÉ
     */
    private function recomputeBookingTotal(DatVe $booking): void
    {
        $booking->loadMissing([
            'chiTietDatVe',
            'chiTietCombo',
            'khuyenMai',
            'nguoiDung.hangThanhVien'
        ]);

        $seatTotal = (float) $booking->chiTietDatVe->sum('gia');
        $comboTotal = (float) $booking->chiTietCombo->sum(fn($c) => ($c->gia_ap_dung ?? 0) * max(1, $c->so_luong ?? 1));
        $subtotal = $seatTotal + $comboTotal;

        $discount = 0;
        if ($booking->khuyenMai) {
            $discount = $booking->khuyenMai->loai_giam === 'phantram'
                ? round($subtotal * ($booking->khuyenMai->gia_tri_giam / 100))
                : (float) $booking->khuyenMai->gia_tri_giam;
        }

        $memberDiscount = $this->getMemberDiscount($booking->id_nguoi_dung);

        $finalTotal = max(0, $subtotal - $discount - $memberDiscount);

        if (Schema::hasColumn('dat_ve', 'tong_tien')) {
            // @phpstan-ignore assign.propertyType (Laravel auto-cast)
            $booking->tong_tien = $finalTotal;
        }
        if (Schema::hasColumn('dat_ve', 'tong_tien_hien_thi')) {
            // @phpstan-ignore assign.propertyType (Laravel auto-cast)
            $booking->tong_tien_hien_thi = $finalTotal;
        }

        $booking->saveQuietly();
    }

    private function recalcMemberPoints(int $userId): void
    {
        $seatTotal = DB::table('chi_tiet_dat_ve as c')
            ->join('dat_ve as v', 'v.id', '=', 'c.id_dat_ve')
            ->where('v.id_nguoi_dung', $userId)
            ->where('v.trang_thai', 1)
            ->sum('c.gia');

        $comboTotal = DB::table('chi_tiet_dat_ve_combo as c')
            ->join('dat_ve as v', 'v.id', '=', 'c.id_dat_ve')
            ->where('v.id_nguoi_dung', $userId)
            ->where('v.trang_thai', 1)
            ->sum(DB::raw('c.gia_ap_dung * COALESCE(c.so_luong,1)'));

        $points = (int) floor(($seatTotal + $comboTotal) / 1000);

        DiemThanhVien::updateOrCreate(
            ['id_nguoi_dung' => $userId],
            ['tong_diem' => $points]
        );
    }

    private function recalcMembershipTier(int $userId): void
    {
        $seatTotal = DB::table('chi_tiet_dat_ve as c')
            ->join('dat_ve as v', 'v.id', '=', 'c.id_dat_ve')
            ->where('v.id_nguoi_dung', $userId)
            ->where('v.trang_thai', 1)
            ->sum('c.gia');

        $comboTotal = DB::table('chi_tiet_dat_ve_combo as c')
            ->join('dat_ve as v', 'v.id', '=', 'c.id_dat_ve')
            ->where('v.id_nguoi_dung', $userId)
            ->where('v.trang_thai', 1)
            ->sum(DB::raw('c.gia_ap_dung * COALESCE(c.so_luong,1)'));

        $total = $seatTotal + $comboTotal;

        $tier = match (true) {
            $total >= 1_500_000 => 'Kim cương',
            $total >= 1_000_000 => 'Vàng',
            $total >= 500_000 => 'Bạc',
            $total >= 150_000 => 'Đồng',
            default => null,
        };

        if ($tier) {
            HangThanhVien::updateOrCreate(
                ['id_nguoi_dung' => $userId],
                ['ten_hang' => $tier]
            );
>>>>>>> origin/khanhPH52932
        } else {
            HangThanhVien::where('id_nguoi_dung', $userId)->delete();
        }
    }
}