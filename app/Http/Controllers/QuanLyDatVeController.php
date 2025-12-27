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
use App\Models\ChiTietCombo; // Thêm dòng này nếu thiếu
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
use App\Models\ShowtimeSeat;
use Carbon\Carbon;


class QuanLyDatVeController extends Controller
{
    private const BASE_TICKET_PRICE = 100000;

    public function index(Request $request)
    {
        // Auto cancel expired offline bookings (using expires_at or created_at + 5 minutes)
        $expiredQuery = DatVe::with('thanhToan')
            ->where('trang_thai', 0);

        // Check if phuong_thuc_thanh_toan column exists before filtering
        if (Schema::hasColumn('dat_ve', 'phuong_thuc_thanh_toan')) {
            $expiredQuery->where('phuong_thuc_thanh_toan', 2); // offline payment
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
        })
            ->get();

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
                } catch (\Throwable $e2) {

                    Log::error('Failed to update booking status: ' . $e2->getMessage());

                    Log::error('Failed to update booking status: '.$e2->getMessage());


                }
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
                ->whereDate('created_at', now()->toDateString())
                ->get()
                ->sum(fn($b) => (float) ($b->tong_tien ?? $b->tong_tien_hien_thi ?? 0)),
        ];

        return view('admin.bookings.index', array_merge(['bookings' => $bookings], $stats));
    }

    /**
     * Staff: Xem vé đã đặt của chính họ
     */
    public function myBookings(Request $request)
    {
        $this->authorizeAction('xem vé của mình');
        
        $userId = Auth::id();
        if (!$userId) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Bạn cần đăng nhập.');
        }

        // Auto cancel expired pending bookings for this staff
        $expiredBookings = DatVe::where('id_nguoi_dung', $userId)
            ->where('trang_thai', 0)
            ->where(function($query) {
                if (Schema::hasColumn('dat_ve', 'expires_at')) {
                    $query->where(function($q) {
                        $q->whereNotNull('expires_at')
                          ->where('expires_at', '<=', now());
                    })->orWhere(function($q) {
                        $q->whereNull('expires_at')
                          ->where('created_at', '<=', now()->subMinutes(15));
                    });
                } else {
                    $query->where('created_at', '<=', now()->subMinutes(15));
                }
            })
            ->get();

        foreach ($expiredBookings as $expiredBooking) {
            try {
                DB::transaction(function () use ($expiredBooking) {
                    $expiredBooking->update(['trang_thai' => 2]);
                    $this->releaseSeats($expiredBooking);
                });
            } catch (\Throwable $e) {
                Log::error('Auto-cancel expired booking error: ' . $e->getMessage());
            }
        }

        // Lấy vé của staff
        $query = DatVe::with([
            'suatChieu.phim',
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe',
            'chiTietDatVe.ghe',
            'chiTietCombo.combo',
            'thanhToan',
            'khuyenMai'
        ])
            ->where('id_nguoi_dung', $userId)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('trang_thai', $request->status);
        }
        if ($request->filled('phim')) {
            $query->whereHas('suatChieu.phim', fn($q) => $q->where('ten_phim', 'like', '%' . $request->phim . '%'));
        }
        if ($request->filled('booking_date')) {
            $query->whereDate('created_at', $request->booking_date);
        }
        if ($request->filled('show_date')) {
            $query->whereHas('suatChieu', function ($q) use ($request) {
                $q->whereDate('thoi_gian_bat_dau', $request->show_date);
            });
        }

        $bookings = $query->paginate(10)->appends($request->query());

        // Stats cho staff
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

            return back()->with('success', 'Đã hủy vé và giải phóng ghế.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi hủy: ' . $e->getMessage());


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
                $booking->update(['trang_thai' => 1]);
                // Vé -> 1
                $booking->update(['trang_thai' => 1]);

                // Thanh toán -> 1 (Thành công)
                if ($booking->thanhToan) {
                    $booking->thanhToan()->update(['trang_thai' => 1, 'thoi_gian' => now()]);
                }
            });
            $this->updateMemberStats($booking->id_nguoi_dung);

            // Send email notification
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
                    $discount = ($promo->loai_giam === 'phantram')
                        ? round(($tongGhe + $tongCombo) * ((float)$promo->gia_tri_giam / 100))
                        : (float)$promo->gia_tri_giam;
                }


                // Hạng thành viên - ĐÃ XÓA LOGIC GIẢM GIÁ Ở ĐÂY
                // Logic tính tổng tiền mới: Chỉ trừ Khuyến mãi (nếu có)
                $booking->tong_tien = max(0, ($tongGhe + $tongCombo) - $discount);

                // Trạng thái

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

                $booking->save();
            });

            if ($booking->id_nguoi_dung) $this->updateMemberStats($booking->id_nguoi_dung);
            return redirect()->route('admin.bookings.index')->with('success', 'Cập nhật thành công.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
    // --- 5. CÁC HÀM API (ĐÃ ĐIỀN LOGIC) ---
    public function seatsByShowtime($suatChieuId, Request $request)
    {
        try {
            $suat = SuatChieu::with('phongChieu')->findOrFail($suatChieuId);
            $seatQuery = Ghe::where('id_phong', $suat->id_phong)
                ->orderBy('so_hang');
            if (\Illuminate\Support\Facades\Schema::hasColumn('ghe', 'pos_x')) {
                $seatQuery->orderByRaw('COALESCE(pos_x, 0) ASC');
            }
            $seats = $seatQuery->orderBy('so_ghe')->get();

            // Lấy ghế đã đặt (trừ đơn hàng hiện tại)
            $bookedSeatIds = [];
            try {
                $bookedQuery = DB::table('chi_tiet_dat_ve as c')
                    ->join('dat_ve as d', 'd.id', '=', 'c.id_dat_ve')
                    ->where('d.id_suat_chieu', $suatChieuId)
                    ->where('d.trang_thai', '!=', 2); // Không tính vé hủy

                if ($request->filled('exclude_booking_id')) {
                    $bookedQuery->where('d.id', '!=', $request->exclude_booking_id);
                }

                $bookedSeatIds = $bookedQuery->pluck('c.id_ghe')->toArray();
            } catch (\Throwable $e) {
                \Log::error('Failed to load booked seats for showtime '.$suatChieuId.': '.$e->getMessage());
                $bookedSeatIds = [];
            }

            return response()->json([
                'room' => [
                    'id' => $suat->id_phong,
                    'ten_phong' => $suat->phongChieu->ten_phong ?? 'N/A',
                ],
                'seats' => $seats->map(function ($g) use ($bookedSeatIds) {
                    // Determine column using explicit position if available; fallback to numeric part of label
                    $label = (string) $g->so_ghe;
                    $num = (int) preg_replace('/\D+/', '', $label);
                    // Ensure type is valid: 1 = Normal, 2 = VIP, 3 = Couple
                    // Default to 1 if id_loai is null or 0
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
            \Log::error('seatsByShowtime error for showtime '.$suatChieuId.': '.$e->getMessage());
            return response()->json(['error' => 'Internal error loading seats'], 500);
        }
    }

    // --- HELPERS ---
    private function authorizeAction($actionName)
    {
        $role = optional(Auth::user()->vaiTro)->ten;
        if (!in_array($role, ['admin', 'staff'])) {
            abort(403, "Bạn không có quyền $actionName.");
        }
    }

    private function applyFilters($query, $request)    {
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
        if ($request->filled('booking_date')) {
            $query->whereDate('created_at', $request->booking_date);
        }

        // Filter theo Ngày chiếu (Showtime Date)
        if ($request->filled('show_date')) {
            $query->whereHas('suatChieu', function ($q) use ($request) {
                $q->whereDate('thoi_gian_bat_dau', $request->show_date);
            });
        }
    }

    private function releaseSeats(DatVe $booking): void
    {
        try {
            // 1. Nhả ghế trong bảng showtime_seats
            if (Schema::hasTable('suat_chieu_ghe')) {
                foreach ($booking->chiTietDatVe as $detail) {
                    if ($detail->id_ghe) {
                        ShowtimeSeat::where('id_suat_chieu', $booking->id_suat_chieu)
                            ->where('id_ghe', $detail->id_ghe)
                            ->update([
                                'status' => 'available',
                                'hold_expires_at' => null
                            ]);
                    }
                }
            }

            // 2. Nhả ghế trong bảng ghe (tương thích ngược)
            foreach ($booking->chiTietDatVe as $detail) {
                if ($detail->ghe) {
                    $detail->ghe->update(['trang_thai' => 1]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error releasing seats: ' . $e->getMessage());
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

        // ĐÃ XÓA LOGIC GIẢM GIÁ HẠNG THÀNH VIÊN
        $final = max(0, $subtotal - $discount);

        if (Schema::hasColumn('dat_ve', 'tong_tien')) {
            $booking->tong_tien = $final;
        }
        if (Schema::hasColumn('dat_ve', 'tong_tien_hien_thi')) {
            $booking->tong_tien_hien_thi = $final;
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

    /**
     * Send ticket email to customer
     */
    public function sendTicket($id)
    {
        $this->authorizeAction('gửi email vé');
        $booking = DatVe::findOrFail($id);

        try {
            $this->sendTicketEmail($booking);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email đã được gửi thành công!'
                ]);
            }

            return back()->with('success', 'Email đã được gửi thành công!');
        } catch (\Throwable $e) {
            Log::error('Send ticket email error: ' . $e->getMessage());

            // Extract user-friendly error message
            $errorMessage = $e->getMessage();
            $userMessage = 'Lỗi khi gửi email.';

            if (str_contains($errorMessage, '535') || str_contains($errorMessage, 'BadCredentials')) {
                $userMessage = 'Lỗi xác thực Gmail. Vui lòng kiểm tra cấu hình email trong file .env. Xem hướng dẫn: GMAIL_SETUP_GUIDE.md';
            } elseif (str_contains($errorMessage, 'Connection') || str_contains($errorMessage, 'timeout')) {
                $userMessage = 'Không thể kết nối đến máy chủ email. Vui lòng kiểm tra kết nối mạng.';
            } elseif (str_contains($errorMessage, 'no email address')) {
                $userMessage = 'Không tìm thấy địa chỉ email của khách hàng.';
            } else {
                $userMessage = 'Lỗi khi gửi email: ' . (strlen($errorMessage) > 200 ? substr($errorMessage, 0, 200) . '...' : $errorMessage);
            }

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $userMessage
                ], 500);
            }

            return back()->with('error', $userMessage);
        }
    }

    /**
     * Helper method to send ticket email
     */
    private function sendTicketEmail(DatVe $booking): void
    {
        $email = $booking->email ?? ($booking->nguoiDung->email ?? null);

        if (!$email) {
            Log::warning("Cannot send ticket email: no email address for booking ID {$booking->id}");
            throw new \Exception('Không tìm thấy địa chỉ email của khách hàng.');
        }

        try {
            Mail::to($email)->send(new TicketMail($booking));
            Log::info("Ticket email sent successfully to {$email} for booking ID {$booking->id}");
        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            if (str_contains($errorMessage, '535') || str_contains($errorMessage, 'BadCredentials') || str_contains($errorMessage, 'Username and Password not accepted')) {
                $helpMessage = "\n\n" .
                    "⚠️ LỖI XÁC THỰC GMAIL:\n" .
                    "Gmail không chấp nhận mật khẩu thông thường. Bạn cần:\n" .
                    "1. Bật 2-Step Verification: https://myaccount.google.com/security\n" .
                    "2. Tạo App Password: https://myaccount.google.com/apppasswords\n" .
                    "3. Sử dụng App Password (16 ký tự) trong file .env\n" .
                    "4. Chạy: php artisan config:clear\n\n" .
                    "Hoặc tạm thời dùng log driver trong .env:\n" .
                    "MAIL_MAILER=log\n\n" .
                    "Xem hướng dẫn chi tiết: GMAIL_SETUP_GUIDE.md";

                Log::error("Gmail authentication failed for booking ID {$booking->id}: {$errorMessage}");
                throw new \Exception('Lỗi xác thực Gmail. Vui lòng kiểm tra cấu hình email.' . $helpMessage);
            }

            Log::error("Failed to send ticket email to {$email} for booking ID {$booking->id}: {$errorMessage}");
            throw $e;
        }
    }

    /**
     * Staff: Hiển thị form đặt vé mới (chỉ cho chính staff)
     */
    public function create()
    {
        $this->authorizeAction('đặt vé');
        
        // Staff chỉ đặt vé cho chính họ
        $user = Auth::user();
        if (!$user || !in_array(optional($user->vaiTro)->ten, ['admin', 'staff'])) {
            abort(403, 'Bạn không có quyền đặt vé.');
        }
        
        $movies = Phim::where('trang_thai', 'dang_chieu')
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->get();
        $combos = Combo::where('trang_thai', 1)->get();
        $promotions = KhuyenMai::where('trang_thai', 1)
            ->where('ngay_bat_dau', '<=', now())
            ->where('ngay_ket_thuc', '>=', now())
            ->get();

        return view('admin.bookings.create', compact('movies', 'combos', 'promotions', 'user'));
    }

    /**
     * Staff: API lấy suất chiếu theo phim và ngày
     */
    public function getShowtimesForStaff(Request $request, $movieId)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $movie = Phim::findOrFail($movieId);
        $date = Carbon::parse($request->date)->format('Y-m-d');
        $now = now();

        $showtimes = SuatChieu::where('id_phim', $movieId)
            ->where('trang_thai', 1)
            ->whereDate('thoi_gian_bat_dau', $date)
            ->where('thoi_gian_ket_thuc', '>', $now)
            ->whereHas('phongChieu', function($q) {
                $q->where('trang_thai', 1);
            })
            ->with(['phongChieu'])
            ->orderBy('thoi_gian_bat_dau')
            ->get()
            ->map(function ($showtime) {
                $totalSeats = $showtime->phongChieu->seats()->where('trang_thai', 1)->count();
                $bookedSeats = DB::table('chi_tiet_dat_ve')
                    ->join('dat_ve', 'chi_tiet_dat_ve.id_dat_ve', '=', 'dat_ve.id')
                    ->where('dat_ve.id_suat_chieu', $showtime->id)
                    ->where('dat_ve.trang_thai', 1)
                    ->count();
                $availableSeats = max(0, $totalSeats - $bookedSeats);

                return [
                    'id' => $showtime->id,
                    'time' => $showtime->thoi_gian_bat_dau->format('H:i'),
                    'end_time' => $showtime->thoi_gian_ket_thuc->format('H:i'),
                    'room_name' => $showtime->phongChieu->ten_phong ?? $showtime->phongChieu->name ?? 'Phòng chiếu',
                    'room_type' => $showtime->phongChieu->loai_phong ?? $showtime->phongChieu->type ?? '2D',
                    'available_seats' => $availableSeats,
                    'total_seats' => $totalSeats,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $showtimes
        ]);
    }

    /**
     * Staff: Tạo đặt vé mới
     */
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
            'payment_method' => 'required|in:online,offline,cash',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $showtime = SuatChieu::with(['phim', 'phongChieu'])->findOrFail($request->showtime_id);
            
            // Kiểm tra suất chiếu còn khả dụng
            if ($showtime->trang_thai != 1) {
                throw new \Exception('Suất chiếu không khả dụng.');
            }

            if ($showtime->thoi_gian_bat_dau < now()) {
                throw new \Exception('Suất chiếu đã bắt đầu.');
            }

            // Staff chỉ đặt vé cho chính họ
            $userId = Auth::id();
            if (!$userId) {
                throw new \Exception('Bạn cần đăng nhập để đặt vé.');
            }

            // Kiểm tra ghế đã bị đặt chưa
            $conflictedSeats = [];
            $selectedSeatIds = $request->seat_ids ?? [];
            
            // Đảm bảo là array và filter các giá trị hợp lệ
            if (!is_array($selectedSeatIds)) {
                $selectedSeatIds = array_filter(explode(',', $selectedSeatIds ?? ''), 'is_numeric');
            }
            
            // Convert string IDs to integers
            $selectedSeatIds = array_map('intval', array_filter($selectedSeatIds, 'is_numeric'));
            
            if (empty($selectedSeatIds)) {
                throw new \Exception('Vui lòng chọn ít nhất một ghế!');
            }
            
            foreach ($selectedSeatIds as $seatId) {
                $isTaken = ChiTietDatVe::whereHas('datVe', function ($query) use ($showtime) {
                    $query->where('id_suat_chieu', $showtime->id)
                        ->whereIn('trang_thai', [0, 1]); // Pending hoặc Paid
                })->where('id_ghe', $seatId)->exists();

                if ($isTaken) {
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
            foreach ($selectedSeatIds as $seatId) {
                $ghe = Ghe::with('loaiGhe')->findOrFail($seatId);
                if ($ghe->id_phong != $showtime->id_phong) {
                    throw new \Exception("Ghế {$ghe->so_ghe} không thuộc phòng chiếu này.");
                }
                
                $gia = ($ghe->loaiGhe->he_so_gia ?? 1) * self::BASE_TICKET_PRICE;
                $tongGhe += $gia;
                $seatDetails[] = ['id' => $ghe->id, 'gia' => $gia];
            }

            // Tính giá combo
            $tongCombo = 0;
            $comboDetails = [];
            $comboQuantities = $request->combo_quantities ?? [];
            
            if (is_array($comboQuantities)) {
                foreach ($comboQuantities as $comboId => $qty) {
                    $qty = (int)($qty ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }
                    
                    $combo = Combo::find($comboId);
                    if (!$combo || $combo->trang_thai != 1) {
                        continue;
                    }
                    
                    $tongCombo += ($combo->gia * $qty);
                    $comboDetails[] = ['id' => $combo->id, 'so_luong' => $qty, 'gia' => $combo->gia];
                }
            }

            // Tính khuyến mãi
            $discount = 0;
            $promotionId = null;
            if ($request->promotion_id) {
                $promo = KhuyenMai::findOrFail($request->promotion_id);
                if ($promo->trang_thai == 1 && 
                    $promo->ngay_bat_dau <= now() && 
                    $promo->ngay_ket_thuc >= now()) {
                    $promotionId = $promo->id;
                    $subtotal = $tongGhe + $tongCombo;
                    $discount = $promo->loai_giam === 'phantram'
                        ? round($subtotal * ((float)$promo->gia_tri_giam / 100))
                        : (float)$promo->gia_tri_giam;
                }
            }

            // Tính tổng tiền
            $tongTien = max(0, ($tongGhe + $tongCombo) - $discount);

            // Xác định phương thức thanh toán và trạng thái
            $paymentMethod = $request->payment_method;
            $phuongThucDB = $paymentMethod === 'online' ? 1 : 2; // 1: Online, 2: Offline/Cash
            $trangThai = ($paymentMethod === 'online') ? 0 : 1; // Online: Pending, Offline/Cash: Đã xác nhận
            
            // Tính thời gian hết hạn
            $expiresAt = null;
            if ($paymentMethod === 'online') {
                $expiresAt = Carbon::now()->addMinutes(15);
            } elseif ($paymentMethod === 'offline') {
                $start = Carbon::parse($showtime->thoi_gian_bat_dau);
                if (now()->diffInMinutes($start, false) < 30) {
                    throw new \Exception('Đã quá trễ để đặt vé giữ chỗ (phải trước 30 phút). Vui lòng thanh toán Online.');
                }
                $expiresAt = $start->subMinutes(30);
            }

            // Tạo đặt vé
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

            // Tạo thanh toán
            $paymentStatus = ($paymentMethod === 'online') ? 0 : 1; // Online: Chưa thanh toán, Offline/Cash: Đã thanh toán
            $paymentMethodName = $paymentMethod === 'online' ? 'VNPAY' : ($paymentMethod === 'cash' ? 'Tiền mặt' : 'Offline');
            
            ThanhToan::create([
                'id_dat_ve' => $booking->id,
                'so_tien' => $tongTien,
                'phuong_thuc' => $paymentMethodName,
                'trang_thai' => $paymentStatus,
                'thoi_gian' => $paymentStatus === 1 ? now() : null,
            ]);

            // Cập nhật ShowtimeSeat nếu có (chỉ khi đã thanh toán)
            if ($paymentStatus === 1 && Schema::hasTable('suat_chieu_ghe')) {
                foreach ($selectedSeatIds as $seatId) {
                    ShowtimeSeat::updateOrCreate(
                        [
                            'id_suat_chieu' => $showtime->id,
                            'id_ghe' => $seatId,
                        ],
                        [
                            'status' => 'booked',
                            'hold_expires_at' => null,
                        ]
                    );
                }
            }

            // Cập nhật thống kê thành viên (chỉ khi đã thanh toán)
            if ($paymentStatus === 1 && $userId) {
                $this->updateMemberStats($userId);
            }

            DB::commit();

            // Nếu thanh toán online, chuyển đến VNPay
            if ($paymentMethod === 'online') {
                $vnpUrl = app(\App\Http\Controllers\PaymentController::class)->createVnpayUrl($booking->id, $tongTien);
                return redirect()->away($vnpUrl);
            }

            // Nếu thanh toán offline/cash, chuyển đến trang "Vé của tôi"
            return redirect()->route('admin.bookings.my-bookings')
                ->with('success', 'Đặt vé thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Staff booking error: ' . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
