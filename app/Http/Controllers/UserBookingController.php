<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DatVe;

class UserBookingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Clean up expired pending bookings for this user
        $expiredBookings = DatVe::where('id_nguoi_dung', $user->id)
            ->where('trang_thai', 0) // Pending
            ->where(function($query) {
                $query->whereNotNull('expires_at')
                      ->where('expires_at', '<=', now());
            })
            ->get();
        
        if ($expiredBookings->count() > 0) {
            foreach ($expiredBookings as $expiredBooking) {
                // Delete seat details
                \App\Models\ChiTietDatVe::where('id_dat_ve', $expiredBooking->id)->delete();
                // Delete combo details
                \App\Models\ChiTietCombo::where('id_dat_ve', $expiredBooking->id)->delete();
                // Delete payment record
                \App\Models\ThanhToan::where('id_dat_ve', $expiredBooking->id)->delete();
                // Delete booking
                $expiredBooking->delete();
            }
        }
        
        // Only show paid bookings or cancelled bookings (for history)
        // Do NOT show pending bookings - they are only for payment processing
        $datVes = DatVe::with(['suatChieu.phim', 'chiTietDatVe.ghe', 'chiTietCombo.combo'])
            ->where('id_nguoi_dung', $user->id)
            ->whereIn('trang_thai', [1, 2]) // Only paid (1) or cancelled (2)
            ->orderByDesc('id')
            ->paginate(7);
        return view('user.booking_history', compact('datVes'));
    }
}
