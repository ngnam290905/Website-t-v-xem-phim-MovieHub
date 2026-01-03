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
            'chiTietFood.food',
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
                                'trang_thai' => 'available',
                                'thoi_gian_het_han' => now()->subDay() // Set về quá khứ để đảm bảo NOT NULL
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
     * Staff/Admin: Hiển thị form đặt vé mới (có thể đặt cho khách hàng tại quầy)
     */
    public function create()
    {
        $this->authorizeAction('đặt vé');
        
        $user = Auth::user();
        if (!$user || !in_array(optional($user->vaiTro)->ten, ['admin', 'staff'])) {
            abort(403, 'Bạn không có quyền đặt vé.');
        }
        
        $movies = Phim::where('trang_thai', 'dang_chieu')
            ->orderBy('ngay_khoi_chieu', 'desc')
            ->get();
        $combos = Combo::where('trang_thai', 1)->get();
        $foods = \App\Models\Food::where('is_active', 1)->get();
        
        // Load danh sách khách hàng (không bao gồm admin và staff)
        $customers = NguoiDung::whereHas('vaiTro', function($q) {
            $q->whereNotIn('ten', ['admin', 'staff']);
        })
        ->orWhereDoesntHave('vaiTro')
        ->orderBy('ho_ten')
        ->get(['id', 'ho_ten', 'email', 'sdt']);

        return view('admin.bookings.create', compact('movies', 'combos', 'foods', 'user', 'customers'));
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
            'customer_id' => 'nullable|integer|exists:nguoi_dung,id',
            'combo_ids' => 'nullable|array',
            'combo_quantities' => 'nullable|array',
            'food_quantities' => 'nullable|array',
            'payment_method' => 'required|in:offline,cash,transfer',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $showtimeId = (int)$request->showtime_id;
            $showtime = SuatChieu::with(['phim', 'phongChieu'])->findOrFail($showtimeId);
            
            // ✅ ĐÚNG: Ép kiểu tất cả attributes từ showtime
            $showtimeIdInt = (int)($showtime->attributes['id'] ?? $showtime->id ?? 0);
            $showtimeTrangThai = (int)($showtime->attributes['trang_thai'] ?? $showtime->trang_thai ?? 0);
            
            if ($showtimeTrangThai != 1) {
                throw new \Exception('Suất chiếu không khả dụng.');
            }

            $thoiGianBatDau = $showtime->thoi_gian_bat_dau;
            if ($thoiGianBatDau && $thoiGianBatDau < now()) {
                throw new \Exception('Suất chiếu đã bắt đầu.');
            }

            // Xác định người dùng: nếu có customer_id thì đặt cho khách hàng, không thì đặt cho chính staff/admin
            $userId = $request->customer_id ? (int)$request->customer_id : (int)Auth::id();
            if (!$userId) {
                throw new \Exception('Vui lòng chọn khách hàng hoặc đăng nhập để đặt vé.');
            }
            
            // Kiểm tra khách hàng có tồn tại không
            $customer = NguoiDung::find($userId);
            if (!$customer) {
                throw new \Exception('Không tìm thấy khách hàng.');
            }
            
            // Kiểm tra nếu chọn khách hàng là admin/staff thì không cho phép (chỉ khi có customer_id)
            if ($request->customer_id) {
                $customerRole = optional($customer->vaiTro)->ten;
                if (in_array($customerRole, ['admin', 'staff'])) {
                    throw new \Exception('Không thể đặt vé cho admin/staff. Vui lòng chọn khách hàng khác.');
                }
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
                $seatIdInt = (int)$seatId;
                $showtimeIdInt = (int)$showtime->id;
                $isTaken = ChiTietDatVe::whereHas('datVe', function ($query) use ($showtimeIdInt) {
                    $query->where('id_suat_chieu', $showtimeIdInt)
                        ->whereIn('trang_thai', [0, 1]); // Pending hoặc Paid
                })->where('id_ghe', $seatIdInt)->exists();

                if ($isTaken) {
                    $seat = Ghe::find($seatIdInt);
                    $conflictedSeats[] = $seat->so_ghe ?? "ID: {$seatIdInt}";
                }
            }

            if (!empty($conflictedSeats)) {
                throw new \Exception('Một hoặc nhiều ghế đã được đặt: ' . implode(', ', $conflictedSeats));
            }

            // Tính giá ghế
            // ✅ ĐÚNG: Tính toán bằng PHP thuần, không dùng DB::raw()
            $tongGhe = 0.0;
            $seatDetails = [];
            // ✅ ĐÚNG: Ép kiểu từ attributes trước
            $showtimePhongId = (int)($showtime->attributes['id_phong'] ?? $showtime->id_phong ?? 0);
            
            foreach ($selectedSeatIds as $seatId) {
                $seatIdInt = (int)$seatId;
                $ghe = Ghe::with('loaiGhe')->findOrFail($seatIdInt);
                
                // ✅ ĐÚNG: Ép kiểu int từ model attribute
                $ghePhongId = (int)($ghe->attributes['id_phong'] ?? $ghe->id_phong ?? 0);
                if ($ghePhongId !== $showtimePhongId) {
                    throw new \Exception("Ghế {$ghe->so_ghe} không thuộc phòng chiếu này.");
                }
                
                // ✅ ĐÚNG: Ép kiểu float từ relationship attribute, không phải Expression
                $loaiGhe = $ghe->loaiGhe;
                if (!$loaiGhe) {
                    throw new \Exception("Không tìm thấy loại ghế cho ghế {$ghe->so_ghe}.");
                }
                
                $heSoGia = (float)($loaiGhe->attributes['he_so_gia'] ?? $loaiGhe->he_so_gia ?? 1.0);
                $gia = (float)($heSoGia * self::BASE_TICKET_PRICE);
                $tongGhe += $gia;
                $seatDetails[] = [
                    'id' => (int)$ghe->id, 
                    'gia' => $gia
                ];
            }

            // Tính giá combo
            // ✅ ĐÚNG: Tính toán bằng PHP thuần, không dùng DB::raw()
            $tongCombo = 0.0;
            $comboDetails = [];
            $comboQuantities = $request->combo_quantities ?? [];
            
            if (is_array($comboQuantities)) {
                foreach ($comboQuantities as $comboId => $qty) {
                    $comboIdInt = (int)$comboId;
                    $qty = (int)($qty ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }
                    
                    $combo = Combo::find($comboIdInt);
                    if (!$combo) {
                        continue;
                    }
                    
                    // ✅ ĐÚNG: Ép kiểu rõ ràng từ model attribute (đã cast decimal:2)
                    $comboTrangThai = (int)($combo->trang_thai ?? 0);
                    if ($comboTrangThai != 1) {
                        continue;
                    }
                    
                    // ✅ ĐÚNG: Ép kiểu float từ model attribute, không phải Expression
                    $comboPrice = (float)($combo->attributes['gia'] ?? $combo->gia ?? 0);
                    $tongCombo += ($comboPrice * $qty);
                    // ✅ ĐÚNG: Ép kiểu ID từ attributes
                    $comboIdInt = (int)($combo->attributes['id'] ?? $combo->id ?? 0);
                    $comboDetails[] = [
                        'id' => $comboIdInt, 
                        'so_luong' => $qty, 
                        'gia' => $comboPrice
                    ];
                }
            }

            // Tính giá đồ ăn
            // ✅ ĐÚNG: Tính toán bằng PHP thuần, không dùng DB::raw()
            $tongFood = 0.0;
            $foodDetails = [];
            $foodQuantities = $request->food_quantities ?? [];
            
            if (is_array($foodQuantities)) {
                foreach ($foodQuantities as $foodId => $qty) {
                    $foodIdInt = (int)$foodId;
                    $qty = (int)($qty ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }
                    
                    $food = \App\Models\Food::find($foodIdInt);
                    if (!$food) {
                        continue;
                    }
                    
                    // ✅ ĐÚNG: Ép kiểu boolean từ model attribute
                    $isActive = (bool)($food->is_active ?? false);
                    if (!$isActive) {
                        continue;
                    }
                    
                    // Kiểm tra số lượng tồn kho
                    // ✅ ĐÚNG: Ép kiểu int từ model attribute
                    $stock = (int)($food->attributes['stock'] ?? $food->stock ?? 0);
                    if ($stock > 0 && $qty > $stock) {
                        throw new \Exception("Đồ ăn '{$food->name}' chỉ còn {$stock} sản phẩm.");
                    }
                    
                    // ✅ ĐÚNG: Ép kiểu float từ model attribute, không phải Expression
                    $price = (float)($food->attributes['price'] ?? $food->price ?? 0);
                    $tongFood += ($price * $qty);
                    // ✅ ĐÚNG: Ép kiểu ID từ attributes
                    $foodIdInt = (int)($food->attributes['id'] ?? $food->id ?? 0);
                    $foodDetails[] = [
                        'id' => $foodIdInt, 
                        'quantity' => $qty, 
                        'price' => $price
                    ];
                }
            }

            // Tính tổng tiền (không áp dụng khuyến mãi cho đặt vé tại quầy)
            // ✅ ĐÚNG: Tất cả đều là float, không có Expression
            $tongTien = (float)($tongGhe + $tongCombo + $tongFood);
            
            // Đảm bảo tổng tiền hợp lệ
            if ($tongTien <= 0) {
                throw new \Exception('Tổng tiền phải lớn hơn 0.');
            }

            // Xác định phương thức thanh toán và trạng thái
            $paymentMethod = $request->payment_method;
            if (!in_array($paymentMethod, ['cash', 'offline', 'transfer'])) {
                throw new \Exception('Phương thức thanh toán không hợp lệ.');
            }
            
            // Nếu là QR payment (transfer), tạo booking pending để chờ xác nhận
            $isQrPayment = $paymentMethod === 'transfer';
            $phuongThucDB = $isQrPayment ? 3 : 2; // 2: Offline/Cash, 3: Chuyển khoản
            $trangThai = $isQrPayment ? 0 : 1; // 0: Pending (QR), 1: Đã xác nhận
            $paymentStatus = $isQrPayment ? 0 : 1; // 0: Chưa thanh toán (QR), 1: Đã thanh toán
            
            // Đặt vé tại quầy không có thời gian hết hạn (trừ QR payment)
            $expiresAt = null;

            // Tạo đặt vé
            // ✅ ĐÚNG: Ép kiểu tất cả giá trị trước khi create
            $booking = DatVe::create([
                'id_nguoi_dung' => (int)$userId,
                'id_suat_chieu' => (int)$showtimeIdInt, // Sử dụng biến đã ép kiểu
                'trang_thai' => (int)$trangThai,
                'tong_tien' => (float)$tongTien,
                'id_khuyen_mai' => null, // Không áp dụng khuyến mãi cho đặt vé tại quầy
                'phuong_thuc_thanh_toan' => (int)$phuongThucDB,
                'expires_at' => $expiresAt,
                'ghi_chu_noi_bo' => $request->notes,
            ]);
            
            // ✅ ĐÚNG: Ép kiểu booking ID ngay sau khi create
            $bookingId = (int)($booking->attributes['id'] ?? $booking->id ?? 0);
            if ($bookingId <= 0) {
                throw new \Exception('Lỗi khi tạo đặt vé.');
            }

            // Tạo chi tiết ghế
            // ✅ ĐÚNG: Sử dụng bookingId đã ép kiểu
            foreach ($seatDetails as $detail) {
                ChiTietDatVe::create([
                    'id_dat_ve' => $bookingId,
                    'id_ghe' => (int)$detail['id'],
                    'gia' => (float)$detail['gia'],
                ]);
            }

            // Tạo chi tiết combo
            // ✅ ĐÚNG: Sử dụng bookingId đã ép kiểu
            foreach ($comboDetails as $detail) {
                ChiTietCombo::create([
                    'id_dat_ve' => $bookingId,
                    'id_combo' => (int)$detail['id'],
                    'so_luong' => (int)$detail['so_luong'],
                    'gia_ap_dung' => (float)$detail['gia'],
                ]);
            }

            // Tạo chi tiết đồ ăn
            // ✅ ĐÚNG: Sử dụng bookingId đã ép kiểu
            foreach ($foodDetails as $detail) {
                \App\Models\ChiTietFood::create([
                    'id_dat_ve' => $bookingId,
                    'food_id' => (int)$detail['id'],
                    'quantity' => (int)$detail['quantity'],
                    'price' => (float)$detail['price'],
                ]);
            }

            // Tạo thanh toán
            // ✅ ĐÚNG: Sử dụng bookingId đã ép kiểu
            $paymentMethodName = $paymentMethod === 'cash' ? 'Tiền mặt' : ($paymentMethod === 'transfer' ? 'Chuyển khoản' : 'Offline');
            
            ThanhToan::create([
                'id_dat_ve' => $bookingId,
                'so_tien' => (float)$tongTien,
                'phuong_thuc' => (string)$paymentMethodName,
                'trang_thai' => (int)$paymentStatus,
                'thoi_gian' => $paymentStatus === 1 ? now() : null,
            ]);

            // Cập nhật ShowtimeSeat nếu có (chỉ khi đã thanh toán)
            // ✅ ĐÚNG: Sử dụng showtimeIdInt đã ép kiểu
            if ($paymentStatus === 1 && Schema::hasTable('suat_chieu_ghe')) {
                $showtime = SuatChieu::find($showtimeIdInt);
                $thoiGianKetThuc = $showtime ? $showtime->thoi_gian_ket_thuc : now()->addDays(1);
                
                foreach ($selectedSeatIds as $seatId) {
                    $seatIdInt = (int)$seatId;
                    
                    // Kiểm tra record đã tồn tại chưa
                    $existing = ShowtimeSeat::where('id_suat_chieu', $showtimeIdInt)
                        ->where('id_ghe', $seatIdInt)
                        ->first();
                    
                    if ($existing) {
                        // Nếu đã tồn tại, chỉ cập nhật trạng thái (không động đến thoi_gian_het_han)
                        $existing->update(['trang_thai' => 'booked']);
                    } else {
                        // Nếu chưa tồn tại, tạo mới với thoi_gian_het_han hợp lệ
                        ShowtimeSeat::create([
                            'id_suat_chieu' => $showtimeIdInt,
                            'id_ghe' => $seatIdInt,
                            'trang_thai' => 'booked',
                            'thoi_gian_giu' => now(),
                            'thoi_gian_het_han' => $thoiGianKetThuc,
                            'gia_giu' => 0,
                        ]);
                    }
                }
            }

            // Cập nhật thống kê thành viên (chỉ khi đã thanh toán)
            if ($paymentStatus === 1 && $userId) {
                $this->updateMemberStats((int)$userId);
            }

            // Trừ kho đồ ăn khi thanh toán thành công (chỉ khi đã thanh toán)
            // ✅ ĐÚNG: Sử dụng decrement() method của Laravel, không dùng DB::raw()
            if ($paymentStatus === 1 && !empty($foodDetails)) {
                try {
                    foreach ($foodDetails as $detail) {
                        $foodId = (int)($detail['id'] ?? 0);
                        $quantity = (int)($detail['quantity'] ?? 0);
                        
                        if ($foodId > 0 && $quantity > 0) {
                            // ✅ ĐÚNG: decrement() tự động xử lý, không cần DB::raw()
                            \App\Models\Food::where('id', $foodId)->decrement('stock', $quantity);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Lỗi trừ kho đồ ăn sau thanh toán: " . $e->getMessage());
                    throw $e; // Re-throw để rollback transaction
                }
            }

            DB::commit();

            // Nếu là QR payment, redirect đến trang QR payment
            if ($isQrPayment) {
                return redirect()->route('admin.bookings.qr-payment', $bookingId)
                    ->with('info', 'Vui lòng thanh toán bằng mã QR.');
            }

            // Đặt vé tại quầy đã thanh toán ngay, chuyển đến trang danh sách đặt vé
            return redirect()->route('admin.bookings.index')
                ->with('success', 'Đặt vé thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Staff booking error: ' . $e->getMessage());
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Hiển thị trang thanh toán QR cho admin bookings
     * GET /admin/bookings/{bookingId}/qr-payment
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
                return redirect()->route('admin.bookings.show', $bookingId)
                    ->with('info', 'Vé đã được thanh toán.');
            }
            
            // Tạo mã QR fake (mã đơn hàng + timestamp)
            $qrCode = 'QR' . str_pad($bookingId, 6, '0', STR_PAD_LEFT) . '-' . time();
            
            return view('admin.bookings.qr-payment', compact('booking', 'qrCode'));
        } catch (\Exception $e) {
            Log::error('QuanLyDatVe showQrPayment error: ' . $e->getMessage());
            return redirect()->route('admin.bookings.index')
                ->with('error', 'Không tìm thấy đơn hàng.');
        }
    }

    /**
     * Xác nhận thanh toán QR
     * POST /admin/bookings/{bookingId}/qr-payment/confirm
     */
    public function confirmQrPayment(Request $request, $bookingId)
    {
        $request->validate([
            'booking_id' => 'required|integer|exists:dat_ve,id',
        ]);

        try {
            DB::beginTransaction();
            
            $booking = DatVe::with(['chiTietFood', 'chiTietDatVe'])->findOrFail($bookingId);
            
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
                \App\Models\Food::where('id', $foodDetail->food_id)
                    ->decrement('stock', $foodDetail->quantity);
            }
            
            // Cập nhật ShowtimeSeat
            if (Schema::hasTable('suat_chieu_ghe')) {
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
            
            // Cập nhật thống kê thành viên
            if ($booking->id_nguoi_dung) {
                $this->updateMemberStats($booking->id_nguoi_dung);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Xác nhận thanh toán thành công',
                'redirect_url' => route('admin.bookings.show', $booking->id),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('QuanLyDatVe confirmQrPayment error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xác nhận thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
}
