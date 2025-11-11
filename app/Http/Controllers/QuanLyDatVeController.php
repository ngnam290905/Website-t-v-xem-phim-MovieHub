<?php

namespace App\Http\Controllers;

use App\Models\ChiTietDatVe;
use App\Models\Combo;
use App\Models\DatVe;
use App\Models\Ghe;
use App\Models\SuatChieu;
use App\Models\KhuyenMai;
use App\Models\HangThanhVien;
use App\Models\DiemThanhVien;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuanLyDatVeController extends Controller
{
    public function index(Request $request)
    {
        $query = DatVe::with(['nguoiDung', 'suatChieu.phim', 'suatChieu.phongChieu', 'chiTietDatVe.ghe', 'chiTietCombo.combo', 'thanhToan', 'khuyenMai'])
            ->orderBy('created_at', 'desc');

        // 🔹 Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        }


        // 🔹 Lọc theo tên phim
        if ($request->filled('phim')) {
            $query->whereHas('suatChieu.phim', function ($q) use ($request) {
                $q->where('ten_phim', 'like', '%' . $request->phim . '%');
            });
        }

        // 🔹 Lọc theo người dùng
        if ($request->filled('nguoi_dung')) {
            $query->whereHas('nguoiDung', function ($q) use ($request) {
                $q->where('ho_ten', 'like', '%' . $request->nguoi_dung . '%');
            });
        }

        $bookings = $query->paginate(10)->appends($request->query());

        // Quick stats for bookings
        $totalBookings = (int) DatVe::count();
        $pendingCount = (int) DatVe::where('trang_thai', 0)->count();
        $confirmedCount = (int) DatVe::where('trang_thai', 1)->count();
        $canceledCount = (int) DatVe::where('trang_thai', 2)->count();
        $requestCancelCount = (int) DatVe::where('trang_thai', 3)->count();
        $todayConfirmed = DatVe::where('trang_thai', 1)
            ->whereDate('created_at', now()->toDateString())
            ->get();
        $revenueToday = (float) $todayConfirmed->sum(function($b){
            return (float) ($b->tong_tien ?? $b->tong_tien_hien_thi ?? 0);
        });

        return view('admin.bookings.index', compact(
            'bookings',
            'totalBookings', 'pendingCount', 'confirmedCount', 'canceledCount', 'requestCancelCount', 'revenueToday'
        ));
    }

    public function show($id)
    {
        $booking = DatVe::with([
            'nguoiDung.diemThanhVien',
            'nguoiDung',
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe',
            'chiTietCombo.combo',
            'thanhToan',
            'khuyenMai'
        ])->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
    }

    // API: showtimes available for this booking (same movie, upcoming/active)
    public function availableShowtimes($id)
    {
        $booking = DatVe::with('suatChieu.phim')->findOrFail($id);
        $movieId = optional($booking->suatChieu)->id_phim;
        if (!$movieId) {
            return response()->json([]);
        }

        $showtimes = SuatChieu::with('phongChieu')
            ->where('id_phim', $movieId)
            ->where('trang_thai', 1)
            ->where('thoi_gian_bat_dau', '>=', now()->subMinutes(1))
            ->orderBy('thoi_gian_bat_dau')
            ->get()
            ->map(function ($s) use ($booking) {
                return [
                    'id' => $s->id,
                    'label' => ($s->thoi_gian_bat_dau ? $s->thoi_gian_bat_dau->format('d/m/Y H:i') : '') . ' • ' . optional($s->phongChieu)->ten_phong,
                    'current' => $s->id === $booking->id_suat_chieu,
                ];
            });

        return response()->json($showtimes);
    }

    // API: seat map for a showtime (mark booked seats)
    public function seatsByShowtime($suatChieuId, Request $request)
    {
        $suat = SuatChieu::with('phongChieu')->findOrFail($suatChieuId);
        $roomId = $suat->id_phong;
        $excludeBookingId = $request->query('exclude_booking_id');

        // Seats in room
        $seats = Ghe::where('id_phong', $roomId)
            ->orderBy('so_hang')
            ->orderBy('so_ghe')
            ->get(['id','so_ghe','so_hang','id_loai']);

        // Seats booked for this showtime (active bookings only)
        $bookedQuery = DB::table('chi_tiet_dat_ve as c')
            ->join('dat_ve as d', 'd.id', '=', 'c.id_dat_ve')
            ->where('d.id_suat_chieu', $suatChieuId)
            ->where('d.trang_thai', '!=', 2) // exclude cancelled
            ;
        if ($excludeBookingId) {
            $bookedQuery->where('d.id', '!=', $excludeBookingId);
        }
        $bookedSeatIds = $bookedQuery->pluck('c.id_ghe')->toArray();

        return response()->json([
            'room' => [
                'id' => $roomId,
                'ten_phong' => optional($suat->phongChieu)->ten_phong,
            ],
            'seats' => $seats->map(function($g) use ($bookedSeatIds){
                return [
                    'id' => $g->id,
                    'label' => $g->so_ghe,
                    'row' => $g->so_hang,
                    'type' => $g->id_loai,
                    'booked' => in_array($g->id, $bookedSeatIds),
                ];
            }),
        ]);
    }

    // ✅ 3. Hủy vé (chỉ Admin)
    public function cancel($id)
    {
        $userRole = optional(Auth::user()->vaiTro)->ten;

        if ($userRole !== 'admin') {
            abort(403, 'Bạn không có quyền hủy vé.');
        }

        $booking = DatVe::findOrFail($id);

        if ($booking->trang_thai == 0 && $booking->suatChieu->thoi_gian_bat_dau > now()) {
            $booking->trang_thai = 2; // 2 = Hủy
            $booking->save();

            foreach ($booking->chiTietDatVe as $detail) {
                $detail->ghe->trang_thai = 1; // Giải phóng ghế
                $detail->ghe->save();
            }
        }

        // Cập nhật hạng thành viên sau khi hủy (nếu cần)
        if ($booking->id_nguoi_dung) {
            $this->recalcMembershipTier((int)$booking->id_nguoi_dung);
            $this->recalcMemberPoints((int)$booking->id_nguoi_dung);
        }

        return redirect()->route('admin.bookings.index')->with('success', 'Vé đã được hủy thành công.');
    }

    // ✅ 4. Sửa vé (chỉ Admin)
    public function edit($id)
    {
        $userRole = optional(Auth::user()->vaiTro)->ten;

        if ($userRole !== 'admin') {
            abort(403, 'Bạn không có quyền chỉnh sửa vé.');
        }

        $booking = DatVe::with(['chiTietDatVe', 'chiTietCombo', 'suatChieu'])->findOrFail($id);
        $gheTrong = Ghe::where('id_phong', $booking->suatChieu->id_phong)->where('trang_thai', 1)->get();
        $combos = Combo::where('trang_thai', 1)->get();

        return view('admin.bookings.edit', compact('booking', 'gheTrong', 'combos'));
    }

    // ✅ 5. Cập nhật vé (chỉ Admin)
    public function update(Request $request, $id)
    {
        $userRole = optional(Auth::user()->vaiTro)->ten;

        if ($userRole !== 'admin') {
            abort(403, 'Bạn không có quyền cập nhật vé.');
        }

        $request->validate([
            'ghe_ids' => 'nullable',
            'suat_chieu_id' => 'nullable|integer',
            'ghi_chu_noi_bo' => 'nullable|string',
            'trang_thai' => 'nullable|in:0,1,2,3',
            'trang_thai_thanh_toan' => 'nullable|in:0,1,2',
            'ma_km' => 'nullable|string',
            'combo_ids' => 'nullable|array',
            'combo_ids.*' => 'integer'
        ]);

        $booking = DatVe::findOrFail($id);

        // Change showtime if provided
        if ($request->filled('suat_chieu_id')) {
            $booking->id_suat_chieu = (int) $request->input('suat_chieu_id');
        }

        $result = DB::transaction(function () use ($request, $booking) {
            // Nếu có truyền danh sách ghế mới, thay thế ghế; nếu không, giữ nguyên ghế cũ
            $replaceSeats = $request->filled('ghe_ids');
            if ($replaceSeats) {
                foreach ($booking->chiTietDatVe as $detail) {
                    if ($detail->ghe) {
                        $detail->ghe->trang_thai = 1;
                        $detail->ghe->save();
                    }
                }
                $booking->chiTietDatVe()->delete();
            }

        // Chuẩn hóa danh sách ghế từ request (có thể là chuỗi '1,2,3' hoặc mảng)
        $rawGhe = $request->input('ghe_ids');
        $seatIds = [];
        if (is_array($rawGhe)) {
            foreach ($rawGhe as $item) {
                foreach (explode(',', (string)$item) as $part) {
                    $part = trim($part);
                    if ($part !== '' && is_numeric($part)) { $seatIds[] = (int)$part; }
                }
            }
        } else {
            foreach (explode(',', (string)$rawGhe) as $part) {
                $part = trim($part);
                if ($part !== '' && is_numeric($part)) { $seatIds[] = (int)$part; }
            }
        }
        $seatIds = array_values(array_unique($seatIds));

            // Tính tổng ghế: nếu thay thế ghế và có danh sách mới thì thêm mới; nếu không, lấy tổng ghế hiện có
            $tong = 0;
            if ($replaceSeats && count($seatIds) > 0) {
                foreach ($seatIds as $gheId) {
                    $ghe = Ghe::with('loaiGhe')->find($gheId);
                    if (!$ghe) continue;
                    $gia = ($ghe->loaiGhe->he_so_gia ?? 1) * 100000;
                    ChiTietDatVe::create([
                        'id_dat_ve' => $booking->id,
                        'id_ghe' => $gheId,
                        'gia' => $gia,
                    ]);
                    $tong += $gia;
                    $ghe->trang_thai = 0;
                    $ghe->save();
                }
            } else {
                // Giữ nguyên và tính tổng từ chi tiết hiện có
                $tong = (float) $booking->chiTietDatVe()->sum('gia');
            }

            // Combo: nếu có truyền danh sách mới thì thay thế; nếu không, giữ nguyên
            $comboTotal = 0;
            $comboIds = $request->input('combo_ids');
            $replaceCombos = is_array($comboIds) && count($comboIds) > 0;
            if ($replaceCombos) {
                if (method_exists($booking, 'chiTietCombo')) {
                    $booking->chiTietCombo()->delete();
                }
                $now = now();
                $validCombos = Combo::whereIn('id', $comboIds)
                    ->where('trang_thai', 1)
                    ->where(function($q) use ($now){
                        $q->whereNull('ngay_bat_dau')->orWhere('ngay_bat_dau', '<=', $now);
                    })
                    ->where(function($q) use ($now){
                        $q->whereNull('ngay_ket_thuc')->orWhere('ngay_ket_thuc', '>=', $now);
                    })
                    ->get();

                foreach ($validCombos as $cb) {
                    $price = (float) ($cb->gia ?? 0);
                    $booking->chiTietCombo()->create([
                        'id_combo' => $cb->id,
                        'so_luong' => 1,
                        'gia_ap_dung' => $price,
                    ]);
                    $comboTotal += $price;
                }
            } else {
                $comboTotal = (float) $booking->chiTietCombo()->sum(DB::raw('gia_ap_dung * COALESCE(so_luong,1)'));
            }

        // Lưu ghi chú nội bộ nếu có (cần cột trong DB)
        if ($request->filled('ghi_chu_noi_bo') && Schema::hasColumn('dat_ve', 'ghi_chu_noi_bo')) {
            $booking->ghi_chu_noi_bo = $request->input('ghi_chu_noi_bo');
        }

        // Áp dụng mã khuyến mãi nếu có
        $discount = 0;
        if ($request->filled('ma_km')) {
            $code = trim($request->input('ma_km'));
            $promo = KhuyenMai::where('ma_km', $code)
                ->where('trang_thai', 1)
                ->whereDate('ngay_bat_dau', '<=', now())
                ->whereDate('ngay_ket_thuc', '>=', now())
                ->first();
            if (!$promo) {
                return back()->withInput()->with('error', 'Mã khuyến mãi không hợp lệ hoặc đã hết hạn.');
            }
            if ($promo->loai_giam === 'phantram') {
                $discount = round(($tong + $comboTotal) * ((float)$promo->gia_tri_giam / 100));
            } else { // codinh
                $discount = (float)$promo->gia_tri_giam;
            }
            $booking->id_khuyen_mai = $promo->id;
        } else {
            // Nếu xóa mã, bỏ liên kết
            $booking->id_khuyen_mai = null;
        }

            // Giảm theo hạng thành viên (Đồng/Bạc/Vàng/Kim cương)
            $memberDiscount = 0;
            if ($booking->id_nguoi_dung) {
                $tier = optional(\App\Models\HangThanhVien::where('id_nguoi_dung', $booking->id_nguoi_dung)->first())->ten_hang;
                if ($tier) {
                    $normalized = mb_strtolower($tier);
                    if ($normalized === 'đồng' || $normalized === 'dong') { $memberDiscount = 10000; }
                    elseif ($normalized === 'bạc' || $normalized === 'bac') { $memberDiscount = 15000; }
                    elseif ($normalized === 'vàng' || $normalized === 'vang') { $memberDiscount = 20000; }
                    elseif ($normalized === 'kim cương' || $normalized === 'kim cuong') { $memberDiscount = 25000; }
                }
            }

            // Cập nhật tổng tiền nếu model có cột này (ghế + combo - KM - giảm theo hạng)
            if (isset($booking->tong_tien)) {
                $booking->tong_tien = max(0, ($tong + $comboTotal) - $discount - $memberDiscount);
            }
            // Cập nhật trạng thái nếu truyền vào
            if ($request->filled('trang_thai')) {
                $booking->trang_thai = (int) $request->input('trang_thai');
            }
            
            // Cập nhật trạng thái thanh toán nếu truyền vào
            if ($request->filled('trang_thai_thanh_toan')) {
                $booking->trang_thai_thanh_toan = (int) $request->input('trang_thai_thanh_toan');
            }
            
            $booking->save();

            return ['tong' => $tong, 'comboTotal' => $comboTotal, 'discount' => $discount, 'memberDiscount' => $memberDiscount];
        });

        return redirect()->route('admin.bookings.index')->with('success', 'Vé đã được điều chỉnh thành công.');
    }
    public function confirm($id)
    {
        $userRole = optional(Auth::user()->vaiTro)->ten;

        if ($userRole !== 'admin') {
            abort(403, 'Bạn không có quyền xác nhận vé.');
        }

        $booking = DatVe::findOrFail($id);

        if ($booking->trang_thai == 0) {
            $booking->trang_thai = 1; // 1 = Đã xác nhận
            $booking->save();

            // Sau khi xác nhận, cập nhật hạng thành viên dựa trên tổng chi tiêu tích lũy
            if ($booking->id_nguoi_dung) {
                $this->recalcMembershipTier((int)$booking->id_nguoi_dung);
                $this->recalcMemberPoints((int)$booking->id_nguoi_dung);
            }

            // Tính lại tổng tiền để hiển thị chính xác ở danh sách
            $this->recomputeBookingTotal($booking);

            return redirect()->route('admin.bookings.index')
                ->with('success', 'Vé đã được xác nhận thành công.');
        }

        return redirect()->route('admin.bookings.index')
            ->with('error', 'Chỉ có thể xác nhận vé đang chờ.');
    }

    private function recalcMemberPoints(int $userId): void
    {
        // Tổng chi tiêu đã xác nhận
        $seatTotal = DB::table('chi_tiet_dat_ve as d')
            ->join('dat_ve as v', 'v.id', '=', 'd.id_dat_ve')
            ->where('v.id_nguoi_dung', $userId)
            ->where('v.trang_thai', 1)
            ->sum('d.gia');

        $comboTotal = DB::table('chi_tiet_dat_ve_combo as c')
            ->join('dat_ve as v', 'v.id', '=', 'c.id_dat_ve')
            ->where('v.id_nguoi_dung', $userId)
            ->where('v.trang_thai', 1)
            ->sum(DB::raw('c.gia_ap_dung * COALESCE(c.so_luong,1)'));

        $total = (float)$seatTotal + (float)$comboTotal;

        // Quy đổi: 1,000 VND = 1 điểm (lấy phần nguyên)
        $points = (int) floor($total / 1000);

        DiemThanhVien::updateOrCreate(
            ['id_nguoi_dung' => $userId],
            ['tong_diem' => $points]
        );
    }

    private function recalcMembershipTier(int $userId): void
    {
        // Tổng chi tiêu đã xác nhận: ghế + combo
        $seatTotal = DB::table('chi_tiet_dat_ve as d')
            ->join('dat_ve as v', 'v.id', '=', 'd.id_dat_ve')
            ->where('v.id_nguoi_dung', $userId)
            ->where('v.trang_thai', 1)
            ->sum('d.gia');

        $comboTotal = DB::table('chi_tiet_dat_ve_combo as c')
            ->join('dat_ve as v', 'v.id', '=', 'c.id_dat_ve')
            ->where('v.id_nguoi_dung', $userId)
            ->where('v.trang_thai', 1)
            ->sum(DB::raw('c.gia_ap_dung * COALESCE(c.so_luong,1)'));

        $total = (float)$seatTotal + (float)$comboTotal;

        // Ngưỡng hạng (≥1,500,000 Kim cương; ≥1,000,000 Vàng; ≥500,000 Bạc; ≥150,000 Đồng)
        $tier = null;
        if ($total >= 1500000) {
            $tier = 'Kim cương';
        } elseif ($total >= 1000000) {
            $tier = 'Vàng';
        } elseif ($total >= 500000) {
            $tier = 'Bạc';
        } elseif ($total >= 150000) {
            $tier = 'Đồng';
        }

        if ($tier) {
            HangThanhVien::updateOrCreate(
                ['id_nguoi_dung' => $userId],
                ['ten_hang' => $tier]
            );
        } else {
            // Nếu chưa đạt ngưỡng nào, có thể xóa hoặc đặt null
            HangThanhVien::where('id_nguoi_dung', $userId)->delete();
        }
    }

    public function updatePayment(Request $request, $id)
    {
        $booking = DatVe::findOrFail($id);
        
        $request->validate([
            'payment_status' => 'required|integer|in:0,1,2'
        ]);

        // Cập nhật trạng thái thanh toán (giả sử có cột trang_thai_thanh_toan)
        $booking->trang_thai_thanh_toan = $request->payment_status;
        $booking->save();

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Cập nhật trạng thái thanh toán thành công!');
    }
}
