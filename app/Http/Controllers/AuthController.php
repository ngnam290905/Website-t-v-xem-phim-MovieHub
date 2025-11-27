<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
<<<<<<< HEAD
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:nguoi_dung,email']);

        // TODO: Implement password reset email sending
        // For now, just return with success message
        return back()->with('status', 'Chúng tôi đã gửi link đặt lại mật khẩu đến email của bạn!');
    }

    public function showResetPasswordForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:nguoi_dung,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // TODO: Implement password reset logic
        // For now, just return with success message
        return redirect()->route('login.form')->with('status', 'Mật khẩu đã được đặt lại thành công!');
=======
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
>>>>>>> origin/hoanganh
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'ho_ten' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('nguoi_dung', 'email')],
            'sdt' => ['required', 'string', 'max:20', Rule::unique('nguoi_dung', 'sdt')],
            'dia_chi' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $userRole = \App\Models\VaiTro::where('ten', 'user')->first();
        
        $user = NguoiDung::create([
            'ho_ten' => $validated['ho_ten'],
            'email' => $validated['email'],
            'sdt' => $validated['sdt'],
            'dia_chi' => $validated['dia_chi'],
            'mat_khau' => Hash::make($validated['password']),
            'id_vai_tro' => $userRole ? $userRole->id : null,
            'trang_thai' => 1,
        ]);

        Auth::login($user);

        return redirect()->route('home');
    }

    public function login(Request $request)
    {
        try {
            // Debug: Log the incoming request data
            Log::info('Login attempt', $request->only('email'));

            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]);

            if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
                $request->session()->regenerate();
                
                $user = Auth::user();
                
                // Check if user exists and has a role
                if ($user && $user->vaiTro) {
                    $userRole = $user->vaiTro->ten;
                    
                    if ($userRole === 'admin') {
                        return redirect()->intended(route('admin.dashboard'));
                    } elseif ($userRole === 'staff') {
                        return redirect()->intended(route('staff.dashboard'));
                    }
                }
                
                // Default redirect for users with no role or invalid role
                return redirect()->intended(route('home'));
            }

            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không chính xác.',
            ])->withInput($request->only('email', 'remember'));
            
        } catch (\Exception $e) {
            Log::error('Login error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors([
                'email' => 'Đã xảy ra lỗi khi đăng nhập. Vui lòng thử lại sau.',
            ])->withInput($request->only('email', 'remember'));
        }
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}


