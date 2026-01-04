<?php

namespace App\Http\Controllers;

use App\Models\NguoiDung;
use App\Models\Phim;
use App\Mail\PasswordResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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
        // Get movies for carousel (now showing and upcoming, limit to 15 for variety)
        $movies = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
            ->orderByRaw("FIELD(trang_thai, 'dang_chieu','sap_chieu')")
            ->orderByDesc('diem_danh_gia')
            ->orderByDesc('ngay_khoi_chieu')
            ->limit(15)
            ->get();
        
        return view('auth.forgot-password', compact('movies'));
    }

    public function sendPasswordResetLink(Request $request)
    {
        Log::info('=== Password Reset Request START ===', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        try {
            // Validate request
            $request->validate(['email' => 'required|email|exists:nguoi_dung,email']);
            
            Log::info('Password reset validation passed', ['email' => $request->email]);

            $email = $request->email;
            
            // Generate token
            $token = Str::random(64);
            Log::info('Password reset token generated', [
                'email' => $email,
                'token_length' => strlen($token)
            ]);
            
            // Store token in database (update or insert)
            $hashedToken = Hash::make($token);
            $createdAt = Carbon::now();
            
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => $hashedToken,
                    'created_at' => $createdAt
                ]
            );
            
            Log::info('Password reset token saved to database', [
                'email' => $email,
                'created_at' => $createdAt->format('Y-m-d H:i:s')
            ]);
            
            // Send email
            try {
                Mail::to($email)->send(new PasswordResetMail($token, $email));
                Log::info('Password reset email sent successfully', [
                    'email' => $email,
                    'mailer' => config('mail.default')
                ]);
            } catch (\Exception $mailException) {
                $errorMessage = $mailException->getMessage();
                
                Log::error('Failed to send password reset email', [
                    'email' => $email,
                    'error' => $errorMessage,
                    'error_code' => $mailException->getCode(),
                    'mailer' => config('mail.default'),
                    'mail_host' => config('mail.mailers.smtp.host'),
                    'trace' => $mailException->getTraceAsString()
                ]);
                
                // Check if it's a Gmail authentication error
                if (str_contains($errorMessage, '535') || 
                    str_contains($errorMessage, 'BadCredentials') || 
                    str_contains($errorMessage, 'Username and Password not accepted')) {
                    Log::error('Gmail authentication error detected for password reset', [
                        'email' => $email,
                        'hint' => 'User needs to use App Password instead of regular password'
                    ]);
                }
                
                throw $mailException; // Re-throw to be caught by outer catch
            }
            
            Log::info('=== Password Reset Request SUCCESS ===', ['email' => $email]);
            
            return back()->with('status', 'Chúng tôi đã gửi link đặt lại mật khẩu đến email của bạn!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Password reset validation failed', [
                'email' => $request->email,
                'errors' => $e->errors(),
                'ip' => $request->ip()
            ]);
            throw $e; // Let Laravel handle validation errors
        } catch (\Exception $e) {
            Log::error('=== Password Reset Request FAILED ===', [
                'email' => $request->email ?? 'N/A',
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors([
                'email' => 'Đã xảy ra lỗi khi gửi email. Vui lòng thử lại sau.'
            ]);
        }
    }

    public function showResetPasswordForm($token)
    {
        // Get movies for carousel (now showing and upcoming, limit to 15 for variety)
        $movies = Phim::whereIn('trang_thai', ['dang_chieu', 'sap_chieu'])
            ->orderByRaw("FIELD(trang_thai, 'dang_chieu','sap_chieu')")
            ->orderByDesc('diem_danh_gia')
            ->orderByDesc('ngay_khoi_chieu')
            ->limit(15)
            ->get();
        
        return view('auth.reset-password', compact('token', 'movies'));
    }

    public function resetPassword(Request $request)
    {
        Log::info('=== Password Reset Submit START ===', [
            'email' => $request->email,
            'has_token' => !empty($request->token),
            'token_length' => strlen($request->token ?? ''),
            'ip' => $request->ip(),
        ]);

        try {
            // Validate request
            $request->validate([
                'token' => 'required',
                'email' => 'required|email|exists:nguoi_dung,email',
                'password' => 'required|string|min:6|confirmed',
            ]);
            
            Log::info('Password reset validation passed', ['email' => $request->email]);

            $email = $request->email;
            $token = $request->token;
            
            // Get token from database
            $tokenRecord = DB::table('password_reset_tokens')->where('email', $email)->first();
            
            if (!$tokenRecord) {
                Log::warning('Password reset token not found', [
                    'email' => $email,
                    'ip' => $request->ip()
                ]);
                return back()->withErrors([
                    'email' => 'Token không hợp lệ hoặc đã hết hạn.'
                ])->withInput($request->only('email'));
            }
            
            Log::info('Password reset token found in database', [
                'email' => $email,
                'token_created_at' => $tokenRecord->created_at
            ]);
            
            // Check if token is expired (60 minutes)
            $createdAt = Carbon::parse($tokenRecord->created_at);
            $expiresAt = $createdAt->copy()->addMinutes(60);
            $now = Carbon::now();
            
            Log::info('Checking token expiry', [
                'email' => $email,
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                'now' => $now->format('Y-m-d H:i:s'),
                'is_expired' => $expiresAt->isPast()
            ]);
            
            if ($expiresAt->isPast()) {
                DB::table('password_reset_tokens')->where('email', $email)->delete();
                Log::warning('Password reset token expired', [
                    'email' => $email,
                    'created_at' => $createdAt->format('Y-m-d H:i:s'),
                    'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
                    'ip' => $request->ip()
                ]);
                return back()->withErrors([
                    'email' => 'Token đã hết hạn. Vui lòng yêu cầu đặt lại mật khẩu mới.'
                ])->withInput($request->only('email'));
            }
            
            // Verify token
            $tokenValid = Hash::check($token, $tokenRecord->token);
            Log::info('Token verification result', [
                'email' => $email,
                'token_valid' => $tokenValid
            ]);
            
            if (!$tokenValid) {
                Log::warning('Password reset token invalid', [
                    'email' => $email,
                    'ip' => $request->ip()
                ]);
                return back()->withErrors([
                    'email' => 'Token không hợp lệ.'
                ])->withInput($request->only('email'));
            }
            
            // Update password
            $user = NguoiDung::where('email', $email)->first();
            if (!$user) {
                Log::error('User not found for password reset', [
                    'email' => $email,
                    'ip' => $request->ip()
                ]);
                return back()->withErrors([
                    'email' => 'Không tìm thấy người dùng.'
                ])->withInput($request->only('email'));
            }
            
            Log::info('Updating user password', [
                'email' => $email,
                'user_id' => $user->id
            ]);
            
            $user->mat_khau = Hash::make($request->password);
            $user->save();
            
            Log::info('User password updated successfully', [
                'email' => $email,
                'user_id' => $user->id
            ]);
            
            // Delete token
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            
            Log::info('Password reset token deleted', ['email' => $email]);
            Log::info('=== Password Reset Submit SUCCESS ===', ['email' => $email]);
            
            return redirect()->route('login.form')->with('status', 'Mật khẩu đã được đặt lại thành công! Vui lòng đăng nhập với mật khẩu mới.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Password reset validation failed', [
                'email' => $request->email,
                'errors' => $e->errors(),
                'ip' => $request->ip()
            ]);
            throw $e; // Let Laravel handle validation errors
        } catch (\Exception $e) {
            Log::error('=== Password Reset Submit FAILED ===', [
                'email' => $request->email ?? 'N/A',
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'ip' => $request->ip()
            ]);
            
            return back()->withErrors([
                'email' => 'Đã xảy ra lỗi khi đặt lại mật khẩu. Vui lòng thử lại sau.'
            ])->withInput($request->only('email'));
        }
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


