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
use App\Mail\TicketMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;


class QuanLyDatVeController extends Controller
{
    private const BASE_TICKET_PRICE = 100000;

    /**
     * Danh sách đặt vé + tự động hủy vé quá hạn 5 phút (COD/chưa chọn PTTT)
     */
    public function index(Request $request)
    {
        // Auto cancel expired offline bookings (using expires_at or created_at + 5 minutes)
        $expiredQuery = DatVe::with('thanhToan')
            ->where('trang_thai', 0);
        
        // Check if phuong_thuc_thanh_toan column exists before filtering
        if (Schema::hasColumn('dat_ve', 'phuong_thuc_thanh_toan')) {
            $expiredQuery->where('phuong_thuc_thanh_toan', 2); // offline payment
        }
        // If column doesn't exist, we'll check all pending bookings (backward compatibility)
        
        $expired = $expiredQuery->where(function($query) {
                // Check if expires_at column exists
                if (Schema::hasColumn('dat_ve', 'expires_at')) {
                    $query->where('expires_at', '<=', now())
                          ->orWhere(function($q) {
                              // Fallback: check created_at + 5 minutes if expires_at is null
                              $q->whereNull('expires_at')
                                ->where('created_at', '<=', now()->subMinutes(5));
                          });
                } else {
                    // Fallback: use created_at + 5 minutes
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
                Log::error('Auto-cancel error: '.$e->getMessage());
                try {
                    $bk->update(['trang_thai' => 2]);
                } catch (\Throwable $e2) {
                    Log::error('Failed to update booking status: '.$e2->getMessage());

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
                ->whereDate('created_at', now()->toDateString())
                ->get()
                ->sum(fn($b) => (float) ($b->tong_tien ?? $b->tong_tien_hien_thi ?? 0)),
        ];

        return view('admin.bookings.index', array_merge(['bookings' => $bookings], $stats));
    }

    
    public function show($id)
    {
        $booking = DatVe::with([
            'suatChieu.phim', 'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe', 'chiTietCombo.combo', 'thanhToan', 'khuyenMai'
            'nguoiDung.diemThanhVien',
            'nguoiDung',
            'suatChieu.phim', 'suatChieu.phongChieu',
            'chiTietDatVe.ghe.loaiGhe', 'chiTietCombo.combo', 'thanhToan', 'khuyenMai'
            'suatChieu.phongChieu',
            'chiTietDatVe.ghe',
            'chiTietCombo.combo',
            'thanhToan',
            'khuyenMai'
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
                'current' => $s->id === $booking->id_suat_chieu,
            ]);

        return response()->json($showtimes);
    }

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
            'room' => ['id' => $suat->id_phong],
            'seats' => $seats->map(fn($g) => [
                'id' => $g->id,
                'label' => $g->so_ghe,
                'booked' => in_array($g->id, $bookedSeatIds)
            ]),
        ]);
    }

    // --- HELPERS ---
    private function authorizeAction($actionName)
    {
        $role = optional(Auth::user()->vaiTro)->ten;
        if (!in_array($role, ['admin', 'staff'])) {
    // --- HELPERS ---
    private function authorizeAction($actionName)
    {
        if (optional(Auth::user()->vaiTro)->ten !== 'admin') {
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
            Log::error('Send ticket email error: '.$e->getMessage());
            
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
            
            // Check if it's a Gmail authentication error
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
}
