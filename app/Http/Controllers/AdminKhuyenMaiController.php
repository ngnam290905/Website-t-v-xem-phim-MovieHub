<?php

namespace App\Http\Controllers;

use App\Models\KhuyenMai;
use Illuminate\Http\Request;

class AdminKhuyenMaiController extends Controller
{
    // Danh sách khuyến mãi
    public function index()
    {
        $khuyenmai = KhuyenMai::orderByDesc('id')->paginate(10);
        return view('admin.khuyenmai.index', compact('khuyenmai'));
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
            'gia_tri_giam' => 'required|numeric|min:0',
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
            'gia_tri_giam' => 'required|numeric|min:0',
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
