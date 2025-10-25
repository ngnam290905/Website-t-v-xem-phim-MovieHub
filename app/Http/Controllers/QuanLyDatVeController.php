<?php

namespace App\Http\Controllers;

use App\Models\ChiTietDatVe;
use App\Models\Combo;
use App\Models\DatVe;
use App\Models\Ghe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuanLyDatVeController extends Controller
{
    public function index(Request $request)
    {
        $query = DatVe::with(['nguoiDung', 'suatChieu.phim', 'suatChieu.phongChieu', 'chiTietDatVe.ghe', 'chiTietCombo.combo', 'thanhToan'])
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

        return view('admin.bookings.index', compact('bookings'));
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
            'thanhToan'
        ])->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
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
            'ghe_ids' => 'required|array'
        ]);

        $booking = DatVe::findOrFail($id);

        // Xóa ghế cũ
        $booking->chiTietDatVe()->delete();

        // Thêm ghế mới
        foreach ($request->ghe_ids as $gheId) {
            $ghe = Ghe::find($gheId);
            ChiTietDatVe::create([
                'id_dat_ve' => $booking->id,
                'id_ghe' => $gheId,
                'gia' => $ghe->loaiGhe->he_so_gia * 100000,
            ]);
            $ghe->trang_thai = 0;
            $ghe->save();
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

            return redirect()->route('admin.bookings.index')
                ->with('success', 'Vé đã được xác nhận thành công.');
        }

        return redirect()->route('admin.bookings.index')
            ->with('error', 'Chỉ có thể xác nhận vé đang chờ.');
    }
}
