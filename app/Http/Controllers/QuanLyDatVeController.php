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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class QuanLyDatVeController extends Controller
{
    private const BASE_TICKET_PRICE = 100000;

    /**
     * Danh sách đặt vé + tự động hủy vé quá hạn 5 phút (COD/chưa chọn PTTT)
     */
    public function index(Request $request)
    {
        $expired = DatVe::with('thanhToan')
            ->where('trang_thai', 0)
            ->where('created_at', '<', now()->subMinutes(5))
            ->get();

        foreach ($expired as $bk) {
            $method = optional($bk->thanhToan)->phuong_thuc;
            if ($method === 'Tiền mặt' || empty($method)) {
                try {
                    DB::transaction(function () use ($bk) {
                        $bk->update(['trang_thai' => 2]);
                        $this->releaseSeats($bk);
                    });
                } catch (\Throwable $e) {
                    Log::error('Auto-cancel error: '.$e->getMessage());
                    $bk->update(['trang_thai' => 2, 'ghi_chu_noi_bo' => 'Lỗi hệ thống khi hủy tự động']);
                }
            }
        }

        $query = DatVe::with([
                'nguoiDung', 'suatChieu.phim', 'suatChieu.phongChieu',
                'chiTietDatVe.ghe', 'chiTietCombo.combo', 'thanhToan', 'khuyenMai'
            ])
            ->orderBy('created_at', 'desc');

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
                ->whereDate('created_at', now())
                ->get()->sum(fn($b) => (float)($b->tong_tien ?? $b->tong_tien_hien_thi ?? 0)),
        ];

        return view('admin.bookings.index', array_merge(['bookings' => $bookings], $stats));
    }

    public function show($id)
    {
        $booking = DatVe::with([
            'nguoiDung.diemThanhVien', 'nguoiDung.hangThanhVien',
            'suatChieu.phim', 'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe', 'chiTietCombo.combo', 'thanhToan', 'khuyenMai'
        ])->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Hủy vé (admin/staff)
     */
    public function cancel($id)
    {
        $this->authorizeAction('hủy vé');
        $booking = DatVe::with(['chiTietDatVe.ghe'])->findOrFail($id);

        if (!in_array($booking->trang_thai, [0, 3])) {
            return back()->with('error', 'Chỉ hủy vé đang chờ hoặc có yêu cầu hủy.');
        }

        try {
            DB::transaction(function () use ($booking) {
                $booking->update(['trang_thai' => 2]);
                $this->releaseSeats($booking);
            });
            $this->updateMemberStats($booking->id_nguoi_dung);
            return back()->with('success', 'Đã hủy vé và giải phóng ghế.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi hủy: '.$e->getMessage());
        }
    }

    /**
     * Xác nhận vé (admin/staff)
     */
    public function confirm($id)
    {
        $this->authorizeAction('xác nhận vé');
        $booking = DatVe::with('thanhToan')->findOrFail($id);
        if ($booking->trang_thai == 2) return back()->with('error', 'Vé đã hủy.');
        if ($booking->trang_thai != 0) return back()->with('error', 'Chỉ xác nhận vé đang chờ.');

        try {
            DB::transaction(function () use ($booking) {
                $booking->update(['trang_thai' => 1]);
                if ($booking->thanhToan) {
                    $booking->thanhToan()->update(['trang_thai' => 1, 'thoi_gian' => now()]);
                }
            });
            $this->updateMemberStats($booking->id_nguoi_dung);
            return redirect()->route('admin.bookings.index')->with('success', 'Đã xác nhận vé.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi: '.$e->getMessage());
        }
    }

    /**
     * Form chỉnh sửa vé
     */
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

    /**
     * Cập nhật vé
     */
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
                // Suất chiếu
                if ($request->filled('suat_chieu_id')) {
                    $booking->id_suat_chieu = (int)$request->input('suat_chieu_id');
                }

                // Ghế
                $tongGhe = 0;
                if ($request->has('ghe_ids')) {
                    $this->releaseSeats($booking);
                    $booking->chiTietDatVe()->delete();

                    $seatIds = array_filter(array_unique(explode(',', $request->input('ghe_ids'))), 'is_numeric');
                    foreach ($seatIds as $gheId) {
                        $ghe = Ghe::with('loaiGhe')->find($gheId);
                        if (!$ghe || $ghe->trang_thai != 1) continue;
                        $gia = ($ghe->loaiGhe->he_so_gia ?? 1) * self::BASE_TICKET_PRICE;
                        $booking->chiTietDatVe()->create(['id_ghe' => $gheId, 'gia' => $gia]);
                        $ghe->update(['trang_thai' => 0]);
                        $tongGhe += $gia;
                    }
                } else {
                    $tongGhe = (float)$booking->chiTietDatVe()->sum('gia');
                }

                // Combo
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
                            'gia_ap_dung' => $cb->gia,
                        ]);
                        $tongCombo += ($cb->gia * $qty);
                    }
                } else {
                    $tongCombo = (float)$booking->chiTietCombo()->sum(DB::raw('gia_ap_dung * COALESCE(so_luong,1)'));
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
                        ->whereDate('ngay_bat_dau', '<=', now())
                        ->whereDate('ngay_ket_thuc', '>=', now())
                        ->first();
                    if (!$promo) throw new \Exception('Mã khuyến mãi không hợp lệ.');
                    $discount = $promo->loai_giam === 'phantram'
                        ? round(($tongGhe + $tongCombo) * ((float)$promo->gia_tri_giam / 100))
                        : (float)$promo->gia_tri_giam;
                    $booking->id_khuyen_mai = $promo->id;
                } elseif ($request->has('ma_km')) {
                    $booking->id_khuyen_mai = null;
                } elseif ($booking->id_khuyen_mai && $booking->khuyenMai) {
                    $promo = $booking->khuyenMai;
                    $discount = $promo->loai_giam === 'phantram'
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
                        $discounts = array_change_key_case($discounts, CASE_LOWER);
                        $memberDiscount = $discounts[$normalized] ?? 0;
                    }
                }

                // Trạng thái
                if ($request->has('trang_thai') && $booking->trang_thai != $request->trang_thai) {
                    $newStatus = (int)$request->trang_thai;
                    if ($newStatus == 2) $this->releaseSeats($booking);
                    $booking->trang_thai = $newStatus;
                    if ($newStatus == 1 && $booking->thanhToan) {
                        $booking->thanhToan()->update(['trang_thai' => 1, 'thoi_gian' => now()]);
                    }
                }

                $booking->tong_tien = max(0, ($tongGhe + $tongCombo) - $discount - $memberDiscount);
                $booking->save();
            });

            if ($booking->id_nguoi_dung) $this->updateMemberStats($booking->id_nguoi_dung);
            return redirect()->route('admin.bookings.index')->with('success', 'Cập nhật thành công.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * API: Danh sách suất chiếu còn hiệu lực theo phim của đơn hiện tại
     */
    public function availableShowtimes($id)
    {
        $booking = DatVe::with('suatChieu.phim')->findOrFail($id);
        $movieId = $booking->suatChieu?->id_phim;
        if (!$movieId) return response()->json([]);

        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movieId)
            ->where('trang_thai', 1)
            ->where('thoi_gian_bat_dau', '>=', now()->subMinutes(1))
            ->orderBy('thoi_gian_bat_dau')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'label' => $s->thoi_gian_bat_dau?->format('d/m/Y H:i') . ' • ' . ($s->phongChieu->ten_phong ?? 'N/A'),
                'current' => $s->id === $booking->id_suat_chieu,
            ]);

        return response()->json($showtimes);
    }

    /**
     * API: Sơ đồ ghế theo suất chiếu (đánh dấu ghế đã đặt)
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
        }

        $bookedSeatIds = $bookedQuery->pluck('c.id_ghe')->toArray();

        return response()->json([
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
            ]),
        ]);
    }

    // ================= Helpers =================
    private function authorizeAction($actionName)
    {
        $role = optional(Auth::user()->vaiTro)->ten;
        if (!in_array($role, ['admin', 'staff'])) {
            abort(403, "Bạn không có quyền $actionName.");
        }
    }

    private function applyFilters($query, Request $request)
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
            $query->whereHas('suatChieu.phim', fn($q) => $q->where('ten_phim', 'like', '%'.$request->phim.'%'));
        }
        if ($request->filled('nguoi_dung')) {
            $query->whereHas('nguoiDung', fn($q) => $q->where('ho_ten', 'like', '%'.$request->nguoi_dung.'%'));
        }
    }

    private function releaseSeats(DatVe $booking): void
    {
        try {
            foreach ($booking->chiTietDatVe as $detail) {
                if ($detail->ghe) {
                    $detail->ghe->update(['trang_thai' => 1]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error releasing seats: '.$e->getMessage());
        }
    }

    private function updateMemberStats(?int $userId): void
    {
        if (!$userId) return;
        $this->recalcMembershipTier($userId);
        $this->recalcMemberPoints($userId);
    }

    private function recomputeBookingTotal(DatVe $booking): void
    {
        $booking->loadMissing(['chiTietDatVe', 'chiTietCombo', 'khuyenMai', 'nguoiDung.hangThanhVien']);
        $seatTotal = (float)$booking->chiTietDatVe->sum('gia');
        $comboTotal = (float)$booking->chiTietCombo->sum(fn($c) => ($c->gia_ap_dung ?? 0) * max(1, $c->so_luong ?? 1));
        $subtotal = $seatTotal + $comboTotal;

        $discount = 0;
        if ($booking->khuyenMai) {
            $discount = $booking->khuyenMai->loai_giam === 'phantram'
                ? round($subtotal * ($booking->khuyenMai->gia_tri_giam / 100))
                : (float)$booking->khuyenMai->gia_tri_giam;
        }

        $memberDiscount = $this->getMemberDiscount($booking->id_nguoi_dung);
        $final = max(0, $subtotal - $discount - $memberDiscount);

        if (Schema::hasColumn('dat_ve', 'tong_tien')) {
            $booking->tong_tien = $final;
        }
        if (Schema::hasColumn('dat_ve', 'tong_tien_hien_thi')) {
            $booking->tong_tien_hien_thi = $final;
        }
        $booking->saveQuietly();
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
        DiemThanhVien::updateOrCreate(['id_nguoi_dung' => $userId], ['tong_diem' => $points]);
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
            HangThanhVien::updateOrCreate(['id_nguoi_dung' => $userId], ['ten_hang' => $tier]);
        } else {
            HangThanhVien::where('id_nguoi_dung', $userId)->delete();
        }
    }
}

