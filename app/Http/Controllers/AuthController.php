<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use App\Models\Phim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        // Lấy danh sách phim đang chiếu để hiển thị trong carousel
        $movies = Phim::where('trang_thai', 'dang_chieu')
            ->orderByDesc('diem_danh_gia')
            ->orderByDesc('ngay_khoi_chieu')
            ->limit(8)
            ->get();
        
        return view('auth.login', compact('movies'));
    }

    public function showRegisterForm()
    {
        // Lấy danh sách phim đang chiếu để hiển thị trong carousel
        $movies = Phim::where('trang_thai', 'dang_chieu')
            ->orderByDesc('diem_danh_gia')
            ->orderByDesc('ngay_khoi_chieu')
            ->limit(8)
            ->get();
        
        return view('auth.register', compact('movies'));
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
            
            if ($userRole === 'admin') {
                return redirect()->intended(route('admin.dashboard'));
            } elseif ($userRole === 'staff') {
                return redirect()->intended(route('staff.dashboard'));
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

    public function showForgotPasswordForm()
    {
        // Lấy danh sách phim đang chiếu để hiển thị trong carousel
        $movies = Phim::where('trang_thai', 'dang_chieu')
            ->orderByDesc('diem_danh_gia')
            ->orderByDesc('ngay_khoi_chieu')
            ->limit(8)
            ->get();
        
        return view('auth.forgot-password', compact('movies'));
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:nguoi_dung,email'],
        ], [
            'email.exists' => 'Email này không tồn tại trong hệ thống.',
        ]);

        // Tạo token reset password
        $token = Str::random(64);
        $email = $request->email;

        // Lưu token vào database (có thể tạo bảng password_resets hoặc dùng cache)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now()
            ]
        );

        // TODO: Gửi email với link reset
        // Mail::to($email)->send(new ResetPasswordMail($token));

        return back()->with('status', 'Chúng tôi đã gửi link đặt lại mật khẩu đến email của bạn!');
    }

    public function showResetPasswordForm($token)
    {
        // Lấy danh sách phim đang chiếu để hiển thị trong carousel
        $movies = Phim::where('trang_thai', 'dang_chieu')
            ->orderByDesc('diem_danh_gia')
            ->orderByDesc('ngay_khoi_chieu')
            ->limit(8)
            ->get();
        
        return view('auth.reset-password', compact('token', 'movies'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:nguoi_dung,email',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'email.exists' => 'Email này không tồn tại trong hệ thống.',
        ]);

        // Kiểm tra token
        $reset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$reset || !Hash::check($request->token, $reset->token)) {
            return back()->withErrors(['token' => 'Token không hợp lệ hoặc đã hết hạn.']);
        }

        // Kiểm tra token còn hạn (24 giờ)
        if (Carbon::parse($reset->created_at)->addHours(24)->isPast()) {
            return back()->withErrors(['token' => 'Token đã hết hạn. Vui lòng yêu cầu lại.']);
        }

        // Cập nhật mật khẩu
        $user = NguoiDung::where('email', $request->email)->first();
        $user->mat_khau = Hash::make($request->password);
        $user->save();

        // Xóa token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login.form')->with('status', 'Mật khẩu đã được đặt lại thành công!');
    }
}


