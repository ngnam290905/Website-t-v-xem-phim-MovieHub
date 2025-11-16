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
    private const BASE_TICKET_PRICE = 100000;
    public function index(Request $request)
    {
        $query = DatVe::with(['nguoiDung', 'suatChieu.phim', 'suatChieu.phongChieu', 'chiTietDatVe.ghe', 'chiTietCombo.combo', 'thanhToan', 'khuyenMai'])
            ->orderBy('created_at', 'desc');

        // 🔹 Lọc theo trạng thái (ĐÃ CẬP NHẬT)
        if ($request->filled('status')) {

            // 💡 LOGIC MỚI: Thêm trường hợp lọc 'expired'
            if ($request->status == 'expired') {
                $query->where('trang_thai', '!=', 2) // Chưa bị hủy
                    ->whereHas('suatChieu', function ($q) {
                        $q->where('thoi_gian_bat_dau', '<', now()); // Nhưng suất chiếu đã qua
                    });
            } else {
                // Lọc như cũ
                $query->where('trang_thai', $request->status);
            }
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

        // 💡 STATS MỚI: Đếm số vé đã hết hạn
        $expiredCount = (int) DatVe::where('trang_thai', '!=', 2) // Chưa bị hủy
            ->whereHas('suatChieu', function ($q) {
                $q->where('thoi_gian_bat_dau', '<', now()); // Suất chiếu đã qua
            })
            ->count();

        $todayConfirmed = DatVe::where('trang_thai', 1)
            ->whereDate('created_at', now()->toDateString())
            ->get();
        $revenueToday = (float) $todayConfirmed->sum(function ($b) {
            return (float) ($b->tong_tien ?? $b->tong_tien_hien_thi ?? 0);
        });

        return view('admin.bookings.index', compact(
            'bookings',
            'totalBookings',
            'pendingCount',
            'confirmedCount',
            'canceledCount',
            'requestCancelCount',
            'expiredCount', // 💡 Thêm biến này
            'revenueToday'
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
            ->get(['id', 'so_ghe', 'so_hang', 'id_loai']);

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
            'seats' => $seats->map(function ($g) use ($bookedSeatIds) {
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
                if ($detail->ghe) { // ⚠️ GỢI Ý: Thêm kiểm tra if ($detail->ghe)
                    $detail->ghe->trang_thai = 1; // Giải phóng ghế
                    $detail->ghe->save();
                }
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

        $booking = DatVe::with(['chiTietDatVe', 'chiTietCombo', 'suatChieu', 'khuyenMai'])->findOrFail($id);
        $combos = Combo::where('trang_thai', 1)->get();

        // 💡 CẢI TIẾN 2: Lấy cả ID và SỐ LƯỢNG combo đã chọn
        $selectedComboIds = $booking->chiTietCombo->pluck('id_combo')->toArray();
        $selectedComboQuantities = $booking->chiTietCombo->pluck('so_luong', 'id_combo')->toArray(); // [id => so_luong]

        $selectedGheIds = $booking->chiTietDatVe->pluck('id_ghe')->toArray();


        return view('admin.bookings.edit', compact(
            'booking',
            'combos',
            'selectedComboIds',
            'selectedGheIds',
            'selectedComboQuantities' // 💡 Thêm
        ));
    }

    // ✅ 5. Cập nhật vé (chỉ Admin)
    public function update(Request $request, $id)
    {
        $userRole = optional(Auth::user()->vaiTro)->ten;

        if ($userRole !== 'admin') {
            abort(403, 'Bạn không có quyền cập nhật vé.');
        }

        $request->validate([
            'ghe_ids' => 'nullable|string',
            'suat_chieu_id' => 'nullable|integer|exists:suat_chieu,id',
            'ghi_chu_noi_bo' => 'nullable|string',
            'trang_thai' => 'nullable|in:0,1,2,3',
            'trang_thai_thanh_toan' => 'nullable|in:0,1,2',
            'ma_km' => 'nullable|string',
            // 💡 CẢI TIẾN 2: Thêm validation cho combo
            'combo_ids' => 'nullable|array',
            'combo_ids.*' => 'integer|exists:combo,id',
            'combo_quantities' => 'nullable|array'
        ]);

        $booking = DatVe::findOrFail($id);

        $result = DB::transaction(function () use ($request, $booking) {

            $oldStatus = (int) $booking->trang_thai;
            $suatChieu = SuatChieu::find($booking->id_suat_chieu); // Lấy suất chiếu

            // 1. THAY ĐỔI SUẤT CHIẾU (nếu có)
            if ($request->filled('suat_chieu_id')) {
                $booking->id_suat_chieu = (int) $request->input('suat_chieu_id');
                // Lấy lại suất chiếu MỚI
                $suatChieu = SuatChieu::find($booking->id_suat_chieu);
            }

            // 2. XỬ LÝ GHẾ
            $tongGhe = 0;
            if ($request->has('ghe_ids')) {
                // ... (Giữ nguyên logic giải phóng ghế cũ) ...
                foreach ($booking->chiTietDatVe as $detail) {
                    if ($detail->ghe) {
                        $detail->ghe->trang_thai = 1;
                        $detail->ghe->save();
                    }
                }
                $booking->chiTietDatVe()->delete();

                // C. Chuẩn hóa và thêm ghế MỚI
                $seatIds = [];
                if ($request->filled('ghe_ids')) {
                    $seatIds = array_filter(array_unique(explode(',', $request->input('ghe_ids'))), 'is_numeric');
                }

                foreach ($seatIds as $gheId) {
                    $ghe = Ghe::with('loaiGhe')->find($gheId);
                    if (!$ghe) continue;
                    if ($ghe->trang_thai != 1) {
                        throw new \Exception("Ghế " . $ghe->so_ghe . " đã có người đặt trong lúc bạn thao tác.");
                    }

                    // 💡 CẢI TIẾN 1: Lấy giá vé từ hằng số
                    $basePrice = self::BASE_TICKET_PRICE;
                    // (Bạn cũng có thể lấy giá từ $suatChieu nếu bạn thêm cột giá vào bảng suat_chieu)
                    $gia = ($ghe->loaiGhe->he_so_gia ?? 1) * $basePrice;

                    ChiTietDatVe::create([
                        'id_dat_ve' => $booking->id,
                        'id_ghe' => $gheId,
                        'gia' => $gia,
                    ]);
                    $tongGhe += $gia;
                    $ghe->trang_thai = 0;
                    $ghe->save();
                }
            } else {
                $tongGhe = (float) $booking->chiTietDatVe()->sum('gia');
            }


            // 3. XỬ LÝ COMBO
            $tongCombo = 0;
            // 💡 CẢI TIẾN 2: Thay đổi logic xử lý combo
            // Chỉ xử lý nếu 'combo_ids' được gửi lên (kể cả khi nó là mảng rỗng)
            if ($request->has('combo_ids')) {
                // A. Xóa combo CŨ
                $booking->chiTietCombo()->delete();

                // B. Thêm combo MỚI
                $comboIds = $request->input('combo_ids', []); // Mảng các ID được check
                $comboQuantities = $request->input('combo_quantities', []); // Mảng [id => so_luong]

                if (is_array($comboIds) && count($comboIds) > 0) {
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

                    foreach ($validCombos as $cb) {
                        $price = (float) ($cb->gia ?? 0);
                        // Lấy số lượng từ mảng quantities, mặc định là 1
                        $quantity = (int) ($comboQuantities[$cb->id] ?? 1);
                        if ($quantity < 1) $quantity = 1; // Đảm bảo số lượng ít nhất là 1

                        $booking->chiTietCombo()->create([
                            'id_combo' => $cb->id,
                            'so_luong' => $quantity,
                            'gia_ap_dung' => $price,
                        ]);
                        $tongCombo += ($price * $quantity);
                    }
                }
            } else {
                // Giữ nguyên combo cũ và tính tổng
                $tongCombo = (float) $booking->chiTietCombo()->sum(DB::raw('gia_ap_dung * COALESCE(so_luong,1)'));
            }

            // 4. LƯU GHI CHÚ NỘI BỘ
            if ($request->has('ghi_chu_noi_bo') && Schema::hasColumn('dat_ve', 'ghi_chu_noi_bo')) {
                $booking->ghi_chu_noi_bo = $request->input('ghi_chu_noi_bo');
            }

            // 5. ÁP DỤNG KHUYẾN MÃI
            $discount = 0;
            if ($request->filled('ma_km')) {
                $code = trim($request->input('ma_km'));
                $promo = KhuyenMai::where('ma_km', $code)
                    ->where('trang_thai', 1)
                    ->whereDate('ngay_bat_dau', '<=', now())
                    ->whereDate('ngay_ket_thuc', '>=', now())
                    ->first();
                if (!$promo) {
                    throw new \Exception('Mã khuyến mãi không hợp lệ hoặc đã hết hạn.');
                }
                if ($promo->loai_giam === 'phantram') {
                    $discount = round(($tongGhe + $tongCombo) * ((float)$promo->gia_tri_giam / 100));
                } else { // codinh
                    $discount = (float)$promo->gia_tri_giam;
                }
                $booking->id_khuyen_mai = $promo->id;
            } elseif ($request->has('ma_km')) { // Gửi lên nhưng rỗng = Xóa mã
                $booking->id_khuyen_mai = null;
            } else {
                // Không gửi 'ma_km', giữ nguyên KM cũ (nếu có) và tính lại
                if ($booking->id_khuyen_mai && $booking->khuyenMai) {
                    $promo = $booking->khuyenMai;
                    if ($promo->loai_giam === 'phantram') {
                        $discount = round(($tongGhe + $tongCombo) * ((float)$promo->gia_tri_giam / 100));
                    } else {
                        $discount = (float)$promo->gia_tri_giam;
                    }
                }
            }

            // 6. GIẢM THEO HẠNG THÀNH VIÊN
            $memberDiscount = 0;
            if ($booking->id_nguoi_dung) {
                // (Logic này nên được đưa ra hàm private hoặc service)
                $tier = optional(\App\Models\HangThanhVien::where('id_nguoi_dung', $booking->id_nguoi_dung)->first())->ten_hang;
                if ($tier) {
                    $normalized = mb_strtolower($tier);
                    if ($normalized === 'đồng' || $normalized === 'dong') {
                        $memberDiscount = 10000;
                    } elseif ($normalized === 'bạc' || $normalized === 'bac') {
                        $memberDiscount = 15000;
                    } elseif ($normalized === 'vàng' || $normalized === 'vang') {
                        $memberDiscount = 20000;
                    } elseif ($normalized === 'kim cương' || $normalized === 'kim cuong') {
                        $memberDiscount = 25000;
                    }
                }
            }

            // 7. CẬP NHẬT TRẠNG THÁI (ĐÃ TÍCH HỢP LOGIC LUỒNG)
            if ($request->has('trang_thai')) {
                $newStatus = (int) $request->input('trang_thai');

                if ($oldStatus !== $newStatus) {
                    $isValidTransition = false;
                    switch ($oldStatus) {
                        case 0: // Từ: Chờ xác nhận
                            $isValidTransition = in_array($newStatus, [1, 2, 3]); // -> Xác nhận, Hủy, Yêu cầu hủy
                            break;
                        case 1: // Từ: Đã xác nhận
                            $isValidTransition = ($newStatus === 2); // -> Chỉ có thể Hủy
                            break;
                        case 3: // Từ: Yêu cầu hủy
                            $isValidTransition = in_array($newStatus, [1, 2]); // -> Xác nhận (từ chối), Hủy (đồng ý)
                            break;
                        case 2: // Từ: Đã hủy
                            $isValidTransition = false; // Không thể đi đâu
                            break;
                    }

                    if (!$isValidTransition) {
                        throw new \Exception('Không thể chuyển trạng thái không hợp lệ (từ ' . $oldStatus . ' sang ' . $newStatus . ').');
                    }

                    $booking->trang_thai = $newStatus;
                }
            }

            // 8. CẬP NHẬT TỔNG TIỀN
            if (isset($booking->tong_tien)) {
                $booking->tong_tien = max(0, ($tongGhe + $tongCombo) - $discount - $memberDiscount);
            }

            // Cập nhật trạng thái thanh toán nếu truyền vào
            if ($request->filled('trang_thai_thanh_toan')) {
                $booking->trang_thai_thanh_toan = (int) $request->input('trang_thai_thanh_toan');
            }

            // LƯU TẤT CẢ THAY ĐỔI
            $booking->save();

            // Return true để báo transaction thành công
            return true;
        });

        // Xử lý nếu Transaction thất bại (do throw Exception)
        if ($result instanceof \Exception) {
            return back()->withInput()->with('error', $result->getMessage());
        }

        // 9. CẬP NHẬT LẠI ĐIỂM/HẠNG (sau khi transaction thành công)
        // Chỉ chạy nếu trạng thái bị thay đổi (ví dụ sang Hủy hoặc Xác nhận)
        if ($booking->id_nguoi_dung && $request->has('trang_thai')) {
            $this->recalcMembershipTier((int)$booking->id_nguoi_dung);
            $this->recalcMemberPoints((int)$booking->id_nguoi_dung);
        }

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
            // ⚠️ GỢI Ý: Tạo hàm private cho việc này
            // $this->recomputeBookingTotal($booking); 

            return redirect()->route('admin.bookings.index')
                ->with('success', 'Vé đã được xác nhận thành công.');
        }

        return redirect()->route('admin.bookings.index')
            ->with('error', 'Chỉ có thể xác nhận vé đang chờ.');
    }

    // ---------------------------------------------------------------------
    // ⚠️ GỢI Ý: Các hàm private này nên chuyển sang Service
    // ---------------------------------------------------------------------

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
