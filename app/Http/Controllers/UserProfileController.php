<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $user->load('diemThanhVien', 'datVe.suatChieu.phim', 'datVe.suatChieu.phongChieu');
        } catch (\Exception $e) {
            // If relationships don't exist, continue without them
        }
        
        // Get recent bookings
        try {
            $recentBookings = $user->datVe()
                ->with(['suatChieu.phim', 'suatChieu.phongChieu'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
        } catch (\Exception $e) {
            $recentBookings = collect();
        }
            
        // Get booking stats
        try {
            $totalBookings = $user->datVe()->count();
            $confirmedBookings = $user->datVe()->where('trang_thai', 1)->count();

            // Admin-like aggregate: sum(seat_total + combo_total) for confirmed bookings
            $seatSub = DB::table('chi_tiet_dat_ve')
                ->select('id_dat_ve', DB::raw('SUM(gia) as seat_total'))
                ->groupBy('id_dat_ve');
            $comboSub = DB::table('chi_tiet_dat_ve_combo')
                ->select('id_dat_ve', DB::raw('SUM(gia_ap_dung * COALESCE(so_luong,1)) as combo_total'))
                ->groupBy('id_dat_ve');
            $totalSpent = (float) DB::table('dat_ve as v')
                ->leftJoinSub($seatSub, 's', function($j){ $j->on('s.id_dat_ve','=','v.id'); })
                ->leftJoinSub($comboSub, 'c', function($j){ $j->on('c.id_dat_ve','=','v.id'); })
                ->where('v.id_nguoi_dung', $user->id)
                ->where('v.trang_thai', 1)
                ->sum(DB::raw('COALESCE(s.seat_total,0) + COALESCE(c.combo_total,0)'));
        } catch (\Exception $e) {
            $totalBookings = 0;
            $confirmedBookings = 0;
            $totalSpent = 0;
        }
        
        // Format user data safely
        // Gender conversion map
        $genderDisplay = [
            1 => 'Nam',
            2 => 'Nữ',
            3 => 'Khác',
        ];
        
        $userData = [
            'id' => $user->id,
            'ho_ten' => $user->ho_ten,
            'email' => $user->email,
            'so_dien_thoai' => $user->sdt,
            'ngay_sinh' => $user->ngay_sinh,
            'gioi_tinh' => $genderDisplay[$user->gioi_tinh] ?? 'Chưa cập nhật',
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
        
        // Member tier (hang_thanh_vien)
        $memberTier = null;
        try {
            $row = DB::table('hang_thanh_vien')
                ->leftJoin('tier', 'hang_thanh_vien.id_tier', '=', 'tier.id')
                ->where('hang_thanh_vien.id_nguoi_dung', $user->id)
                ->select(
                    DB::raw('COALESCE(tier.ten_hang, hang_thanh_vien.ten_hang) as ten_hang'),
                    DB::raw('COALESCE(tier.uu_dai, hang_thanh_vien.uu_dai) as uu_dai'),
                    DB::raw('COALESCE(tier.diem_toi_thieu, hang_thanh_vien.diem_toi_thieu) as diem_toi_thieu'),
                    'hang_thanh_vien.ngay_cap_nhat_hang'
                )
                ->first();
            if ($row) {
                $memberTier = [
                    'ten_hang' => $row->ten_hang ?? null,
                    'uu_dai' => $row->uu_dai ?? null,
                    'diem_toi_thieu' => $row->diem_toi_thieu ?? null,
                    'ngay_cap_nhat_hang' => $row->ngay_cap_nhat_hang ?? null,
                ];
            }
        } catch (\Exception $e) {
            $memberTier = null;
        }

        // Fallback computed tier if no memberTier
        $computedTier = null;
        if (!$memberTier) {
            if ($totalSpent >= 1500000) $computedTier = 'Kim cương';
            elseif ($totalSpent >= 1000000) $computedTier = 'Vàng';
            elseif ($totalSpent >= 500000) $computedTier = 'Bạc';
            elseif ($totalSpent >= 150000) $computedTier = 'Đồng';
        }

        // Fallback points if no diem_thanh_vien
        if (!$loyaltyPoints) {
            $loyaltyPoints = [
                'tong_diem' => (int) floor($totalSpent / 1000),
                'ngay_het_han' => null,
            ];
        }
        
        return view('user.profile', compact('userData', 'loyaltyPoints', 'memberTier', 'computedTier', 'recentBookings', 'totalBookings', 'confirmedBookings', 'totalSpent'));
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
        // Map form fields to DB columns and normalize values
        $genderMap = [
            'Nam' => 1,
            'Nữ' => 2,
            'Khác' => 3,
        ];

        $userData = [
            'ho_ten' => $request->ho_ten,
            'email' => $request->email,
            // DB column is 'sdt'
            'sdt' => $request->so_dien_thoai,
            'ngay_sinh' => $request->ngay_sinh ?: null,
            // store gender as tinyInteger per migration
            'gioi_tinh' => $request->gioi_tinh ? ($genderMap[$request->gioi_tinh] ?? null) : null,
        ];

        $user->update($userData);
        
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
     * Booking history page (alias) -> reuse BookingController@index
     */
    public function bookingHistory()
    {
        return redirect()->route('user.bookings');
    }
    
    /**
     * Cancel a booking.
     */
    public function cancelBooking($id)
    {
        $user = Auth::user();
        
        try {
            $booking = $user->datVe()->findOrFail($id);
            
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
