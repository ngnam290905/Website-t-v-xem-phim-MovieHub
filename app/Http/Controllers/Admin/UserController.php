<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NguoiDung;
use App\Models\VaiTro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = NguoiDung::with('vaiTro')
        ->whereNull('deleted_at') // chỉ lấy user chưa bị xóa
        ->paginate(10);
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

    public function edit($id)
    {
        $user = NguoiDung::findOrFail($id);
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
        ]);

        // Chỉ cập nhật mật khẩu nếu có nhập
        $data = $validated;
        if ($request->filled('mat_khau')) {
            $data['mat_khau'] = Hash::make($validated['mat_khau']);
        } else {
            unset($data['mat_khau']);
        }

        // Đảm bảo trạng thái là 0 hoặc 1
        $data['trang_thai'] = $request->has('trang_thai') ? 1 : 0;

        // Cập nhật dữ liệu
        $user->update($data);

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