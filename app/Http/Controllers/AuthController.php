<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
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
        // Debug: Log the incoming request data
        \Log::info('Login attempt', $request->only('email'));

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            // First find the user by email
            $user = NguoiDung::where('email', $credentials['email'])->first();
            \Log::info('User found:', $user ? ['id' => $user->id, 'email' => $user->email] : ['user' => 'not found']);

            if ($user) {
                // Debug: Check password hash
                $passwordMatches = Hash::check($credentials['password'], $user->mat_khau);
                \Log::info('Password check:', ['matches' => $passwordMatches]);
                
                if ($passwordMatches) {
                    // Manually log in the user
                    Auth::login($user, $request->filled('remember'));
                    
                    $request->session()->regenerate();
                    
                    $userRole = optional($user->vaiTro)->ten;
                    \Log::info('User role:', ['role' => $userRole]);
                    
                    if ($userRole === 'admin') {
                        return redirect()->intended(route('admin.dashboard'))->with('success', 'Đăng nhập thành công với quyền quản trị!');
                    } elseif ($userRole === 'staff') {
                        return redirect()->intended(route('staff.dashboard'))->with('success', 'Đăng nhập thành công với quyền nhân viên!');
                    } else {
                        return redirect()->intended(route('home'))->with('success', 'Đăng nhập thành công!');
                    }
                }
            }

            return back()->withErrors([
                'email' => 'Email hoặc mật khẩu không chính xác.',
            ])->withInput($request->only('email', 'remember'));
            
        } catch (\Exception $e) {
            \Log::error('Login error:', [
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


