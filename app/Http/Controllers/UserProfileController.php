<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserProfileController extends Controller
{
    /**
     * Display the user profile page.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Load relationships safely
        try {
            $user->load('diemThanhVien', 'datVeu.suatChieu.phim', 'datVeu.suatChieu.phongChieu');
        } catch (\Exception $e) {
            // If relationships don't exist, continue without them
        }
        
        // Get recent bookings
        try {
            $recentBookings = $user->datVeu()
                ->with(['suatChieu.phim', 'suatChieu.phongChieu'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            $recentBookings = collect();
        }
            
        // Get booking stats
        try {
            $totalBookings = $user->datVeu()->count();
            $confirmedBookings = $user->datVeu()->where('trang_thai', 1)->count();
            $totalSpent = $user->datVeu()->where('trang_thai', 1)->sum('tong_tien');
        } catch (\Exception $e) {
            $totalBookings = 0;
            $confirmedBookings = 0;
            $totalSpent = 0;
        }
        
        // Format user data safely
        $userData = [
            'id' => $user->id,
            'ho_ten' => $user->ho_ten,
            'email' => $user->email,
            'so_dien_thoai' => $user->so_dien_thoai,
            'ngay_sinh' => $user->ngay_sinh,
            'gioi_tinh' => $user->gioi_tinh,
            'created_at' => $user->created_at,
        ];
        
        // Format loyalty points safely
        $loyaltyPoints = null;
        if (isset($user->diemThanhVien)) {
            try {
                $loyaltyPoints = [
                    'tong_diem' => $user->diemThanhVien->tong_diem,
                    'ngay_het_han' => $user->diemThanhVien->ngay_het_han,
                ];
            } catch (\Exception $e) {
                $loyaltyPoints = null;
            }
        }
        
        return view('user.profile', compact('userData', 'loyaltyPoints', 'recentBookings', 'totalBookings', 'confirmedBookings', 'totalSpent'));
    }
    
    /**
     * Show the form for editing the user profile.
     */
    public function edit()
    {
        $user = Auth::user();
        return view('user.edit-profile', compact('user'));
    }
    
    /**
     * Update the user profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'ho_ten' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:nguoi_dung,email,' . $user->id,
            'so_dien_thoai' => 'nullable|string|max:20',
            'ngay_sinh' => 'nullable|date|before:today',
            'gioi_tinh' => 'nullable|in:Nam,Nữ,Khác',
        ]);
        
        $user->update([
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            'so_dien_thoai' => $request->so_dien_thoai,
            'ngay_sinh' => $request->ngay_sinh,
            'gioi_tinh' => $request->gioi_tinh,
        ]);
        
        return redirect()->route('user.profile')
            ->with('success', 'Cập nhật thông tin thành công!');
    }
    
    /**
     * Show the form for changing password.
     */
    public function showChangePasswordForm()
    {
        return view('user.change-password');
    }
    
    /**
     * Change the user password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        
        $user = Auth::user();
        
        // Check current password - handle both password field names
        $currentPasswordField = 'mat_khau'; // Default Laravel field name
        if (!Hash::check($request->current_password, $user->mat_khau)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng']);
        }
        
        // Update password
        $user->update([
            'mat_khau' => Hash::make($request->password),
        ]);
        
        return redirect()->route('user.profile')
            ->with('success', 'Đổi mật khẩu thành công!');
    }
    
    /**
     * Display user booking history.
     */
    public function bookingHistory(Request $request)
    {
        $user = Auth::user();
        
        try {
            $bookings = $user->datVeu()
                ->with(['suatChieu.phim', 'suatChieu.phongChieu', 'chiTietDatVe.ghe', 'chiTietCombo.combo'])
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } catch (\Exception $e) {
            $bookings = collect();
        }
            
        return view('user.booking-history', compact('bookings'));
    }
    
    /**
     * Cancel a booking.
     */
    public function cancelBooking($id)
    {
        $user = Auth::user();
        
        try {
            $booking = $user->datVeu()->findOrFail($id);
            
            // Only allow cancellation if booking is pending or confirmed
            if (!in_array($booking->trang_thai, [0, 1])) {
                return back()->withErrors(['error' => 'Không thể hủy vé này']);
            }
            
            // Check if showtime is in the future (at least 2 hours before)
            if (isset($booking->suatChieu->thoi_gian_bat_dau) && $booking->suatChieu->thoi_gian_bat_dau < now()->addHours(2)) {
                return back()->withErrors(['error' => 'Chỉ có thể hủy vé trước suất chiếu ít nhất 2 giờ']);
            }
            
            $booking->update(['trang_thai' => 3]); // Request cancellation
            
            return back()->with('success', 'Yêu cầu hủy vé đã được gửi. Vui lòng chờ admin xác nhận.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Không thể hủy vé này. Vui lòng thử lại.']);
        }
    }
}
