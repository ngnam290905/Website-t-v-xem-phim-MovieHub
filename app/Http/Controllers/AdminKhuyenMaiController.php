<?php

namespace App\Http\Controllers;

use App\Models\KhuyenMai;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminKhuyenMaiController extends Controller
{
    // Danh sách khuyến mãi
    public function index(Request $request)
    {
        $query = KhuyenMai::query();
        
        // Tìm kiếm
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ma_km', 'like', "%{$search}%")
                  ->orWhere('mo_ta', 'like', "%{$search}%")
                  ->orWhere('dieu_kien', 'like', "%{$search}%");
            });
        }
        
        // Lọc theo trạng thái thời gian
        if ($request->filled('status')) {
            $now = Carbon::now();
            if ($request->status === 'active') {
                // Còn hạn sử dụng
                $query->where('ngay_bat_dau', '<=', $now)
                      ->where('ngay_ket_thuc', '>=', $now);
            } elseif ($request->status === 'expired') {
                // Hết hạn
                $query->where('ngay_ket_thuc', '<', $now);
            } elseif ($request->status === 'upcoming') {
                // Chưa bắt đầu
                $query->where('ngay_bat_dau', '>', $now);
            }
        }
        
        $khuyenmai = $query->orderByDesc('id')->paginate(10)->withQueryString();
        
        // Thống kê
        $now = Carbon::now();
        $stats = [
            'total' => KhuyenMai::count(),
            'active' => KhuyenMai::where('ngay_bat_dau', '<=', $now)
                                 ->where('ngay_ket_thuc', '>=', $now)
                                 ->count(),
            'expired' => KhuyenMai::where('ngay_ket_thuc', '<', $now)->count(),
            'upcoming' => KhuyenMai::where('ngay_bat_dau', '>', $now)->count(),
        ];
        
        return view('admin.khuyenmai.index', compact('khuyenmai', 'stats'));
    }

    // Xem chi tiết khuyến mãi
    public function show($id)
    {
        $khuyenmai = KhuyenMai::findOrFail($id);
        return view('admin.khuyenmai.show', compact('khuyenmai'));
    }

    // Form tạo mới
    public function create()
    {
        return view('admin.khuyenmai.create');
    }

    // Lưu khuyến mãi mới
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ma_km' => 'required|string|unique:khuyen_mai,ma_km',
            'mo_ta' => 'nullable|string',
            'ngay_bat_dau' => 'required|date',
            'ngay_ket_thuc' => 'required|date|after_or_equal:ngay_bat_dau',
            'gia_tri_giam' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->loai_giam === 'phantram' && $value > 40) {
                        $fail('Giá trị giảm theo phần trăm không được lớn hơn 40%.');
                    }
                },
            ],
            'loai_giam' => 'required|in:phantram,codinh',
            'dieu_kien' => 'nullable|string',
            'trang_thai' => 'required|boolean',
        ]);
        KhuyenMai::create($validated);
        return redirect()->route('admin.khuyenmai.index')->with('success', 'Tạo mã khuyến mãi thành công!');
    }

    // Form sửa
    public function edit($id)
    {
    $khuyenmai = KhuyenMai::findOrFail($id);
    return view('admin.khuyenmai.edit', compact('khuyenmai'));
    }

    // Cập nhật khuyến mãi
    public function update(Request $request, $id)
    {
        $km = KhuyenMai::findOrFail($id);
        $validated = $request->validate([
            'ma_km' => 'required|string|unique:khuyen_mai,ma_km,' . $km->id,
            'mo_ta' => 'nullable|string',
            'ngay_bat_dau' => 'required|date',
            'ngay_ket_thuc' => 'required|date|after_or_equal:ngay_bat_dau',
            'gia_tri_giam' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->loai_giam === 'phantram' && $value > 40) {
                        $fail('Giá trị giảm theo phần trăm không được lớn hơn 40%.');
                    }
                },
            ],
            'loai_giam' => 'required|in:phantram,codinh',
            'dieu_kien' => 'nullable|string',
            'trang_thai' => 'required|boolean',
        ]);
        $km->update($validated);
        return redirect()->route('admin.khuyenmai.index')->with('success', 'Cập nhật mã khuyến mãi thành công!');
    }

    // Xóa khuyến mãi
    public function destroy($id)
    {
        $km = KhuyenMai::findOrFail($id);
        $km->delete();
        return redirect()->route('admin.khuyenmai.index')->with('success', 'Đã xóa mã khuyến mãi!');
    }
}
