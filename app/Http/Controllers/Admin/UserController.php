<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\VaiTro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\DiemThanhVien;
use App\Models\HangThanhVien;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = NguoiDung::with(['vaiTro', 'diemThanhVien', 'hangThanhVien'])
        ->whereNull('deleted_at');

        // 🔍 Nếu có tìm kiếm
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(10);

        return view('admin.users.index', compact('users'));
    }


    public function create()
    {
        $roles = VaiTro::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ho_ten' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:nguoi_dung,email',
            'mat_khau' => 'required|string|min:6|confirmed',
            'sdt' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string|max:255',
            'id_vai_tro' => 'required|exists:vai_tro,id',
            'trang_thai' => 'boolean',
        ]);

        // Hash mật khẩu
        $validated['mat_khau'] = Hash::make($validated['mat_khau']);

        // Đảm bảo trạng thái là 0 hoặc 1
        $validated['trang_thai'] = $request->has('trang_thai') ? 1 : 0;

        // Tạo user mới
        NguoiDung::create($validated);

        // Redirect về trang danh sách người dùng với thông báo
        return redirect()->route('admin.users.index')->with('success', 'Tạo tài khoản thành công.');
    }

    public function show($id)
    {
        $user = NguoiDung::with('vaiTro')->findOrFail($id);
        return view('admin.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = NguoiDung::with(['diemThanhVien', 'hangThanhVien'])->findOrFail($id);
        $roles = VaiTro::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
    {
        $user = NguoiDung::findOrFail($id);

        $validated = $request->validate([
            'ho_ten' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:100', Rule::unique('nguoi_dung', 'email')->ignore($user->id)],
            'mat_khau' => 'nullable|string|min:6|confirmed',
            'sdt' => 'nullable|string|max:20',
            'dia_chi' => 'nullable|string|max:255',
            'id_vai_tro' => 'required|exists:vai_tro,id',
            'trang_thai' => 'boolean',
            'tong_diem' => 'nullable|integer|min:0',
            'ten_hang' => 'nullable|string|max:50',
            'tong_chi_tieu' => 'nullable|numeric|min:0',
        ]);

        $data = $request->only([
            'ho_ten', 'email', 'sdt', 'dia_chi', 'id_vai_tro', 'trang_thai', 'tong_chi_tieu'
        ]);

        if ($request->filled('mat_khau')) {
            $data['mat_khau'] = Hash::make($request->mat_khau);
        }

        $data['trang_thai'] = $request->has('trang_thai') ? 1 : 0;

        // Cập nhật user
        $user->update($data);

        // Cập nhật điểm thành viên
        if ($request->filled('tong_diem')) {
            DiemThanhVien::updateOrCreate(
                ['id_nguoi_dung' => $user->id],
                ['tong_diem' => $request->tong_diem]
            );
        }

        // Cập nhật hạng thành viên
        if ($request->filled('ten_hang')) {
            HangThanhVien::updateOrCreate(
                ['id_nguoi_dung' => $user->id],
                ['ten_hang' => $request->ten_hang]
            );
        }

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật tài khoản thành công.');
    }

    public function destroy($id)
    {
        $user = NguoiDung::findOrFail($id);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Xóa tài khoản thành công.');
    }

    public function trash()
    {
        // Lấy danh sách user đã bị xóa mềm
        $users = NguoiDung::onlyTrashed()->with('vaiTro')->paginate(10);
        return view('admin.users.trash', compact('users'));
    }

    public function restore($id)
    {
        $user = NguoiDung::withTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.trash')->with('success', 'Khôi phục tài khoản thành công.');
    }


}