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
                Log::info('User authenticated', ['user_id' => $user->id, 'email' => $user->email]);
                
                // Check if user exists and has a role
                if ($user && $user->vaiTro) {
                    $userRole = $user->vaiTro->ten;
                    Log::info('User role', ['role' => $userRole]);
                    
                    if ($userRole === 'admin') {
                        Log::info('Redirecting admin to dashboard');
                        return redirect()->route('admin.dashboard');
                    } elseif ($userRole === 'staff') {
                        Log::info('Redirecting staff to movies');
                        return redirect()->route('admin.movies.index');
                    }
                } else {
                    Log::warning('User has no role', ['user_id' => $user->id]);
                }
                
                // Default redirect for users with no role or invalid role
                Log::info('Redirecting to home');
                return redirect()->route('home');
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


