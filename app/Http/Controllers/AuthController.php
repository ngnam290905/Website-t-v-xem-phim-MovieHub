<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        $query = NguoiDung::with('vaiTro')->orderBy('id', 'desc');

        // 🔍 Nếu có tìm kiếm theo tên hoặc email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ho_ten', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // ✅ Phân trang 10 người dùng / trang
        $users = $query->paginate(10);

        // ✅ Giữ lại từ khóa khi chuyển trang
        $users->appends(['search' => $request->search]);

        return view('admin.users.index', compact('users'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'ho_ten' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('nguoi_dung', 'email')],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $userRole = \App\Models\VaiTro::where('ten', 'user')->first();
        
        $user = NguoiDung::create([
            'ho_ten' => $validated['ho_ten'],
            'email' => $validated['email'],
            'mat_khau' => Hash::make($validated['password']),
            'id_vai_tro' => $userRole ? $userRole->id : null,
            'trang_thai' => 1,
        ]);

        Auth::login($user);

        return redirect()->route('home');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            $userRole = optional($user->vaiTro)->ten;
            
            if (in_array($userRole, ['admin', 'staff'])) {
                return redirect()->intended(route('admin.dashboard'));
            } else {
                return redirect()->intended(route('home'));
            }
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}


